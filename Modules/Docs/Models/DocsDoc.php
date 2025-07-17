<?php

namespace Modules\Docs\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HigherOrderWhenProxy;

class DocsDoc extends Model
{
    // 公开状态
    const OPEN_TYPE_OPEN = 1;

    const OPEN_TYPE_NEED_LOGIN = 2;

    const OPEN_TYPE_ONLY_SELF = 3;

    const OPEN_TYPE_SENSITIVE = 9;

    public static $openTypeMaps = [
        self::OPEN_TYPE_OPEN => '公开',
        self::OPEN_TYPE_NEED_LOGIN => '登录可见',
        self::OPEN_TYPE_ONLY_SELF => '仅自己可见',
        self::OPEN_TYPE_SENSITIVE => '敏感待审核',
    ];

    // 文档类型
    const TYPE_EDITOR = 1;

    const TYPE_MARKDOWN = 2;

    const TYPE_API = 3;

    public static $typeMaps = [
        self::TYPE_EDITOR => 'editor',
        self::TYPE_MARKDOWN => 'markdown',
        self::TYPE_API => 'api',
    ];

    // 反转
    public static function getTypeReversal(): array
    {
        return array_flip(self::$typeMaps);
    }

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
        'request_headers' => 'array',
        'request_body' => 'array',
        'request_examples' => 'array',
        'response_examples' => 'array',
    ];

    /**
     * 数组中的属性会被隐藏。
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * 默认加载的关联
     *
     * @var array
     */
    //    protected $with = [
    //        'menu'
    //    ];

    /**
     * 模型的「引导」方法。
     *
     * @return void
     */
    protected static function booted() {}

    /**
     * 获取文档内容 实体转html
     */
    public function content(): Attribute
    {
        // withoutObjectCaching 禁用属性的缓存
        // shouldCache 启用缓存
        return Attribute::make(
            get: function ($value, $attributes) {
                return ! empty($value) && $this->type == DocsDoc::TYPE_EDITOR ? html_entity_decode($value) : $value;
            },
            set: function ($value, $attributes) {
                return ! empty($value) && $attributes['type'] == DocsDoc::TYPE_EDITOR ? htmlentities($value) : $value;
            },
        )->withoutObjectCaching();
    }

    public function app()
    {
        return $this->belongsTo(DocsApp::class, 'doc_app_id', 'id');
    }

    public function menu()
    {
        return $this->belongsTo(DocsAppMenu::class, 'doc_menu_id', 'id');
    }

    public function getUrl()
    {
        // return route('docs.doc_info', ['doc' => $this->id]); //
    }

    public function canEdit()
    {
        if (auth()->guest()) {
            return false;
        }
        $userId = auth()->id();
        if ($this->user_id == $userId) {
            return true;
        }

        if ($this->open_type == self::OPEN_TYPE_ONLY_SELF) {
            // 仅自己可见 时 只有自己和管理员可编辑
            return $this->user_id == $userId || $this->app->isManager();
        } else {
            return $this->app->isEditor();
        }
    }

    public function canDelete()
    {
        if (auth()->guest()) {
            return false;
        }
        $userId = auth()->id();
        if ($this->user_id == $userId) {
            return true;
        }

        if ($this->open_type == self::OPEN_TYPE_ONLY_SELF) {
            // 仅自己可见 时 只有自己和管理员可编辑
            return $this->user_id == $userId || $this->app->isManager();
        }

        return false;
    }

    /**
     * 全文搜索文章标题或内容
     *
     * @param  string  $string  搜索的内容
     * @param  int  $limit  每次检索多少条数据，默认20
     * @return array|Builder[]|Collection|\Illuminate\Support\Collection|HigherOrderWhenProxy[]|mixed
     */
    public static function search(string $string, ?DocsApp $docsApp, int $limit = 20)
    {
        $appId = ! empty($docsApp->id) ? $docsApp->id : 0;
        $isLogin = auth('web')->check(); // 是否登录

        // 需要处理好权限问题
        return self::select([
            'id',
            'title',
            DB::raw('substring(content,1,50) AS content'),
        ])
            ->where(function ($query) use ($string) {
                $query->whereFullText(['title', 'content'], to_full_text_search_str($string), ['mode' => 'boolean']);
                $query->orWhere('title', 'like', "%{$string}%");
                $query->orWhere('content', 'like', "%{$string}%");
            })
            ->when($appId, function ($query, $appId) {
                $query->where('doc_app_id', $appId);
            }, function ($query) {
                $query->addSelect('doc_app_id');
                // 继续关联查询出app的名称app_name
                $query->with('app:id,app_name');
            })
            ->when($isLogin, function ($query) {
                // 已经登录用户 可查看 公开的、登录可见、仅自己可见的文档

                $query->where(function ($query) {
                    // 公开和需要登录的
                    $query->whereIn('open_type', [self::OPEN_TYPE_OPEN, self::OPEN_TYPE_NEED_LOGIN]);

                    $query->where(function ($query) {
                        $query->whereHasIn('app', function (Builder $appQuery) {
                            $appQuery->where('status', DocsApp::STATUS_NORMAL); // 状态正常
                            $appQuery->where('open_type', DocsApp::OPEN_TYPE_OPEN); // 应用类型是公开
                            $appQuery->orWhereHasIn('appUsers', function (Builder $appUserQuery) {
                                // TODO:有没有办法区分管理员和编辑者？
                            });
                        });
                    });
                });
            }, function ($query) {
                // 未登录用户 仅可查看 公开的文档
                $query->where('open_type', self::OPEN_TYPE_OPEN); // 文档公开
                $query->whereHasIn('app', function (Builder $appQuery) {
                    $appQuery->where('status', DocsApp::STATUS_NORMAL); // 状态正常
                    $appQuery->where('open_type', DocsApp::OPEN_TYPE_OPEN); // 应用类型是公开
                });
            })
            ->limit($limit)
            ->get();
    }
}
