<?php
/**
 * 针对命令行的服务配置文件
 */

use Soli\Console\Dispatcher as ConsoleDispatcher;

// 调度器
$container->set('dispatcher', function () {
    $dispatcher = new ConsoleDispatcher();
    // 设置控制器的命名空间
    $dispatcher->setNamespaceName("App\\Console\\");
    return $dispatcher;
});
