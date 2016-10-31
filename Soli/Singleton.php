<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 单例
 */
class Singleton
{
    /** @var array $instances 实例列表 */
    protected static $instances = [];

    /**
     * 获取单例实例
     *
     * @return Singleton
     */
    public static function instance()
    {
        $class = get_called_class();
        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new static;
        }

        return static::$instances[$class];
    }

    /**
     * 避免外部通过 new 进行实例化
     */
    protected function __construct()
    {
    }

    /**
     * 避免克隆实例
     */
    final private function __clone()
    {
    }
}
