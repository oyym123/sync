<?php


namespace App\Http\Controllers;

use AsyncCenter\View\Route;
use Illuminate\Routing\Controller as BaseController;

class AsyncController extends BaseController
{
    public function __construct()
    {
        //入口函数需要设定 这个文件的绝对路径
        putenv("CONFIG_FILE_PATH=" . config_path() . '/async.php');          //日志文件地址
    }

    public function list()
    {
        (new Route())->renderView(1);
    }
}
