<?php

namespace App\Console\Commands;

use AsyncCenter\Library\AmqpLib;
use Illuminate\Console\Command;

class AsyncTest extends Command
{
    const MASTER_NAME = 'QUEUE_DCM_TCC';  //唯一主任务名称 需跟界面上配置一致

    /**
     * The name and signature of the console command.
     * php artisan async-test 测试数据
     * @var string
     */
    protected $signature = 'async-test {param=""}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '异步测试demo 往测试mq中添加数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        //入口函数需要设定 这个文件的绝对路径
        putenv("CONFIG_FILE_PATH=" . config_path() . '/async.php');          //日志文件地址
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $msg = $this->argument('param');

        //消息推送到队列
        AmqpLib::sendMsg('MQ_EXCHANGE_DCM_TCC', 'dcm_tcc_test', $msg);

        //广播消息
        AmqpLib::sendMsgFanout('MQ_EXCHANGE_DCM_TCC', $msg);
    }
}
