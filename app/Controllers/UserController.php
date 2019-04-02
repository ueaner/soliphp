<?php

namespace App\Controllers;

use App\Services\UserService;

use App\Events\UserEvents;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

        // 添加 user 事件空间，监听 user.* 的事件
        $this->listen('user', new UserEvents());
    }

    public function view($id)
    {
        $this->view->setVar('user', $this->userService->findById($id));
    }

    public function register()
    {
        $registerData = [
            'username' => 'wukong',
            'password' => 'encrypted password',
        ];

        // 触发 user.register 事件
        $this->trigger('user.register', $registerData);

        // do something ...

        return 'registration success!';
    }
}
