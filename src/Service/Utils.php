<?php


namespace Pupilcp\Service;

use Pupilcp\Config;
use Pupilcp\Library\RedisLib;

class Utils
{
    public static function getMillisecond()
    {
        return microtime(true);
    }

    /**
     * Get Server Memory Usage.
     *
     * @return string
     */
    public static function getServerMemoryUsage()
    {
        return round(memory_get_usage(true) / (1024 * 1024), 2) . ' MB';
    }

    /**
     * Get Server load avg.
     *
     * @return string
     */
    public static function getSysLoadAvg()
    {
        $loadavg = function_exists('sys_getloadavg') ? array_map('round', sys_getloadavg(), [2]) : ['-', '-', '-'];

        return 'Load Average: ' . implode(', ', $loadavg);
    }

    /**
     * http post request.
     *
     * @param mixed $url_mixed
     * @param mixed $dataString
     * @param mixed $timeoutTime
     * @param mixed $https
     *
     * @return array
     */
    public static function httpPost($url_mixed, $dataString, $timeoutTime = 5, $https = false)
    {
        $headerArr = [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($dataString),
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url_mixed);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $https);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
        if (null !== $timeoutTime) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutTime);
        }
        ob_start();
        curl_exec($ch);
        $response = ob_get_contents();
        ob_end_clean();

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errInfo = curl_error($ch);
        curl_close($ch); //释放cURL句柄
        return [
            'httpCode' => $httpCode,
            'response' => $response,
            'errInfo' => $errInfo,
        ];
    }

    public static function writeLog($msg, $fileName = 'dev.log', $sourceRequest = '')
    {
        if (is_array($msg)) {
            $msg = array_merge($msg, ['time' => date('Y-m-d H:i:s')]);
            $msg = stripslashes(json_encode($msg, JSON_UNESCAPED_UNICODE));
        } else {
            $msg = ['data' => $msg, 'time' => date('Y-m-d H:i:s')];
            $msg = stripslashes(json_encode($msg, JSON_UNESCAPED_UNICODE));
        }
        file_put_contents(Config::LOG_PATH . $fileName, $msg . PHP_EOL, FILE_APPEND);
        if (!empty($sourceRequest)) {
            self::retryAdd($sourceRequest);
        }
    }

    /**
     * 添加重试信息
     * @param $sourceRequest
     */
    public static function retryAdd($sourceRequest)
    {
        //查询该调用方法 对应的任务
        $config = require_once "config/smc_" . $sourceRequest['master'] . '.php';
        $redis = RedisLib::getInstance(Config::RETRY_INFO['redis'], false);
        $random = md5(uniqid() . time());
        $isGet = 0;
        do {
            //先获取锁
            $isGet = $redis->lock('lock_key', $random);
            if ($isGet) {
                $timesPrefixKey = Utils::retryTimesKey($sourceRequest['data']);

                //查询这个值是第几次重试
                $timesRes = $redis->get($timesPrefixKey);
                $times = 1;
                if (!empty($timesRes)) {
                    $times = $timesRes + 1;
                }

                $timeEnd = $config['retry'][$times] ?? 0;
                if (empty($timeEnd)) { //当没有值时，表示不再重试
                    return false;
                }

                //获取截止时间所在的小时--
                $endTime = time() + $timeEnd;
                $prefixKey = 'SMC_RETRY_' . $sourceRequest['master'] . '_' . date('Y-m-d_H', $endTime);
                $sourceData = json_decode($sourceRequest['data'], true);
                if (empty($sourceData) && !empty($sourceRequest['data'])) {  //表示可能是字符串
                    $sourceData = $sourceRequest['data'];
                }

                $save = [
                    'end_time' => $endTime,
                    'status' => 1,
                    'data' => $sourceData
                ];

                //查询这一个小时已经存在的值
                $res = $redis->get($prefixKey);
                if (!empty($res)) {
                    if ($res == 'null') {
                        $res = [];
                    } else {
                        $res = json_decode($res, true);
                        if (!is_array($res)) {
                            $res = [];
                        }
                    }
                    $saveData = array_merge($res, [$save]);
                } else {
                    $saveData = [$save];
                }
                $redis->set($timesPrefixKey, $times, Config::RETRY_INFO['info_max_day']);
                $redis->set($prefixKey, json_encode($saveData), Config::RETRY_INFO['info_max_day']);
                //释放锁
                $redis->unlock('lock_key', $random);
            } else {
                sleep(2);
            }
        } while (!$isGet);
    }

    /**
     * 获取某个任务的执行次数
     */
    public static function retryTimesKey($data)
    {
        return 'SMC_RETRY_KEY_' . md5($data);
    }

    /**
     * 解析队列数据
     */
    public static function getQueueData($msg)
    {
        return base64_decode(str_replace(['.', ':', '_'], ['=', '/', '+'], $msg));
    }

    /**
     * 转换队列数据
     */
    public static function setQueueData($msg)
    {
        $baseStr = base64_encode($msg);
        return str_replace(['=', '/', '+'], ['.', ':', '_'], $baseStr);
    }


    public static function showLog($fileName, $title)
    {
        $name = '系统错误日志';
        $num = $_GET['num'] ?? 1500;
        $refresh = $_GET['r'] ?? 0;

        $title = '<b style="color: #ffa500"> ' . $title . '</b>';
        if (!file_exists($fileName)) {
            exit('没有找到  ' . $fileName . ' 这个文件，请检查路径是否正确');
        }

        $res = file_get_contents($fileName);
        $res = self::cleanHtml(mb_substr($res, -$num));
        echo '<div style="color: #009900;background-color: black;">';
        echo "<h2 style='color: wheat'>【" . $name . "】{$title}（只展示最后{$num}个字符）更多请&nbsp;↑  &nbsp;&nbsp;url后加&num=100000 | 自动2秒刷新加： &r=2</h2>";
        if ($refresh) {
            echo '<meta http-equiv="refresh" content="' . $refresh . '">';
        }

        echo '<pre>';
        print_r($res);
        echo '<br>';
        echo '</div>';
        exit;
    }

    public static function cleanHtml($string)
    {
        $string = strip_tags($string);
        $string = stripslashes(str_replace("\\r\\n", " ", $string));
        return trim($string);
    }

    /**
     * 判断字符串是否为 Json 格式
     * @param string $data Json 字符串
     * @param bool $assoc 是否返回关联数组。默认返回对象
     * @return array|bool|object 成功返回转换后的对象或数组，失败返回 false
     */
    public static function isJson($data = '', $assoc = true)
    {
        $data = json_decode($data, $assoc);
        if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
            return $data;
        }
        return false;
    }

    public static function redisJsonUpdate($save, $data, $primaryKey = 'branch_id')
    {
        $saveInfo = [];
        foreach ($save as $re) {
            $saveInfo[$re[$primaryKey]] = $re;
        }

        $saveData = [];
        foreach ($data as $value) {
            if (isset($saveInfo[$value[$primaryKey]])) {   //表示有相同值 ，则将值替换
                $saveData[] = array_merge($value, $saveInfo[$value[$primaryKey]]);
            }
        }
        return $saveData;
    }
}
