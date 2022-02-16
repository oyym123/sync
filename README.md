# sync
PHP + swoole + rabbitmq 实现并发异步

一、简介：
 
      1、是一个可以独立运行的，异步服务中心，支持 Cli 以及 Http 的形式发送并发请求

      2、支持TCC事务机制

      3、支持灵活的重试机制

      4、具有完善的日志信息

      5、界面化操作

      
二、 解决的问题点：

      1、将多进程异步并发操作跟业务代码完全分离开，减少多进程编写的心智负担

      2、支持分布式事务，以及重试机制，再也不用为跨库嵌套事务而烦恼了

      3、灵活的重试补偿机制，保证程序运行的稳定
  
      4、可以作为独立的服务运行，无需嵌入主程序代码中
      
      5、添加一个多进程操作，几分钟搞定，且因为统一，出错概率极少
     
  
界面展示 列表：
     ![image](https://user-images.githubusercontent.com/20701868/154227147-93006c40-4263-40b4-a63e-001efb151f22.png)

界面展示 新增：
![image](https://user-images.githubusercontent.com/20701868/154227788-cb72aa4b-93ec-4928-b6f0-f02f1ce76542.png)

演示地址：
http://101.37.174.19:876/

三、目录结构：
![image](https://user-images.githubusercontent.com/20701868/154232793-0d3ea1c7-4d4a-494d-b477-8c40832f1687.png)



四、操作步骤：
      1、安装 PHP swoole 扩展 、 rabbitmq 和 redis
      2、修改主配置文件  src/Config.php
      3、点击启动



