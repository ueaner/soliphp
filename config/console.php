<?php
/**
 * 针对命令行的服务配置文件
 */

use Soli\Console\Router as ConsoleRouter;

// 调度器
$container->set('router', ConsoleRouter::class);
