<?php

use Soli\Di\Container;
use Soli\Dispatcher;
use Soli\Db\Connection as DbConnection;
use Soli\View;
use Soli\View\Engine\Twig as TwigEngine;
use Soli\View\Engine\Smarty as SmartyEngine;
use Soli\Session;
use Soli\Session\Flash;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ChromePHPHandler;

$container = new Container();

// 将配置信息扔进容器
$container->setShared('config', require BASE_PATH . '/config/config.php');

// 配置数据库信息, Model中默认获取的数据库连接标志为"db"
// 可使用不同的服务名称设置不同的数据库连接信息，供 Model 中做多库的选择
$container->setShared('db', function () {
    return new DbConnection($this->config['database']);
});

// 日志记录器
$container->setShared('logger', function () {
    $logFile = $this->config['application']['logsDir'] . '/soli.log';
    $stream = new StreamHandler($logFile, Logger::DEBUG);

    // 创建应用的主要日志服务实例
    $logger = new Logger('production');
    $logger->pushHandler($stream);

    return $logger;
});

// 路由
$container->setShared('router', function () {
    $routesConfig = include BASE_PATH . '/config/routes.php';

    $router = new \Soli\Router();

    $router->setDefaults([
        // 控制器的命名空间
        'namespace' => "App\\Controllers\\"
    ]);

    foreach ($routesConfig as $route) {
        list($methods, $pattern, $handler) = $route;
        $router->map($methods, $pattern, $handler);
    }
    return $router;
});

// TwigEngine
$container->setShared('view', function () {
    $config = $this->config;

    $view = new View();
    $view->setViewsDir($config['application']['viewsDir']);
    $view->setViewExtension('.twig');

    // 通过匿名函数来设置模版引擎，延迟对模版引擎的实例化
    $view->setEngine(function () use ($config, $view) {
        $engine = new TwigEngine($view);
        // 开启 debug 不进行缓存
        //$engine->setDebug(true);
        $engine->setCacheDir($config['application']['cacheDir'] . 'twig');
        return $engine;
    });

    return $view;
});

// Session
$container->setShared('session', function () {
    $session = new Session();
    $session->start();

    return $session;
});

// 闪存消息
$container->setShared('flash', function () {
    return new Flash([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ]);
});
