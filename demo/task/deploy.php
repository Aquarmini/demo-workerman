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
    if (file_exists(__DIR__ . '/../../config/redis.php')) {
        $config = include __DIR__ . '/../../config/redis.php';
    }
    $redis = MyRedis::getInstance($config);
    $redis->setPrefix('deploy');

    // 每1秒执行一次
    $time_interval = 1;
    Timer::add($time_interval, function () use ($config, $redis) {
        //获取文件大小
        $fileDir = '/_html/zips';
        $tp5Dir = '/_html/html/tp5';
        $res = [];
        traverse($fileDir, $res, 'zip');

        if (!empty($res)) {
            foreach ($res as $i => $v) {
                if (empty($redis->keys(md5($v)))) {
                    $redis->set(md5($v), filesize($v));
                } else {
                    $fileSize = filesize($v);
                    echo $v . $fileSize . "\n";
                    if ($fileSize == $redis->get(md5($v))) {
                        //加压缩文件
                        $zip = new ZipArchive();
                        // open archive
                        if ($zip->open($v) !== TRUE) {
                            break;
                        }
                        // extract contents to destination directory
                        $zip->extractTo($tp5Dir);
                        // close archive
                        $zip->close();
                        //删除文件
                        unlink($v);
                        //清除redis
                        $redis->del(md5($v));
                    } else {
                        $redis->set(md5($v), filesize($v));
                    }
                }
            }
        }
    });
};

// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}