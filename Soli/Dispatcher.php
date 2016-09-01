<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\BaseDispatcher;

/**
 * 应用调度器
 */
class Dispatcher extends BaseDispatcher
{
    protected $defaultHandler = 'index';
    protected $defaultAction = 'index';

    protected $handlerSuffix = 'Controller';

    public function setControllerSuffix($handlerSuffix)
    {
        $this->handlerSuffix = $handlerSuffix;
    }

    public function setDefaultController($handlerName)
    {
        $this->defaultHandler = $handlerName;
    }

    public function getDefaultController()
    {
        return $this->defaultHandler;
    }

    public function setControllerName($handlerName)
    {
        $this->handlerName = $handlerName;
    }

    public function getControllerName()
    {
        return $this->handlerName;
    }

    public function getPreviousControllerName()
    {
        return $this->previousHandlerName;
    }

    public function getPreviousActionName()
    {
        return $this->previousActionName;
    }
}
