<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/9/3 Time: 9:53
// +----------------------------------------------------------------------
require_once __DIR__ . '/../../vendor/autoload.php';
use Workerman\Worker;
use PHPSocketIO\SocketIO;

// listen port 2020 for socket.io client
$io = new SocketIO(11520);
$io->on('connection', function ($socket) {
    $socket->addedUser = false;
    // when the client emits 'new message', this listens and executes
    $socket->on('new message', function ($data) use ($socket) {
        // we tell the client to execute 'new message'
        $socket->broadcast->emit('new message', array(
            'username' => $socket->username,
            'message' => $data
        ));
    });
    // when the client emits 'add user', this listens and executes
    $socket->on('add user', function ($username) use ($socket) {
        global $usernames, $numUsers;
        // we store the username in the socket session for this client
        $socket->username = $username;
        // add the client's username to the global list
        $usernames[$username] = $username;
        ++$numUsers;
        $socket->addedUser = true;
        $socket->emit('login', array(
            'numUsers' => $numUsers
        ));
        // echo globally (all clients) that a person has connected
        $socket->broadcast->emit('user joined', array(
            'username' => $socket->username,
            'numUsers' => $numUsers
        ));
    });
    // when the client emits 'typing', we broadcast it to others
    $socket->on('typing', function () use ($socket) {
        $socket->broadcast->emit('typing', array(
            'username' => $socket->username
        ));
    });
    // when the client emits 'stop typing', we broadcast it to others
    $socket->on('stop typing', function () use ($socket) {
        $socket->broadcast->emit('stop typing', array(
            'username' => $socket->username
        ));
    });
    // when the user disconnects.. perform this
    $socket->on('disconnect', function () use ($socket) {
        global $usernames, $numUsers;
        // remove the username from global usernames list
        if ($socket->addedUser) {
            unset($usernames[$socket->username]);
            --$numUsers;
            // echo globally that this client has left
            $socket->broadcast->emit('user left', array(
                'username' => $socket->username,
                'numUsers' => $numUsers
            ));
        }
    });
});

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}