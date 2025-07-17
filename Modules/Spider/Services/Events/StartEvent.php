<?php

namespace Modules\Spider\Services\Events;

use Modules\Spider\Contracts\SpiderRunEventInterface;
use Modules\Spider\Models\SpiderTask;

// 爬虫采集开始时的事件
class StartEvent implements SpiderRunEventInterface
{
    /**
     * @return mixed
     */
    public static function handle(SpiderTask $task, array $data = [], ?string $message = '') {}
}
