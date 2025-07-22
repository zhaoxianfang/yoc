<?php

namespace Modules\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\System\Contracts\MiddlewareAbstract;
use Symfony\Component\HttpFoundation\Response;

/**
 * auth:admin Admin 模块授权验证中间件
 */
class AdminAuthMiddleware extends MiddlewareAbstract
{
    /**
     * 白名单路由:不需要授权的路由
     *
     * 支持通配符(*)和路径匹配
     * 路径会自动添加 'admin/' 前缀
     */
    protected array $whiteList = [
        'auth/*',
    ];

    /**
     * 登录路由名称
     */
    protected string $loginRoute = 'admin.auth.login';

    /**
     * Admin 模块授权验证中间件
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        if (auth('admin')->guest() || ! auth('admin')->user()->checkAuth()) {
            return to_route($this->loginRoute);
        }

        return $next($request);
    }

    /**
     * 检查请求是否应该跳过验证
     */
    protected function shouldPassThrough(Request $request): bool
    {
        if (in_array('*', $this->whiteList)) {
            return true;
        }

        foreach ($this->whiteList as $uri) {
            $uri = 'admin/'.trim($uri, '/');

            if ($request->fullUrlIs($uri) || $request->is($uri) || $request->routeIs($uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 在响应发送到浏览器后处理任务。
     * 该方法在应用程序的生命周期中最后执行, 通常用于清理任务
     */
    public function terminate(Request $request, Response $response): void {}
}
