<?php

namespace AsyncCenter\Service;

use Exception;
use AsyncCenter\Config;
use AsyncCenter\Library\RedisLib;
use Throwable;

/**
 * Author: OYYM
 * Class TccService
 */
class TccService
{
    public $idGen;
    public $dtm;
    public $gid;
    const TCC_PREFIX = 'TCC_PREFIX_';

    public function __construct($dtmUrl = '', $gid = '')
    {
        $httpHost = Config::info('BASE_HTTP_HOST');
        $this->dtm = $dtmUrl ?: $httpHost . '/tcc?action=';
        $this->gid = $gid;
        $this->idGen = new IdGenerator();
    }

    public function tcc($infos, $req)
    {
        try {
            $tcc = new TccService($this->dtm, $this->idGen->genGid());
        } catch (Exception $e) {
            return [-1, 'gid 获取失败！'];
        }

        $tbody = $errorInfo = [];
        foreach ($infos as $k => $info) {
            @$req['trans_name'] = $info['title'];
            $tbody = [
                'gid' => $tcc->gid,
                'trans_type' => 'tcc',
            ];

            try {
                //注册事务 prepare
                list($getStatusCode) = $this->idGen->curlPost($tcc->dtm . 'prepare', ['json' => $tbody]);
                $this->checkStatus($getStatusCode);
                //尝试执行 try
                $getContents = $tcc->callBranch($req, $info['urls']['try'], $info['urls']['confirm'], $info['urls']['cancel'], $info['title']);
            } catch (Throwable $e) {
                Utils::writeLog($info['title'] . '  Error: ' . $e->getMessage(), 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
                $da = json_decode($e->getMessage(), true);
                if (!empty($da)) {
                    $msg = $da;
                }
                $errorInfo[] = [
                    'name' => $info['title'],
                    'msg' => $msg
                ];
            }

            //当存在该参数时 将下次的请求body替换掉
            if (isset($infos[$k + 1]) && isset($getContents[$infos[$k + 1]['urls']['try']])) {
                $req = $getContents[$infos[$k + 1]['urls']['try']];
            }
            echo $tcc->gid . PHP_EOL;
        }

        $name = implode(',', array_column($infos, 'title'));

        if (empty($errorInfo)) {
            //成功确认 submit
            $this->idGen->curlPost($tcc->dtm . 'submit', ['json' => $tbody]);
            Utils::writeLog(PHP_EOL . PHP_EOL . '=========================================================', 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
            return [1, $name . '   事务ID: ' . $tcc->gid . ' 执行成功！'];
        } else {
            //返回错误信息 并且提交取消操作 让其他业务方收到错误信息
            $this->idGen->curlPost($tcc->dtm . 'abort', [
                'json' => $tbody,
                'error' => $errorInfo
            ]);

            $errorStr = [
                'info' => '事务ID：' . $tcc->gid . ' 执行失败！【' . $name . '】',
                'error' => $errorInfo
            ];
            return [-1, $errorStr];
        }
    }

    /**
     * @param array $body
     * @param string $tryUrl
     * @param string $confirmUrl
     * @param string $cancelUrl
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function callBranch($body, $tryUrl, $confirmUrl, $cancelUrl, $name)
    {
        $branchId = $this->idGen->newBranchId();
        list($getStatusCode) = $this->idGen->curlPost($this->dtm . 'registerTccBranch', [
            'json' => [
                'title' => $name,
                'gid' => $this->gid,
                'branch_id' => $branchId,
                'trans_type' => 'tcc',
                'status' => 'prepared',
                'data' => json_encode($body, JSON_UNESCAPED_UNICODE),
                'try_url' => $tryUrl,
                'confirm_url' => $confirmUrl,
                'cancel_url' => $cancelUrl,
            ],
        ]);

        $this->checkStatus($getStatusCode);
        Utils::writeLog($name . ' ****注册成功****！', 'Tcc/' . date('Y_m_d') . '_TCC_API.log');

        $query = [
            'gid' => $this->gid,
            'trans_type' => 'tcc',
            'branch_id' => $branchId,
            'branch_type' => 'try',
            'try_url' => $tryUrl,
            'data' => $body
        ];

        list($getStatusCode, $getContents, $errInfo) = $this->idGen->curlPost($tryUrl, [
            'json' => $body,
            'query' => $query
        ]);

        Utils::writeLog([$name . ' 发送TRY', 'request' => ['json' => $body, 'query' => $query], 'response' => json_decode($getContents, true)], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        $this->idGen->checkStatus($getStatusCode, $errInfo);
        return $this->idGen->checkFailure($getContents, $getContents);
    }


    public function registerTccBranch()
    {
        $_POST['action'] = 'registerTccBranch';
        $_POST['json']['created_at'] = date('Y-m-d H:i:s');
        if (isset($_POST['json']) && isset($_POST['json']['gid'])) {
            //查询该调用方法 对应的任务
            $redis = RedisLib::getInstance(Config::info('TCC_INFO')['redis'], false);
            $res = $redis->get(self::TCC_PREFIX . $_POST['json']['gid']);
            if (!empty($res)) {
                $data = array_merge(json_decode($res, true), [$_POST['json']]);
            } else {
                $data = [$_POST['json']];
            }
            $redis->set(self::TCC_PREFIX . $_POST['json']['gid'], json_encode($data), Config::info('TCC_INFO')['info_max_day']);
        }
        Utils::writeLog($_POST, 'TCC.log');
    }

    /**
     *  异常回滚
     *  http://192.168.31.78:998/systems/tcc/abort
     * @param array $post
     */
    public function abort($post = [])
    {
        $data = $post ?: $_POST;
        $gid = $data['json']['gid'] ?? 0;
        //查询出当前的数据进行提交
        $updateInfo = [];
        $redis = RedisLib::getInstance(Config::info('TCC_INFO')['redis'], false);
        $res = $redis->get(self::TCC_PREFIX . $gid);

        $res = json_decode($res, true);

        foreach ($res as $value) {
            $id = $value['branch_id'];
            $url = $value['cancel_url'];
            if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
                list($getStatusCode) = $this->idGen->curlPost($url, $data);
                if ($getStatusCode == 200) {
                    Utils::writeLog([$value['title'] . '-- 发送取消成功', $data['error']], 'TCC.log');
                } else {
                    Utils::writeLog([$value['title'] . '-- 发送取消失败！HttpCode:' . $getStatusCode, $data['error']], 'TCC.log');
                }
            }

            if (!empty($data['error'])) {
                $updateInfo[] = [
                    'branch_id' => $id,
                    'cancel_status' => $getStatusCode ?? 0,
                    'try_error' => json_encode($data['error'], JSON_UNESCAPED_UNICODE)
                ];
            }
        }

        if (!empty($updateInfo)) {
            $data = Utils::redisJsonUpdate($updateInfo, $res);
            $redis->set(self::TCC_PREFIX . $gid, json_encode($data), Config::info('TCC_INFO')['info_max_day']);
        }
    }

    public function submit()
    {
        $_POST['action'] = 'submit';
        $gid = $_POST['json']['gid'] ?? 0;

        //查询出当前的数据进行提交
        $redis = RedisLib::getInstance(Config::info('TCC_INFO')['redis'], false);
        $res = $redis->get(self::TCC_PREFIX . $gid);

        if (!empty($res)) {
            $res = json_decode($res, true);
            $updateInfo = $confirmId = [];
            foreach ($res as $value) {
                $confirmStatus = $this->idGen->curlPost($value['confirm_url'], ['json' => json_decode($value['data'], true)]);
                $da = json_decode($confirmStatus[1], true);
                if (!empty($da)) {
                    $confirmStatus[1] = $da;
                }
                Utils::writeLog([$value['title'] . '发送SUBMIT', 'request' => json_decode($value['data'], true),
                    'response' => $confirmStatus], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
                $updateInfo[] = [
                    'branch_id' => $value['branch_id'],
                    'confirm_status' => $confirmStatus
                ];
            }
            $data = Utils::redisJsonUpdate($updateInfo, $res);
            $redis->set(self::TCC_PREFIX . $gid, json_encode($data, JSON_UNESCAPED_UNICODE), Config::info('TCC_INFO')['info_max_day']);
        }
    }

    /**
     * @param int $status
     * @throws Exception
     */
    public function checkStatus($status)
    {
        if ($status !== 200) {
            throw new Exception("bad http response status: {$status}");
        }
    }
}