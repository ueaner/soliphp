<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace App\Events;

use Soli\Component;
use Soli\Events\Event;
use App\Controllers\UserController;

/**
 * 用户相关事件
 */
class UserEvents extends Component
{
    public function register(Event $event, UserController $controller)
    {
        $data = json_encode($event->getData());
        echo "trigger on user.register with $data <br>\n";
    }
}
