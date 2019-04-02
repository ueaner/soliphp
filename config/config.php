<?php
/**
 * 基本配置信息
 */
return new \Soli\Config([
    'app' => [
        'viewsDir' => BASE_PATH . '/views/',
        'logDir'   => BASE_PATH . '/var/log/',
        'cacheDir' => BASE_PATH . '/var/cache/',
    ],
    'db' => [
        'dsn'      => env('MYSQL_DSN'),
        'username' => env('MYSQL_USERNAME'),
        'password' => env('MYSQL_PASSWORD'),
    ],
]);
