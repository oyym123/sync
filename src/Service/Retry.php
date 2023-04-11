<?php


namespace AsyncCenter\Service;

use Exception;
use AsyncCenter\Config;
use AsyncCenter\Library\AmqpLib;
use AsyncCenter\Library\RedisLib;

/**
 * 错误重试
 * Class Retry
 * @package AsyncCenter\Service
 */
class Retry
{
    /**
     *  重试
     * 每分钟执行一次
     * @throws Exception
     */
    public function start()
    {
        $info = json_decode(file_get_contents(Config::info('JSON_DATABASE_PATH')), true);
        $redis = RedisLib::getInstance(Config::info('RETRY_INFO')['redis'], false);
        $retryArr = [];
        //扫描所有任务
        foreach ($info as $item) {
            //获取当前一小时的异常
            $prefixKey = 'SMC_RETRY_' . $item['mq_master_name'] . '_' . date('Y-m-d_H');
            $keyTime = $redis->ttl($prefixKey);

            if ($keyTime == -2) {  //表示已过期，或者不存在
                continue;
            } else {
                $resData = $redis->get($prefixKey);

                $res = json_decode($resData, true);

                $retryType = new RetryType();
                $newRes = [];
                if (is_array($res) && !empty($res)) {
                    $ampq = AmqpLib::getInstanceNew($item);
                    foreach ($res as $re) {
                        if (abs(time() - $re['end_time']) < 120 && $re['status'] == 1) {   //小于2分钟之内的则重新推入队列
                            $re['status'] = 0;
                            if (!empty($re['data'])) {
                                $oldData = [];
                                if (is_array($re['data'])) {
                                    $oldData = $re['data'];
                                    $re['data'] = json_encode($re['data'], JSON_PRETTY_PRINT);
                                }
                                $timesRes = $redis->get(Utils::retryTimesKey($re['data']));
                                if (!empty($timesRes)) {
                                    $dir = 'retry/';
                                    $path = $dir . $item['mq_master_name'] . '.log';
                                    $ampq->publish($item['mq_exchange'], $item['queue_name'], $re['data']);
                                    $nextTimeArr = $retryType->retry($item['retry']);
                                    if (isset($nextTimeArr[(integer)$timesRes + 1])) {
                                        $nextTime = date('Y-m-d H:i:s', time() + $nextTimeArr[(integer)$timesRes + 1]);
                                    } else {
                                        $nextTime = '此次为最后一次重试！';
                                    }

                                    $logArr = [
                                        'times' => $timesRes,       //当前第几次推送
                                        'retry_type' => $retryType->retryStr($item['retry']),
                                        'master' => $item['mq_master_name'],
                                        'next_time' => $nextTime,    //下一次推送时间
                                        'data' => $oldData
                                    ];

                                    $retryArr[] = $item['mq_master_name'] . ' 第' . $timesRes . '次推送';
                                    Utils::writeLog($logArr, $path);
                                }
                            }
                        } else {
                            $newRes[] = $re;
                        }
                    }
                }
                $redis->setex($prefixKey, $keyTime, json_encode($newRes));
            }
        }

        $data = [
            'title' => count($retryArr) . '个任务进行了重试',
            'info' => $retryArr
        ];
        Utils::writeLog($data, 'RETRY.log');
    }
}