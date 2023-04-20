<?php

namespace App\Console\Commands;

use AsyncCenter\RetryTask;
use Illuminate\Console\Command;

class AsyncRetry extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan async-retry
     * @var string
     */
    protected $signature = 'async-retry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '异步处理中心 添加每分钟任务重试 需要在crontab中加入 * * * * *  cd  /根目录 &&  php artisan async-retry';

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
     */
    public function handle()
    {
        (new RetryTask())->start();
    }
}
