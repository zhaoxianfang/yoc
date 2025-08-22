<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\Spider\Services\SpiderTasksService;
use Modules\System\Services\SecurityServices;
use Modules\Task\Services\CronTaskService;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 公共中间件【放在前面】
        $middleware->append(\Modules\System\Http\Middleware\CommonBaseMiddleware::class);

        // 安全拦截
        // $middleware->append(\zxf\Laravel\Modules\Middleware\SecurityMiddleware::class);
        // [高级用法]安全拦截配置[配置中的每一项都是可选的]
        $securityConfig = [
            'whitelist_path_of_not_verify_body' => SecurityServices::$whitelistPathOfNotVerifyBody, // 不需要验证请求body的请求uri 列表（仅path）
            'blacklist_handle' => [SecurityServices::class, 'blacklistIp'], // 黑名单ip处理类 ; 要求返回结构 [bool(是否拦截),'拦截信息']
            'send_security_alarm_handle' => [SecurityServices::class, 'sendSecurityAlarm'], // 发送安全告警
            'allow_methods' => SecurityServices::$allowMethods, // 允许的请求方式
            'reg_exp_body' => SecurityServices::$banRegExpBody, // 自定义正则匹配拦截请求body
            'reg_exp_url' => SecurityServices::$banRegExpUrl, // 自定义正则匹配拦截请求 URL
            'forbid_upload_file_ext' => SecurityServices::$forbidUploadFileExt, // 禁止上传的文件后缀
            'forbid_user_agent' => SecurityServices::$forbidUserAgent, // 禁止包含的 User-Agent
            'ajax_resp_format' => SecurityServices::$ajaxRespFormat, // API/Ajax 请求拦截时的返回参数格式
            'custom_handle' => [SecurityServices::class, 'customHandle'], // 自定义拦截处理
        ];
        $encoded = base64_encode(json_encode($securityConfig));
        $middleware->append(\zxf\Laravel\Modules\Middleware\SecurityMiddleware::class.':'.$encoded);
        // $middleware->append(\zxf\Laravel\Modules\Middleware\SecurityMiddleware::class);

        // Csrf 排除
        $middleware->validateCsrfTokens(except: \Modules\System\Services\ExceptCsrfTokensServices::$except);

        // 添加到分组，定义不存在的分组
        $middleware->prependToGroup('admin', [
            // 复制 laravel 默认的web中间件组进来
            Illuminate\Cookie\Middleware\EncryptCookies::class,
            Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            Illuminate\Session\Middleware\StartSession::class,
            Illuminate\View\Middleware\ShareErrorsFromSession::class,
            Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // 追加到分组,组已经存在，例如：web、api
        $middleware->appendToGroup('admin', [
            // Custom admin Middleware
            \Modules\System\Http\Middleware\AdminAuthMiddleware::class,
            // 'throttle:global_admin',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 接入异常处理类
        \zxf\Laravel\Trace\CustomExceptionHandler::handle($exceptions, function ($code, $message) {
            if ($code == 401) {
                return to_route('login');
            }
        }, [401]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // 自定任务调度

        // 爬虫自定义定时任务
        SpiderTasksService::customCronTasks($schedule);

        // 调度普通定时任务
        CronTaskService::handle($schedule);
    })
    ->create();
