<?php

namespace Modules\System\Services;

/**
 * 继承此服务类的 类 可以通过 YourService::instance() 获取对象实例
 */
class BaseService
{
    /**
     * @var array 对象实例
     */
    protected static array $instance = [];

    /**
     * 初始化类
     */
    public static function instance()
    {
        // $calledClass = \Illuminate\Support\Str::slug(get_called_class(), '');
        $calledClass = static::class;
        if (empty(self::$instance[$calledClass]) || is_null(self::$instance[$calledClass])) {
            self::$instance[$calledClass] = new static(...func_get_args());
        }

        return self::$instance[$calledClass];
    }
}
