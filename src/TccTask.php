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
        if (strpos($res, 'urls_data') === false) {
            exit('数据异常！');
        }
        $info = json_decode($res, true);
        list($code, $msg) = (new TccService())->tcc($info['urls_data'], $info['request']);
        if ($code < 0) {
            //进入重试状态
            Utils::asyncErrorLog($msg, 'QUEUE_DCM_TCC', $res);
        } else {
            Utils::asyncSuccessLog($msg, 'QUEUE_DCM_TCC');
        }
    }
}

