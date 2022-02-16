<?php

namespace Pupilcp\Base;

use Pupilcp\Config;
use Pupilcp\Service\Utils;

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

        $oldInfo = Utils::isJson($jobArray['msg']) ?: $jobArray['msg'];

        if (strpos($jobArray['command'], 'http://') !== false || strpos($jobArray['command'], 'https://') !== false) {    //表示http方式
            $info = Utils::httpPost($jobArray['command'], $jobArray['msg']);
            if ($info['httpCode'] != 200) {
                Utils::writeLog(['msg' => $jobArray['command'] . ' 请求失败', 'info' => $info, 'master' => $jobArray['master_process_name']], 'ERROR.log');
            } else {
                Utils::writeLog(['msg' => $jobArray['command'] . ' 请求成功', 'info' => $info, 'master' => $jobArray['master_process_name']], 'SUCCESS.log');
            }
            $setRequest = $jobArray['command'];
        } else { //表示cli方式
            $jobArray['msg'] = Utils::setQueueData($jobArray['msg']);
            //写入执行日志中
            $str = $jobArray['command'] . " " . $jobArray['msg'] . "   >> /tmp/EXEC.log ";
            system($str);
            $setRequest = $jobArray['command'] . " " . $jobArray['action'] . " " . $jobArray['msg'];
        }

        $saveLog = [
            'set_request' => $setRequest,
            'data' => $oldInfo,
            'arg1' => $jobArray['argument_first'] ?? '',
            'arg2' => $jobArray['argument_second'] ?? ''
        ];

        //记录整体日志信息
        Utils::writeLog($saveLog, 'smc.log');

        //记录单个日志信息
        Utils::writeLog($saveLog, 'callback/' . $masterName);
    }
}
