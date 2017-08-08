<?php
// 命令行应用
// 调用：php /path/to/app/cli.php <task> <action>

error_reporting(E_ALL);

define('APP_PATH', __DIR__);
define('BASE_PATH', getenv('BASE_PATH') ?: dirname(APP_PATH));

try {

    // 基础配置
    $config = include APP_PATH . '/config/config.php';

    // 引入自动加载
    include APP_PATH . '/config/loader.php';

    // 引入容器服务配置
    $container = new \Soli\Di\Container();
    include APP_PATH . '/config/services.php';

    $cli = new \Soli\Cli\Application($container);

    $return = $cli->handle();
    echo $return;

} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
