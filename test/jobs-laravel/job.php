<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/9/3 Time: 9:34
// +----------------------------------------------------------------------
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../utils/helper.php';
use \Workerman\Worker;
use \Workerman\Lib\Timer;
use \limx\tools\MyRedis;

$task = new Worker();
// 开启多少个进程运行定时任务，注意多进程并发问题
$task->count = 1;
$task->name = 'ATUO_DEPLOY_TASK';


$task->onWorkerStart = function ($task) {
    $config = [];
    if (file_exists(__DIR__ . '/../../config/db.php')) {
        $config = include __DIR__ . '/../../config/db.php';
    }

    // 每1秒执行一次
    $time_interval = 1;
    Timer::add($time_interval, function () use ($config) {

    });
};

Worker::runAll();