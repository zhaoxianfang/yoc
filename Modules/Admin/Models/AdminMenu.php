<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
    protected $guarded = ['id'];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
    ];
}
