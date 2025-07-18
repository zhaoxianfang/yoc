<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class AdminGroup extends Model
{
    protected $guarded = ['id'];

    // 状态
    const STATUS_NORMAL = 1;

    const STATUS_CLOSE = 0;

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
    ];

    // 管理员组下关联的菜单和按钮权限
    public function menus()
    {
        // 多对对关联
        return $this->belongsToMany(AdminMenu::class, AdminRole::class, 'group_id', 'menu_id')->where('admin_menus.status', 1);
    }
}
