<?php

namespace AsyncCenter;

use AsyncCenter\Library\AmqpLib;
use AsyncCenter\Service\Utils;

class TccRun
{
    /**
     * 路由
     *
     * @throws Exception
     */
    public function route()
    {
        if (!isset($_GET['action'])) {
            exit('no action!');
        }

        //日志访问
        switch ($_GET['action']) {
            case 'batchTest':                //批量全局事务准备 判断服务是否正常
                $this->batchTest();
                break;
            case 'test':                      //全局事务准备 判断服务是否正常
                $this->test();
                break;
            case 'TransOutTry':                //发送尝试请求
                $this->TransOutTry();
                break;
            case 'TransOutConfirm':            //成功确认
                $this->TransOutConfirm();
                break;
            case 'TransOutCancel':             //发送取消请求
                $this->TransOutCancel();
                break;
            case 'TransInTry2':
                $this->TransInTry2();
                break;
            case 'TransInConfirm2':
                $this->TransInConfirm2();
                break;
            case 'TransInTry3':
                $this->TransInTry3();
                break;
            case 'TransInConfirm3':
                $this->TransInConfirm3();
                break;
            case 'TransInCancel3':
                $this->TransInCancel3();
                break;
            case 'TransInTry4':
                $this->TransInTry4();
                break;
            case 'TransInConfirm4':
                $this->TransInConfirm4();
                break;
            case 'TransInCancel4':
                $this->TransInCancel4();
                break;
            case 'newGid':            //生成唯一id
                exit(json_encode(['gid' => md5(time() . uniqid())]));
                break;
            default :
                return http_response_code(404);
        }
    }

    /**
     * 批量tcc 测试
     * @throws Exception
     */
    public function batchTest()
    {
        $num = $_GET['num'] ?? 5;
        for ($i = 0; $i < $num; $i++) {
            $this->test();
            print_r($i);
        }
    }

    /**
     * 测试 TCC
     * @throws Exception
     */
    public function test()
    {
        $svcUrl = Config::info('BASE_HTTP_HOST') . '/tccTest?action=';

        //当try接口返回的json中 含有 FAILURE 字符 或者http状态码 != 200 将会自动终止执行 ，并且记录 try 的错误信息，发送cancel请求
        /**
         * 业务逻辑:多级嵌套 【建议不要超过2层嵌套】
         * 金额：
         * 银行一：100元 ；其他银行都是 0 元
         * 操作：
         * 银行一 转账 30 给银行二
         * 银行二 收到 30 立马转账给银行三 10 块钱
         * 银行三 收到 10 立马转账给银行四 2 块钱
         * 结果：
         * 银行一：70元； 银行二：20元；银行三：8元；银行四：2元；
         */
        $data = [
            'urls_data' => [
                [
                    'title' => '一号银行 ',                       //请求的接口名称 方便后续日志排查
                    'urls' => [
                        'try' => $svcUrl . 'TransOutTry',        //主要是逻辑检测，判断是否可执行下去
                        'confirm' => $svcUrl . 'TransOutConfirm',//确认后执行
                        'cancel' => 'TransOutCancel'             //可省略 但 cancel 字段需存在
                    ],
                ],
                [
                    'title' => '二号银行 ',
                    'urls' => [
                        'try' => $svcUrl . 'TransInTry2',
                        'confirm' => $svcUrl . 'TransInConfirm2',
                        'cancel' => 'TransInCancel2'             //可省略
                    ],
                ],
                [
                    'title' => '三号银行 ',
                    'urls' => [
                        'try' => $svcUrl . 'TransInTry3',
                        'confirm' => $svcUrl . 'TransInConfirm3',
                        'cancel' => 'TransInCancel3'             //可省略
                    ],
                ],
                [
                    'title' => '四号银行 ',
                    'urls' => [
                        'try' => $svcUrl . 'TransInTry4',
                        'confirm' => $svcUrl . 'TransInConfirm4',
                        'cancel' => 'TransInCancel4'             //可省略
                    ],
                ]
            ],
            'request' => [
                'bank_1_out' => 30,      //一号银行转出 30元
                'bank_2_in' => 30,       //二号银行接收 30元
            ],
            'retry' => 1                //1=需要错误重试 0=不需要错误重试
        ];
        $ampq = AmqpLib::getInstanceNew((new Action())->getOne(1)); //默认1号 TCC 事务
        $ampq->publishNew(Config::info('MQ_EXCHANGE_DCM_TCC'), Config::info('QUEUE_DCM_TCC'), json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES));
        echo '数据推送成功！' . PHP_EOL . '<pre>' . json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }

    /************************************   1.0   *******************************/
    //接收尝试连接信息 返回成功状态值 200
    public function TransOutTry()
    {
        $name = '一号银行';
        Utils::writeLog([$name . ' 收到 Try', $_POST], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        $this->TransOutConfirmServer($_POST, 1);
    }

    //确认信息 返回成功状态值 200
    public function TransOutConfirm()
    {
        $this->TransOutConfirmServer($_POST);
    }

    //执行 业务逻辑
    public function TransOutConfirmServer($res, $isTry = 0)
    {
        $res = $res['json'];
        $name = '一号银行';
        list($code, $msg) = $this->commonCheck($res);
        if ($code < 0) {
            exit(json_encode([
                'tcc_code' => -1,
                'msg' => $msg
            ]));
        }

        if ($isTry) {
            if ($code > 0) {
                exit(json_encode([
                    'tcc_code' => 1,
                    'msg' => $msg
                ]));
            }
        }

        //确认执行
        $totalAmount = 100;
        $amount = $totalAmount - $res['bank_1_out'];
        $info = '总金额：' . $totalAmount . '元；转出：' . $res['bank_1_out'] . '元； 剩余：' . $amount . '元；';
        Utils::writeLog([$name . '收到 Submit', $info], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        exit(json_encode(['tcc_code' => 1]));
    }

    public function commonCheck($res)
    {
        $totalAmount = 100;
        if ($res['bank_1_out'] > $totalAmount) {
            return [-1, '余额剩余：' . $totalAmount . '元；不支持转账' . $totalAmount . '元'];
        }

        return [1, '检验成功！'];
    }

    public function TransOutCancel()
    {
        $name = '一号银行';
        Utils::writeLog([$name . '收到 Cancel', $_POST], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        exit(json_encode(['tcc_code' => 1]));
    }

    /************************************   2.0   *******************************/
    //接收尝试连接信息 返回成功状态值 200
    public function TransInTry2()
    {
        $name = '二号银行';
        Utils::writeLog([$name . ' 收到 Try', $_POST], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        $this->TransInConfirmServer2($_POST, 1);
    }

    //确认信息 返回成功状态值 200
    public function TransInConfirm2()
    {
        $this->TransInConfirmServer2($_POST);
    }

    //执行 业务逻辑
    public function TransInConfirmServer2($res, $isTry = 0)
    {
        $res = $res['json'];
        $code = 1;
        $msg = '校验成功';
        if ($code < 0) {
            exit(json_encode([
                'tcc_code' => -1,
                'msg' => $msg
            ]));
        }

        if ($isTry) {
            //逻辑：一收到钱 就立马转账5块钱给三号银行
            if ($code > 0) {
                exit(json_encode([
                    'tcc_code' => 1,
                    Config::info('BASE_HTTP_HOST') . '/tccTest?action=TransInTry3' => [
                        'bank_3_in' => 10,    //二号银行 转给 三号银行 10元 ，将给三号银行接口的参数返回，用于后续请求三号银行的try接口
                        'bank_2_out' => 10,
                    ],
                    'msg' => $msg
                ]));
            }
        }

        $info = '';

        //确认执行
        $name = '二号银行';
        $totalAmount = 0;
        $amount = $totalAmount + $res['bank_2_in'];
        $info .= '总金额：' . $totalAmount . '元；转入：' . $res['bank_2_in'] . '元； 剩余：' . $amount . '元；';

        //转账 10元给 三号银行
        $info .= '转出：10 元； 剩余：' . ($amount - 10) . '元；';
        Utils::writeLog([$name . ' 收到 Submit', $info], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        exit(json_encode(['tcc_code' => 1]));
    }

    /************************************   3.0   *******************************/
    public function TransInTry3()
    {
        $name = '三号银行';
        Utils::writeLog([$name . '收到 Try', $_POST], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        $this->TransInConfirmServer3($_POST, 1);
    }

    public function TransInConfirm3()
    {
        $this->TransInConfirmServer3($_POST);
    }

    public function TransInConfirmServer3($res, $isTry = 0)
    {
        $res = $res['json'] ?? '';
        $code = 1;
        $msg = '校验成功';

        if ($code < 0) {
            exit(json_encode([
                'tcc_code' => -1,
                'msg' => $msg
            ]));
        }

        if ($isTry) {
            if ($code > 0) {
                exit(json_encode([
                    'tcc_code' => 1,
                    Config::info('BASE_HTTP_HOST') . '/tccTest?action=TransInTry4' => [
                        'bank_4_in' => 2,    //三号银行 转给 四号银行转 2元 ，将给四号银行接口的参数返回，用于后续请求四号银行的try接口
                        'bank_3_out' => 2,
                    ],
                    'msg' => $msg
                ]));
            }
        }

        //确认执行
        $info = '';
        $name = '三号银行';
        $totalAmount = 0;
        $amount = $totalAmount + $res['bank_3_in'];
        $info .= '总金额：' . $totalAmount . '元；转入：' . $res['bank_3_in'] . '元； 剩余：' . $amount . '元；';

        //转账 2元给 四号银行
        $info .= '转出：2元； 剩余：' . ($amount - 2) . '元；';
        Utils::writeLog([$name . ' 收到 Submit', $info], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        exit(json_encode(['tcc_code' => 1]));
    }


    public function TransInCancel3()
    {
        $name = '三号银行';
        Utils::writeLog([$name . '收到 Cancel', $_POST], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        exit(json_encode(['tcc_code' => 1]));
    }


    /************************************   4.0   *******************************/
    public function TransInTry4()
    {
        $name = '四号银行';
        Utils::writeLog([$name . '收到 Try', $_POST], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        $this->TransInConfirmServer4($_POST, 1);
    }

    public function TransInConfirm4()
    {
        $this->TransInConfirmServer4($_POST);
    }

    public function TransInConfirmServer4($res, $isTry = 0)
    {
        $res = $res['json'];

        $code = 1;
        $msg = '校验成功';

        if ($code < 0) {
            exit(json_encode([
                'tcc_code' => -1,
                'msg' => $msg
            ]));
        }

        if ($isTry) {
            if ($code > 0) {
                exit(json_encode([
                    'tcc_code' => 1,
                    'msg' => $msg
                ]));
            }
        }

        $name = '四号银行';
        $totalAmount = 0;
        $amount = $totalAmount + $res['bank_4_in'];
        $info = '总金额：' . $totalAmount . '元；转入：' . $res['bank_4_in'] . '元； 剩余：' . $amount . '元；';
        Utils::writeLog([$name . ' 收到 Submit', $info], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        exit(json_encode(['tcc_code' => 1]));
    }

    public function TransInCancel4()
    {
        $name = '四号银行';
        Utils::writeLog([$name . '收到 Cancel', $_POST], 'Tcc/' . date('Y_m_d') . '_TCC_API.log');
        exit(json_encode(['tcc_code' => 1]));
    }
}
