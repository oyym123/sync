<?php

include_once "action.php";
include_once "src/Config.php";
include_once "src/Service/TccService.php";

use Pupilcp\Service\TccService;
use Pupilcp\Service\Utils;

class Tcc
{
    /**
     * 路由
     */
    public function route()
    {
        $tcc = new TccService();
        //日志访问
        switch ($_GET['action']) {
            case 'prepare':           //全局事务准备 判断服务是否正常
                $_POST['action'] = 'prepare';
                Utils::writeLog($_POST, 'TCC.log');
                break;
            case 'registerTccBranch': //注册事务
                $tcc->registerTccBranch();
                break;
            case 'submit':            //成功确认
                $tcc->submit();
                break;
            case 'abort':             //异常取消 以及记录异常信息
                $tcc->abort();
                break;
        }
    }
}

if (isset($_GET['action'])) {
    (new Tcc())->route();
}