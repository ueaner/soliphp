<?php
/**
 * Web 应用入口
 */
require dirname(__DIR__) . '/app/bootstrap.php';

$app = new \Soli\Web\App();
$app->listen('app', new \App\Events\AppEvents());

// 处理请求，输出响应内容
$app->handle()->send();

$app->terminate();
