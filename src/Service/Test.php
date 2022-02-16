<?php


namespace Pupilcp\Service;


use Pupilcp\Smc;

class Test
{
    public static function loadQueueConfig()
    {
//        $globalConfig = Smc::getGlobalConfig()['global'];
//        $queueConfig = $globalConfig['masterProcessName'];

        //实现热加载的方式
        //1. include queueConfig.php 配置文件
        return include APPPATH . "config/smc_queue.php";
        //2. 在这里获取数据库的配置，如mysql、es、redis等存储服务
    }

    public static function loadQueueConfig2()
    {
        return include APPPATH . "config/smc_queue2.php";
    }

}