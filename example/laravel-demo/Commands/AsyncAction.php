<?php

namespace App\Console\Commands;

use AsyncCenter\Start;
use Illuminate\Console\Command;

class AsyncAction extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan async-action QUEUE_DCM_TCC start
     * @var string
     */
    protected $signature = 'async-action {masterName=""} {action=""}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '异步处理中心 每个任务都需要建一个执行命令';

    /**
     * Create a new command instance.
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
     * @return mixed
     */
    public function handle()
    {
        (new Start())->run($this->argument('masterName'), $this->argument('action'));
    }
}
