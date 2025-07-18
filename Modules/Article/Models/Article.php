<?php

namespace Modules\Article\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\Spider\Contracts\SpiderArticleInterface;
use Modules\Users\Models\User;

class Article extends Model implements SpiderArticleInterface
{
    // 文档类型；1：富文本，2：Markdown
    const TYPE_EDITOR = 1;

    const TYPE_MARKDOWN = 2;

    public static $typeMaps = [
        self::TYPE_EDITOR => '富文本',
        self::TYPE_MARKDOWN => 'Markdown',
    ];

    // 开放状态；0：待审，1：正常，2:不公开，3:敏感待审核
    const STATUS_WAIT = 0;

    const STATUS_NORMAL = 1;

    const STATUS_PROTECT = 2;

    const STATUS_SENSITIVE = 3;

    public static $statusMaps = [
        self::STATUS_WAIT => '待审核',
        self::STATUS_NORMAL => '正常',
        self::STATUS_PROTECT => '不公开',
        self::STATUS_SENSITIVE => '敏感待审核',
    ];

    // 文章来源类型；1：用户发布，2:爬虫采集
    const SOURCE_TYPE_USER = 1;

    const SOURCE_TYPE_SPIDER = 2;

    public static $sourceTypeMaps = [
        self::SOURCE_TYPE_USER => '用户发布',
        self::SOURCE_TYPE_SPIDER => '爬虫采集',
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
    protected $fillable = [
        'user_id',
        'classify_id',
        'title',
        'content',
        'summary',
        'author',
        'publish_time',
        'sort',
        'type',
        'read',
        'like',
        'spider',
        'source_type',
        'source_url',
        'created_at',
        'updated_at',
        'status',
    ];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
        // "rules" => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

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
    protected static function booted()
    {
        // 模型事件 处理
        static::creating(function ($model) {
            $model->title = zxf_substr($model->title, 0, 190);                  // 限制标题长度
            $model->content = mb_substr($model->content, 0, 4294967000, 'UTF-8'); // 限制长度
        });
        static::updating(function ($model) {
            $model->title = zxf_substr($model->title, 0, 190);                  // 限制标题长度
            $model->content = mb_substr($model->content, 0, 4294967000, 'UTF-8'); // 限制长度
        });
        static::saving(function ($model) {
            $model->title = zxf_substr($model->title, 0, 190);                  // 限制标题长度
            $model->content = mb_substr($model->content, 0, 4294967000, 'UTF-8'); // 限制长度
        });
    }

    public static function getEditorTypeValue(): int
    {
        return self::TYPE_EDITOR;
    }

    public static function getSourceTypeSpiderValue(): int
    {
        return self::SOURCE_TYPE_SPIDER;
    }

    public static function getStatusNormalValue(): int
    {
        return self::STATUS_NORMAL;
    }

    //    protected function createdAt(): Attribute
    //    {
    //        return Attribute::make(
    //            get: fn(string $value) => Carbon::parse($value)->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
    //        );
    //    }

    /**
     * 全文搜索文章标题或内容
     *
     * @param  string  $string  搜索的内容
     * @param  int  $limit  每次检索多少条数据，默认20
     */
    public static function search(string $string, int $limit = 20)
    {
        try {
            return self::query()
                ->select([
                    'id', 'title',
                    'content',
                    'user_id', 'classify_id', 'summary', 'author', 'publish_time', 'sort',
                    'type', 'read', 'like', 'spider', 'source_type', 'source_url',
                    'created_at', 'updated_at', 'status',
                ])
                ->where(function ($query) use ($string) {
                    $query->whereFullText(['title', 'content'], to_full_text_search_str($string), [
                        'expanded' => true, // 是否查询扩展(MySQL) (会进行二次搜索)
                        'mode' => 'boolean', // 模式: natural/boolean(MySQL)
                    ]);
                })
                ->where('status', self::STATUS_NORMAL)
                ->paginate($limit);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function classify()
    {
        return $this->belongsTo(ArticleClassifies::class, 'classify_id', 'id');
    }

    /**
     * 插入一条文章数据
     *
     *
     * @return bool
     */
    public static function insertRow($data)
    {
        // 插入一条文章数据
        $article = new Article($data);

        return $article->save();
    }
}
