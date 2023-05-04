<?php

namespace AsyncCenter;

use AsyncCenter\Library\AmqpLib;
use AsyncCenter\Library\RedisLib;
use AsyncCenter\Service\RetryType;
use AsyncCenter\Service\TemplateConfig;
use AsyncCenter\Service\Utils;

/**
 * 功能：
 * 1、队列监听消费界面数据展示
 * 2、增删改查
 * 3、启动、重启、暂停
 * 4、日志查询
 * PS:所有数据都以json形式存储在单个文件中
 */
class Action
{
    /**
     * 路由
     */
    public function route()
    {
        //操作
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $this->smcAction();
        }

        //日志访问
        switch ($_GET['action']) {
            case 'tcc_view':        //Tcc日志展示
                include_once "View/tccView.php";
                break;
            case 'add':             //新增展示
                include_once "View/add.php";
                break;
            case 'update':          //修改展示
                include_once "View/update.php";
                break;
            case 'update_submit':   //提交修改
                $this->update();
                break;
            case 'add_submit':      //提交新增
                $this->add();
                break;
            case 'errorLog':        //错误运行日志
                $this->runLog();
                break;
            case 'successLog':      //成功运行日志
                $this->runLog();
                break;
            case 'callbackLog':     //回调运行日志
                $this->runLog();
                break;
            case 'retryLog':        //重试运行日志
                $this->runLog();
                break;
            case 'listenLog':        //监听日志
                $this->listenLog();
                break;
            case 'systemErrorLog':   //整体回调错误日志
                $this->systemErrorLog();
                break;
            case 'smcActionLog':     //操作日志
                $this->smcActionLog();
                break;
            case 'tccLog':              //tcc日志
                $this->tccLog();
                break;
            case 'systemRetryLog':       //重试日志
                $this->systemRetryLog();
                break;
        }
    }

    const CALLBACK_LOG_YES = 10;   //记录回调日志
    const CALLBACK_LOG_NO = 20;    //不记录回调日志

    const REPEAT_CLEAN_YES = 10;    //清除重复信息
    const REPEAT_CLEAN_NO = 20;     //不清除重复信息

    const IS_COUNT_YES = 10;       //统计数据
    const IS_COUNT_NO = 20;        //不统计数据

    const IS_QUEUE_YES = 10;       //使用队列参数
    const IS_QUEUE_NO = 20;        //不使用队列参数

    /**
     * @param string $key
     * @return int|string|string[]
     */
    public function getExchangeType($key = 'all')
    {
        $data = [
            'direct' => 'direct',
            'fanout' => 'fanout',
            'topic' => 'topic',
            'headers' => 'headers',
        ];
        return $key === 'all' ? $data : ($data[$key] ?? self::CALLBACK_LOG_YES);
    }

    /**
     * @param string $key
     * @return int|string|string[]
     */
    public static function getLogStatus($key = 'all')
    {
        $data = [
            self::CALLBACK_LOG_YES => '记录',
            self::CALLBACK_LOG_NO => '不记录',
        ];
        return $key === 'all' ? $data : ($data[$key] ?? self::CALLBACK_LOG_YES);
    }

    /**
     * @param string $key
     * @return int|string|string[]
     */
    public static function getRepeatCleanStatus($key = 'all')
    {
        $data = [
            self::REPEAT_CLEAN_NO => '不去重',
            self::REPEAT_CLEAN_YES => '去重',
        ];
        return $key === 'all' ? $data : ($data[$key] ?? self::REPEAT_CLEAN_YES);
    }

    /**
     * @param string $key
     * @return int|string|string[]
     */
    public static function getIsCountStatus($key = 'all')
    {
        $data = [
            self::IS_COUNT_YES => '统计',
            self::IS_COUNT_NO => '不统计',
        ];
        return $key === 'all' ? $data : ($data[$key] ?? self::IS_COUNT_YES);
    }


    /**
     * @param string $key
     * @return int|string|string[]
     */
    public static function getIsArgQueue($key = 'all')
    {
        $data = [
            self::IS_QUEUE_NO => '不使用',
            self::IS_QUEUE_YES => '使用',
        ];
        return $key === 'all' ? $data : ($data[$key] ?? self::IS_COUNT_YES);
    }

    /**
     * 存储
     * @param $data
     * @param int $id
     */
    public function writeSave($data, $id = 0)
    {
        $redis = RedisLib::getInstance(Config::info('REDIS_CONFIG'), false);
        $lockKey = 'async_center_db_lock';
        if ($redis->exists($lockKey)) {
            exit("有其他用户正在操作，请重试！");
        }
        $redis->setex($lockKey, 2, 1);
        $info = $this->allInfo();
        if ($id) { //更新
            $saveData = [];
            foreach ($info as $item) {
                if ($item['id'] == $id) {
                    foreach ($data as $key => $datum) {
                        $item[$key] = $datum;
                    }
                    $item['updated_at'] = date('Y-m-d H:i:s');
                }
                $saveData[] = $item;
            }
        } else {
            $data['status'] = 0;
            //获取最后一位id
            $data['id'] = end($info)['id'] + 1;
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['created_at'] = date('Y-m-d H:i:s');
            $saveData = array_merge($info, [$data]);
            $id = $data['id'];
        }

        $saveData = json_encode($saveData, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
        file_put_contents(Config::info('JSON_DATABASE_PATH'), str_replace('\/', '/', $saveData));

        //生成具体配置文件
        $this->createConfig($id);

        //根据配置创建队列
        AmqpLib::setMqInfo($this->getOne($id));

        $redis->del($lockKey);
        Header("Location: /async.php");
    }

    /**
     * 获取重试类型
     * @param string $key
     * @return int[]|string[]|string[][]
     */
    public function getRetry($key = 'all')
    {
        return (new RetryType())->retryStr($key);
    }

    /**
     * 获取所有数据
     * @return array|mixed
     */
    public function allInfo()
    {
        $res = file_get_contents(Config::info('JSON_DATABASE_PATH'));
        return $res ? json_decode($res, true) : [];
    }

    /**
     * 获取单条数据
     * @param int $id
     * @return array|mixed
     */
    public function getOne($id = 0)
    {
        $id = $id ?: $_GET['id'];
        $res = json_decode(file_get_contents(Config::info('JSON_DATABASE_PATH')), true) ?: [];
        $info = [];
        foreach ($res as $re) {
            if ($re['id'] == $id) {
                $info = $re;
            }
        }
        return $info;
    }

    /**
     * 唯一性检查
     * @param string $field
     * @return int|mixed
     */
    public function checkUnique($field = 'mq_master_name')
    {
        $uniqueFlag = 0;
        foreach ($this->allInfo() as $value) {
            if ($value[$field] == $_POST[$field]) {
                $uniqueFlag = $value['id'];
            }
        }
        return $uniqueFlag;
    }

    /**
     * 判空检查
     * @param $data
     * @return string
     */
    public function commonCheck($data)
    {
        $flagStr = '';
        foreach ($data as $key => $value) {
            if (empty($value) && $value !== 0 && $key != 'retry') {
                $flagStr .= $key . ',';
            }
        }

        if (!empty($flagStr)) {
            $flagStr = $flagStr . ' 必填！';
        }

        if (isset($data['max_consumer']) && isset($data['min_consumer']) && $data['max_consumer'] < $data['min_consumer']) {
            $flagStr = $flagStr . "最小进程数不可大于最大进程数！";
        }
        return $flagStr;
    }

    /**
     * 创建配置文件
     * @param $id
     */
    public function createConfig($id)
    {
        $data = $this->getOne($id);
        $template = TemplateConfig::config();

        foreach ($data as $key => $datum) {
            if ($key == 'call_back_func') {
                $datum = "['$datum']";
            }
            if ($key == 'retry') {
                $str = '[' . PHP_EOL;
                $retry = (new RetryType())->retry($datum);
                foreach ($retry as $k => $item) {
                    $str .= "\t\t" . $k . ' => ' . $item . ',' . PHP_EOL;
                }
                $datum = $str . ']';
            }
            $template = str_replace('$' . $key, $datum, $template);
        }
        $template = str_replace("'logPath' => '", "'logPath' => '" . Config::info('LOG_PATH') . 'listen/', $template);
        Utils::mkdirs(Config::info('CONFIG_PATH'));
        file_put_contents(Config::info('CONFIG_PATH') . "/smc_" . $data['mq_master_name'] . '.php', $template);
    }

    public function update()
    {

        if ($flag = $this->commonCheck($_POST)) {
            exit($flag);
        }
        $uniqueFlag = $this->checkUnique();
        if ($uniqueFlag && $_POST['id'] != $uniqueFlag) {
            exit('全局唯一英文任务名，不可重复！');
        }
        $this->writeSave($_POST, $_POST['id']);
    }


    public function add()
    {
        if ($flag = $this->commonCheck($_POST)) {
            exit($flag);
        }
        if ($this->checkUnique()) {
            exit('全局唯一英文任务名，不可重复！');
        }
        $this->writeSave($_POST);
    }

    public function smcAction()
    {
        $fileName = $this->getOne()['mq_master_name'];
        $cmdPath = 'cd ' . Config::info('BASE_PATH') . ' && ';

        //启动
        if ($_GET['action'] == 'start') {
            $exec = $cmdPath . Config::info('SMC_ACTION_PHP_ENV_START') . ' ' . $fileName . ' start  >> ' . Config::info('LOG_PATH') . 'SMC_ACTION.log';
            system($exec);
            Utils::writeLog(PHP_EOL . $exec . ' 启动操作', 'SMC_ACTION.log');
            $this->writeSave(['status' => 1], $_GET['id']);
        }

        //禁用
        if ($_GET['action'] == 'stop') {
            $exec = $cmdPath . Config::info('SMC_ACTION_PHP_ENV_START') . ' ' . $fileName . ' stop  >> ' . Config::info('LOG_PATH') . 'SMC_ACTION.log';
            system($exec);
            Utils::writeLog(PHP_EOL . $exec . ' 暂停操作', 'SMC_ACTION.log');
            $this->writeSave(['status' => 0], $_GET['id']);
        }

        //重启
        if ($_GET['action'] == 'restart') {
            $exec1 = $cmdPath . Config::info('SMC_ACTION_PHP_ENV_START') . ' ' . $fileName . ' stop  >> ' . Config::info('LOG_PATH') . 'SMC_ACTION.log';
            $exec2 = $cmdPath . Config::info('SMC_ACTION_PHP_ENV_START') . ' ' . $fileName . ' start  >> ' . Config::info('LOG_PATH') . 'SMC_ACTION.log';
            system($exec1);
            sleep(2);
            system($exec2);
            Utils::writeLog(PHP_EOL . $exec1 . PHP_EOL . $exec2 . ' 重启操作', 'SMC_ACTION.log');
            $this->writeSave(['status' => 1], $_GET['id']);
        }
    }

    public function systemRetryLog()
    {
        Utils::showLog(Config::info('LOG_PATH') . '/RETRY.log', '重试日志');
    }

    public function tccLog()
    {
        Utils::showLog(Config::info('LOG_PATH') . 'Tcc/' . $_GET['tcc_date'] . '_TCC_API.log', 'Tcc日志');
    }

    public function smcActionLog()
    {
        Utils::showLog(Config::info('LOG_PATH') . '/SMC_ACTION.log', '操作日志', 0);
    }

    public function systemErrorLog()
    {
        Utils::showLog(Config::info('LOG_PATH') . '/ERROR.log', '回调错误日志');
    }

    public function listenLog()
    {
        $info = $this->getOne();
        $fileName = Config::info('LOG_PATH') . '/listen/' . $info['mq_master_name'] . '/smc-server.log';
        Utils::showLog($fileName, '监听日志', 0);
    }

    public function runLog()
    {
        $info = $this->getOne();
        $name = $info['name'];
        $num = $_GET['num'] ?? 50;
        $isArr = $_GET['arr'] ?? 0;
        $refresh = $_GET['r'] ?? 0;
        $keywords = $_GET['k'] ?? 0;
        $title = '';
        $dir = 'success';
        if ($_GET['action'] == 'successLog') {
            $title = '成功日志信息';
            $dir = 'success';
        } elseif ($_GET['action'] == 'errorLog') {
            $title = '失败日志信息';
            $dir = 'error';
        } elseif ($_GET['action'] == 'callbackLog') {
            $title = '回调日志信息';
            $dir = 'callback';
        } elseif ($_GET['action'] == 'retryLog') {
            $title = '重试日志信息';
            $dir = 'retry';
        }

        $fileName = Config::info('LOG_PATH') . $dir . '/' . $info['mq_master_name'] . '.log';
        if (!file_exists($fileName)) {
            exit("暂无数据！");
        }

        $title = '<b style="color: orange">' . $title . '</b>';
        $res = Utils::fileLastLines($fileName, $num);
        echo '<div style="color: #009900;background-color: black;">';
        echo "<h2 style='color: wheat'>【" . $name . "】{$title}（只展示最后{$num}行）更多请&nbsp;↑  &nbsp;&nbsp;url后加&num=10000，字符搜索 加 &k=关键词 ，转换数组 加 &arr=1 | 自动刷新加： &r=1</h2>";
        $arr = array_filter(explode(PHP_EOL, $res));
        $res = [];
        $keywordsInfo = [];

        foreach ($arr as $key => $item) {
            $item = str_replace(['n    ', '"n}"', ':"{"', '"}",', '}","arg1"'], ['', '"}', ':{"', '"},', '},"arg1"'], $item);
            $data = json_decode($item, true);
            if (empty($data)) {
                $data['data'] = $item;
            } else {
                $data['data'] = $isArr ? $data['data'] : (json_encode($data['data'], JSON_UNESCAPED_UNICODE) ?: $data['data']);
            }

            if (!empty($keywords)) {
                if (strpos($item, $keywords) !== false) {
                    $keywordsInfo[] = $data['data'];
                }
            }
            $res[] = $data;
        }

        if (!empty($keywords)) {
            $res = $keywordsInfo;
        }
        if ($refresh) {
            echo '<meta http-equiv="refresh" content="' . $refresh . '">';
        }
//        $res = array_reverse($res);
        echo '<pre>';
        print_r($res);
        echo '</div>';
        exit;
    }
}

if (isset($_GET['action'])) {
    (new Action())->route();
}
