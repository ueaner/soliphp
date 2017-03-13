<?php

// Composer autoloader
include $config['application']['vendorDir'] . "autoload.php";

// Soli autoloader
$loader = new \Soli\Loader();

// 注册需要自动加载的目录
$loader->registerDirs([
    $config['application']['controllersDir'],
    $config['application']['modelsDir'],
    $config['application']['tasksDir'],
    $config['application']['libraryDir'],
]);

// 执行注册
$loader->register();
