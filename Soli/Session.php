<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use SessionHandlerInterface;

/**
 * 会话
 */
class Session
{
    /**
     * 标识 session 是否已启动
     */
    protected $started = false;

    protected $options;

    /**
     * Session constructor
     *
     * @param array $options
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * 启动 session
     *
     * @return bool
     */
    public function start()
    {
        if (!headers_sent() && !$this->started && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            $this->started = true;
            return true;
        }
        return false;
    }

    /**
     * 设置用户自定义会话存储函数
     *
     * @param SessionHandlerInterface $handler
     * @param bool $registerShutdown
     */
    public function setSaveHandler(SessionHandlerInterface $handler, $registerShutdown = true)
    {
        return session_set_save_handler($handler, $registerShutdown);
    }

    /**
     * 设置 session 名称
     *
     * @param string $name
     */
    public function setName($name)
    {
        session_name($name);
    }

    /**
     * 获取 session 名称
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * 使用新生成的会话 ID 更新现有会话 ID
     * 如果启用了 session.use_trans_sid 选项，调用此函数之前不可有输出工作
     *
     * @param bool $deleteOldSession 是否删除原 session id 所关联的会话存储文件
     * @return bool
     */
    public function regenerateId($deleteOldSession = true)
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * 获取一个 session 变量
     *
     * @param string $key
     * @param mixed $defaultValue
     * @param bool $remove 是否获取完就删除掉
     * @return mixed
     */
    public function get($key, $defaultValue = null, $remove = false)
    {
        if (isset($_SESSION[$key])) {
            return $remove ? $this->remove($key) : $_SESSION[$key];
        }

        return $defaultValue;
    }

    /**
     * 设置一个 session 变量
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 检查某个 session 变量是否存在
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * 移除一个 session 变量
     *
     * @param string $key
     */
    public function remove($key)
    {
        $value = null;
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
        }
        return $value;
    }

    /**
     * 获取当前的 session id
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * 设置当前的 session id
     *
     * @param string $id
     */
    public function setId($id)
    {
        session_id($id);
    }

    /**
     * 检查 session 是否已启动
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * 销毁一个会话中的全部数据
     *
     * @param bool $removeData 是否连同会话变量一起销毁
     * @return
     */
    public function destroy($removeData = false)
    {
        if ($removeData) {
            session_unset();
        }

        $this->started = false;
        return session_destroy();
    }

    /**
     * Session destruct
     */
    public function __destruct()
    {
        if ($this->started) {
            session_write_close();
            $this->started = false;
        }
    }
}
