<?php
/**
 * @author ueaner <ueaner#gmail.com>
 */
namespace Soli\Model;

use Soli\Model;

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
     * @param array $binds 绑定条件
     * @return int|bool 更新成功返回影响行数，失败返回false
     */
    public static function save($fields, array $binds = [])
    {
        if (empty($fields)) {
            return false;
        }

        $model = static::instance();

        // 通过主键更新一条数据
        if (isset($fields[$model->primaryKey()]) && $fields[$model->primaryKey()]) {
            return $model::update($fields, $fields[$model->primaryKey()], $binds);
        } else {
            return $model::create($fields);
        }
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

        // 获取某个主键ID的数据
        if (is_numeric($params)) {
            $params = $model->primaryKey() . ' = ' . $params;
        }

        if (!empty($params)) {
            $params = " WHERE $params ";
        }

        $sql = "SELECT {$fields} FROM {$model->tableName()} $params";

        return $model->query($sql, $binds);
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

    /**
     * 获取执行的SQL语句
     *
     * @param string $sql
     * @param array $binds
     * @return string
     */
    public static function getRawSql($sql, $binds)
    {
        if (!empty($binds)) {
            $binds = array_map(function ($value) {
                return is_string($value) ? "'$value'" : $value;
            }, $binds);
            $sql = strtr($sql, $binds);
        }
        return $sql;
    }
}