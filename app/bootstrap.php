<?php

define('APP_PATH', __DIR__);
define('BASE_PATH', dirname(APP_PATH));

// 报告哪些类型的错误
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/var/log/error.log');

// 程序执行开始时间
defined('START_TIME') or define('START_TIME', microtime(true));
// 程序执行开始内存
defined('START_MEMORY') or define('START_MEMORY', memory_get_usage());

require BASE_PATH . '/vendor/autoload.php';

$envFile = env_file();
try {
    (new Dotenv\Dotenv(BASE_PATH, $envFile))->load(); // dotenv 2
    // Dotenv\Dotenv::create(BASE_PATH, $envFile)->load(); // dotenv 3
} catch (Exception $e) {
    die(BASE_PATH . "/$envFile for test is missing." . PHP_EOL);
}

define('APP_DEBUG', env('APP_DEBUG', false));

// DEBUG 环境显示错误，否则不显示错误
ini_set('display_errors', APP_DEBUG);

// 容器服务配置
require BASE_PATH . '/config/services.php';
