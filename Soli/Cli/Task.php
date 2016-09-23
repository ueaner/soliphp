<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Cli;

use Soli\Di\Injectable;

/**
 * 命令行任务基类
 *
 * @property \Soli\Cli\Dispatcher $dispatcher
 */
class Task extends Injectable
{
    /**
     * Task constructor.
     */
    final public function __construct()
    {
    }
}
