<?php
/**
 * 基本配置信息
 */
return [
    'application' => [
        'viewsDir' => BASE_PATH . '/views/',
        'logsDir'  => BASE_PATH . '/var/logs/',
        'cacheDir' => BASE_PATH . '/var/cache/',
    ],
    'database' => [
        'dsn'      => 'mysql:host=localhost;port=3306;dbname=test;charset=utf8',
        'username' => 'root',
        'password' => 'root',
    ],
];
