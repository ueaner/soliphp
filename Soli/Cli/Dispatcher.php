<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Cli;

use Soli\BaseDispatcher;

/**
 * 命令行应用调度器
 */
class Dispatcher extends BaseDispatcher
{
    protected $defaultHandler = 'main';
    protected $defaultAction = 'main';

    protected $handlerSuffix = 'Task';

    public function setTaskSuffix($handlerSuffix)
    {
        $this->handlerSuffix = $handlerSuffix;
    }

    public function setDefaultTask($handlerName)
    {
        $this->defaultHandler = $handlerName;
    }

    public function getDefaultTask()
    {
        return $this->defaultHandler;
    }

    public function setTaskName($handlerName)
    {
        $this->handlerName = $handlerName;
    }

    public function getTaskName()
    {
        return $this->handlerName;
    }

    public function getPreviousTaskName()
    {
        return $this->previousHandlerName;
    }

    public function getPreviousActionName()
    {
        return $this->previousHandlerName;
    }
}
