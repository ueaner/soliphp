<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

use Soli\Di\Container as DiContainer;
use Soli\Events\ManagerInterface as EventsManager;
use Soli\Di\InjectionAwareInterface;
use Soli\Events\EventsAwareInterface;

/**
 * 便于子类访问容器服务
 *
 * 通过 $this->{serviceName} 访问属性的方式访问所有注册到 DiContainer 中的服务
 *
 * @property \Soli\Di\Container $di
 * @property \Soli\Events\Manager $eventsManager
 * @property \Soli\Dispatcher|\Soli\Cli\Dispatcher $dispatcher
 * @property \Soli\Http\Request $request
 * @property \Soli\Http\Response $response
 * @property \Soli\Session $session
 * @property \Soli\Session\Flash $flash
 * @property \Soli\View $view
 */
abstract class Injectable implements InjectionAwareInterface, EventsAwareInterface
{
    /**
     * @var Soli\Di\Container
     */
    protected $diContainer;

    /**
     * @var Soli\Events\Manager
     */
    protected $eventsManager;

    public function setDi(DiContainer $di)
    {
        $this->diContainer = $di;
    }

    /**
     * @return \Soli\Di\Container
     */
    public function getDi()
    {
        if ($this->diContainer === null) {
            $this->diContainer = DiContainer::instance();
        }
        return $this->diContainer;
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
     * 获取 DiContainer 中的某个 Service
     *
     * @param string $name
     * @return \Soli\Di\Container|\Soli\Di\Service
     */
    public function __get($name)
    {
        $di = $this->getDi();

        if ($di->has($name)) {
            $service = $di->getShared($name);
            // 将找到的服务添加到属性, 以便下次直接调用
            $this->$name = $service;
            return $service;
        }

        if ($name == 'di') {
            $this->di = $di;
            return $di;
        }

        trigger_error("Access to undefined property $name");
        return null;
    }
}
