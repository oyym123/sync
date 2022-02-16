<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? '' ?></title>
    <link rel="stylesheet" href="../static/css/normalize.min.css">
    <link rel="stylesheet" href="../static/css/style.css">
</head>
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
                <a href="#">
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
                <a href="">
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
            <li class="sidebar-list-item active">
                <a href="?action=tcc_view">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-shopping-bag">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>Tcc事务处理</span>
                </a>
            </li>
            <li class="sidebar-list-item active">
                <a href="?action=systemRetryLog" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-shopping-bag">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>重试日志</span>
                </a>
            </li>
            <li class="sidebar-list-item active">
                <a href="?action=systemErrorLog" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-shopping-bag">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>回调执行错误日志</span>
                </a>
            </li>
            <li class="sidebar-list-item active">
                <a href="?action=smcActionLog" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-shopping-bag">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>操作日志</span>
                </a>
            </li>
            <li class="sidebar-list-item active">
                <a href="">

                    <span></span>
                </a>
            </li>
    </div>
    <div class="app-content">
        <div>
            <h1 class="app-content-headerText"><?= $title ?? '' ?> </h1>
            <div class="app-content-header" style="right: 50%;position: relative;">
                <button class="mode-switch" title="Switch Theme">
                    <svg class="moon" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                         stroke-width="2" width="24" height="24" viewbox="0 0 24 24">
                        <defs></defs>
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path>
                    </svg>
                </button>
                <a href="../action.php?action=add" target="_blank">
                    <button class="app-content-headerButton">新增配置</button>
                </a>
            </div>
            <label>
                <input class="search-bar" placeholder="Search..." type="text" name="search" style="right: 80%">
            </label>
            <div class="app-content-actions" style="right: 50%;position: relative;">
                <div class="app-content-actions-wrapper">
                    <div class="filter-button-wrapper">
                        <button class="action-button filter jsFilter">
                        </button>
                    </div>

                    <button id="list_view" class="action-button list active" title="List View">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewbox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="feather feather-list">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </button>

                    <button id="grid_view" class="action-button grid" title="Grid View">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewbox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="feather feather-grid">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </button>

                </div>

            </div>

            <div class="products-area-wrapper tableView" style="width: 230%;position: relative;">
                <div class="products-header">
                    <?php
                    $title = [
                        '序号' => 1.8,
                        '状态' => 1,
                        '任务名称' => 5.7,
                        '监听日志' => 2,
                        '回调日志' => 2,
                        '成功日志' => 2,
                        '失败日志' => 2,
                        '重试日志' => 2,
                        '任务英文名' => 6,
                        '交换机' => 6,
                        '队列' => 6,
                        '最小进程数' => 3,
                        '最大进程数' => 3,
                        '回调方法' => 10,
                        '最后修改时间' => 5,
                        '创建时间' => 4.8,
                        '修改' => 2.3,
                        '重启' => 2,
                        '启动' => 2,
                        '暂停' => 2,
                    ];

                    foreach ($title as $name => $num) {
                        $style = 'style="width:' . $num . '%;"';
                        ?>
                        <div class="product-cell" <?= $style ?>><?= $name ?></div>
                        <?php
                    }
                    ?>
                    <div class="product-cell" style="width:2%">状态</div>
                    <div class="product-cell" style="width:6%">任务名称</div>
                    <div class="product-cell" style="width:10%">重试时间</div>
                </div>

                <?php
                foreach ((new Action())->allInfo() ?? [] as $datum) {
                $funcArr = explode('/', $datum['call_back_func']);
                $lastFunc = explode(' ', $funcArr[count($funcArr) - 1])[0];
                $listen = '?action=listenLog&id=' . $datum['id'];
                $success = '?action=successLog&id=' . $datum['id'];
                $error = '?action=errorLog&id=' . $datum['id'];
                $retry = '?action=retryLog&id=' . $datum['id'];
                $callback = '?action=callbackLog&id=' . $datum['id'];
                $exchangeUrl = 'http://' . $datum['mq_host'] . ':15672/#/exchanges/%2F/';
                $queueUrl = 'http://' . $datum['mq_host'] . ':15672/#/queues/%2F/';
                ?>

                <div class="products-row">
                    <div class="product-cell image">
                        <img src="static/picture/p<?= rand(1, 7) ?>.jpg"
                             alt="product">
                    </div>
                    <input type="hidden" name="id" value="<?= $datum['id'] ?>">
                    <div class="product-cell">
                        <div class="cell-label"> 序号:</div><?= $datum['id'] ?>
                    </div>
                    <div class="product-cell status-cell"
                    ">
                    <div class="cell-label">状态:</div>
                    <!--                        <span class="status disabled">Disabled</span>-->
                    <span class="status <?php echo $datum['status'] == 1 ? 'active' : 'disabled' ?>">
                                <?php echo $datum['status'] == 1 ? '启用' : '停止' ?></span>
                </div>
                <div class="product-cell" style="width: 5.7%">
                    <div class="cell-label"> 任务名称:</div>
                    <strong><?php echo $datum['name'] ?? '' ?></strong>
                </div>
                <div class="product-cell" style="width: 2%;"><span class="cell-label">  监听日志:</span>
                    <a href="<?= $listen ?>" target="_blank">
                        <button class="app-content-headerButton"
                                style="background-color: orange;width: 5.5rem;">监听日志
                        </button>
                    </a>
                </div>
                <div class="product-cell" style="width: 2%"><span class="cell-label">  回调日志:</span>
                    <a href="<?= $callback ?>" target="_blank">
                        <button class="app-content-headerButton"
                                style="background-color: rebeccapurple;width: 5.5rem;">回调日志
                        </button>
                    </a>
                </div>
                <div class="product-cell" style="width: 2%"><span class="cell-label">  成功日志:</span>
                    <a href="<?= $success ?>" target="_blank">
                        <button class="app-content-headerButton"
                                style="background-color: #009900;width: 5.5rem;">成功日志
                        </button>
                    </a>
                </div>
                <div class="product-cell" style="width: 2%"><span class="cell-label">  失败日志:</span>
                    <a href="<?= $error ?>" target="_blank">
                        <button class="app-content-headerButton"
                                style="background-color: red;width: 5.5rem;">
                            失败日志
                        </button>
                    </a>
                </div>
                <div class="product-cell"><span class="cell-label">  重试:</span>
                    <a href="<?= $retry ?>" target="_blank">
                        <button class="app-content-headerButton" style="background-color: #002166;width: 5.5rem;">重试日志
                        </button>
                    </a>
                </div>
                <div class="product-cell" style="width: 6%"><span
                            class="cell-label">  任务英文名:</span><?= $datum['mq_master_name'] ?>
                </div>

                <div class="product-cell" style="width: 6%"><span
                            class="cell-label">  交换机:</span>
                    <a style="color: wheat" href="<?= $exchangeUrl . $datum['mq_exchange'] ?>"
                       target="_blank">
                        <?= $datum['mq_exchange'] ?>
                    </a>
                </div>
                <div class="product-cell" style="width: 6%"><span
                            class="cell-label">  队列:</span>
                    <a style="color: wheat" href="<?= $queueUrl . $datum['queue_name'] ?>" target="_blank">
                        <?= $datum['queue_name'] ?>
                    </a>
                </div>
                <div class="product-cell" style="width: 3%"><span
                            class="cell-label"> 最小进程数:</span><?= $datum['min_consumer'] ?></div>
                <div class="product-cell" style="width: 3%"><span
                            class="cell-label"> 最大进程数:</span><?= $datum['max_consumer'] ?>
                </div>
                <div class="product-cell" style="width: 10%"><span
                            class="cell-label">  回调方法:</span><?= $datum['call_back_func'] ?>
                </div>
                <div class="product-cell" style="width: 5%"><span
                            class="cell-label">  最后修改时间:</span><?= $datum['updated_at'] ?? '' ?>
                </div>
                <div class="product-cell" style="width: 5%"><span
                            class="cell-label">  创建时间:</span><?= $datum['created_at'] ?? '' ?> </div>
                <div class="product-cell" style="width: 2%"><span class="cell-label">  操作1:</span>
                    <a href="../action.php?action=update&id=<?= $datum['id'] ?>" target="_blank">
                        <button class="app-content-headerButton"
                                style="background-color: royalblue;width: 3.8rem;">修改
                        </button>
                    </a>
                </div>

                <div class="product-cell" style="width: 2%"><span class="cell-label">  操作2:</span>
                    <a href="../action.php?id=<?= $datum['id'] ?>&action=restart" target="_blank">
                        <button class="app-content-headerButton"
                                style="background-color: orange;width: 3.8rem;">重启
                        </button>
                    </a>
                </div>

                <div class="product-cell" style="width: 2%"><span class="cell-label">  操作3:</span>
                    <a href="../action.php?id=<?= $datum['id'] ?>&action=start" target="_blank">
                        <button type="button" class="app-content-headerButton"
                                style="background-color: green;width: 3.8rem;">
                            启动
                        </button>
                    </a>
                </div>
                <div class="product-cell" style="width: 2%"><span class="cell-label">  操作4:</span>
                    <a href="../action.php?id=<?= $datum['id'] ?>&action=stop" target="_blank">
                        <button class="app-content-headerButton"
                                style="background-color: red;width: 3.8rem;">
                            暂停
                        </button>
                    </a>
                </div>

                <div class="product-cell status-cell" style="width: 1.5%">
                    <span class="cell-label">状态:</span>
                    <!--                        <span class="status disabled">Disabled</span>-->
                    <span class="status <?php echo $datum['status'] == 1 ? 'active' : 'disabled' ?>">
                                <?php echo $datum['status'] == 1 ? '启用' : '停止' ?>
                            </span>
                </div>

                <div class="product-cell" style="width: 6%">
                    <span class="cell-label" style="right: 50%;">  任务名称:</span>
                    <strong><?= $datum['name'] ?></strong>
                </div>
                <div class="product-cell" style="width: 10%"><span
                            class="cell-label">  重试时间:</span><?= implode('、', (new Action())->getRetry($datum['retry'])) ?>
                </div>
            </div>
            <?php
            } ?>

        </div>
    </div>
    <script src="../static/js/script.js"></script>
    <script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <script>
        //回车事件绑定
        $('.search-bar').bind('keyup', function (event) {
            if (event.keyCode == "13") {
                //回车执行查询
                window.location.href = "/systems/smc?keywords=" + $("input[name='search']").val();
            }
        });

        $("#grid_view").click(function () {
            $(".product-cell").removeAttr("style");
        });

        $("#list_view").click(function () {
            location.reload();
        });
        // $(".products-row").click(function (e) {
        //     e.stopPropagation()
        //     window.open("/systems/smc/cAu?id=" + $("input[name='id']").val());
        // });

    </script>
</body>
</html>
