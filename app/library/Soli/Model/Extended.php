<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Model;

use Soli\Model;
use ArrayObject;

/**
 * 模型扩展方法
 */
class Extended extends Model
{
    /**
     * 新增一条纪录
     *
     * @example
     *  $data = [
     *      'name' => 'jack',
     *      'age' => 20,
     *      'email' => 'mail@domain.com'
     *  ];
     *  $model::create($data);
     *
     * @param array|\ArrayAccess $fields 新增纪录的字段列表与值的键值对
     * @return int|bool 新增成功返回插入的主键值，失败返回 false
     */
    public static function create($fields)
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
     * 通过条件删除纪录
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
     * 但对于 hits = hits+1 这样的语句需要使用 query 方法来做
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
     * @param array|\ArrayAccess $fields 更新纪录的字段列表与值的键值对, 不可为空
     * @param int|string $params 更新条件
     * @param array $binds 绑定条件
     * @return int|bool 更新成功返回影响行数，失败返回false
     */
    public static function update($fields, $params, array $binds = [])
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
     * @param array|\ArrayAccess $fields 更新纪录的字段列表与值的键值对, 不可为空
     * @param bool $checkPrimaryKey 检查主键是否存在，再确实是执行更新还是新增
     * @return int|bool 更新成功返回影响行数，失败返回false
     */
    public static function save($fields, $checkPrimaryKey = false)
    {
        if (empty($fields)) {
            return false;
        }

        $model = static::instance();

        $id = isset($fields[$model->primaryKey()]) ? $fields[$model->primaryKey()] : 0;

        if ($id) {
            if (!$checkPrimaryKey) {
                return $model::update($fields, $id);
            }
            if ($model::findById($id)) {
                return $model::update($fields, $id);
            }
        }

        return $model::create($fields);
    }

    /**
     * 通过条件查询纪录
     *
     * @example
     *  1. 获取全部纪录
     *  $model::find();
     *  2. 获取主键为 123 的纪录
     *  $model::find(123);
     *  3. 按传入的条件查询
     *  $model::find("age > 20 and email == ''");
     *  4. 按传入的条件查询, 并过滤传入的查询条件
     *  $binds = [':created_at' => '2015-10-27 07:16:16'];
     *  $model::find("created_at < :created_at", $binds);
     *
     * @param int|string $params 查询条件
     * @param array $binds 绑定条件
     * @param string $fields 返回的字段列表
     * @return array 返回记录列表
     */
    public static function find($params = null, $binds = [], $fields = '*')
    {
        /** @var Model $model */
        $model = static::instance();

        $fields = $model->normalizeFields($fields);

        // 获取某个主键ID的数据
        if (is_numeric($params)) {
            $params = $model->primaryKey() . ' = ' . $params;
        }

        if (!empty($params)) {
            $params = " WHERE $params ";
        }

        $sql = "SELECT {$fields} FROM {$model->tableName()} $params";

        $data = $model->query($sql, $binds);

        if (empty($data)) {
            return $data;
        }

        // 结果集中含有主键则用主键做下标
        $first = reset($data);
        if (isset($first[$model->primaryKey()])) {
            return array_column($data, null, $model->primaryKey());
        }

        return $data;
    }

    /**
     * 通过条件查询纪录的第一条数据
     *
     * @example
     *  1. 获取全部纪录
     *  $model::findFirst();
     *  2. 获取主键为 123 的纪录
     *  $model::findFirst(123);
     *  3. 按传入的条件查询
     *  $model::findFirst("age > 20 and email == ''");
     *  4. 按传入的条件查询, 并过滤传入的查询条件
     *  $binds = [':created_at' => '2015-10-27 07:16:16'];
     *  $model::findFirst("created_at < :created_at", $binds);
     *
     * @param int|string $params 查询条件
     * @param array $binds 绑定条件
     * @param string $fields 返回的字段列表
     * @return array 返回记录列表
     */
    public static function findFirst($params = null, $binds = [], $fields = '*')
    {
        /** @var Model $model */
        $model = static::instance();

        $fields = $model->normalizeFields($fields);

        // 获取某个主键ID的数据
        if (is_numeric($params)) {
            $params = $model->primaryKey() . ' = ' . $params;
        }

        if (!empty($params)) {
            $params = " WHERE $params ";
        }

        $sql = "SELECT {$fields} FROM {$model->tableName()} $params";

        return $model->queryRow($sql, $binds);
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

        $fields = $model->normalizeFields($fields);

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

        $fields = $model->normalizeFields($fields);

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
        return array_column($data, null, $model->primaryKey());
    }

    /**
     * 通过某个字段获取多条记录
     *
     * @param string $column 字段名
     * @param string $value  字段值
     * @param string $fields
     * @return array|false
     */
    public static function findByColumn($column, $value, $fields = '*')
    {
        $model = static::instance();

        $fields = $model->normalizeFields($fields);

        $binds = [];
        $binds[':' . $column] = $value;

        $sql = "SELECT {$fields} FROM {$model->tableName()} WHERE $column = :$column";

        return $model->queryAll($sql, $binds);
    }

    /**
     * 通过某个字段获取一条记录
     *
     * @param string $column 字段名
     * @param string $value  字段值
     * @param string $fields
     * @return array|false
     */
    public static function findFirstByColumn($column, $value, $fields = '*')
    {
        $model = static::instance();

        $fields = $model->normalizeFields($fields);

        $binds = [];
        $binds[':' . $column] = $value;

        $sql = "SELECT {$fields} FROM {$model->tableName()} WHERE $column = :$column";

        return $model->queryRow($sql, $binds);
    }

    public function __call($name, $parameters)
    {
        return static::__callStatic($name, $parameters);
    }

    public static function __callStatic($name, $parameters)
    {
        $model = static::instance();
        // 字段列表
        $columns = array_column($model->columns(), 'Field');

        $prefixes = ['findBy', 'findFirstBy'];

        foreach ($prefixes as $prefix) {
            $prefixLen = strlen($prefix);

            if ($prefix == substr($name, 0, $prefixLen)) {
                // 当前查询的字段名称
                $column = substr($name, $prefixLen, strlen($name));
                $column = uncamelize($column);

                if (!in_array($column, $columns)) {
                    throw new \Exception("Call to undefined method $name");
                }

                $func = "static::{$prefix}Column";
                array_unshift($parameters, $column);
                return call_user_func_array($func, $parameters);
            }
        }

        throw new \Exception("Call to undefined method '$name'");
    }

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
     * 空的分页结构
     *
     * @param int $totalItems 总记录数
     * @param int $currentPage 当前页数
     * @param int $pageSize 条数
     * @return \ArrayObject
     */
    public function emptyPage($totalItems = 0, $currentPage = 1, $pageSize = 20)
    {
        $totalPages = (int) ceil($totalItems / $pageSize);
        $current    = $currentPage < $totalPages ? $currentPage : $totalPages;
        $before     = $currentPage - 1 > 0 ? $currentPage - 1 : 0;
        $next       = $currentPage + 1 > $totalPages ? $totalPages : $currentPage + 1;

        $r = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        $r->items      = [];          // 当前页显示的记录列表
        $r->current    = $current;    // 当前页
        $r->before     = $before;     // 上一页
        $r->next       = $next;       // 下一页
        $r->last       = $totalPages; // 最后一页
        $r->totalPages = $totalPages; // 总页数
        $r->totalItems = $totalItems; // 总条数
        return $r;
    }

    protected function normalizeFields($fields)
    {
        if ($fields != '*') {
            return $fields;
        }

        if (empty($this->fields)) {
            $columns = array_column($this->columns(), 'Field');
            $this->fields = implode(', ', $columns);
        }

        return $this->fields;
    }
}
