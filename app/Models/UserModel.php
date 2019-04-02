<?php

namespace App\Models;

class UserModel extends Model
{
    /**
     * 当前模型访问的数据库连接服务名称
     */
    protected $connection = 'db';

    /**
     * 当前模型操作的表名
     */
    protected $table = 'user';

    /**
     * 当前模型所操作表的主键
     */
    protected $primaryKey = 'id';
}
