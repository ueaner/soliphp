<?php

// 报告哪些类型的错误
error_reporting(E_ALL);

define('APP_PATH', __DIR__);
define('BASE_PATH', getenv('BASE_PATH') ?: dirname(APP_PATH));

// 程序执行开始时间
defined('START_TIME') or define('START_TIME', microtime(true));
// 程序执行开始内存
defined('START_MEMORY') or define('START_MEMORY', memory_get_usage());

require BASE_PATH . '/vendor/autoload.php';

// 加载配置
$config = require BASE_PATH . '/config/config.php';

// 容器服务配置
require BASE_PATH . '/config/services.php';
