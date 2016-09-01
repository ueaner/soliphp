<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Cli;

use Soli\Di\Container as DiContainer;
use Soli\Events\ManagerInterface as EventsManager;
use Soli\Di\InjectionAwareInterface;
use Soli\Events\EventsAwareInterface;
use Soli\ErrorHandler;

/**
 * 命令行应用
 */
class Application implements InjectionAwareInterface, EventsAwareInterface
{
    /**
     * @var Soli\Di\Container
     */
    protected $di;

    /**
     * @var Soli\Events\Manager
     */
    protected $eventsManager;

    /**
     * 默认注册服务
     */
    protected $defaultServices = [
        'dispatcher' => \Soli\Cli\Dispatcher::class,
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

    public function setDi(DiContainer $di)
    {
        $this->di = $di;
    }

    /**
     * @return \Soli\Di\Container
     */
    public function getDi()
    {
        return $this->di;
    }

    public function setEventsManager(EventsManager $eventsManager)
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * @return \Soli\Events\Manager
     */
    public function getEventsManager()
    {
        return $this->eventsManager;
    }

    /**
     * 应用程序启动方法
     */
    public function handle(array $args = null)
    {
        /** @var \Soli\Dispatcher $dispatcher */
        $dispatcher = $this->di->getShared('dispatcher');
        $eventsManager = $this->getEventsManager();

        // Call boot event
        if (is_object($eventsManager)) {
            $eventsManager->fire('application:boot', $this);
        }

        $this->router($args);

        // 执行调度，并返回调度结果
        return $dispatcher->dispatch();
    }

    /**
     * 开启调试，注册 ErrorHandler
     */
    public function debug($handler)
    {
        $errorHandler = $this->di->getShared(ErrorHandler::class);
        $errorHandler->register($handler);
    }

    protected function router($args)
    {
        if (empty($args)) {
            array_shift($_SERVER['argv']);
            $args = $_SERVER['argv'];
        }
        $args = array_values($args);

        // 设置控制器、方法及参数
        if (isset($args[0])) {
            $dispatcher->setTaskName($args[0]);
        }
        if (isset($args[1])) {
            $dispatcher->setActionName($args[1]);
        }
        if (isset($args[2])) {
            $dispatcher->setParams(array_slice($args, 2));
        }
    }
}
