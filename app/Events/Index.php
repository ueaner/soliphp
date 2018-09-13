<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace App\Events;

use Soli\Component;
use Soli\Events\Event;
use App\Controllers\IndexController;

/**
 * IndexController 事件列表
 */
class Index extends Component
{
    public function prepare(Event $event, IndexController $controller)
    {
        var_dump($event->getData());
        echo "trigger on index.prepare\n";
    }
}
