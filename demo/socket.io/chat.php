<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/9/3 Time: 11:17
// +----------------------------------------------------------------------
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/function/helper.php';
use Workerman\Worker;
use PHPSocketIO\SocketIO;


$io = new SocketIO(11521);
$io->on('connection', function ($conn) use ($io) {

    echo "conn success\n";
    $conn->emit('conn success', '连接成功');

    $conn->on('in room', function ($room_id) use ($io, $conn) {
        echo 'the room is:' . $room_id . "\n";
        $json = ['action' => 'msg', 'name' => '系统消息', 'msg' => "聊天室进来新人啦"];
        $io->to($room_id)->emit('broadcast', toJson($json));
        $conn->join($room_id);
        $json = ['action' => 'msg', 'name' => '系统消息', 'msg' => "您已成功进入聊天室"];
        $conn->emit('broadcast', toJson($json));
        unset($json);
    });

    $conn->on('send message', function ($data) use ($io, $conn) {
        $json = ['action' => 'msg', 'name' => $data['name'], 'msg' => "{$data['msg']}"];
        $io->to($data['room'])->emit('broadcast', toJson($json));
        unset($json);
    });

    $conn->on('test', function ($data) use ($io, $conn) {
        var_dump($data);
        $json = ['action' => 'msg', 'msg' => "{$data['data']}"];
        $io->to($data['room'])->emit('broadcast', toJson($json));
        unset($json);
    });

});

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}