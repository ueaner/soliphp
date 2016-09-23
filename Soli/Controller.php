<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Injectable;

/**
 * 控制器基类
 *
 * @property \Soli\Dispatcher $dispatcher
 */
class Controller extends Injectable
{
    /**
     * Controller constructor.
     */
    final public function __construct()
    {
    }
}
