<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 公共中间件【放在前面】
        $middleware->append(\Modules\Core\Http\Middleware\CommonBaseMiddleware::class);
        // 安全拦截配
        $middleware->append(\zxf\Laravel\Modules\Middleware\SecurityMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 接入异常处理类
        \zxf\Laravel\Trace\LaravelCommonException::initLaravelException($exceptions);

        // 全局接管所有继承了 \Exception 的异常 渲染
        // $exceptions->render(function (\Throwable $e, Request $request) {
        //     dd($e);
        // });
    })
    ->create();
