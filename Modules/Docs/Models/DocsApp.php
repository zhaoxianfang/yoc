<?php

namespace Modules\Docs\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\Users\Models\User;

class DocsApp extends Model
{
    // use SoftDeletes; // SoftDeletes trait 会自动将 deleted_at 属性转换为 DateTime / Carbon 实例。

    /**
     * 与模型关联的数据表.
     *
     * @var string
     */
    // protected $table = 'docs_apps';

    /**
     * 与数据表关联的主键.
     *
     * @var string
     */
    // protected $primaryKey = 'id';

    /**
     * 指明模型的 ID 是不是自增。
     *
     * @var bool
     */
    // public $incrementing = true;

    /**
     * 自增 ID 的数据类型。
     *
     * @var string || integer/string...
     */
    // protected $keyType = 'integer';

    /**
     * 指示模型是否主动维护时间戳。 created_at 和 updated_at
     *
     * @var bool
     */
    // public $timestamps = true;

    /**
     * 模型日期字段的存储格式。
     *
     * @var string
     */
    // protected $dateFormat = 'U';

    // 需要自定义 时间字段名
    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    // protected $connection = 'sqlite';

    /**
     * 默认加载的关联
     *
     * @var array
     */
    // protected $with = ['menus'];

    // 公开状态
    const OPEN_TYPE_OPEN = 1;

    const OPEN_TYPE_INTERNAL = 2;

    public static $openTypeMaps = [
        self::OPEN_TYPE_OPEN => '公开',
        self::OPEN_TYPE_INTERNAL => '仅文档内部成员可见',
    ];

    // 文档状态
    const STATUS_NORMAL = 1;

    const STATUS_DISABLE = 0;

    public static $statusMaps = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '停用',
    ];

    // 默认封面图
    const DEFAULT_COVER_PATH = '/static/modules/docs/images/default_apps_cover.jpeg';

    // 默认封面图
    const DEFAULT_VIEW_PAGE = 'docs::apps.help';

    /**
     * 模型的属性默认值。 自动赋值属性
     *
     * @var array
     */
    protected $attributes = [
        'status' => 1,
        'theme' => 'default',
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urls' => 'array',
        ];
    }

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

    /**
     * 只查询 文档标识为公开的 局部作用域。[局部作用域] 无传参
     *
     * @demo  DocsApp::Open()->...
     *        DocsApp::open()->orWhere->...
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeOpen($query)
    {
        $query->where('open_type', self::OPEN_TYPE_OPEN);
        $query->where('status', self::STATUS_NORMAL);
    }

    /**
     * 将查询作用域限制为仅包含给定类型的用户。 [动态作用域] 可传参
     *
     * @demo   DocsApp::ofOpen(2)
     *
     * @return mixed
     */
    public function scopeOfOpen($query, $openType)
    {
        return $query->where('open_type', $openType);
    }

    /**
     * 「我的」文档
     *
     * @demo   DocsApp::mine()
     *
     * @return void
     */
    public function scopeMine($query)
    {
        $query->where('create_by', auth('web')->id())
            ->orWhereHasIn('appUsers', function (Builder $builder) {
                $builder->where('user_id', auth('web')->id());
            });
    }

    // 获取文档「正式」成员列表 多对多
    public function users()
    {
        return $this->belongsToMany(
            User::class,        // 最终想要访问的模型的名称
            DocsAppUser::class, // 中间模型的名称
            'doc_app_id',       // 中间表 映射 本模型 表的外键名
            'user_id',          // 中间表 映射 目标 表的外键名
            'id',
            'id'
        )
            ->where('docs_app_users.role', '>', DocsAppUser::ROLE_WAIT)
            ->where('docs_app_users.status', DocsAppUser::STATUS_PASS)
            ->withPivot('audit_id', 'extra_nickname', 'role', 'status')
            ->withTimestamps();
    }

    // 获取文档「申请」人员列表 多对多
    public function applyUsers()
    {
        return $this->belongsToMany(
            User::class,        // 最终想要访问的模型的名称
            DocsAppUser::class, // 中间模型的名称
            'doc_app_id',       // 中间表 映射 本模型 表的外键名
            'user_id',          // 中间表 映射 目标 表的外键名
            'id',
            'id'
        )->where('role', DocsAppUser::ROLE_WAIT)
            ->withPivot('audit_id', 'extra_nickname', 'role', 'status')
            ->withTimestamps();
    }

    // 获取「中间表」数据
    public function appUsers()
    {
        return $this->hasMany(DocsAppUser::class, 'doc_app_id', 'id')
            ->where('role', '<>', DocsAppUser::ROLE_WAIT)
            ->where('status', DocsAppUser::STATUS_PASS);
    }

    // 文档的菜单列表
    public function menus()
    {
        return $this->hasMany(DocsAppMenu::class, 'doc_app_id', 'id')->where('parent_id', 0)->orderByDesc('sort')->orderBy('created_at');
    }

    // 文档列表
    public function docs()
    {
        return $this->hasMany(DocsDoc::class, 'doc_app_id', 'id')->orderByDesc('sort')->orderBy('created_at');
    }

    // 定义一个访问器
    // 当前登录用在本文档中的角色 $app->user_role
    protected function userRole(): Attribute
    {
        return Attribute::make(
            // get: fn ($value) use ($this) => $this->getUserRole(),
            get: function () {
                //                if ($this->userRole) {
                //                    return $this->userRole;
                //                }
                $userId = auth('web')->guest() ? null : auth('web')->id();
                // 游客 和 待审人员都 标识为 0;
                // 0:待审核，3：参与者/伙伴：5：文档编辑；7：管理员，9：创始人
                $role = $userId ? DocsAppUser::where('doc_app_id', $this->id)->where('user_id', $userId)->where('status', DocsAppUser::STATUS_PASS)->value('role') : 0;

                //                $this->userRole = $role;
                return (int) $role;
            }
        );
    }

    /**
     * 判断当前文档是否是「我的」,只要加入到文档的就算
     */
    public function isMine(): bool
    {
        if (auth()->guest() || empty($this->id)) {
            return false;
        }
        $userId = auth()->id();
        if ($this->create_by == $userId) {
            return true;
        }

        // return $this->users->contains($userId);
        return $this->appUsers->where('user_id', $userId);
    }

    /**
     * 判断「我」是否为此文档的编辑
     */
    public function isEditor(): bool
    {
        if (auth()->guest() || empty($this->id)) {
            return false;
        }
        $userId = auth()->id();
        if ($this->create_by == $userId) {
            return true;
        }

        // $role = $this->users->where('id', $userId)->first()?->pivot?->role;
        // return $this->users->contains($userId) && ($role >= DocsAppUser::ROLE_EDITOR);
        return $this->appUsers->where('user_id', $userId)->where('role', '>=', DocsAppUser::ROLE_EDITOR)->exists();
    }

    /**
     * 判断「我」是否为此文档的管理员
     */
    public function isManager(): bool
    {
        if (auth()->guest() || empty($this->id)) {
            return false;
        }
        $userId = auth()->id();
        if ($this->create_by == $userId) {
            return true;
        }

        // $role = $this->users->where('id', $userId)->first()?->pivot?->role;
        // return $this->users->contains($userId) && ($role >= DocsAppUser::ROLE_MANAGER);
        return $this->appUsers->where('user_id', $userId)->where('role', '>=', DocsAppUser::ROLE_MANAGER)->exists();
    }

    /**
     * 判断「我」是否为此文档的创建者
     */
    public function isSuper(): bool
    {
        if (auth()->guest() || empty($this->id)) {
            return false;
        }

        return $this->create_by == auth()->id();
    }
}
