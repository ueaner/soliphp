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
    /** @var Singleton $instance 存储实例 */
    protected static $instance;

    /**
     * 获取单例实例
     *
     * @return Singleton
     */
    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
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
