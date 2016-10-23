<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Psr\Log\LoggerInterface;
use JsonSerializable;

/**
 * 日志记录器
 */
class Logger implements LoggerInterface
{
    /**
     * 日志文件路径
     *
     * @var string
     */
    protected $path = null;

    // levels
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * Logger constructor.
     *
     * @param string $path 日志文件路径
     */
    public function __construct($path = null)
    {
        if (!empty($path)) {
            $this->path = $path;
        }
    }

    /**
     * 设置日志文件路径
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * 写入日志
     *
     * @param string $level 日志级别
     * @param mixed $message 日志信息
     * @param array $context
     * @return int|false 成功返回写入文件的字节数，失败返回 false
     * @throws \Soli\Exception
     */
    public function log($level, $message, array $context = [])
    {
        if (empty($this->path)) {
            throw new Exception('Logger path is not set.');
        }

        $level = strtoupper($level);
        if (!defined(__CLASS__ . '::' . $level)) {
            throw new Exception("Level '$level' is not defined.");
        }

        $message = $this->format($message, $context);
        $output = '[' . date('Y-m-d H:i:s') . "] [$level] $message\n";

        if (!is_file($this->path)) {
            // 创建日志目录
            $dirname = dirname($this->path);
            is_dir($dirname) || mkdir($dirname, 0775, true);
        }

        return file_put_contents($this->path, $output, FILE_APPEND);
    }

    /**
     * 格式化日志信息
     *
     * @param mixed $data 日志信息
     * @param array $context
     * @return string
     */
    protected function format($data, array $context = [])
    {
        if (is_string($data)) {
            return $data;
        }

        if (is_object($data)) {
            if (method_exists($data, '__toString')) {
                return (string)$data;
            }

            if ($data instanceof JsonSerializable) {
                return json_encode($data);
            }
        }

        return print_r($data, true);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->log(static::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->log(static::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->log(static::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log(static::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log(static::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log(static::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log(static::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log(static::DEBUG, $message, $context);
    }
}
