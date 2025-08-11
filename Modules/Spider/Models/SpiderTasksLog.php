<?php

namespace Modules\Spider\Models;

use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SpiderTasksLog extends Model
{
    // 采集状态；1成功，2失败
    const STATUS_SUCCESS = 1;

    const STATUS_FAIL = 2;

    public static $statusMaps = [
        self::STATUS_SUCCESS => '成功',
        self::STATUS_FAIL => '失败',
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
        // "content" => 'array',
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

    public function content(): Attribute
    {
        // withoutObjectCaching 禁用属性的缓存
        // shouldCache 启用缓存
        return Attribute::make(
            get: function ($value, $attributes) {
                return ! empty($value) ? json_decode_plus($value, true) : [];
            },
            set: function ($value, $attributes) {
                return ! empty($value) ? json_encode((array) $value) : '';
            },
        )->withoutObjectCaching();

    }

    /**
     * 记录采集日志
     *
     * @param  SpiderTask|null  $task  采集任务
     * @param  string  $title  采集提示语。例如：采集完成
     * @param  array|null  $content  内容信息
     * @param  string  $url  可单独传入采集的url ,优先级高于 $task->url
     * @param  int  $status  状态SpiderTasksLog::STATUS_SUCCESS =>成功；SpiderTasksLog::STATUS_FAIL =>失败；
     * @return void
     */
    public static function writeLog(?SpiderTask $task, string $title = '', ?array $content = [], string $url = '', int $status = self::STATUS_SUCCESS)
    {
        self::create([
            'spider_tasks_id' => $task ? $task->id : 0,
            'url' => empty($url) ? $task->url : $url,
            'content' => $content,
            'name' => ($title ?? '').':'.$task->name,
            'status' => $status,
        ]);
    }

    /**
     * 统一记录抛出的异常错误信息
     */
    public static function writeErr(?SpiderTask $task, Exception $err)
    {
        self::writeLog($task, '', [
            '异常信息' => $err->getMessage(), // 返回用户自定义的异常信息
            '异常代码' => $err->getCode(),   // 返回用户自定义的异常代码
            '异常文件' => str_replace(base_path(), '', $err->getFile()),   // 返回发生异常的PHP程序文件名
            '异常行号' => $err->getLine(),   // 返回发生异常的代码所在行的行号
        ], '', SpiderTasksLog::STATUS_FAIL);
    }
}
