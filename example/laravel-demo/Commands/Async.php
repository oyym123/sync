<?php

namespace App\Console\Commands;

use AsyncCenter\Library\AmqpLib;
use AsyncCenter\Service\Utils;

class Async extends AsyncAction
{
    const MASTER_NAME = 'QUEUE_DCM_TCC';  //唯一主任务名称 需跟界面上配置一致

    /**
     * The name and signature of the console command.
     * php artisan async
     * @var string
     */
    protected $signature = 'async {param=""}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '异步处理中心 任务回调 每个任务都需要建一个执行命令';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        //获取Cli传参回调的数据 （建议传参字符不宜超过5000，更多的字符请使用队列参数,需在界面上配置）
        $data = Utils::getQueueData($this->argument('param'));

        //当填写master_name时 默认调用队列里的参数
        $data = Utils::getQueueData('', self::MASTER_NAME);

        //TODO 执行业务逻辑

        $msgSuccess = '成功日志';
        Utils::asyncSuccessLog($msgSuccess, self::MASTER_NAME);

        $msgError = '失败日志';
        Utils::asyncErrorLog($msgError, self::MASTER_NAME);

        $msgError = '失败且需要重试';
        Utils::asyncErrorLog($msgError, self::MASTER_NAME, $data);

        $msg = '消息内容';

        //消息推送到队列
        AmqpLib::sendMsg('MQ_EXCHANGE_DCM_TCC_TEST', 'dcm_tcc_test', $msg);

        //广播消息
        AmqpLib::sendMsgFanout('MQ_EXCHANGE_DCM_TCC_TEST', $msg);

        return true;
    }
}
