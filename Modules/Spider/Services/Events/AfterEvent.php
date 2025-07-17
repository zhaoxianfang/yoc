<?php

namespace Modules\Spider\Services\Events;

// 爬虫采集后的事件
use Modules\Spider\Contracts\SpiderRunEventInterface;
use Modules\Spider\Models\SpiderTask;

class AfterEvent implements SpiderRunEventInterface
{
    public static function handle(SpiderTask $task, array $data = [], ?string $message = '')
    {
        $after = $task->after ?? [];
    }
}
