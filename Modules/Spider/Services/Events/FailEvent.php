<?php

namespace Modules\Spider\Services\Events;

use Modules\Spider\Contracts\SpiderRunEventInterface;
use Modules\Spider\Models\SpiderTask;

// 爬虫采集失败时的事件
class FailEvent implements SpiderRunEventInterface
{
    public static function handle(SpiderTask $task, array $data = [], ?string $message = '')
    {
        $url = $data['spider_url'] ?? ''; // 当前采集的url

        $fail = $task->fail ?? [];
    }
}
