<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Cli;

use Soli\Di\Container as DiContainer;
use Soli\Di\Injectable;

/**
 * 命令行应用
 */
class Application extends Injectable
{
    /**
     * @var \Soli\Di\Container
     */
    protected $di;

    /**
     * @var \Soli\Events\Manager
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
     * @param \Soli\Di\Container $di
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
     *
     * @param array|null $args
     * @return mixed
     */
    public function handle(array $args = null)
    {
        /** @var \Soli\Cli\Dispatcher $dispatcher */
        $dispatcher = $this->dispatcher;
        $eventsManager = $this->getEventsManager();

        // Call boot event
        if (is_object($eventsManager)) {
            $eventsManager->fire('application:boot', $this);
        }

        $this->router($args);

        // 执行调度，并返回调度结果
        return $dispatcher->dispatch();
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
            $this->dispatcher->setTaskName($args[0]);
        }
        if (isset($args[1])) {
            $this->dispatcher->setActionName($args[1]);
        }
        if (isset($args[2])) {
            $this->dispatcher->setParams(array_slice($args, 2));
        }
    }
}
