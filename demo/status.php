<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/10/12 Time: 16:51
// +----------------------------------------------------------------------
require_once __DIR__ . '/../vendor/autoload.php';
$arr = [];
foreach (glob(__DIR__ . '/*/*.php') as $task) {
    system("php {$task} status");
}