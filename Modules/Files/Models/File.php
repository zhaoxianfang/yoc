<?php

namespace Modules\Files\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    // 文档状态
    const STATUS_USED = 1;

    const STATUS_UNUSED = 0;

    public static $statusMaps = [
        self::STATUS_USED => '被引用',
        self::STATUS_UNUSED => '未被引用',
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
     * 只查询 被引用的文件 局部作用域。[局部作用域] 无传参
     *
     * @demo  File::Used()->...
     */
    public function scopeUsed($query)
    {
        $query->where('status', self::STATUS_USED);
    }

    /**
     * 只查询 没有被引用的文件
     */
    public function scopeUnused($query)
    {
        $query->where('status', self::STATUS_UNUSED);
    }

    /**
     * 获取文件访问地址完整路径
     */
    public function getUrl()
    {
        return Storage::disk($this->driver)->url($this->path).'?uni_file='.base_convert_any($this->id, 10, 62);
    }

    /**
     * 获取文件绝对路径
     */
    public function getPath()
    {
        return Storage::disk($this->driver)->path($this->path);
    }

    /**
     * 获取文件大小
     */
    public function getSize()
    {
        return $this->formatSize($this->size);
    }

    /**
     * 人性化显示文件大小
     *
     *
     * @return string
     */
    protected function formatSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $size > 1024; $i++) {
            $size /= 1024;
        }

        return round($size, 2).' '.$units[$i];
    }
}
