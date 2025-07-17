<?php

namespace Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 黑名单管理
 */
class Blacklist extends Model
{
    protected $guarded = ['id'];

    // 类型：0:疑似非法请求(观测中) 1:非法请求(拦截请求) 2:白名单(允许访问)
    const TYPE_SUSPICIOUS = 0;

    const TYPE_INTERCEPT = 1;

    public static array $typeMaps = [
        self::TYPE_SUSPICIOUS => '可疑的',
        self::TYPE_INTERCEPT => '黑名单',
    ];

    /**
     * 类型转换
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
        ];
    }
}
