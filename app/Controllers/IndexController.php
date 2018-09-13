<?php

namespace App\Controllers;

use Soli\Events\EventManager;
use App\Events\Index as IndexEvents;

class IndexController extends Controller
{
    public function __construct()
    {
        $eventManager = new EventManager();
        // 添加 index 事件空间，监听 index.* 的事件
        $eventManager->attach('index', new IndexEvents());

        $this->setEventManager($eventManager);
    }

    public function index()
    {
        $this->trigger('index.prepare', 'some data from index');

        $this->view->setVar('name', 'Soli');
    }
}
