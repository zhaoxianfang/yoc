<?php

namespace Modules\Task\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Spider\Models\SpiderTask;
use Modules\Spider\Models\SpiderTasksLog;
use Modules\Spider\Services\SpiderHandleService;

/**
 * 爬虫队列任务
 */
class SpiderJob implements ShouldQueue
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
    public function __construct(SpiderTask $task)
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
            // 调度采集任务
            (new SpiderHandleService)->entry($this->task);
        } catch (\Exception $err) {
            // 手动发布  手动将任务发布回队列，以便稍后再次尝试
            // $this->release();

            // 手动使任务失败
            // $this->fail($err);

            $model = SpiderTask::where('id', $this->task->id)->first();
            $model->run_status = SpiderTask::RUN_STATUS_FAIL; // 失败
            $model->run_at = now()->toDateTimeString();
            $model->save();
            try {
                SpiderTasksLog::writeErr($this->task, $err);
            } catch (\Exception $e) {
            }
        }
    }
}
