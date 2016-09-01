<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Exception;
use Soli\Di\Container as DiContainer;
use Soli\Events\ManagerInterface as EventsManager;
use Soli\Di\InjectionAwareInterface;
use Soli\Events\EventsAwareInterface;

/**
 * 调度器基类
 */
class BaseDispatcher implements InjectionAwareInterface, EventsAwareInterface
{
    protected $namespaceName = null;
    protected $handlerName = null;
    protected $actionName = null;
    protected $params = null;

    protected $defaultNamespace = '';
    protected $defaultHandler = null;
    protected $defaultAction = null;

    protected $handlerSuffix = null;
    protected $actionSuffix = 'Action';

    protected $previousHandlerName = null;
    protected $previousActionName = null;

    /**
     * dispatch loop 是否结束
     *
     * @var bool
     */
    protected $finished = null;

    /**
     * @var Soli\Di\Container
     */
    protected $di;

    /**
     * @var Soli\Events\Manager
     */
    protected $eventsManager;

    const EXCEPTION_CYCLIC_ROUTING = 1;

    const EXCEPTION_HANDLER_NOT_FOUND = 2;

    const EXCEPTION_INVALID_PARAMS = 3;

    const EXCEPTION_ACTION_NOT_FOUND = 4;

    /**
     * BaseDispatcher constructor.
     */
    public function __construct()
    {
        $this->params = [];
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
     * 执行调度
     */
    public function dispatch()
    {
        $numberDispatches = 0;
        $returnedResponse = null;
        $this->finished = false;

        $eventsManager = $this->getEventsManager();

        if (is_object($eventsManager)) {
            if ($eventsManager->fire('dispatch:beforeDispatchLoop', $this) === false) {
                return false;
            }
        }

        // dispatch loop
        while (!$this->finished) {
            ++$numberDispatches;

            if ($numberDispatches >= 256) {
                $this->throwDispatchException(
                    'Dispatcher has detected a cyclic routing causing stability problems',
                    static::EXCEPTION_CYCLIC_ROUTING
                );
                break;
            }

            $this->finished = true;
            $this->resolveEmptyProperties();

            $namespaceName = $this->getNamespaceName();
            $handlerName = $namespaceName . ucfirst($this->handlerName) . $this->handlerSuffix;
            $actionName = $this->actionName . $this->actionSuffix;
            $params = $this->params;

            if (is_object($eventsManager)) {
                if ($eventsManager->fire('dispatch:beforeDispatch', $this) === false) {
                    continue;
                }
                // Check if the user made a forward in the listener
                if ($this->finished === false) {
                    continue;
                }
            }

            // Handler 是否存在
            if (!class_exists($handlerName)) {
                $status = $this->throwDispatchException(
                    'Not Found handler: ' . $handlerName,
                    static::EXCEPTION_HANDLER_NOT_FOUND
                );

                // Check if the user made a forward in the listener
                if ($status === false && $this->finished === false) {
                    continue;
                }
                break;
            }

            // 参数格式是否正确
            if (!is_array($params)) {
                $status = $this->throwDispatchException(
                    "Action parameters must be an Array",
                    static::EXCEPTION_INVALID_PARAMS
                );

                // Check if the user made a forward in the listener
                if ($status === false && $this->finished === false) {
                    continue;
                }
                break;
            }

            // Action 是否可调用
            if (!is_callable([$handlerName, $actionName])) {
                if (is_object($eventsManager)) {
                    if ($eventsManager->fire('dispatch:beforeNotFoundAction', $this) === false) {
                        continue;
                    }

                    if ($this->finished === false) {
                        continue;
                    }
                }

                $status = $this->throwDispatchException(
                    sprintf('Not Found Action: %s->%s', $handlerName, $actionName),
                    static::EXCEPTION_ACTION_NOT_FOUND
                );
                // Check if the user made a forward in the listener
                if ($status === false && $this->finished === false) {
                    continue;
                }
                break;
            }

            $handler = $this->di->getShared($handlerName);

            // 初始化
            if (method_exists($handler, 'initialize')) {
                $handler->initialize();
            }

            try {
                // 调用 Action
                $returnedResponse = call_user_func_array([$handler, $actionName], $params);
            } catch (\Exception $e) {
                if ($this->handleException($e) === false) {
                    // forward to exception handler
                    if ($this->finished === false) {
                        continue;
                    }
                } else {
                    // rethrow it
                    throw $e;
                }
            }

            if (is_object($eventsManager)) {
                $eventsManager->fire('dispatch:afterDispatch', $this, $returnedResponse);
            }
        }

        if (is_object($eventsManager)) {
            $eventsManager->fire('dispatch:afterDispatchLoop', $this, $returnedResponse);
        }

        return $returnedResponse;
    }

    /**
     * 无需 redirect 跳转，而直接调用对应的 Handler->Action
     *
     * @param array $forward {
     *   @var string namespace
     *   @var string controller
     *   @var string task
     *   @var string action
     *   @var array  params
     * }
     */
    public function forward(array $forward)
    {
        if (isset($forward['namespace'])) {
            $this->namespaceName = $forward['namespace'];
        }

        if (isset($forward['controller'])) {
            $this->previousHandlerName = $this->handlerName;
            $this->handlerName = $forward['controller'];
        } else {
            if (isset($forward['task'])) {
                $this->previousHandlerName = $this->handlerName;
                $this->handlerName = $forward['task'];
            }
        }

        if (isset($forward['action'])) {
            $this->previousActionName = $this->actionName;
            $this->actionName = $forward['action'];
        }

        if (isset($forward['params'])) {
            $this->params = $forward['params'];
        }

        $this->finished = false;
    }

    public function setDefaultNamespace($namespaceName)
    {
        $this->defaultNamespace = $namespaceName;
    }

    public function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

    public function setNamespaceName($namespaceName)
    {
        $this->namespaceName = $namespaceName;
    }

    public function getNamespaceName()
    {
        $namespaceName = $this->namespaceName ?: $this->defaultNamespace;
        // fixed namespaceName
        if ($namespaceName) {
            $namespaceName = "\\" . trim($namespaceName, "\\") . "\\";
        }

        return $namespaceName;
    }

    public function setDefaultAction($actionName)
    {
        return $this->defaultAction = $actionName;
    }

    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * 设置 Action 参数
     *
     * @param string|array $params
     * @param mixed $value
     */
    public function setParams($params, $value = null)
    {
        if (is_array($params)) {
            $this->params = $params;
        } elseif (is_string($params)) {
            $this->params[$params] = $value;
        }
    }

    /**
     * 获取 Action 参数
     *
     * @param string|null $name
     * @param null $defaultValue
     * @return array|mixed|null
     */
    public function getParams($name = null, $defaultValue = null)
    {
        if (empty($name)) {
            return $this->params;
        }
        return isset($this->params[$name]) ? $this->params[$name] : $defaultValue;
    }

    /**
     * 初始化 namespace, handler, action 的值
     */
    protected function resolveEmptyProperties()
    {
        if (!$this->namespaceName) {
            $this->namespaceName = $this->defaultNamespace;
        }

        if (!$this->handlerName) {
            $this->handlerName = $this->defaultHandler;
        }

        if (!$this->actionName) {
            $this->actionName = $this->defaultAction;
        }
    }

    /**
     * Throws an internal exception
     */
    protected function throwDispatchException($message, $exceptionCode = 0)
    {
        // Create the real exception
        $e = new Exception($message, $exceptionCode);

        if ($this->handleException($e) === false) {
            return false;
        }

        // Throw the exception if it wasn't handled
        throw $e;
    }

    /**
     * Handles a user exception
     */
    protected function handleException($e)
    {
        $eventsManager = $this->getEventsManager();
        if (is_object($eventsManager)) {
            if ($eventsManager->fire('dispatch:beforeException', $this, $e) === false) {
                return false;
            }
        }
    }
}
