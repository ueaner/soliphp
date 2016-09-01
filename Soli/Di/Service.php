<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

use Closure;
use Soli\Exception;

/**
 * 服务原型
 *
 * 容器中对单个服务单元的定义
 */
class Service
{
    /**
     * 服务名称
     *
     * @var string $name
     */
    protected $name;

    /**
     * 服务的定义, 类名|对象实例(实例化后对象或Closure)|数组
     *
     * @var array|object|string
     */
    protected $definition;

    protected $shared = false;

    protected $sharedInstance;

    /**
     * Service constructor.
     *
     * @param string $name 服务名称
     * @param object|string|array $definition
     * @param bool $shared
     */
    public function __construct($name, $definition, $shared = false)
    {
        $this->name = $name;
        $this->definition = $definition;
        $this->shared = $shared;
    }

    /**
     * 设置服务是否为共享的
     *
     * @param bool $shared
     */
    public function setShared($shared)
    {
        $this->shared = $shared;
    }

    /**
     * 检查服务是否为共享的
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * 解析服务
     *
     * @param array $parameters
     * @return object|array|null
     * @throws Exception
     */
    public function resolve(array $parameters = null)
    {
        $shared = $this->shared;

        // 为 shared 服务且解析过则直接返回实例
        if ($shared && $this->sharedInstance !== null) {
            return $this->sharedInstance;
        }

        // 创建实例
        $instance = null;
        $definition = $this->definition;
        $type = gettype($definition);

        if ($type == 'object') {
            if ($definition instanceof Closure) {
                // Closure
                if (is_array($parameters)) {
                    $instance = call_user_func_array($definition, $parameters);
                } else {
                    $instance = call_user_func_array($definition, []);
                }
            } else {
                // 实例化的类
                $instance = $definition;
            }
        } elseif ($type == 'string' && class_exists($definition)) {
            // 已存在的类名
            $reflection = new \ReflectionClass($definition);

            if (is_array($parameters)) {
                $instance = $reflection->newInstanceArgs($parameters);
            } else {
                $instance = $reflection->newInstance();
            }
        } elseif ($type == 'array') {
            // 数组
            $instance = $definition;
        } else {
            throw new Exception("Service '{$this->name}' cannot be resolved");
        }

        // 如果是 shared, 保存实例
        if ($shared) {
            $this->sharedInstance = $instance;
        }

        return $instance;
    }
}
