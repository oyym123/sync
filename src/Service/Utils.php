<?php


namespace AsyncCenter\Service;

use AsyncCenter\Config;
use AsyncCenter\Library\RedisLib;

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

    /**
     * 普通日志记录
     * @param $msg
     * @param string $fileName
     * @param string $sourceRequest
     */
    public static function writeLog($msg, $fileName = 'dev.log', $request = '')
    {
        $msg = ['data' => $msg, 'time' => date('Y-m-d H:i:s')];
        $msg = stripslashes(json_encode($msg, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES));
        self::mkdirs(Config::info('LOG_PATH'));
        $path = Config::info('LOG_PATH') . $fileName;
        if (!file_exists($path)) {
            file_put_contents($path, PHP_EOL, FILE_APPEND);
        }
        file_put_contents($path, $msg . PHP_EOL, FILE_APPEND);
        if (!empty($request)) {
            $sourceRequest['data'] = $request;
            $sourceRequest['master'] = $master;
            self::retryAdd($sourceRequest);
        }
    }

    /**
     * 回调失败日志记录
     * @param $msg
     * @param $master
     * @param string $request 回调的原参数 有则表示需要重试
     */
    public static function asyncErrorLog($msg, $master, $request = '')
    {
        if (!empty($request)) {
            $msg = ['data' => array_merge(['retry' => '已进入重试'], ['msg' => $msg]), 'time' => date('Y-m-d H:i:s')];
        } else {
            $msg = ['data' => $msg, 'time' => date('Y-m-d H:i:s')];
        }
        $msg = stripslashes(json_encode($msg, JSON_UNESCAPED_UNICODE));
        $dir = Config::info('LOG_PATH') . 'error/';
        self::mkdirs($dir);
        $path = $dir . $master . '.log';
        if (!file_exists($path)) {
            file_put_contents($path, PHP_EOL, FILE_APPEND);
        }
        file_put_contents($path, $msg . PHP_EOL, FILE_APPEND);
        if (!empty($request)) {
            $sourceRequest['data'] = $request;
            $sourceRequest['master'] = $master;
            self::retryAdd($sourceRequest);
        }
    }

    /**
     * 回调成功日志记录
     * @param $msg
     * @param $master
     * @param string $request 回调的原参数 有则表示需要重试
     */
    public static function asyncSuccessLog($msg, $master, $request = '')
    {
        if (!empty($request)) {
            $msg = ['data' => array_merge(['retry' => '已进入重试'], ['msg' => $msg]), 'time' => date('Y-m-d H:i:s')];
        } else {
            $msg = ['data' => $msg, 'time' => date('Y-m-d H:i:s')];
        }
        $msg = stripslashes(json_encode($msg, JSON_UNESCAPED_UNICODE));
        $dir = Config::info('LOG_PATH') . 'success/';
        self::mkdirs($dir);
        $path = $dir . $master . '.log';
        if (!file_exists($path)) {
            file_put_contents($path, PHP_EOL, FILE_APPEND);
        }
        file_put_contents($path, $msg . PHP_EOL, FILE_APPEND);
        if (!empty($request)) {
            $sourceRequest['data'] = $request;
            $sourceRequest['master'] = $master;
            self::retryAdd($sourceRequest);
        }
    }

    public static function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
        if (!mkdirs(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode);
    }

    /**
     * 添加重试信息
     * @param $sourceRequest
     */
    public static function retryAdd($sourceRequest)
    {
        //查询该调用方法 对应的任务
        $config = require_once Config::info('CONFIG_PATH') . "/smc_" . $sourceRequest['master'] . '.php';
        $redis = RedisLib::getInstance(Config::info('RETRY_INFO')['redis'], false);
        $random = md5(uniqid() . time());
        $isGet = 0;

        do {
            //先获取锁
            $isGet = $redis->lock('lock_key', $random);
            if ($isGet) {
                $timesPrefixKey = Utils::retryTimesKey($sourceRequest['data']);

                //查询这个值是第几次重试
                $timesRes = (int)$redis->get($timesPrefixKey);
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
                $redis->set($timesPrefixKey, $times, Config::info('RETRY_INFO')['info_max_day']);
                $redis->set($prefixKey, json_encode($saveData, JSON_PRETTY_PRINT), Config::info('RETRY_INFO')['info_max_day']);
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
    public static function getQueueData($msg, $masterName = '')
    {
        if (!empty($masterName)) {
            $redis = RedisLib::getInstance(Config::info('REDIS_CONFIG'), false);
            return $redis->rPop('ARGUMENTS_' . $masterName);
        }
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


    public static function showLog($fileName, $title, $format = 1)
    {
        $name = '系统日志';
        $num = $_GET['num'] ?? 1500;
        $refresh = $_GET['r'] ?? 0;
        $isArr = $_GET['arr'] ?? 0;
        $title = '<b style="color: #ffa500"> ' . $title . '</b>';
        if (!file_exists($fileName)) {
            exit('没有找到  ' . $fileName . ' 这个文件，请检查路径是否正确');
        }

        echo '<div style="color: #009900;background-color: black;">';
        echo "<h2 style='color: wheat'>【" . $name . "】{$title}（只展示最后{$num}个字符）更多请&nbsp;↑  &nbsp;&nbsp;url后加&num=100000 | 自动2秒刷新加： &r=2</h2>";

        if ($refresh) {
            echo '<meta http-equiv="refresh" content="' . $refresh . '">';
        }
        $res = self::fileLastLines($fileName, $num);
        if ($format) {
            $arr = array_filter(explode(PHP_EOL, $res));
            $result = $dataNew = [];
            foreach ($arr as $key => $item) {
                $result[] = json_decode($item, true);
            }
            echo '<pre>';
            print_r($result);
            echo '</div>';
            exit;
        } else {
            $arr = array_reverse(explode(PHP_EOL, $res));
            $result = '';
            foreach ($arr as $key => $item) {
                $result .= $item . PHP_EOL;
            }
            echo '<pre>';
            print_r($result);
            echo '</div>';
            exit;
        }
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

    /**
     * 取文件最后$n行
     * @param string $filename 文件路径
     * @param int $n 最后几行
     * @return mixed false表示有错误，成功则返回字符串
     */
    public static function fileLastLines($filename, $n)
    {
        if (!$fp = fopen($filename, 'r')) {
            echo "打开文件失败，请检查文件路径是否正确，路径和文件名不要包含中文";
            return false;
        }
        $pos = -2;
        $eof = "";
        $str = "";
        while ($n > 0) {
            while ($eof != "\n") {
                if (!fseek($fp, $pos, SEEK_END)) {
                    $eof = fgetc($fp);
                    $pos--;
                } else {
                    break;
                }
            }
            $str .= fgets($fp);
            $eof = "";
            $n--;
        }
        return $str;
    }


    public static function numsCount($masterName, $startTime, $endTime)
    {
        $list = range(strtotime($startTime), strtotime($endTime), 24 * 60 * 60);
        $list = array_map(function ($v) {
            return date("Y-m-d", $v);
        }, $list);

        $redis = RedisLib::getInstance(Config::info('REDIS_CONFIG'), false);
        $num = 0;
        foreach ($list as $v) {
            $num += (int)$redis->get($masterName . '_' . $v);
        }
        return $num;
    }
}
