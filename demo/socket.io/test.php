<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/9/3 Time: 10:22
// +----------------------------------------------------------------------
require_once __DIR__ . '/../../vendor/autoload.php';
use Workerman\Worker;
use PHPSocketIO\SocketIO;


$io = new SocketIO(2021);
$io->on('connection', function ($conn) use ($io) {
    echo '连接成功！' . "\n";

    $conn->on('set room', function ($data) use ($io, $conn) {
        echo 'the room is:' . $data . "\n";
        $conn->join($data);
        $conn->emit('test message', 'The Server Set Your Room Is:' . $data);
    });

    $conn->on('test message', function ($data) use ($io, $conn) {
        var_dump($data);
        $io->to($data['room'])->emit('test message', 'Room ' . $data['room'] . ' Another Say:' . $data['data']);
    });

});

//$web = new WebServer('http://0.0.0.0:2022');
//$web->addRoot('localhost', __DIR__ . '/public');

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}