<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\Core\Exceptions\CommonException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 去重复报告的异常,确保单个实例的异常只被报告一次
        $exceptions->dontReportDuplicates();

        // 自定义异常处理类
        $customException = new CommonException;

        // 定义不需要被报告的异常
        $exceptions->dontReport($customException->getDontReport());

        // 全局接管所有继承了 \Exception 的异常 报告
        $exceptions->report(function (\Throwable $e) use ($customException) {
            $customException->report($e);
        })->stop(); // 调用 stop() 阻止异常传播到默认的日志记录栈

        // 全局接管所有继承了 \Exception 的异常 渲染
        $exceptions->render(function (\Throwable $e, Request $request) use ($customException) {
            return $customException->render($request, $e);
        });
    })
    ->create();
