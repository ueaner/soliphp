Soli PHP Framework
--------------------

Soli 是一个轻量级的 PHP 框架，参考了 [Phalcon]
框架的设计，意在松耦合、可扩展、简洁易用。

## 环境需求

- PHP 7.0+
- 视图模块需要 [composer] 或者手动下载，目前支持 [Twig] 和 [Smarty]

## 提供的功能

MVC、[依赖注入]、[事件管理]、[闪存消息]、[模版引擎]（[Twig]、[Smarty]）
[路由]、[命令行应用]等功能

## 请求的生命周期

![Soli请求的生命周期][Soli_lifecycle]

从上图我们可以看到 Soli 请求的处理流程为：

[Application] 将接收到的 HTTP [请求]交给[路由]处理，并将路由结果交给控制器[调度器]；
[控制器]处理应用程序的业务逻辑，调用相应的[模型]和[视图]，并将处理结果通过调度器返给 Application 做最终的 HTTP [响应]封装。

另外，Soli 通过[依赖注入]容器提供的[组件]机制，可以供开发者在开发组件时方便的使用容器中的各种服务。

Soli 的[事件管理]器允许开发者通过创建"钩子"拦截框架或应用中的部分组件操作。
以便获得状态信息、操纵数据或者改变某个组件进程中的执行流向。

## 快速运行当前项目

    $ composer create-project soliphp/soliphp my-project
    $ php -S localhost:8000 -t my-project/public

浏览器访问 [http://localhost:8000/].

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
    root  /path/to/soliphp/public;

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
    DocumentRoot "/path/to/soliphp/public"
    DirectoryIndex index.php
    ServerName www.soliphp.com

    <Directory "/path/to/soliphp/public">
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

    ├── app                          应用程序目录
    │   ├── Console                  命令行应用控制器目录
    │   │   └── Demo.php             Demo命令
    │   ├── Controllers              WEB应用控制器目录
    │   │   ├── Controller.php       控制器基类
    │   │   └── IndexController.php  默认控制器
    │   ├── Models                   模型文件目录
    │   └── bootstrap.php
    ├── composer.json                Composer配置文件
    ├── config                       配置文件目录
    │   ├── config.php               基础配置文件
    │   ├── console.php              针对命令行的容器服务配置文件
    │   ├── routes.php               路由配置文件
    │   └── services.php             容器服务配置文件
    ├── console                      命令行应用入口文件
    ├── public                       公共可被访问的文件目录
    │   ├── css
    │   ├── img
    │   ├── index.php                WEB程序入口文件
    │   └── js
    ├── var                          生成的文件目录
    │   ├── cache                    缓存文件目录
    │   └── log                      日志文件目录
    └── views                        视图文件目录
        └── index                    IndexController 对应的视图目录
            └── index.twig           index 函数对应的视图文件

目录结构并非固定不变，可以依据实际项目需要和团队开发习惯，约定目录结构，定义和表达每个目录的含义。

## 使用

`bootstrap.php` 中定义了两个基本的常量：

    APP_PATH   对应 app 目录
    BASE_PATH  项目根目录

#### 基本配置信息

基本配置信息默认存放在 `config/config.php` 文件：

    // 基本配置信息
    $config = array(
        // 应用
        'app' => array(
            'viewsDir' => BASE_PATH . '/views/',
            'logDir'   => BASE_PATH . '/var/log/',
            'cacheDir' => BASE_PATH . '/var/cache/',
        ),
        // 数据库
        'db' => array(
            'dsn'      => 'mysql:host=localhost;port=3306;dbname=test;charset=utf8',
            'username' => 'root',
            'password' => 'root',
        ),
        // 更多...
    );

#### 自动加载配置

[composer] 是一个优秀的包管理工具，也是一种趋势，所以 Soli 使用 composer 作自动加载和依赖管理。

在 composer.json 中配置了 app 目录作为 App 开头的命名空间：

    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }

所以在 app 目录下按 PSR-4 规则定义的类，在调用时都可以被自动加载，
像 Controllers 和 Console 目录那样。

#### 容器服务配置

[依赖注入]容器的目的为了降低代码的耦合度，提高应用的可维护性。
把组件之间的依赖，转换为对容器的依赖，通过容器进行服务管理(创建、配置和定位)。

容器服务的配置默认存放在 `config/services.php` 文件：

    use Soli\Di\Container;
    use Soli\Db\Connection as DbConnection;
    use Soli\Logger;
    use Soli\View;
    use Soli\View\Engine\Twig as TwigEngine;
    use Soli\View\Engine\Smarty as SmartyEngine;

    $container = new Container();

    // 将配置信息扔进容器
    $container->set('config', require BASE_PATH . '/config/config.php');

    // 配置数据库信息, Model中默认获取的数据库连接标志为"db"
    // 可使用不同的服务名称设置不同的数据库连接信息，供 Model 中做多库的选择
    $container->set('db', function () {
        return new DbConnection($this->config->db);
    });

    // 路由
    $container->set('router', function () {
        $routesConfig = require BASE_PATH . '/config/routes.php';

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
    $container->set('view', function () {
        $config = $this->config;

        $view = new View();
        $view->setViewsDir($config->app->viewsDir);
        $view->setViewExtension('.twig');

        // 通过匿名函数来设置模版引擎，延迟对模版引擎的实例化
        $view->setEngine(function () use ($config, $view) {
            $engine = new TwigEngine($view);
            // 开启 debug 不进行缓存
            //$engine->setDebug(true);
            $engine->setCacheDir($config->app->cacheDir . 'twig');
            return $engine;
        });

        return $view;
    });

    // 如果使用 Smarty 的话，可进行如下设置：

    // SmartyEngine
    $container->set('view', function () {
        $config = $this->config;

        $view = new View();
        $view->setViewsDir($config->app->viewsDir);
        $view->setViewExtension('.tpl');

        // 通过匿名函数来设置模版引擎，延迟对模版引擎的实例化
        $view->setEngine(function () use ($config, $view) {
            $engine = new SmartyEngine($view);
            // 开启 debug 不进行缓存
            $engine->setDebug(true);
            $engine->setOptions(array(
                'compile_dir'    => $config->app->cacheDir . 'templates_c',
                'cache_dir'      => $config->app->cacheDir . 'templates',
                'caching'        => true,
                'caching_type'   => 'file',
                'cache_lifetime' => 86400,
            ));
            return $engine;
        });

        return $view;
    });

另外 [Soli\Application] 默认注册了以下常用服务，供控制器和自定义组件直接使用：

 服务名称   | 介绍             | 默认                 | 是否是共享服务
 -----------|------------------|----------------------|-----------------
 router     | 路由服务         | [Soli\Router]        | 是
 dispatcher | 控制器调度服务   | [Soli\Dispatcher]    | 是
 request    | HTTP请求环境服务 | [Soli\Http\Request]  | 是
 response   | HTTP响应环境服务 | [Soli\Http\Response] | 是
 session    | Session服务      | [Soli\Session]       | 是
 filter     | 过滤器服务       | [Soli\Filter]        | 是
 flash      | 闪存消息服务     | [Soli\Session\Flash] | 是

允许开发者自定义同名的服务覆盖以上默认的服务。

#### 入口文件

Web 应用程序的入口文件默认存放在 `public/index.php`，看起来像下面这样：

    require dirname(__DIR__) . '/app/bootstrap.php';

    $app = new \Soli\Application();

    // 处理请求，输出响应内容
    $app->handle()->send();

    $app->terminate();

#### 控制器

[控制器]类默认以 "Controller" 为后缀，action 无后缀。

控制器可以通过访问属性的方式访问所有注册到容器中的服务。

    use Soli\Controller;
    use App\Models\User;

    class UserController extends Controller
    {
        /**
         * 用户详情
         *
         * 自动渲染 views/user/view.twig 视图
         */
        public function view($id)
        {
            // 这里调用了容器中的 view 服务，设置一个模版变量
            $this->view->setVar('user', User::findById($id));
        }
    }

#### 模型

Soli [模型]仅仅提供了操作数据库的一些常用方法，并没有去实现 ORM，
这是由我们的数据来源和项目架构决定的，有可能数据是来自远程接口，
也有可能团队更习惯使用 [Doctrine]。
Soli 尊重开发者在不同应用场景下的选择和使用习惯，提供了易于扩展的方法，
让你去实现适用于团队和实际需求的数据层。

使用模型：

    use Soli\Model;

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

Soli 模型支持的方法请移步 [soliphp/db]。

#### 视图

[视图]文件存放在 views 目录下，控制器与视图对应关系的目录结构为：

    ├── app                          应用程序目录
    │   └── Controllers              WEB应用控制器目录
    │       └── UserController.php
    └── views                        视图文件目录
        └── user                     UserController 对应的视图目录
            └── view.twig            view 函数对应的视图文件

控制器 app/Controllers/UserController.php：

    use Soli\Controller;
    use App\Models\User;

    class UserController extends Controller
    {
        public function view($id)
        {
            $this->view->setVar('user', User::findById($id));
            $this->flash->notice('user info');
        }
    }

视图文件 views/user/view.twig，这里以 twig 模版引擎为例：

    用户姓名：{{ user.name }}
    用户邮箱：{{ user.email }}

    {{ flash.output() }}

更多视图的使用方法，请移步 [soliphp/view]。

感谢您的阅读。

[Soli_lifecycle]: https://i.imgur.com/mPQMdIv.png
[Phalcon]: https://phalconphp.com/
[composer]: https://getcomposer.org/
[Twig]: http://twig.sensiolabs.org/
[Smarty]: http://www.smarty.net/
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[Doctrine]: http://www.doctrine-project.org/
[soliphp/db]: https://github.com/soliphp/db "Soli Database"
[soliphp/view]: https://github.com/soliphp/view "Soli View"
[Soli\Application]: http://api.soliphp.com/Soli/Application.html "应用"
[Application]: http://api.soliphp.com/Soli/Application.html "应用"
[Soli\Dispatcher]: http://api.soliphp.com/Soli/Dispatcher.html "控制器调度器"
[调度器]: http://api.soliphp.com/Soli/Dispatcher.html "控制器调度器"
[Soli\Http\Request]: http://api.soliphp.com/Soli/Http/Request.html "HTTP请求环境"
[请求]: http://api.soliphp.com/Soli/Http/Request.html "HTTP请求环境"
[Soli\Http\Response]: http://api.soliphp.com/Soli/Http/Response.html "HTTP响应环境"
[响应]: http://api.soliphp.com/Soli/Http/Response.html "HTTP响应环境"
[Soli\Session]: http://api.soliphp.com/Soli/Session.html "会话"
[Soli\Filter]: http://api.soliphp.com/Soli/Filter.html "过滤与清理"
[Soli\Session\Flash]: http://api.soliphp.com/Soli/Session/Flash.html "闪存消息"
[依赖注入]: https://github.com/soliphp/di
[事件管理]: https://github.com/soliphp/events
[闪存消息]: http://api.soliphp.com/Soli/Session/Flash.html
[模版引擎]: https://github.com/soliphp/view
[路由]: http://api.soliphp.com/Soli/Router.html
[命令行应用]: http://api.soliphp.com/Soli/Console.html
[控制器]: http://api.soliphp.com/Soli/Controller.html
[模型]: https://github.com/soliphp/db
[视图]: https://github.com/soliphp/view
[组件]: http://api.soliphp.com/Soli/Component.html
[http://localhost:8000/]: http://localhost:8000/
