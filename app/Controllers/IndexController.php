<?php

namespace App\Controllers;

use Soli\Events\EventManager;
use App\Events\Index as IndexEvents;

class IndexController extends Controller
{
    public function index()
    {
        $this->view->setVar('name', 'Soli');
    }
}
