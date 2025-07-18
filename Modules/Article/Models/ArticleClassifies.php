<?php

namespace Modules\Article\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Models\Admin;

class ArticleClassifies extends Model
{
    // 开放状态；0：待审，1：正常，2:不公开，3:敏感待审核
    const STATUS_WAIT = 0;

    const STATUS_NORMAL = 1;

    const STATUS_PROTECT = 2;

    const STATUS_SENSITIVE = 3;

    public static array $statusMaps = [
        self::STATUS_WAIT => '待审核',
        self::STATUS_NORMAL => '正常',
        self::STATUS_PROTECT => '不公开',
        self::STATUS_SENSITIVE => '敏感待审核',
    ];

    // nav导航展示; 0:不展示；1仅移动端(app);2仅后台;3都展示
    const SHOW_NAV_DISABLE = 0;

    const SHOW_NAV_ONLY_APP = 1;

    const SHOW_NAV_ONLY_WEB = 2;

    const SHOW_NAV_ALL = 3;

    public static array $showNavMaps = [
        self::SHOW_NAV_DISABLE => '不展示',
        self::SHOW_NAV_ONLY_APP => '仅移动端(app)',
        self::SHOW_NAV_ONLY_WEB => '仅后台(WEB)',
        self::SHOW_NAV_ALL => '都展示',
    ];

    // type; 类型；1：用户发布，2:爬虫采集
    const TYPE_DEFAULT = 1;

    const TYPE_SPIDER = 2;

    public static array $typeMaps = [
        self::TYPE_DEFAULT => '用户发布',
        self::TYPE_SPIDER => '爬虫采集',
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
        // "rules" => 'array',
    ];

    // 截断表
    public function truncate()
    {
        // return self::truncate();
    }

    /**
     * 模型的「引导」方法。
     */
    protected static function booted(): void {}

    /**
     * 只查询 文档标识为公开的 局部作用域。[局部作用域] 无传参
     *
     * @demo  ArticleClassifies::open()->...
     *        ArticleClassifies::open()->orWhere->...
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeOpen($query)
    {
        $query->where('status', self::STATUS_NORMAL);
    }

    // 创建人
    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    // 父级分类
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'pid', 'id');
    }

    // 此分类下的文章
    public function articles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Article::class, 'classify_id', 'id');
    }
}
