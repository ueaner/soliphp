<?php

namespace App\Controllers;

class IndexController extends Controller
{
    public function index()
    {
        $this->logger->info('some log info');
        var_dump(\App\Models\User::findFirst());
        $this->view->setVar('name', 'Soli');
    }

    public function test()
    {
        return "test string.";
    }
}
