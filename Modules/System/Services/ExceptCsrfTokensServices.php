<?php

namespace Modules\System\Services;

/**
 * 排除 csrf 拦截的路由
 */
class ExceptCsrfTokensServices
{
    public static array $except = [
        //  内部会使用 $request->fullUrlIs($except) || $request->is($except) 判断是否需要跳过csrf验证
        '*/callback',
        'callback/*',
        // 'docs/*',
        // 'docs/doc/*/create/*/markdown',
        // 'docs/doc/*/update',
        'admin/*',
        'demo/*',
        'tools/*',
        '*/auth/*',
        'test/*',
    ];
}
