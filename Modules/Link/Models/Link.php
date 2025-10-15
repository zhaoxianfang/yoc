<?php

namespace Modules\Link\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Modules\Link\Models\Constants\LinkType;

/**
 * 短链接
 *
 * @property int $id
 * @property string $short_code 短码:短链接编码
 * @property string $url 原始长链接
 * @property LinkType $type 链接类型
 * @property Carbon $expire_at 过期时间
 * @property int $click_count 点击次数
 * @property int $is_active 是否激活，0=禁用，1=启用
 * @property int $user_id 创建用户ID
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Link extends Model
{
    protected $guarded = ['id'];
}
