<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace App\Events;

use Soli\Component;
use Soli\Events\Event;

use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler;

use Throwable;

/**
 * 应用相关事件
 *
 * @property \Monolog\Logger logger
 */
class AppEvents extends Component
{
    public function exception(Event $event, $app, Throwable $e)
    {
        $this->logger->debug($e);
        $output = APP_DEBUG ? $e : $e->getMessage();

        if (class_exists(Whoops::class)) {
            $whoops = new Whoops();
            $handler = new PrettyPageHandler();
            if (extension_loaded('xdebug')) {
                $handler->setEditor('xdebug');
            }
            $whoops->pushHandler($handler);
            $whoops->allowQuit(false);
            $whoops->writeToOutput(false);
            $output = $whoops->handleException($e);
        }

        // 更新响应信息
        $app->response->setStatusCode(
            $e->getCode() ?: 500,
            $e->getMessage()
        )->setContent($output);
    }
}
