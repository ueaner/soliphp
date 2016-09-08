<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Container as DiContainer;
use Soli\Di\InjectionAwareInterface;
use Soli\Traits\EmptyPageTrait;
use PDOException;

/**
 * 模型
 */
abstract class Model implements InjectionAwareInterface
{
    /** @var Soli\Di\Container $di */
    protected $di;

    /** @var string $connectionService */
    protected $connectionService;

    protected $db;

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
            // 初始化方法可以设置 connectionService
            $this->initialize();
        }

        // 获取数据库连接，Model 中只写关于 Db 的操作
        $this->db = $di->getShared($this->getConnectionService());

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
     */
    public static function instance()
    {
        return DiContainer::instance()->getShared(get_called_class());
    }

    /**
     * 设置数据库连接服务名称
     *
     * @example $this->setConnectionService('db2');
     *
     * @param string $connectionService
     */
    public function setConnectionService($connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * 获取数据库连接服务名称
     *
     * @return string
     */
    public function getConnectionService()
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
     * 查询 SQL 语句返回结果的第一行
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
     * 新增一条纪录
     *
     * @example
     *  $data = [
     *      'name' => 'jack',
     *      'age' => 20,
     *      'email' => 'mail@domain.com'
     *  ];
     *  $model::insert($data);
     *
     * @param array $fields 新增纪录的字段列表与值的键值对
     * @return int|bool 新增成功返回插入的主键值，失败返回 false
     */
    public static function insert($fields)
    {
        if (empty($fields)) {
            return false;
        }

        /** @var Model $model */
        $model = static::instance();

        $binds = [];
        foreach ($fields as $field => $value) {
            $binds[':'.$field] = $value;
        }

        $fields = implode(',', array_keys($fields));
        $fieldBinds = implode(',', array_keys($binds));

        $sql = "INSERT INTO {$model->tableName()}($fields) VALUES($fieldBinds)";

        return $model->query($sql, $binds);
    }

    /**
     * 按照条件删除纪录
     *
     * @example
     *  1. 删除主键为 123 的纪录
     *  $model::delete(123);
     *  2. 按传入的条件删除
     *  $model::delete("age > 20 and email == ''");
     *  3. 按传入的条件删除, 并过滤传入的删除条件
     *  $binds = [':created_at' => '2015-10-27 07:16:16'];
     *  $model::delete("created_at < :created_at", $binds);
     *
     * @param int|string $params 条件, 不可为空
     * @param array $binds 绑定条件
     * @return int|bool 成功返回影响行数，失败返回 false
     */
    public static function delete($params, $binds = [])
    {
        if (empty($params)) {
            return false;
        }

        /** @var Model $model */
        $model = static::instance();

        // 通过主键删除一条数据
        if (is_numeric($params)) {
            $params = $model->primaryKey() . ' = ' . $params;
        }

        $sql = "DELETE FROM {$model->tableName()} WHERE $params";

        return $model->query($sql, $binds);
    }

    /**
     * 更新一条数据
     * 但对于 hits = hits+1 这样的语句需要用 crement 或 query 来做
     *
     * @example
     *  $data = [
     *      'name' => 'jack',
     *      'age' => 20,
     *      'email' => ':email'
     *  ];
     *  $binds = [
     *      ':email' => 'mail@domain.com',
     *      ':created_at' => '2015-10-27 08:36:42'
     *  ];
     *
     *  $rowCount = $model::update($data, 12);
     *  $rowCount = $model::update($data, 'created_at = :created_at', $binds);
     *
     * @param array $fields 更新纪录的字段列表与值的键值对, 不可为空
     * @param int|string $params 更新条件
     * @param array $binds 绑定条件
     * @return int|bool 更新成功返回影响行数，失败返回false
     */
    public static function update(array $fields, $params, array $binds = [])
    {
        if (empty($fields)) {
            return false;
        }

        /** @var Model $model */
        $model = static::instance();

        // 通过主键更新一条数据
        if (is_numeric($params)) {
            $params = $model->primaryKey() . ' = ' . $params;
        }

        // 自动绑定参数
        $sets = [];
        foreach ($fields as $field => $value) {
            if (!isset($binds[":$field"])) {
                $binds[":$field"] = $value;
                $sets[] = "$field = :$field";
            }
        }

        $sets = implode(',', $sets);
        $sql = "UPDATE {$model->tableName()} SET $sets WHERE $params";

        return $model->query($sql, $binds);
    }

    /**
     * 保存(更新或者新增)一条数据
     *
     * @example
     *  $data = [
     *      'id' => 12, // 保存的数据中有主键，则按主键更新，否则新增一条数据
     *      'name' => 'jack',
     *      'age' => 20,
     *      'email' => ':email'
     *  ];
     *  $binds = [
     *      ':email' => 'mail@domain.com',
     *      ':created_at' => '2015-10-27 08:36:42'
     *  ];
     *
     *  $rowCount = $model::save($data);
     *  相当于：$rowCount = $model::update($data, 12);
     *
     *  $rowCount = $model::save($data, 'created_at = :created_at', $binds);
     *
     * @param array $fields 更新纪录的字段列表与值的键值对, 不可为空
     * @param array $binds 绑定条件
     * @return int|bool 更新成功返回影响行数，失败返回false
     */
    public static function save(array $fields, array $binds = [])
    {
        if (empty($fields)) {
            return false;
        }

        /** @var Model $model */
        $model = static::instance();

        // 通过主键更新一条数据
        if (isset($fields[$model->primaryKey()]) && $fields[$model->primaryKey()]) {
            return $model::update($fields, $fields[$model->primaryKey()], $binds);
        } else {
            return $model::insert($fields);
        }
    }

    /**
     * 将一个或多个字段的值加减某个数
     *
     * @example
     *  $crementFields = [
     *      'counter' => '+1',
     *      'sum' => '-2',
     *  ];
     *  or
     *  $crementFields = 'counter = counter +1, sum = sum -2';
     *
     *  $rowCount = $model->crement($crementFields, 'id = 12');
     *
     * @param array $fields 更新纪录的字段列表与值的键值对, 不可为空
     * @param int|string $params 更新条件
     * @param array $binds 绑定条件
     * @return int|bool 更新成功返回影响行数，失败返回false
     */
    public static function crement($fields, $params, array $binds = [])
    {
        if (empty($fields)) {
            return false;
        }

        /** @var Model $model */
        $model = static::instance();

        // 通过主键更新一条数据
        if (is_numeric($params)) {
            $params = $model->primaryKey() . ' = ' . $params;
        }

        if (is_string($fields)) {
            $sets = $fields;
        } else {
            $sets = [];
            foreach ($fields as $field => $value) {
                $value = strtr($value, [$field => '']);
                $sets[] = "$field = $field $value";
            }

            $sets = implode(',', $sets);
        }

        $sql = "UPDATE {$model->tableName()} SET $sets WHERE $params";

        return $model->query($sql, $binds);
    }

    /**
     * 通过ID查询一条记录
     *
     * @param int $id
     * @param string $fields
     * @return array|false
     */
    public static function findById($id, $fields = '*')
    {
        if (empty($id)) {
            return false;
        }

        /** @var Model $model */
        $model = static::instance();

        $sql = "SELECT {$fields} FROM {$model->tableName()} WHERE {$model->primaryKey()} = :id";
        $binds = [':id' => $id];

        return $model->queryRow($sql, $binds);
    }

    /**
     * 通过ID列表获取多条记录，
     * 注意，返回结果不一定按传入的ID列表顺序排序
     *
     * @param array $ids
     * @param string $fields
     * @return array|false
     */
    public static function findByIds(array $ids, $fields = '*')
    {
        if (empty($ids)) {
            return false;
        }

        /** @var Model $model */
        $model = static::instance();

        $binds = [];
        foreach ($ids as $id) {
            $binds[':id'.$id] = $id;
        }

        $fieldBinds = implode(',', array_keys($binds));
        $number = count($ids);

        $sql = "SELECT {$fields} FROM {$model->tableName()} WHERE {$model->primaryKey()} IN ($fieldBinds)"
             . " LIMIT {$number}";

        $data = $model->query($sql, $binds);
        if (empty($data)) {
            return $data;
        }

        // 以主键为下标
        $result = [];
        foreach ($data as $item) {
            $result[$item[$model->primaryKey()]] = $item;
        }

        return $result;
    }

    // 引入空的分页结构
    use EmptyPageTrait;

    /**
     * 分页
     *
     * @param string $sql SQL语句
     * @param array $binds 绑定数据
     * @param int $page 当前页数
     * @param int $pageSize 每页的条数
     * @return \ArrayObject
     */
    public static function page($sql, $binds = [], $page = 1, $pageSize = 20)
    {
        /** @var Model $model */
        $model = static::instance();

        $page   = $page > 1 ? $page : 1;
        $offset = ($page - 1) * $pageSize;
        $limit  = $pageSize;

        $sql .= " LIMIT $limit OFFSET $offset";

        $sql = 'SELECT SQL_CALC_FOUND_ROWS ' . substr($sql, strpos($sql, ' '));

        // 获取查询结果
        $items = $model->query($sql, $binds);
        // 获取总数
        $totalItems = $model->queryColumn('SELECT FOUND_ROWS()');

        $result = $model->emptyPage($totalItems, $page, $pageSize);
        $result->items = $items;
        return $result;
    }

    /**
     * 获取最后一次SQL执行的错误信息
     */
    public static function getLastError()
    {
        return static::instance()->lastError;
    }
}
