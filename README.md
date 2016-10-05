Soli
--------------------

Soli 是一个轻量级的 PHP 框架，参考了 [Phalcon]
框架的设计，意在松耦合、可扩展、简洁易用。

## 环境需求

- PHP 5.5+
- 视图模块需要 [composer] 或者手动下载，目前支持 [Twig] 和 [Smarty]

## 提供的功能

MVC、[依赖注入]、[事件管理]、[自动加载]、[闪存消息]、[模版引擎]（[Twig]、[Smarty]）
[日志]、[命令行应用]等功能

## 请求的生命周期

![Soli请求的生命周期][Soli_lifecycle]

从上图我们可以看到 Soli 请求的处理流程为：

[Application] 接收 HTTP [请求]之后，交给控制器[调度器]，[控制器]处理应用程序的业务流程，
调用相应的[模型]和[视图]，并将处理结果通过调度器返给 Application 做最终的 HTTP [响应]封装。

而开发者拿到响应之后，就可以做输出等操作。

Soli 通过[依赖注入]容器提供的[组件]机制，可以供开发者在开发组件时方便的使用容器中的各种服务。

Soli 的[事件管理]器允许开发者通过创建"钩子"拦截框架或应用中的部分组件操作。
以便获得状态信息、操纵数据或者改变某个组件进程中的执行流向。

## NGiNX 配置

```
upstream php-fpm
{
    server unix:/tmp/php-fpm.sock;
}

server
{
    listen 80;
    server_name www.soliphp.com;
    index index.html index.php;
    root  /path/to/soli/app/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include        fastcgi.conf;
        fastcgi_pass   php-fpm;
    }
}
```

## Apache 配置

```
# Apache 2.4

<VirtualHost *:80>

    ServerAdmin admin@example.host
    DocumentRoot "/path/to/soli/app/public"
    DirectoryIndex index.php
    ServerName www.soliphp.com

    <Directory "/path/to/soli/app/public">
        Options All
        AllowOverride All
        Allow from all
        Require all granted

        RewriteEngine on
        RedirectMatch 403 /\..*$
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.php
    </Directory>

</VirtualHost>
```

## 应用程序结构

    .
    ├── app                           应用程序目录
    │   ├── cache                     文件缓存目录
    │   ├── cli.php                   命令行应用入口文件
    │   ├── config                    配置文件目录
    │   │   ├── config.php            基本配置文件
    │   │   ├── loader.php            自动加载配置文件
    │   │   └── services.php          容器服务配置文件
    │   ├── controllers               WEB应用控制器文件目录
    │   │   ├── ControllerBase.php
    │   │   ├── ErrorController.php
    │   │   └── UserController.php
    │   ├── library                   自定义库文件目录
    │   ├── logs                      日志文件目录
    │   ├── models                    模型文件目录
    │   │   └── User.php
    │   ├── public                    公共可被访问的文件目录
    │   │   ├── favicon.ico
    │   │   ├── index.php             WEB程序入口文件
    │   │   └── static                静态文件目录
    │   ├── tasks                     命令行应用控制器目录
    │   │   └── ResqueTask.php
    │   ├── vendor                    composer第三方包目录
    │   └── views                     视图文件目录
    │       ├── error
    │       ├── user                  UserController 对应的视图目录
    │       │   └── view.twig         viewAction 对应的视图文件
    │       └── base.twig
    └── Soli                          Soli框架目录

## 使用

#### 基本配置信息

基本配置信息默认存放在 `app/config/config.php` 文件：

    // 基本配置信息
    $config = array(
        // 应用
        'application' => array(
            'controllersDir' => __DIR__ . '/../controllers/',
            'tasksDir'       => __DIR__ . '/../tasks/',
            'modelsDir'      => __DIR__ . '/../models/',
            'viewsDir'       => __DIR__ . '/../views/',
            'libraryDir'     => __DIR__ . '/../library/',
            'cacheDir'       => __DIR__ . '/../cache/',
            'vendorDir'      => __DIR__ . '/../vendor/',
            'logFile'        => __DIR__ . '/../logs/' . date('Ym') . '.log',
        ),
        // 数据库
        'database' => array(
            'adapter'     => 'mysql',
            'host'        => '192.168.56.101',
            'port'        => '3306',
            'username'    => 'root',
            'password'    => 'root',
            'dbname'      => 'soli',
        ),
        // 更多...
    );

#### 自动加载配置

Soli 自动加载器符合 [PSR-4] 规范，我们在类的命名和文件的命名上也需尽量基于 [PSR-4]
规范进行使用。

自动加载同时支持注册目录、命名空间、classmap 和以下划线连接的类名。

自动加载配置默认存放在 `app/config/loader.php` 文件：

    // 引入 Soli 框架自动加载器
    include __DIR__ . "/../../Soli/Loader.php";

    $loader = new Soli\Loader();
    // 注册需要自动加载的目录，目录下的类将被自动加载
    $loader->registerDirs(array(
        $config['application']['controllersDir'],
        $config['application']['modelsDir'],
        $config['application']['tasksDir'],
        $config['application']['libraryDir'],
    ));
    // 执行注册
    $loader->register();

注册命名空间：

    $loader->registerNamespaces([
        'Example\Base' => 'vendor/example/base/',
        'Example' => 'vendor/example/'
    ]);

注册多个类(classmap)，如我们将 composer 生成的 classmap 注册进来：

    $vendorClassmap = __DIR__ . "/../vendor/composer/autoload_classmap.php";
    $loader->registerClasses(include $vendorClassmap);

注册以下划线连接的类：

    $loader->registerDirs(array(
        $config['application']['vendorDir'] . 'twig/twig/lib/',
    ));

#### 容器服务配置

[依赖注入]容器的目的为了降低代码的耦合度，提高应用的可维护性。
把组件之间的依赖，转换为对容器的依赖，通过容器进行服务管理(创建、配置和定位)。

容器服务的配置默认存放在 `app/config/services.php` 文件：

    use Soli\Di\Container as DiContainer;
    use Soli\Db;
    use Soli\Logger;
    use Soli\View;
    use Soli\View\Engine\Twig as TwigEngine;
    use Soli\View\Engine\Smarty as SmartyEngine;

    $di = new DiContainer();

    // 配置数据库信息, Model中默认获取的数据库连接标志为"db"
    // 可使用不同的服务名称设置不同的数据库连接信息，供 Model 中做多库的选择
    $di->set('db', function () use ($config) {
        return new Db($config['database']);
    });

    // 日志记录器
    $di->set('logger', function () use ($config) {
        return new Logger($config['application']['logFile']);
    });

    // TwigEngine
    $di->set('view', function () use ($config) {
        $view = new View();
        $view->setViewsDir($config['application']['viewsDir']);
        $view->setViewExtension('.twig');

        // 通过匿名函数来设置模版引擎，延迟对模版引擎的实例化
        $view->setEngine(function () use ($config, $view) {
            $engine = new TwigEngine($view);
            // 开启 debug 不进行缓存
            $engine->setDebug(true);
            $engine->setCacheDir($config['application']['cacheDir'] . 'twig');
            return $engine;
        });

        return $view;
    });

    // 如果使用 Smarty 的话，可进行如下设置：

    // SmartyEngine
    $di->set('view', function () use ($config) {
        $view = new View();
        $view->setViewsDir($config['application']['viewsDir']);
        $view->setViewExtension('.tpl');

        // 通过匿名函数来设置模版引擎，延迟对模版引擎的实例化
        $view->setEngine(function () use ($config, $view) {
            $engine = new SmartyEngine($view);
            // 开启 debug 不进行缓存
            $engine->setDebug(true);
            $engine->setOptions(array(
                'compile_dir'    => $config['application']['cacheDir'] . 'templates_c',
                'cache_dir'      => $config['application']['cacheDir'] . 'templates',
                'debugging'      => false,
                'caching'        => true,
                'caching_type'   => 'file',
                'cache_lifetime' => 86400,
            ));
            return $engine;
        });

        return $view;
    });

另外 [Soli\Application] 默认注册了以下常用服务，供控制器和自定义组件直接使用：

 服务名称   | 介绍             | 默认                 | 是否是shared服务
 -----------|------------------|----------------------|-----------------
 dispatcher | 控制器调度服务   | [Soli\Dispatcher]    | 是
 request    | HTTP请求环境服务 | [Soli\Http\Request]  | 是
 response   | HTTP响应环境服务 | [Soli\Http\Response] | 是
 session    | Session服务      | [Soli\Session]       | 是
 filter     | 过滤器服务       | [Soli\Filter]        | 是
 flash      | 闪存消息服务     | [Soli\Session\Flash] | 是

允许开发者自定义同名的服务覆盖以上默认的服务。

#### 入口文件

Web 应用程序的入口文件默认存放在 `app/public/index.php`，看起来像下面这样：

    try {
        $config = require __DIR__ . '/../config/config.php';
        require __DIR__ . '/../config/loader.php';
        require __DIR__ . '/../config/services.php';

        $app = new \Soli\Application($di);

        // Handle the request
        $response = $app->handle();

        // 输出响应内容
        echo $response->getContent();
    } catch (\Exception $e) {
        echo $e->getMessage();
    }

#### 控制器

[控制器]类默认以"Controller"为后缀，action 默认以"Action"为后缀。

控制器可以通过访问属性的方式访问所有注册到容器中的服务。

    use Soli\Controller;

    class UserController extends Controller
    {
        /**
         * 用户详情
         *
         * 自动渲染 views/user/view.twig 视图
         */
        public function viewAction()
        {
            // 这里便使用了容器服务的注入机制，直接调用容器中的 request 服务
            $uid = $this->request->getQuery('uid', 'int');
            $user = User::findById($uid);
            // 这里调用了容器中的 view 服务，设置一个模版变量
            $this->view->setVar('user', $user);
        }
    }

#### 模型

Soli [模型]仅仅提供了操作数据库的一些常用方法，并没有去实现 ORM，
这是由我们的数据来源和项目架构决定的，有可能你的数据是来自远程接口，
也有可能你更习惯使用 [Doctrine] 呢。
Soli 尊重开发者在不同应用场景下的选择和使用习惯，提供了易于扩展的方法，
让你去实现针对团队和实际需求的数据层。

使用模型：

    use Soli\ModelExtra as Model;

    class User extends Model
    {
    }

这里外部在调用 User 模型时默认会调用容器中以"db"命名的服务，且操作的表名为"user"。

如果需要指定其它数据库连接服务，使用以下方式：

    public function initialize()
    {
        // 设置当前模型的数据库连接服务
        $this->connectionService = 'db_service_name';
    }

    或者是这样：

    /**
     * 获取当前模型的数据库连接服务
     */
    public function connectionService()
    {
        return 'db_service_name';
    }

由于数据库连接服务可以被指定，所以自然而然的支持多数据库操作。

模型会自动将类名的驼峰格式转换为对应表名的下划线格式，
如 RememberToken 模型默认转换后操作的表名为 remember_token。

我们也可以手动指定表名：

    /**
     * 当前模型操作的表名
     */
    public function tableName()
    {
        return 'db_user';
    }

Soli 模型支持的方法请移步 [Soli\Model] 和 [Soli\ModelExtra]。

#### 视图

[视图]文件存放在 views 目录下，控制器与视图对应关系的目录结构为：

    ├── app                           应用程序目录
        ├── controllers               WEB应用控制器文件目录
        │   └── UserController.php
        └── views                     视图文件目录
            └── user                  UserController 对应的视图目录
                └── view.twig         viewAction 对应的视图文件

控制器 app/controllers/UserController.php：

    class UserController extends \Soli\Controller
    {
        public function viewAction()
        {
            $uid = $this->request->getQuery('uid', 'int');
            $user = User::findById($uid);
            $this->view->setVar(user, $user);
            $this->flash->notice("user info");
        }
    }

视图文件 app/views/user/view.twig，这里以 twig 模版引擎为例：

    用户ID：{{ user.user_id }}
    用户名：{{ user.username }}

    {{ flash.output() }}

更多视图的使用方法，请移步 [Soli\View]。

感谢您的阅读。

[Soli_lifecycle]: Soli_lifecycle.png
[Phalcon]: https://phalconphp.com/
[composer]: https://getcomposer.org/
[Twig]: http://twig.sensiolabs.org/
[Smarty]: http://www.smarty.net/
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[Doctrine]: http://www.doctrine-project.org/
[Soli\Model]: http://soli-api.aboutc.net/Soli/Model.html "模型"
[Soli\ModelExtra]: http://soli-api.aboutc.net/Soli/Model.html "模型扩展方法"
[Soli\View]: http://soli-api.aboutc.net/Soli/View.html "视图"
[Soli\Application]: http://soli-api.aboutc.net/Soli/Application.html "应用"
[Application]: http://soli-api.aboutc.net/Soli/Application.html "应用"
[Soli\Dispatcher]: http://soli-api.aboutc.net/Soli/Dispatcher.html "控制器调度器"
[调度器]: http://soli-api.aboutc.net/Soli/Dispatcher.html "控制器调度器"
[Soli\Http\Request]: http://soli-api.aboutc.net/Soli/Http/Request.html "HTTP请求环境"
[请求]: http://soli-api.aboutc.net/Soli/Http/Request.html "HTTP请求环境"
[Soli\Http\Response]: http://soli-api.aboutc.net/Soli/Http/Response.html "HTTP响应环境"
[响应]: http://soli-api.aboutc.net/Soli/Http/Response.html "HTTP响应环境"
[Soli\Session]: http://soli-api.aboutc.net/Soli/Session.html "会话"
[Soli\Filter]: http://soli-api.aboutc.net/Soli/Filter.html "过滤与清理"
[Soli\Session\Flash]: http://soli-api.aboutc.net/Soli/Session/Flash.html "闪存消息"
[依赖注入]: http://soli-api.aboutc.net/Soli/Di.html
[事件管理]: http://soli-api.aboutc.net/Soli/Events.html
[自动加载]: http://soli-api.aboutc.net/Soli/Loader.html
[闪存消息]: http://soli-api.aboutc.net/Soli/Session/Flash.html
[模版引擎]: http://soli-api.aboutc.net/Soli/View/Engine.html
[日志]: http://soli-api.aboutc.net/Soli/Logger.html
[命令行应用]: http://soli-api.aboutc.net/Soli/Cli.html
[控制器]: http://soli-api.aboutc.net/Soli/Controller.html
[模型]: http://soli-api.aboutc.net/Soli/Model.html
[视图]: http://soli-api.aboutc.net/Soli/View.html
[组件]: http://soli-api.aboutc.net/Soli/Component.html
