<?php

namespace AsyncCenter;

use AsyncCenter\Service\TccService;
use AsyncCenter\Service\Utils;

class TccTask
{
    /**
     * 消费tcc信息
     * @param $params
     */
    public function callbackTask($params)
    {
        $res = Utils::getQueueData($params);
        $sourceRequest['master'] = 'QUEUE_DCM_TCC';
        $sourceRequest['data'] = json_encode(json_decode($res, true), JSON_PRETTY_PRINT);
        if (strpos($res, 'urls_data') === false) {
            exit('数据异常！');
        }
        $info = json_decode($res, true);
        list($code, $msg) = (new TccService())->tcc($info['urls_data'], $info['request']);
        if ($code < 0) {
            //进入重试状态
            Utils::asyncErrorLog($msg, $sourceRequest['master'], $sourceRequest);
        } else {
            Utils::asyncSuccessLog($msg, $sourceRequest['master']);
        }
    }
}

