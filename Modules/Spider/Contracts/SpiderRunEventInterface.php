<?php

namespace Modules\Spider\Contracts;

use Modules\Spider\Models\SpiderTask;

/**
 * 爬虫 SpiderTask 运行时的事件模型 应该具备以下方法
 */
interface SpiderRunEventInterface
{
    /**
     * @param  SpiderTask  $task  爬虫任务
     */
    public static function handle(SpiderTask $task);
}
