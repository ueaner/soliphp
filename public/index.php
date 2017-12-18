<?php
/**
 * Web 应用入口
 */
use Soli\Application;

require dirname(__DIR__) . '/app/bootstrap.php';

$app = new Application();

// 处理请求，输出响应内容
$app->handle()->send();

$app->terminate();
