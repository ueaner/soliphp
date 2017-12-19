<?php

namespace App\Controllers;

class IndexController extends Controller
{
    public function index()
    {
        $this->logger->info('some log info');

        // 注意在 config/config.php 文件中修改数据库配置
        // var_dump(\App\Models\User::findFirst());

        $this->view->setVar('name', 'Soli');
    }

    public function test()
    {
        return "test string.";
    }
}
