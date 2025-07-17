<?php

namespace Modules\Spider\Models;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\Spider\Services\SpiderHandleService;
use Modules\Task\Contracts\ExecutableInterface;

class SpiderTask extends Model implements ExecutableInterface
{
    // 采集目标类型;属于「文章」类型的采集结果才会记录到文章表;1文章正文，2文章列表,3报刊,4其他
    const TYPE_CONTENT = 1;

    const TYPE_LIST = 2;

    const TYPE_NEWS = 3;

    const TYPE_OTHER = 4;

    // 成功
    public const SUCCESS = 0;

    // 失败
    public const FAILURE = 1;

    // 无效
    public const INVALID = 2;

    public static array $typeMaps = [
        self::TYPE_CONTENT => '正文',
        self::TYPE_LIST => '文章列表',
        self::TYPE_NEWS => '报刊',
        self::TYPE_OTHER => '其他',
    ];

    // 采集状态；1成功，2失败
    const RUN_STATUS_SUCCESS = 1;

    const RUN_STATUS_FAIL = 2;

    public static $runStatusMaps = [
        self::RUN_STATUS_SUCCESS => '成功',
        self::RUN_STATUS_FAIL => '失败',
    ];

    // 是否子任务;1是0否
    const SUB_TASKS_YES = 1;

    const SUB_TASKS_NO = 0;

    public static $subTasksMaps = [
        self::SUB_TASKS_YES => '是',
        self::SUB_TASKS_NO => '否',
    ];

    // 任务状态；1正常，2关闭
    const STATUS_NORMAL = 1;

    const STATUS_CLOSE = 2;

    public static $statusMaps = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_CLOSE => '关闭',
    ];

    /**
     * 模型的属性默认值。 自动赋值属性
     *
     * @var array
     */
    protected $attributes = [
    ];

    /**
     * 不能被批量赋值的属性
     * 如果你想让所有属性都可以批量赋值， 你可以将 $guarded 定义成一个空数组。 如果你选择解除你的模型的保护，你应该时刻特别注意传递给 Eloquent 的 fill、create 和 update 方法的数组：
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    // protected $fillable = [
    //    'options->enabled', // options 是 JSON 列属性
    // ];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
        'rules' => 'array',
        'extend' => 'array', // 扩展
        'before' => 'array',
        'after' => 'array',
        'fail' => 'array',
        'success' => 'array',
        'run_at' => 'datetime:Y-m-d H:i:s',
        'next_tasks_id' => 'integer',
        'domain_prefix' => 'string',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // 截断表
    public function truncate()
    {
        // return self::truncate();
    }

    /**
     * 模型的「引导」方法。
     *
     * @return void
     */
    protected static function booted() {}

    /**
     * 只查询 主任务 列表
     *
     * @demo  SpiderTask::main()->...
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeMain($query)
    {
        $query->where('sub_tasks', self::SUB_TASKS_NO); // 不是子任务
        $query->where('status', self::STATUS_NORMAL);   // 任务状态为正常
        $query->whereNotIn('timer', ['* * * * *', '*/1 * * * *']);      // cron 定时任务时间不是每分钟
    }

    public function nextTasks()
    {
        return $this->belongsTo(self::class, 'next_tasks_id', 'id');
    }

    // 单独执行此采集任务
    public function execute()
    {
        try {
            // 调度采集任务
            (new SpiderHandleService)->entry($this);
        } catch (\Exception $e) {
        }
    }

    /**
     * 定义一个获取器，获取任务的下次执行时间
     *   调用demo: $task->next_run_date
     */
    protected function nextRunDate(): Attribute
    {
        // 创建 CronExpression 对象
        $cron = new CronExpression($this->timer);
        // 获取最近一次运行时间
        // $lastRun = $cron->getPreviousRunDate()->format('Y-m-d H:i:s');
        // 获取下一次运行时间
        $nextRun = $cron->getNextRunDate()->format('Y-m-d H:i:s');

        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $nextRun
        );
    }
}
