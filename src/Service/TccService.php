<?php

namespace Pupilcp\Service;

use Exception;
use Pupilcp\Config;
use Pupilcp\Library\RedisLib;
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
        require_once "./autoload.php";
        $httpHost = Config::BASE_HTTP_HOST;
        $this->dtm = $dtmUrl ?: $httpHost . '/tcc.php?action=';
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
                $errorInfo[] = [
                    'name' => $info['title'],
                    'msg' => json_decode($e->getMessage(), true)
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
            $errorStr = ['info' => '事务ID：' . $tcc->gid . ' 执行失败！【' . $name . '】', 'error' => $errorInfo];
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
            'data' => json_encode($body, JSON_UNESCAPED_UNICODE)
        ];

        list($getStatusCode, $getContents, $errInfo) = $this->idGen->curlPost($tryUrl, [
            'json' => $body,
            'query' => $query
        ]);

        Utils::writeLog([$name . ' 发送TRY', 'request' => ['json' => $body, 'query' => $query], 'response' => $getContents], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        $this->idGen->checkStatus($getStatusCode, $errInfo);
        return $this->idGen->checkFailure($getContents, $getContents);
    }


    public function registerTccBranch()
    {
        $_POST['action'] = 'registerTccBranch';
        $_POST['json']['created_at'] = date('Y-m-d H:i:s');
        if (isset($_POST['json']) && isset($_POST['json']['gid'])) {
            //查询该调用方法 对应的任务
            $redis = RedisLib::getInstance(Config::TCC_INFO['redis'], false);
            $res = $redis->get(self::TCC_PREFIX . $_POST['json']['gid']);
            if (!empty($res)) {
                $data = array_merge(json_decode($res, true), [$_POST['json']]);
            } else {
                $data = [$_POST['json']];
            }
            $redis->set(self::TCC_PREFIX . $_POST['json']['gid'], json_encode($data), Config::TCC_INFO['info_max_day']);
        }
        Utils::writeLog($_POST, 'TCC.log');
    }

    /**
     *  异常回滚
     *  http://192.168.33.210:88/systems/tcc/abort
     * @param array $post
     */
    public function abort($post = [])
    {
        $data = $post ?: $_POST;
        $gid = $data['json']['gid'] ?? 0;
        //查询出当前的数据进行提交
        $updateInfo = $cancelId = [];
        $redis = RedisLib::getInstance(Config::TCC_INFO['redis'], false);
        $res = $redis->get(self::TCC_PREFIX . $gid);
        $res = json_decode($res, true);
        foreach ($res as $value) {
            $cancelId[$value['branch_id']] = $value['cancel_url'];
        }

        foreach ($cancelId as $id => $url) {
            if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
                list($getStatusCode) = $this->idGen->curlPost($url, $data);
                if ($getStatusCode == 200) {
                    Utils::writeLog($id . ' ---- 发送取消成功' . json_encode($data['error']), 'TCC.log');
                } else {
                    Utils::writeLog($id . ' ---- 发送取消失败！' . json_encode($data['error']), 'TCC.log');
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
            $redis->set(self::TCC_PREFIX . $gid, json_encode($data), Config::TCC_INFO['info_max_day']);
        }
    }

    public function submit()
    {
        $_POST['action'] = 'submit';
        $gid = $_POST['json']['gid'] ?? 0;

        //查询出当前的数据进行提交
        $redis = RedisLib::getInstance(Config::TCC_INFO['redis'], false);
        $res = $redis->get(self::TCC_PREFIX . $gid);

        if (!empty($res)) {
            $res = json_decode($res, true);
            $titleInfo = $dataInfo = $updateInfo = $confirmId = [];
            foreach ($res as $value) {
                $confirmId[$value['branch_id']] = $value['confirm_url'];
                $dataInfo[$value['branch_id']] = $value['data'];
                $titleInfo[$value['branch_id']] = $value['title'];
            }

            foreach ($confirmId as $id => $url) {
                $confirmStatus = $this->idGen->curlPost($url, ['json' => json_decode($dataInfo[$id], true)]);
                Utils::writeLog([$titleInfo[$id] . '发送SUBMIT', 'request' => json_decode($dataInfo[$id], true),
                    'response' => $confirmStatus], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
                $updateInfo[] = [
                    'branch_id' => $id,
                    'confirm_status' => $confirmStatus
                ];
            }
            $data = Utils::redisJsonUpdate($updateInfo, $res);
            $redis->set(self::TCC_PREFIX . $gid, json_encode($data), Config::TCC_INFO['info_max_day']);
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