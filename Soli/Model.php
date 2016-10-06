<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Container as DiContainer;
use Soli\Di\InjectionAwareInterface;

/**
 * 模型
 *
 * @property \Soli\Db $db
 */
abstract class Model implements InjectionAwareInterface
{
    /** @var \Soli\Di\Container $di */
    protected $di;

    /** @var string $connectionService */
    protected $connectionService;

    protected $tableName;
    protected $primaryKey;
    protected $columns;

    /** @var string $lastError 最后一次SQL执行的错误信息 */
    protected $lastError;

    /**
     * Model constructor.
     *
     * @param \Soli\Di\Container|null $di
     */
    final public function __construct(DiContainer $di = null)
    {
        if (!is_object($di)) {
            $di = DiContainer::instance();
        }

        if (method_exists($this, 'initialize')) {
            // 初始化方法可以设置：connectionService，tableName，primaryKey
            $this->initialize();
        }

        $di->setShared(get_called_class(), $this);
        // 虽然尽量避免使用 new，而是使用 instance() 方法取
        // 但也保证两者拿到的结构是一样的
        $this->di = $di;
    }

    public function setDi(DiContainer $di)
    {
        $this->di = $di;
    }

    /**
     * @return \Soli\Di\Container
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * 获取 Model 对象实例
     *
     * @return $this
     */
    public static function instance()
    {
        return DiContainer::instance()->getShared(get_called_class());
    }

    /**
     * 获取数据库连接服务名称
     *
     * @return string
     */
    public function connectionService()
    {
        return $this->connectionService ? $this->connectionService : 'db';
    }

    /**
     * 获取表名称
     */
    public function tableName()
    {
        if ($this->tableName === null) {
            $path = explode("\\", get_called_class());
            $this->tableName = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1_', array_pop($path)));
        }
        return $this->tableName;
    }

    /**
     * 获取当前 table 的全部字段信息
     */
    public function columns()
    {
        if ($this->columns === null) {
            $sql = 'DESCRIBE ' . $this->tableName();
            $this->columns = $this->query($sql);
        }

        return $this->columns;
    }

    /**
     * 获取主键名称
     */
    public function primaryKey()
    {
        if ($this->primaryKey === null) {
            foreach ($this->columns() as $column) {
                if ($column['Key'] == 'PRI') {
                    $this->primaryKey = $column['Field'];
                    break;
                }
            }
        }

        return $this->primaryKey;
    }

    /**
     * 执行一条 SQL 语句
     *
     * @param string $sql SQL语句
     * @param array  $binds 绑定数据
     * @param string $fetchMode column|row|all 返回的数据结果类型
     * @return array|int|string
     *   插入数据返回插入数据的主键ID，更新/删除数据返回影响行数
     *   查询语句则根据 $fetchMode 返回对应类型的结果集
     * @throws \Soli\Exception
     */
    protected function query($sql, $binds = [], $fetchMode = 'all')
    {
        try {
            return $this->db->query($sql, $binds, $fetchMode);
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * 查询 SQL 语句返回结果的所有行
     *
     * @param string $sql SQL语句
     * @param array $binds 绑定条件
     * @return array
     */
    public function queryAll($sql, $binds = [])
    {
        return $this->query($sql, $binds, 'all');
    }

    /**
     * 查询 SQL 语句返回结果的第一行
     *
     * @param string $sql SQL语句
     * @param array $binds 绑定条件
     * @return array
     */
    public function queryRow($sql, $binds = [])
    {
        return $this->query($sql, $binds, 'row');
    }

    /**
     * 查询 SQL 语句中第一个字段的值
     *
     * @param string $sql SQL语句
     * @param array $binds 绑定条件
     * @return int|string
     */
    public function queryColumn($sql, $binds = [])
    {
        return $this->query($sql, $binds, 'column');
    }

    /**
     * 获取 Db 连接或 DiContainer 中的某个 Service
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $di = $this->di;

        if ($name == 'db') {
            $this->db = $di->getShared($this->connectionService());
            return $this->db;
        }

        if ($di->has($name)) {
            $this->$name = $di->getShared($name);
            // 将找到的服务添加到属性, 以便下次直接调用
            return $this->$name;
        }

        trigger_error("Access to undefined property $name");
        return null;
    }

    /**
     * 获取最后一次SQL执行的错误信息
     */
    public static function getLastError()
    {
        return static::instance()->lastError;
    }
}