<?php

namespace Modules\Task\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Task\Models\TaskCronTabs;
use Modules\Task\Services\CronTaskService;

/**
 * 定时队列任务
 */
class CronTabsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    /**
     * 可以尝试任务的次数
     *
     * @var int
     */
    public $tries = 25;

    /**
     * 失败前允许的最大未处理异常数
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * 如果任务的模型不存在，则删除该任务
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TaskCronTabs $task)
    {
        $this->task = $task;

        // 指定队列
        // $this->onQueue('processing');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // 调度定时任务
            (new CronTaskService)->run($this->task);
        } catch (\Exception $err) {
            // 手动发布  手动将任务发布回队列，以便稍后再次尝试
            // $this->release();

            // 任务失败

            $this->task->run_status = TaskCronTabs::RUN_STATUS_FAIL; // 失败
            $this->task->run_at = now()->toDateTimeString();
            $this->task->save();
        }
    }
}
