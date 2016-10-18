<?php
/**
 * run with command
 * php start.php start
 */

ini_set('display_errors', 'on');
use Workerman\Worker;

// 检查扩展
if (!extension_loaded('pcntl')) {
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if (!extension_loaded('posix')) {
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

// 标记是全局启动
define('GLOBAL_START', 1);
define('ROOT_PATH', __DIR__);

require_once __DIR__ . '/../vendor/autoload.php';

// 加载所有Applications/*/start.php，以便启动所有服务
foreach (glob(__DIR__ . '/*/*.php') as $start_file) {
    require_once $start_file;
}
// 运行所有服务
Worker::$logFile = ROOT_PATH . '/logs/workerman.log';
Worker::runAll();
