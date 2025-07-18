<?php

namespace Modules\Admin\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// 使用laravel 默认模式 登录
// use Laravel\Sanctum\HasApiTokens;
// 使用 Passport 方式登录 Passport也是支持 session 登录模式滴
use Laravel\Passport\HasApiTokens;
use Modules\Users\Models\User;

class Admin extends Authenticatable
{
    // use HasApiTokens, HasFactory, Notifiable;
    use HasFactory, Notifiable;

    // 超管id
    const SUP_ADMIN_ID = 1;

    /**
     * 不能被批量赋值的属性
     * 如果你想让所有属性都可以批量赋值， 你可以将 $guarded 定义成一个空数组。 如果你选择解除你的模型的保护，你应该时刻特别注意传递给 Eloquent 的 fill、create 和 update 方法的数组：
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * 自动追加的属性值
     * login_group_id ：属性调用了本类中的  loginGroupId() 修改器方法
     *                  如果值为 * 表示 超管组
     * is_super       ：属性调用了本类中的  isSuper() 修改器方法 true：超级管理员 false：非超管
     *
     * @var string[]
     */
    protected $appends = ['login_group_id', 'is_super'];

    /**
     * 默认加载的关联
     *
     * @var array
     */
    protected $with = ['user'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    //    protected $fillable = [
    //        'name',
    //        'email',
    //        'password',
    //    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mobile_verified_at' => 'datetime:Y-m-d H:i:s',
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
    ];

    // 用户状态 0未激活，1正常，2冻结
    const STATUS_NOT_USED = 0;

    const STATUS_NORMAL = 1;

    const STATUS_FREEZE = 2;

    public static $statusMaps = [
        self::STATUS_NOT_USED => '未激活',
        self::STATUS_NORMAL => '正常',
        self::STATUS_FREEZE => '冻结',
    ];

    // 定义超级管理员ID
    public function superAdminId(): int
    {
        return self::SUP_ADMIN_ID;
    }

    // 拥有的 所有角色组列表
    public function groups(): BelongsToMany
    {
        // return $this->belongsToMany(AdminGroup::class, 'admin_group_map', 'admin_id', 'group_id');
        return $this->belongsToMany(AdminGroup::class, AdminGroupMap::class, 'admin_id', 'group_id');
    }

    // 当前登录的 角色组
    public function group()
    {
        $admin = auth('admin');
        $adminGroupId = $admin->check() ? $admin->user()['group_id'] : null;

        return ! empty($adminGroupId) ? AdminGroup::find($adminGroupId) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // 通过 login_group_id 获取值, * 表示超管组
    public function loginGroupId(): Attribute
    {
        // shouldCache 启用缓存
        // withoutObjectCaching 禁用属性的缓存
        return Attribute::make(
            get: function ($value, $attributes) {
                $admin = auth('admin');
                if ($admin->guest()) {
                    return null;
                }
                $adminId = $admin->id();
                if ($adminId == $this->superAdminId()) {
                    return '*';
                }

                if (empty($login_group_id = session('login_group_id'))) {
                    $map = AdminGroupMap::where('admin_id', $adminId)->first();
                    $login_group_id = $map->group_id;
                }

                return ! empty($login_group_id) ? (! empty($group = AdminGroup::find($login_group_id)) ? $group->id : null) : null;
            },
        )->shouldCache();

    }

    /**
     * 是否为超级管理员 $admin->is_super
     * 通过 is_super 获取值, true 表示超级管理员
     */
    public function isSuper(): Attribute
    {
        return new Attribute(
            get: function ($value, $attributes) {
                return auth('admin')->guest() ? false : $this->id == $this->superAdminId();
            }
        );
    }

    // 获取当前登录的管理员 拥有的所有权限（menu or button）
    public function authList() {}

    /**
     * 判断当前管理员是否有 某些权限
     * 通过 check_auth 获取值, true 表示有权限
     *
     * @param  string  $auth  admin_menus表的 name 或者 identify 字段
     *
     * 提示：会去掉 $auth 左侧的 /admin/，重新组装成 admin/xxx;所以传入的权限字符串可以不需要带上 admin/，
     *          例如：'/admin/abc/def' 'admin/abc/def']'和 'abc/def' 都是可以的，都会处理成 'admin/abc/def' 来判断
     */
    public function checkAuth(string $auth = ''): bool
    {
        // 游客无权限
        if (auth('admin')->guest()) {
            return false;
        }

        // 超管有所有权限
        if ($this->is_super || $this->login_group_id == '*') {
            return true;
        }
        // 如果需要验证的权限字符串为空，则返回false
        $auth = empty($auth) ? request()->path() : $auth;
        // $auth 不包含左侧的 admin/ 则加上admin/
        $checkStr = 'admin/'.trim(ltrim(trim($auth, '/'), 'admin/'), '/');

        $groupRules = group_rules();
        // 如果没有当前登录人员对应的管理员组权限数据，则返回false
        if (empty($adminGroup = $groupRules[$this->login_group_id])) {
            return false;
        }

        $hasAuth = false; // 是否有权限
        foreach ($adminGroup as $name => $identify) {
            $nameStr = 'admin/'.trim(ltrim(trim($name, '/'), 'admin/'), '/');
            if (in_array($checkStr, [$nameStr, $identify])) {
                // 跳出foreach循环
                $hasAuth = true;
                break;
            }
        }

        return $hasAuth;
    }
}
