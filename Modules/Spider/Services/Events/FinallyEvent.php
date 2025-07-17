<?php

namespace Modules\Spider\Services\Events;

use Modules\Spider\Contracts\SpiderRunEventInterface;
use Modules\Spider\Models\SpiderTask;

// 爬虫采集结束后的事件
class FinallyEvent implements SpiderRunEventInterface
{
    public static function handle(SpiderTask $task, array $data = [], ?string $message = '')
    {
        $extend = $task->extend ?? [];
    }
}
