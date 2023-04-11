<?php

namespace AsyncCenter;

use AsyncCenter\Service\Utils;

class LogTask
{
    /**
     * 消费日志信息
     * @param $argv
     */
    public function callbackLog($params)
    {
//    $d = '{"mq_master_name":"QUEUE_DCM_TCC","msg":"成功！","code":-1,"sourceRequest":""}';
        $info = Utils::getQueueData($params);
        $data = json_decode($info, true);
        if (!empty($data)) {
            if (!isset($data['mq_master_name']) || !isset($data['msg']) || !isset($data['code'])) {
                Utils::asyncErrorLog($data['mq_master_name'] . ' 日志记录没有传mq_master_name字段！', $data['mq_master_name']);
            } else {
                if (isset($data['code']) && $data['code'] >= 0) {
                    Utils::asyncSuccessLog($data['msg'], $data['mq_master_name'], $data['sourceRequest'] ?? '');
                } else {
                    Utils::asyncErrorLog($data['msg'], $data['mq_master_name'], $data['sourceRequest'] ?? '');
                }
            }
        }
    }
}

