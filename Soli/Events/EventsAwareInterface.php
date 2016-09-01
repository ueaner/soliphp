<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

use Soli\Events\ManagerInterface;

/**
 * 事件感知接口
 */
interface EventsAwareInterface
{
    /**
     * 设置事件管理器
     *
     * @param Soli\Events\ManagerInterface $eventsManager
     */
    public function setEventsManager(ManagerInterface $eventsManager);

    /**
     * 获取事件管理器
     *
     * @return Soli\Events\Manager
     */
    public function getEventsManager();
}
