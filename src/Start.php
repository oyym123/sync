<?php

namespace AsyncCenter;

class Start
{
    /**
     * 开始smc服务
     * 常用命令  start|restart|stop|status|help
     * @param $argv
     * @param bool $daemon
     */
    public function run($config, $command, $daemon = true)
    {
        $file = Config::info('CONFIG_PATH') . 'smc_' . $config . ".php";
        $globalConfig = include $file;
        try {
            $app = new App($globalConfig);
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


