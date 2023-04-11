<?php

namespace AsyncCenter\Base;

use AsyncCenter\Action;
use AsyncCenter\BloomFilter\BloomFilter;
use AsyncCenter\Config;
use AsyncCenter\Library\RedisLib;
use AsyncCenter\Service\Utils;
use Exception;

class BaseApplication
{
    public function run($jobArray)
    {
        $argumentStr = '';
        if (isset($jobArray['argument_first']) && !empty($jobArray['argument_first'])) {
            $argumentStr .= $jobArray['argument_first'];
        }


        if (isset($jobArray['argument_second']) && !empty($jobArray['argument_second'])) {
            $argumentStr .= ' ' . $jobArray['argument_second'];
        }

        $masterName = 'smc.log';
        if (isset($jobArray['master_process_name']) && !empty($jobArray['master_process_name'])) {
            $masterName = $jobArray['master_process_name'] . '.log';
        }
        $infoData = Utils::isJson($jobArray['msg']);
        $oldInfo = $infoData ? json_encode($infoData, JSON_PRETTY_PRINT) : $jobArray['msg'];
        $isExec = 1;
        if (isset($jobArray['cleanRepeat']) && $jobArray['cleanRepeat'] == 1) {
            //布隆过滤器：用于判断是否重复  默认存在 redis database =  3
            try {
                $bloomFilter = new BloomFilter($jobArray['master_process_name']);
                $filterKey = md5($jobArray['msg']);
                if ($bloomFilter->exists($filterKey)) {
                    $isExec = 0;
                }
                $bloomFilter->add($filterKey);
            } catch (Exception $e) {
                //记录布隆过滤器运行异常
                Utils::writeLog($e->getMessage(), 'SMC_ERROR.log');
            }
        }

        if ($isExec) { //表示可执行
            if (strpos($jobArray['command'], 'http://') !== false || strpos($jobArray['command'], 'https://') !== false) {
                //表示http方式
                $info = Utils::httpPost($jobArray['command'], $jobArray['msg']);
                if (!empty($info['response'])) {  //进行转义 方便后面保存json日志
                    $info['response'] = Utils::isJson($info['response']) ?: $info['response'];
                }
                if ($info['httpCode'] != 200) {
                    Utils::asyncErrorLog(['msg' => $jobArray['command'] . ' 请求失败', 'info' => $info], $jobArray['master_process_name']);
                } else {
                    Utils::asyncSuccessLog(['msg' => $jobArray['command'] . ' 请求成功', 'info' => $info], $jobArray['master_process_name']);
                }
                $setRequest = $jobArray['command'];
            } else {
                //表示cli方式
                $jobArray['msg'] = Utils::setQueueData($jobArray['msg']);
                //表示使用队列参数
                if (isset($jobArray['isQueue']) && $jobArray['isQueue'] == Action::IS_QUEUE_YES) {
                    $redis = RedisLib::getInstance(Config::info('REDIS_CONFIG'), false);
                    $redis->lPush('ARGUMENTS_' . $jobArray['master_process_name'], $oldInfo);
                    $jobArray['msg'] = 'deal-by-queue-argument';
                }
                //写入执行日志中
                $setRequest = Config::info('SMC_ACTION_PHP_ENV') . ' ' . $jobArray['command'] . " " . $jobArray['msg'] . " " . $argumentStr;
                system($setRequest);
            }

            $saveLog = [
                'request' => $setRequest,
                'data' => $oldInfo,
                'arg1' => $jobArray['argument_first'] ?? '',
                'arg2' => $jobArray['argument_second'] ?? '',
                'is_repeat' => Action::getRepeatCleanStatus($jobArray['cleanRepeat'])
            ];

            if ($jobArray['isCount'] == Action::IS_COUNT_YES) {
                //统计数据
                $redis = RedisLib::getInstance(Config::info('REDIS_CONFIG'), false);
                $redis->incr($masterName . '_' . date('Y-m-d'));
            }

            if ($jobArray['logFlag'] == Action::CALLBACK_LOG_YES) {
                //记录单个日志信息
                Utils::writeLog($saveLog, 'callback/' . $masterName);
            }
        } else { //不可执行 记录去重日志
            Utils::writeLog([
                'reason' => '此消息已被去重处理',
                'data' => $oldInfo
            ], 'error/' . $masterName);
        }
    }
}
