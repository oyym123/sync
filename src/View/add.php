<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>添加配置信息</title>
    <link rel="stylesheet" href="../static/css/normalize.min.css">
    <link rel="stylesheet" href="../static/css/style.css">
</head>
<style>
    .form-add {
        color: greenyellow
    }
</style>
<body>

<div class="app-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="app-icon">
                <svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor"
                          d="M507.606 371.054a187.217 187.217 0 00-23.051-19.606c-17.316 19.999-37.648 36.808-60.572 50.041-35.508 20.505-75.893 31.452-116.875 31.711 21.762 8.776 45.224 13.38 69.396 13.38 49.524 0 96.084-19.286 131.103-54.305a15 15 0 004.394-10.606 15.028 15.028 0 00-4.395-10.615zM27.445 351.448a187.392 187.392 0 00-23.051 19.606C1.581 373.868 0 377.691 0 381.669s1.581 7.793 4.394 10.606c35.019 35.019 81.579 54.305 131.103 54.305 24.172 0 47.634-4.604 69.396-13.38-40.985-.259-81.367-11.206-116.879-31.713-22.922-13.231-43.254-30.04-60.569-50.039zM103.015 375.508c24.937 14.4 53.928 24.056 84.837 26.854-53.409-29.561-82.274-70.602-95.861-94.135-14.942-25.878-25.041-53.917-30.063-83.421-14.921.64-29.775 2.868-44.227 6.709-6.6 1.576-11.507 7.517-11.507 14.599 0 1.312.172 2.618.512 3.885 15.32 57.142 52.726 100.35 96.309 125.509zM324.148 402.362c30.908-2.799 59.9-12.454 84.837-26.854 43.583-25.159 80.989-68.367 96.31-125.508.34-1.267.512-2.573.512-3.885 0-7.082-4.907-13.023-11.507-14.599-14.452-3.841-29.306-6.07-44.227-6.709-5.022 29.504-15.121 57.543-30.063 83.421-13.588 23.533-42.419 64.554-95.862 94.134zM187.301 366.948c-15.157-24.483-38.696-71.48-38.696-135.903 0-32.646 6.043-64.401 17.945-94.529-16.394-9.351-33.972-16.623-52.273-21.525-8.004-2.142-16.225 2.604-18.37 10.605-16.372 61.078-4.825 121.063 22.064 167.631 16.325 28.275 39.769 54.111 69.33 73.721zM324.684 366.957c29.568-19.611 53.017-45.451 69.344-73.73 26.889-46.569 38.436-106.553 22.064-167.631-2.145-8.001-10.366-12.748-18.37-10.605-18.304 4.902-35.883 12.176-52.279 21.529 11.9 30.126 17.943 61.88 17.943 94.525.001 64.478-23.58 111.488-38.702 135.912zM266.606 69.813c-2.813-2.813-6.637-4.394-10.615-4.394a15 15 0 00-10.606 4.394c-39.289 39.289-66.78 96.005-66.78 161.231 0 65.256 27.522 121.974 66.78 161.231 2.813 2.813 6.637 4.394 10.615 4.394s7.793-1.581 10.606-4.394c39.248-39.247 66.78-95.96 66.78-161.231.001-65.256-27.511-121.964-66.78-161.231z"></path>
                </svg>
            </div>
        </div>
        <ul class="sidebar-list">
            <li class="sidebar-list-item">
                <a href="/async<?= $_GET['beautyRoute'] ?? '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-home">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span>首页</span>
                </a>
            </li>
            <li class="sidebar-list-item active">
                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-shopping-bag">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>异步处理中心</span>
                </a>
            </li>
    </div>
    <div class="app-content" style="height: 200%">
        <?php
        $redisConfig = \AsyncCenter\Config::info('REDIS_CONFIG');
        $mqConfig = \AsyncCenter\Config::info('MQ_CONFIG');
        $action = new \AsyncCenter\Action();
        ?>
        <div class="app-content-header">
            <h1 class="app-content-headerText">新增配置</h1>
        </div>
        <br/>
        <br/>
        <form action="../async<?= $_GET['beautyRoute'] ?? '' ?>?action=add_submit" method="post" class="form-add">
            <span> <span style="color: red"> * </span>任务备注名称：</span>
            <input class="input-bar" placeholder="【产品】更新sku信息" name="name" type="text">
            <br/>
            <br/>
            <span> <span style="color: red;"> * </span>唯一英文任务名：</span>
            <input class="input-bar" placeholder="Search..." name="mq_master_name" type="text">
            <br/>
            <br/>
            <span> <span style="color: red;"> * </span>交换机名称：</span>
            <input class="input-bar" placeholder="" name="mq_exchange" type="text">
            <br/>
            <br/>

            <span> <span style="color: red;"> * </span>队列名称：</span>
            <input class="input-bar" placeholder="" name="queue_name" type="text">
            <br/>
            <br/>
            <span> <span style="color: red;"> * </span>路由名称：</span>
            <input class="input-bar" placeholder="" name="route_key" type="text">
            <br/>
            <br/>
            <span> <span style="color: red;"> * </span>回调方法：</span>
            <input class="input-bar"
                   placeholder="/usr/bin/php /demo/index.php abc 或者 http://demo.com/abc"
                   name="call_back_func"
                   type="text">
            <br/>
            <br/>
            <h5 style="color: cornsilk">以下皆为默认配置 - 可自行修改</h5>
            <hr style="width: 37%;position: relative;right: 30%">

            <div class="filter-button-wrapper" style="right: 35%;bottom: 30%;">
                <div class="filter-menu active">
                    <label style="color: greenyellow">交换机模式 默认 direct</label>
                    <select name="exchange_type">
                        <?php
                        foreach ($action->getExchangeType() as $k => $value) {
                            if ($k == 1) {
                                echo '<option selected value="' . $k . '">' . $value . '</option>';
                            } else {
                                echo '<option value="' . $k . '">' . $value . '</option>';
                            }
                        } ?>
                    </select>
                    <label style="color: greenyellow">最小进程数</label>
                    <select name="min_consumer">
                        <?php for ($i = 1; $i <= 20; $i++) {
                            echo " <option>" . $i . "</option>";
                        } ?>
                    </select>
                    <label style="color: greenyellow">最大进程数</label>
                    <select name="max_consumer">
                        <?php for ($i = 1; $i <= 20; $i++) {
                            echo " <option>" . $i . "</option>";
                        } ?>
                    </select>
                    <label style="color: greenyellow">失败重试类型</label>
                    <select name="retry">
                        <?php
                        foreach ($action->getRetry() as $k => $value) {
                            $value = implode('、', $value);
                            if ($k == 0) {
                                echo '<option selected value="' . $k . '">' . $value . '</option>';
                            } else {
                                echo '<option value="' . $k . '">' . $value . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <label style="color: greenyellow">是否记录回调日志</label>
                    <select name="is_log">
                        <?php
                        foreach ($action::getLogStatus() as $k => $value) {
                            echo '<option value="' . $k . '">' . $value . '</option>';
                        }
                        ?>
                    </select>

                    <label style="color: greenyellow">是否去重-相同的参数只执行一次</label>
                    <select name="is_repeat">
                        <?php
                        foreach ($action::getRepeatCleanStatus() as $k => $value) {
                            echo '<option value="' . $k . '">' . $value . '</option>';
                        }
                        ?>
                    </select>
                    <label style="color: greenyellow">是否统计消费数量</label>
                    <select name="is_count">
                        <?php
                        foreach ($action::getIsCountStatus() as $k => $value) {
                            echo '<option value="' . $k . '">' . $value . '</option>';
                        }
                        ?>
                    </select>
                    <label style="color: greenyellow">是否使用队列参数</label>
                    <select name="is_queue">
                        <?php
                        foreach ($action::getIsArgQueue() as $k => $value) {
                            echo '<option value="' . $k . '">' . $value . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <br/>
            <span> redis_host：</span>
            <input class="input-bar" placeholder="" name="redis_host" value="<?= $redisConfig['host'] ?? '' ?>"
                   type="text">
            <br/>
            <br/>
            <span> redis_password：</span>
            <input class="input-bar" placeholder="" name="redis_password" value="<?= $redisConfig['password'] ?? '' ?>"
                   type="text">
            <br/>
            <br/>
            <span> redis_database：</span>
            <input class="input-bar" placeholder="" name="redis_database" value="<?= $redisConfig['database'] ?? '' ?>"
                   type="text">
            <br/>
            <br/>
            <span> redis_port：</span>
            <input class="input-bar" placeholder="" name="redis_port" value="<?= $redisConfig['port'] ?? '' ?>"
                   type="text">

            <br/>
            <br/>

            <br/>
            <span> mq_host：</span>
            <input class="input-bar" placeholder="" name="mq_host" value="<?= $mqConfig['mq_host'] ?? '' ?>"
                   type="text">
            <br/>
            <br/>
            <span> mq_vhost：</span>
            <input class="input-bar" placeholder="" name="mq_vhost" value="<?= $mqConfig['mq_vhost'] ?? '' ?>"
                   type="text">
            <br/>
            <br/>

            <span> mq_user：</span>
            <input class="input-bar" placeholder="" name="mq_user" value="<?= $mqConfig['mq_user'] ?? '' ?>"
                   type="text">
            <br/>
            <br/>
            <span> mq_pass：</span>
            <input class="input-bar" placeholder="" name="mq_pass" value="<?= $mqConfig['mq_pass'] ?? '' ?>"
                   type="text">
            <br/>
            <br/>
            <span> mq_port：</span>
            <input class="input-bar" placeholder="" name="mq_port" value="<?= $mqConfig['mq_port'] ?? '' ?>"
                   type="text">
            <br/>
            <br/>
            <span> prefetchCount：</span>
            <input class="input-bar" placeholder="" name="prefetch_count" value="10" type="text">
            <br/>
            <br/>
            <br/>
            <button type="submit" class="app-content-headerButton" style="width: 55%;">保存配置</button>
            <br/>
            <br/>
        </form>
    </div>
</div>
<script src="../static/js/script.js"></script>
</body>
</html>
