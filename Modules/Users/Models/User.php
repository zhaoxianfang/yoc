<?php

namespace Modules\Users\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// 使用 Passport 方式登录 Passport也是支持 session 登录模式滴
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 不能被批量赋值的属性
     * 如果你想让所有属性都可以批量赋值， 你可以将 $guarded 定义成一个空数组。 如果你选择解除你的模型的保护，你应该时刻特别注意传递给 Eloquent 的 fill、create 和 update 方法的数组：
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * 可大量赋值的属性。
     *
     * @var array
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
     * 类型转换
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime:Y-m-d H:i:s',
            'password' => 'hashed',
        ];
    }

    // 用户状态 0未激活，1正常，2冻结
    const STATUS_NOT_USED = 0;

    const STATUS_NORMAL = 1;

    const STATUS_FREEZE = 2;

    public static array $statusMaps = [
        self::STATUS_NOT_USED => '未激活',
        self::STATUS_NORMAL => '正常',
        self::STATUS_FREEZE => '冻结',
    ];
}
