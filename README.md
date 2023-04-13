# Async Center
PHP + Swoole + Rabbitmq + Redis 实现并发异步处理 

TPS：
    
    由于Swoole的原因，只能在linux环境运行，请勿在windows环境中启动。
    php需安装 amqp、redis、swoole扩展
    PHP版本 默认 > 7.0   8.0版本没有试过

安装 

     composer require oyym/async-center


一、简介：
 
      1、一个可独立运行的异步服务中心，支持 Cli 以及 Http 的形式发送并发请求

      2、支持TCC事务机制

      3、支持灵活的重试机制

      4、具有完善的日志信息

      5、可界面化操作进程
      
二、 解决的问题点：

      1、将多进程异步并发操作跟业务代码完全分离开，减少多进程编写的心智负担

      2、支持tcc分布式事务，以及重试机制

      3、灵活的重试补偿机制，保证程序运行的稳定
  
      4、可以作为独立的服务运行，无需嵌入主程序代码中
      
      5、几分钟搞定一个多进程操作
      
      
swoole扩展安装教程：

    https://zhuanlan.zhihu.com/p/106764018
    目前只支持4.4.12 及以下版本的 swoole

Laravel集成 

       第一步：
           composer require oyym/async-center
       
       第二步：
           将vendor/oyym/async-center/example/laravel-demo/Commands 下的三个文件移到 app/Console/Commands下
           将vendor/oyym/async-center/example/laravel-demo/config 下的两个文件移到 app/config下
           将vendor/oyym/async-center/example/laravel-demo/Controllers 下的一个文件移到 app/Http/Controllers下
           最后添加路由 Route::any('async', 'AsyncController@list');
       
       第三步：
           填写好 config/async.php中的 rabbitmq 和 redis 配置文件 
       
       第四步：
           给app/config 、 app/storage 设置可写入的权限 
           chmod -r 777 app/config
           chmod -r 777 app/storage
      
           访问：http://localhouse/async  加上配置文件 点击启动
       
           根目录下执行测试命令
           php artisan async-test content
           
           即可以在界面上看到对应的回调日志


