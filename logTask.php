<?php
include_once "src/Service/Utils.php";
include_once "src/Config.php";

use Pupilcp\Service\Utils;

/**
 * 消费日志信息
 * @param $argv
 */
function callbackLog($argv)
{
//    $d = '{"mq_master_name":"QUEUE_DCM_TCC","msg":"成功！","code":-1,"sourceRequest":""}';
    $info = Utils::getQueueData($argv[1]);
    $data = json_decode($info, true);
    if (!empty($data)) {
        if (!isset($data['mq_master_name']) || !isset($data['msg']) || !isset($data['code'])) {
            Utils::writeLog($data['mq_master_name'] . ' 日志记录没有传mq_master_name字段！', 'ERROR.log');
        } else {
            if (isset($data['code']) && $data['code'] >= 0) {
                Utils::writeLog($data['msg'], 'success/' . $data['mq_master_name'] . '.log', $data['sourceRequest'] ?? '');
            } else {
                Utils::writeLog($data['msg'], 'error/' . $data['mq_master_name'] . '.log', $data['sourceRequest'] ?? '');
            }
        }
    }
}

callbackLog($argv);