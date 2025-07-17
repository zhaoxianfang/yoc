<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 中国五级行政区
 */
class ChinaRegion extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'system_area';

    protected $guarded = ['id'];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
    ];

    // 状态
    public const STATUS_OPEN = 0;

    public const STATUS_CLOSE = 1;

    public static array $statusMaps = [
        self::STATUS_OPEN => '开启',
        self::STATUS_CLOSE => '关闭',
    ];

    /**
     * 通过类型和父级区域编码获取区域列表
     *
     * @param  int  $type  行政级别类型： 1：国；2：省；3：市；4：县；5：街道；6：村
     * @param  string  $parentCode  父级区域编码
     */
    public static function getRegionList(int $type = 2, string $parentCode = '1'): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()->where('type', $type)
            ->where('status', self::STATUS_OPEN)
            ->where('parent_code', $parentCode)
            ->orderByDesc('sort')
            ->orderBy('code')
            ->get();
    }
}
