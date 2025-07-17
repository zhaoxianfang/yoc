<?php

namespace Modules\Docs\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Users\Models\User;

class DocsAppUser extends Pivot
{
    /**
     * 与模型关联的数据表. 「继承 Pivot 模型的中间表 不走 实体类名的复数形式规则，可以直接定义一个 $table 字段来指明具体的表」
     *
     * @var string
     */
    protected $table = 'docs_app_users';

    /**
     * 不能被批量赋值的属性
     * 如果你想让所有属性都可以批量赋值， 你可以将 $guarded 定义成一个空数组。 如果你选择解除你的模型的保护，你应该时刻特别注意传递给 Eloquent 的 fill、create 和 update 方法的数组：
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    // protected $fillable = [
    //    'options->enabled', // options 是 JSON 列属性
    // ];

    /**
     * 自定义中继模型和递增 ID# 标识 ID 是否自增
     *
     * @var bool
     */
    public $incrementing = true;

    const STATUS_WAIT = 0;

    const STATUS_PASS = 1;

    const STATUS_REJECT = 2;

    const STATUS_OUT = 3;

    public static $statusMaps = [
        self::STATUS_WAIT => '待审',
        self::STATUS_PASS => '通过',
        self::STATUS_REJECT => '驳回',
        self::STATUS_OUT => '踢出',
    ];

    // 用户角色 保留0~9 几个数字权限，数字越大，权限越高
    const ROLE_WAIT = 0;

    const ROLE_PARTNER = 3;

    const ROLE_EDITOR = 5;

    const ROLE_MANAGER = 7;

    const ROLE_FOUNDER = 9;

    public static $rolesMaps = [
        self::ROLE_WAIT => '待审核',
        self::ROLE_PARTNER => '参与者/伙伴',
        self::ROLE_EDITOR => '文档编辑',
        self::ROLE_MANAGER => '管理员',
        self::ROLE_FOUNDER => '创始人',
    ];

    /**
     * 审核员
     */
    public function auditor()
    {
        return $this->hasOne(User::class, 'id', 'audit_id');
    }
}
