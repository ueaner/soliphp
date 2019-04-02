<?php

namespace App\Services;

use App\Models\UserModel;

class UserService extends Service
{
    /** @var UserModel $userModel */
    protected $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function findById(int $id)
    {
        return $this->userModel->findById($id);
    }
}
