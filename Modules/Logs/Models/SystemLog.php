<?php

namespace Modules\Logs\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Constants\ExceptParams;
use Modules\Users\Models\User;

class SystemLog extends Model
{
    // 日志级别[error:系统异常;warning:警告;notice:普通提示;lowest:最低级别]
    const LEVEL_ERROR = 'error';

    const LEVEL_WARNING = 'warning';

    const LEVEL_NOTICE = 'notice';

    const LEVEL_LOWEST = 'lowest';

    public static $levelMaps = [
        self::LEVEL_ERROR => '异常',
        self::LEVEL_WARNING => '警告',
        self::LEVEL_NOTICE => '普通',
        self::LEVEL_LOWEST => '最低',
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
        'extra' => 'array',
        'content' => 'array',
        'params' => 'array',
        'is_crawler' => 'boolean',
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

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value)->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value)->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
        );
    }

    public function title(): Attribute
    {
        // withoutObjectCaching 禁用属性的缓存
        // shouldCache 启用缓存
        return Attribute::make(
            get: function ($value, $attributes) {
                return $value;
            },
            set: function ($value, $attributes) {
                return ! empty($value) && strlen($value) > 191 ? mb_substr($value, 0, 191) : $value;
            },
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
