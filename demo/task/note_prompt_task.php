<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/9/12 Time: 23:28
// +----------------------------------------------------------------------
require_once __DIR__ . '/../../vendor/autoload.php';
use \Workerman\Worker;
use \Workerman\Lib\Timer;
use \limx\tools\MyRedis;

$task = new Worker();
// 开启多少个进程运行定时任务，注意多进程并发问题
$task->count = 1;
$task->name = 'NOTE_TASK';

$task->onWorkerStart = function ($task) {
    $config = [];
    $db = [];
    if (file_exists(__DIR__ . '/../../config/redis.php')) {
        $config = include __DIR__ . '/../../config/redis.php';
    }
    if (file_exists(__DIR__ . '/../../config/db.php')) {
        $db = include __DIR__ . '/../../config/db.php';
    }
    $redis = MyRedis::getInstance($config);
    $redis->setPrefix('note_');

    // 每2.5秒执行一次
    $time_interval = 1;
    Timer::add($time_interval, function () use ($db, $redis) {
        $res = $redis->zRange('note_prompt_task', 0, 1);
        if (empty($res)) {
            echo "无任务\n";
            return;
        }
        $note_id = $res[0];
        $score = $redis->zScore('note_prompt_task', $note_id);
        echo $score . "\n";
        if ($score < time()) {
            //发送邮件
            $db['dbname'] = 'note';
            $pdo = \limx\tools\MyPDO::getInstance($db);
            $sql = "SELECT n.msg,u.name,u.email FROM note as n
                LEFT JOIN user as u on n.uid = u.id
                WHERE n.id = {$note_id};";
            $res = $pdo->query($sql);
            if ($res[0]) {
                echo '发送邮件' . "\n";
                $email = $res[0]['email'];
                $name = $res[0]['name'];
                $msg = $res[0]['msg'];

                $mail = new \PHPMailer;
                $mail->SMTPDebug = 3;                               // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = 'smtp.126.com';  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'limingxinleo@126.com';                 // SMTP username
                $mail->Password = 'Xin910123';                           // SMTP password
                $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 465;                                    // TCP port to connect to

                $mail->setFrom('limingxinleo@126.com', '祎信NOTE');
                $mail->addAddress($email, $name);     // Add a recipient
                $mail->isHTML(true);                                  // Set email format to HTML

                $mail->Subject = '祎信NOTE 提示邮件';
                $mail->Body = $msg;
                $mail->AltBody = $msg;

                if (!$mail->send()) {
                    echo 'Message could not be sent.';
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                } else {
                    echo 'Message has been sent';
                }
            }
            //删除任务
            $redis->zRem('note_prompt_task', $note_id);
        }

    });
};

// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}