<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

use Soli\Di\Container as DiContainer;

/**
 * 依赖注入感知接口
 */
interface InjectionAwareInterface
{
    /**
     * 设置依赖注入容器
     *
     * @param \Soli\Di\Container $di
     */
    public function setDi(DiContainer $di);

    /**
     * 获取依赖注入容器
     *
     * @return \Soli\Di\Container
     */
    public function getDi();
}
