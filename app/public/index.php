<?php

error_reporting(E_ALL);

define('APP_PATH', dirname(__DIR__));
define('BASE_PATH', getenv('BASE_PATH') ?: dirname(APP_PATH));

try {

    // 基础配置
    $config = include APP_PATH . '/config/config.php';

    // 引入自动加载
    include APP_PATH . '/config/loader.php';

    // 引入容器服务配置
    $container = new \Soli\Di\Container();
    include APP_PATH . '/config/services.php';

    // 处理请求
    $application = new \Soli\Application($container);

    // 输出响应内容
    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
