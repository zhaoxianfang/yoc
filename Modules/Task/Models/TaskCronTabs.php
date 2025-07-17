<?php

namespace Modules\Task\Models;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class TaskCronTabs extends Model
{
    /**
     * 不能被批量赋值的属性
     * 如果你想让所有属性都可以批量赋值， 你可以将 $guarded 定义成一个空数组。 如果你选择解除你的模型的保护，你应该时刻特别注意传递给 Eloquent 的 fill、create 和 update 方法的数组：
     *
     * @var array
     */
    protected $guarded = ['id'];

    // 执行类型
    const TYPE_MODEL = 'model';

    const TYPE_FUNC = 'func';

    const TYPE_CURL = 'curl';

    public static array $typeMaps = [
        self::TYPE_MODEL => '多态模型',
        self::TYPE_FUNC => '类或方法',
        self::TYPE_CURL => 'HTTP请求',
    ];

    // 执行状态
    const RUN_STATUS_WAIT = '0';

    const RUN_STATUS_SUCCESS = '1';

    const RUN_STATUS_FAIL = '2';

    public static array $runStatusMaps = [
        self::RUN_STATUS_WAIT => '未执行',
        self::RUN_STATUS_SUCCESS => '成功',
        self::RUN_STATUS_FAIL => '失败',
    ];

    // 任务状态
    const STATUS_OPEN = '1';

    const STATUS_CLOSE = '2';

    public static array $statusMaps = [
        self::STATUS_OPEN => '正常',
        self::STATUS_CLOSE => '关闭',
    ];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
        'curl_params' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 只查询 开启 列表
     *
     * @demo  SpiderTask::open()->...
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeOpen($query)
    {
        $query->where('status', self::STATUS_OPEN);   // 任务状态为正常
    }

    public function child(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * 多态关联 执行的对象
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function executable()
    {
        return $this->morphTo();
    }

    /**
     * 定义一个获取器，获取任务的下次执行时间
     *   调用demo: $task->cron_next_run_date
     */
    protected function cronNextRunDate(): Attribute
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
