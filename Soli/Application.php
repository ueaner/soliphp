<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Injectable;
use Soli\Di\Container as DiContainer;
use Soli\Http\Response;

/**
 * 应用
 */
class Application extends Injectable
{
    const VERSION = '1.0';

    /**
     * 默认注册服务
     */
    protected $defaultServices = [
        'dispatcher' => \Soli\Dispatcher::class,
        'request'    => \Soli\Http\Request::class,
        'response'   => \Soli\Http\Response::class,
        'session'    => \Soli\Session::class,
        'filter'     => \Soli\Filter::class,
        'flash'      => \Soli\Session\Flash::class,
    ];

    /**
     * Application 初始化
     *
     * @param Soli\Di\Container $di
     */
    public function __construct(DiContainer &$di)
    {
        foreach ($this->defaultServices as $name => $service) {
            // 允许自定义同名的 Service 覆盖默认的 Service
            if (!$di->has($name)) {
                $di->set($name, $service, true);
            }
        }

        $this->di = $di;
    }

    /**
     * 应用程序启动方法
     */
    public function handle($uri = null)
    {
        /** @var \Soli\Dispatcher $dispatcher */
        $dispatcher = $this->dispatcher;
        $eventsManager = $this->getEventsManager();

        // Call boot event
        if (is_object($eventsManager)) {
            $eventsManager->fire('application:boot', $this);
        }

        $this->router($uri);

        // 不自动渲染视图的四种方式:
        // 1. 返回 Response 实例
        // 2. 返回 string 类型作为响应内容
        // 3. 返回 false
        // 4. 禁用视图

        // 执行调度
        $returnedResponse = $dispatcher->dispatch();

        if ($returnedResponse instanceof Response) {
            $response = $returnedResponse;
        } else {
            $response = $this->response;
            if (is_string($returnedResponse)) {
                // 作为响应内容
                $response->setContent($returnedResponse);
            } elseif ($returnedResponse !== false) {
                // 渲染视图
                $response->setContent($this->viewRender());
            }
        }

        // Calling beforeSendResponse
        if (is_object($eventsManager)) {
            $eventsManager->fire('application:beforeSendResponse', $this, $response);
        }

        $response->sendHeaders();
        $response->sendCookies();

        return $response;
    }

    protected function router($uri)
    {
        if (empty($uri)) {
            $uri = isset($_GET['_uri']) ? $_GET['_uri'] : $_SERVER['REQUEST_URI'];
        }
        $uri = filter_var($uri, FILTER_SANITIZE_URL);

        // 去除 query string
        list($uri) = explode('?', $uri);
        // 去除左右斜杠，并以斜杠切分为数组
        $args = explode('/', trim($uri, '/'));

        // 设置控制器、方法及参数
        if (isset($args[0])) {
            $this->dispatcher->setControllerName($args[0]);
        }
        if (isset($args[1])) {
            $this->dispatcher->setActionName($args[1]);
        }
        if (isset($args[2])) {
            $this->dispatcher->setParams(array_slice($args, 2));
        }
    }

    /**
     * 获取视图自动渲染内容
     *
     * @return string
     */
    protected function viewRender()
    {
        // 视图实例
        $view = $this->view;

        // 视图被禁用
        if ($view->isDisabled()) {
            return null;
        }

        // 获取模版文件路径
        $controller = $this->dispatcher->getControllerName();
        $action     = $this->dispatcher->getActionName();
        $template   = "$controller/$action";

        // 自动渲染视图
        return $view->render($template);
    }
}
