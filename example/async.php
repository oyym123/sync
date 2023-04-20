<?php

namespace AsyncCenter;
require "../vendor/autoload.php";

use AsyncCenter\Service\Test;
use AsyncCenter\Service\Utils;
use AsyncCenter\View\Route;

class Index
{
    public $func;
    public $avg1;
    public $avg2;

    public function __construct($argv = [])
    {
        //入口函数需要设定 这个文件的绝对路径
        putenv("CONFIG_FILE_PATH=" . __DIR__ . '/config.php');   //日志文件地址
        $this->func = $argv[1] ?? '';    //方法名称
        $this->avg1 = $argv[2] ?? '';    //第一个参数
        $this->avg2 = $argv[3] ?? '';    //第二个参数
    }

    /**
     * cli路由跳转
     */
    public function route()
    {
        $func = $this->func;
        if (empty($func)) {
          (new Route())->renderView(0);
        } else {
            $this->$func();
        }
    }

    /**
     * 设置任务状态
     * php async.php run QUEUE_DCM_TCC start
     * php async.php run QUEUE_DCM_TCC stop
     */
    public function run()
    {
        (new Start())->run($this->avg1, $this->avg2);
    }

    /**
     * 任务重试
     * php async.php retryTask
     */
    public function retryTask()
    {
        (new RetryTask())->start();
    }

    /**
     * 日志任务
     * php async.php  logTask 123
     */
    public function logTask()
    {
        $this->avg1 = Utils::setQueueData('{"mq_master_name":"QUEUE_DCM_TCC","msg":"内容！","code":1,"sourceRequest":""}');
        (new LogTask())->callbackLog($this->avg1);
    }

    /**
     * Tcc测试
     * 访问 https://sy.com/tccTest?action=test
     */
    public function tccTest()
    {
        (new TccRun())->route();
    }

    /**
     * tcc服务中心
     */
    public function tcc()
    {
        (new Tcc())->route();
    }

    /**
     * Tcc测试回调
     * php async.php  callbackTask 参数1
     */
    public function callbackTask()
    {
        (new TccTask())->callbackTask($this->avg1);
    }

    /**
     * 测试cli执行
     * php async.php  callbackTest
     */
    public function callbackTest()
    {
        (new Test())->callbackTest($this->avg1);
    }

    /**
     * 测试cli执行
     * php async.php  setTest
     */
    public function setTest()
    {
        (new Test())->setTest();
    }

    /**
     * 测试Http执行
     * php async.php  setTestHttp
     */
    public function setTestHttp()
    {
        (new Test())->setTestHttp();
    }

    /**
     * mq消息推送
     * php async.php  sendMq
     */
    public function sendMq()
    {
        (new Test())->sendMq();
    }
}

if (!empty($argv)) {
    (new Index($argv))->route();
} else {
    (new Index())->route();
}
