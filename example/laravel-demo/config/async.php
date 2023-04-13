<?php
return [
    'JSON_DATABASE_PATH' => config_path() . '/asyncJsonDb.json',         //服务信息存储文件地址
    'LOG_STORAGE_MODE' => 'file',                    //存储模式 暂时支持 file   TODO 后续加上其他存储支持
    'BASE_PATH' => base_path() . '/',                //代码根目录
    'LOG_PATH' => storage_path() . '/logs/async/',   //日志存储目录
    'CONFIG_PATH' => config_path() . '/async/',      //服务配置文件目录
    'RETRY_INFO' => [                                //重试 redis信息存储地址 【启用重试机制时必填】
        'redis' => [
            'host' => '192.168.71.244',              //redis服务地址
            'port' => '7001',
            'database' => '1',
            'timeout' => 5,
            'password' => 'foo#09213',
        ],
        'info_max_day' => 86400 * 10                 //重试信息允许保留的最大时间
    ],
    'REDIS_CONFIG' => [                              //服务进程信息 redis 存储地址 默认地址
        'host' => '192.168.71.244',
        'port' => '7001',
        'database' => '1',
        'timeout' => 5,
        'password' => 'foo#09213',
    ],
    'MQ_CONFIG' => [                                 //rabbitmq默认地址
        'mq_host' => '192.168.71.244',
        'mq_port' => '5672',
        'mq_vhost' => '/',
        'mq_user' => 'devdrm',
        'mq_pass' => 'foo#09213'
    ],

    'SMC_ACTION_PHP_ENV' => (PHP_OS == "Linux" ? '/usr/local/php/bin/php' : 'php'),  //php 运行的环境 【linux环境部署时必填】

    //Laravel 启动监听进程命令
    'SMC_ACTION_PHP_ENV_START' => (PHP_OS == "Linux" ? '/usr/local/php/bin/php artisan async-action' : 'php artisan async-action'),

    /****************************TCC分布式配置  不使用可以忽略以下配置 ************************************************/
    'TCC_INFO' => [                   //TCC分布式事务redis存储信息地址 【启用TCC时必填】
        'redis' => [
            'host' => '192.168.71.244',
            'port' => '7001',
            'database' => '2',
            'timeout' => 5,
            'password' => 'foo#09213',
        ],
        'info_max_day' => 86400 * 10  //TCC信息允许保留的最大时间
    ],
    'MQ_EXCHANGE_DCM_TCC' => 'MQ_EXCHANGE_DCM_TCC',  //TCC分布式事务交换机名称 【启用TCC时必填】
    'QUEUE_DCM_TCC' => 'dcm_tcc',                    //TCC分布式事务队列名称   【启用TCC时必填】
    'TCC_HTTP_HOST' => 'http://192.168.31.78:998/tcc?action=',    //TCC分布式事务服务地址 即SMC服务地址  IP格式  【启用TCC时必填】
    'BASE_HTTP_HOST' => 'http://192.168.31.78:998',  //服务地址
];
