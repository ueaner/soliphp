<?php
/**
 * 基本配置信息
 */
return [
    'application' => [
        'controllersDir' => APP_PATH . '/controllers/',
        'tasksDir'       => APP_PATH . '/tasks/',
        'modelsDir'      => APP_PATH . '/models/',
        'viewsDir'       => APP_PATH . '/views/',
        'libraryDir'     => APP_PATH . '/library/',
        'logsDir'        => APP_PATH . '/logs/',
        'cacheDir'       => APP_PATH . '/cache/',
        'vendorDir'      => APP_PATH . '/vendor/',
    ],
    'database' => [
        'adapter'     => 'mysql',
        'host'        => 'localhost',
        'port'        => '3306',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'test',
        'charset'     => 'utf8',
    ],
];
