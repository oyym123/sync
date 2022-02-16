<?php

use Pupilcp\Config;

class Start
{
    public function __construct()
    {
        require_once "./autoload.php";
    }

    /**
     * 开始smc服务
     * 常用命令  start|restart|stop|status|help
     * @param $argv
     * @param bool $daemon
     */
    public function run($argv, $daemon = true)
    {
        $config = $argv[1];
        $command = $argv[2];
        $file = __DIR__ . "/config/" . $config . ".php";
        $globalConfig = include_once $file;

        try {
            $app = new \Pupilcp\App($globalConfig);
            $app->run($command, $daemon);
        } catch (\Throwable $e) {
            //处理异常情况 TODO
            echo $config . $command . '操作失败!' . PHP_EOL;
            print_r($e->getMessage());
            exit;
        }
        echo $config . ' ' . $command . '操作成功!';
    }
}

(new Start())->run($argv);
