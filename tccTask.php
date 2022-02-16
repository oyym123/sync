<?php
include_once "src/Service/TccService.php";
include_once "src/Service/IdGenerator.php";
include_once "src/Service/Utils.php";
include_once "src/Config.php";

use Pupilcp\Service\TccService;
use Pupilcp\Service\Utils;

/**
 * 消费tcc信息
 * @param $argv
 */
function callbackTask($argv)
{
    $res = Utils::getQueueData($argv[1]);
    $sourceRequest['master'] = 'QUEUE_DCM_TCC';
    $sourceRequest['data'] = $res;
    if (strpos($res, 'urls_data') === false) {
        exit('数据异常！');
    }
    $info = json_decode($res, true);
    list($code, $msg) = (new TccService())->tcc($info['urls_data'], $info['request']);
    if ($code < 0) {
        if (isset($info['retry']) && $info['retry'] == 1) { //进入重试状态
            Utils::writeLog(array_merge(['retry' => '已进入重试'], $msg), 'error/QUEUE_DCM_TCC.log', $sourceRequest);
        } else {
            Utils::writeLog($msg, 'error/QUEUE_DCM_TCC.log');
        }
    } else {
        Utils::writeLog($msg, 'success/QUEUE_DCM_TCC.log');
    }
}

callbackTask($argv);