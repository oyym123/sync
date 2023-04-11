<?php


namespace AsyncCenter\Service;


class RetryType
{
    //默认重试时间间隔  默认5次 【重试还是失败状态，则将永不处理】
    public $defaultRetry = [
        1 => 60,     //1分钟后重试
        2 => 300,    //5分钟后重试
        3 => 1200,   //20分钟后重试
        4 => 3600,   //1小时后重试
        5 => 86400,  //24小时后重试
    ];

    //自定义 第一种 重试4次
    public $typeFirst = [
        1 => 60,     //1分钟后重试
        2 => 300,    //5分钟后重试
        3 => 1200,   //20分钟后重试
        4 => 3600,   //1小时后重试
    ];


    //自定义 第二种 重试1次
    public $typeSecond = [
        1 => 60,     //1分钟后重试
    ];

    //自定义 第三种 重试2次
    public $typeThird = [
        1 => 60,     //1分钟后重试
        2 => 3600,   //1小时后重试
    ];

    //自定义 第四种 重试3次
    public $typeFourth = [
        1 => 60,     //1分钟后重试
        2 => 600,    //10分钟后重试
    ];

    //自定义 第五种 重试1次
    public $typeFifth = [
        1 => 1200,    //20分钟后重试
    ];

    //自定义 第六种 2天 尝试 48 次 平均一小时 重试一次
    public function typeSixth($time = 86400 * 2, $times = 24 * 2)
    {
        $retry = [];
        $num = ceil($time / $times);
        for ($i = 1; $i <= $times; $i++) {
            $retry[$i] = $num;
        }
        return $retry;
    }

    //自定义 第七种 7天  平均1小时 重试一次
    public function typeSeventh($time = 86400 * 7, $times = 24 * 7)
    {
        $retry = [];
        $num = ceil($time / $times);
        for ($i = 1; $i <= $times; $i++) {
            $retry[$i] = $num;
        }
        return $retry;
    }

    //自定义 第八种 7天  平均4小时 重试一次
    public function typeEighth($time = 86400 * 7, $times = 6 * 7)
    {
        $retry = [];
        $num = ceil($time / $times);
        for ($i = 1; $i <= $times; $i++) {
            $retry[$i] = $num;
        }
        return $retry;
    }

    //自定义 第九种 7天  平均4小时 重试一次
    public function typeNinth($time = 86400 * 7, $times = 6 * 7)
    {
        $retry = [];
        $num = ceil($time / $times);
        for ($i = 1; $i <= $times; $i++) {
            $retry[$i] = $num;
        }
        return $retry;
    }

    /**
     * 重试时间间隔
     * 单位秒
     */
    public function retryStr($key = 0)
    {
        $data = [
            0 => ['1分钟', '5分钟', '20分钟', '1小时', '24小时', '总次数：5'],
//            1 => ['1分钟', '5分钟', '20分钟', '1小时', '间隔1小时 持续1天', '总次数：28'],
            2 => ['1分钟', '总次数：1'],
            3 => ['1分钟', '1小时', '总次数：2'],
            4 => ['1分钟', '10分钟', '总次数：2'],
            5 => ['20分钟', '总次数：1'],
            6 => ['1分钟', '5分钟', '20分钟', '1小时', '间隔1小时 持续2天 总次数：52'],
            7 => ['间隔半小时', '持续3天', '总次数：' . 48 * 3],
            8 => ['间隔1小时', '持续7天', '总次数：' . 24 * 7],
            9 => ['间隔4小时', '持续7天', '总次数：' . 6 * 7],
        ];

        if ($key === 'all') {
            return $data;
        }
        return $data[$key] ?? $data[0];
    }

    /**
     * 最长重试时间 总和 不可超过 10天
     * 重试时间间隔
     * 单位秒
     */
    public function retry($key = 0)
    {
        $data = [
            0 => $this->defaultRetry,
//            1 => '',
            2 => $this->typeSecond,
            3 => $this->typeThird,
            4 => $this->typeFourth,
            5 => $this->typeFifth,
            6 => array_merge($this->typeFirst, $this->typeSixth()),
            7 => $this->typeSeventh(),
            8 => $this->typeEighth(),
            9 => $this->typeNinth(),
        ];

        if ($key === 'all') {
            return $data;
        }
        return $data[$key] ?? $this->defaultRetry;
    }

}