<?php
namespace AsyncCenter\Service;


/**
 * 配置模板
 * Class TemplateConfig
 * @package AsyncCenter\Service
 */
class TemplateConfig
{
    public static function config(){
            return '<?php
return [
    //通用配置
    \'global\' => [
        \'masterProcessName\' => \'$mq_master_name\', //主进程名称
        \'enableNotice\' => false, //是否开启预警通知
        \'dingDingToken\' => \'钉钉机器人token\', //钉钉机器人token
        \'logPath\' => \'$mq_master_name\', //日志文件路径
        \'childProcessMaxExecTime\' => 864000, //子进程最大执行时间，避免运行时间过长，释放内存，单位：秒
        \'smcServerStatusTime\' => 120, //可选，定时监测smc-server状态的时间间隔，默认为null，不开启
        \'queueStatusTime\' => 60, //可选，定时监测消息队列数据积压的状态，自动伸缩消费者，默认为null，不开启
        \'checkConfigTime\' => 60, //可选，定时监测队列相关配置状态的时间间隔，结合queueCfgCallback实现热加载，默认为null，不开启
        \'logFlag\' => $is_log,         //是否记录回调日志 10=记录 =20不记录
        \'cleanRepeat\' => $is_repeat,  //是否判断消息重复  10=清除 =20不清除
        \'isCount\' => $is_count,  //是否统计消息数量  10=统计 =20不统计
        \'isQueue\' => $is_queue,  //是否使用队列参数  10=使用 =20不使用
    ],
    //redis连接信息，用于消息积压预警和进程信息的记录
    \'redis\' => [
        \'host\' => \'$redis_host\', //redis服务地址
        \'port\' => \'$redis_port\', //端口号
        \'database\' => \'$redis_database\',
        \'timeout\' => 5,
        \'password\' => \'$redis_password\', //不用密码请注释该配置
    ],
    \'amqp\' => [
        //消息服务连接配置
        \'connection\' => [
            \'host\' => \'$mq_host\',
            \'user\' => \'$mq_user\',
            \'pass\' => \'$mq_pass\',
            \'port\' => \'$mq_port\',
            \'vhost\' => \'$mq_vhost\',
            \'exchange\' => \'$mq_exchange\',
            \'timeout\' => 180,
        ],
        \'queues\' => [
            \'$queue_name\' => [   //产品单独调用更新
                \'queueName\' => \'$queue_name\',
                \'routeKey\' => \'$route_key\',
                \'vhost\' => \'$mq_vhost\',
                \'prefetchCount\' => $prefetch_count,
                \'minConsumerNum\' => $min_consumer,
                \'maxConsumerNum\' =>$max_consumer,
                \'warningNum\' => 10000,
                \'callback\' => $call_back_func,
            ],
        ]
    ],
    \'retry\' => $retry,
];
';
    }
}
