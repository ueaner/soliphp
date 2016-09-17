<?php

// 引入 Soli 框架自动加载器
include BASE_PATH . "/Soli/Loader.php";

$loader = new Soli\Loader();

// 注册需要自动加载的目录，目录下的类将被自动加载
$loader->registerDirs([
    $config['application']['controllersDir'],
    $config['application']['modelsDir'],
    $config['application']['tasksDir'],
    //$config['application']['libraryDir'],
    //$config['application']['vendorDir'] . 'twig/twig/lib/',
]);

$vendorClassmap = $config['application']['vendorDir'] . "composer/autoload_classmap.php";
if (is_readable($vendorClassmap)) {
    $loader->registerClasses(include $vendorClassmap);
}

// 执行注册
$loader->register();
