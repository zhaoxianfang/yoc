<?php

namespace Modules\Spider\Services\Events;

use Modules\Spider\Contracts\SpiderRunEventInterface;
use Modules\Spider\Models\SpiderTask;

// 爬虫采集前的事件
class BeforeEvent implements SpiderRunEventInterface
{
    public static function handle(SpiderTask $task, array $data = [], ?string $message = '')
    {
        $before = $task->before ?? [];
    }
}
