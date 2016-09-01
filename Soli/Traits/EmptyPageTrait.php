<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Traits;

use ArrayObject;

/**
 * 空的分页结构
 */
trait EmptyPageTrait
{
    /**
     * 空的分页结构
     *
     * @param int $totalItems 总记录数
     * @param int $currentPage 当前页数
     * @param int $pageSize 条数
     * @return \ArrayObject
     */
    public function emptyPage($totalItems = 0, $currentPage = 1, $pageSize = 20)
    {
        $totalPages = (int) ceil($totalItems / $pageSize);
        $current    = $currentPage < $totalPages ? $currentPage : $totalPages;
        $before     = $currentPage - 1 > 0 ? $currentPage - 1 : 0;
        $next       = $currentPage + 1 > $totalPages ? $totalPages : $currentPage + 1;

        $r = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        $r->items      = [];          // 当前页显示的记录列表
        $r->current    = $current;    // 当前页
        $r->before     = $before;     // 上一页
        $r->next       = $next;       // 下一页
        $r->last       = $totalPages; // 最后一页
        $r->totalPages = $totalPages; // 总页数
        $r->totalItems = $totalItems; // 总条数
        return $r;
    }
}
