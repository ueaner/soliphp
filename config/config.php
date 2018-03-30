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
        'dsn'      => 'mysql:host=localhost;port=3306;dbname=test;charset=utf8',
        'username' => 'root',
        'password' => 'root',
    ],
]);
