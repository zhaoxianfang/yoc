<?php

namespace Modules\Users\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use zxf\Laravel\Modules\Traits\PathNamespace;

class UsersServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Users';

    protected string $nameLower = 'users';

    /**
     * 启动应用程序事件
     */
    public function boot(): void
    {
        // 配置 Passport 令牌生命周期
        $this->configPassport();

        // 加载观察者
        $this->bootObservers();

        $this->registerCommands();
        $this->registerCommandSchedules();
    }

    /**
     * 注册服务提供
     */
    public function register(): void
    {
        // 事件注册
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * 以以下格式注册命令 Command::class
     * 主要是针对 不是 config('modules.paths.generator.command.path') 的一级目录的 Command
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * 注册定时命令计划
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     // 每周一和周四早上5点0分执行 command:test 任务调度
        //     $schedule->command('custom:command')->cron('0 5 * * 1,4');
        //     // 每周在周一的下午 1 点运行一次...
        //     $schedule->call(function () {
        //         // ...
        //     })->weekly()->mondays()->at('13:00');
        // });
    }

    /**
     * 获取提供商提供的服务.
     */
    public function provides(): array
    {
        return [];
    }

    // 加载观察者
    protected function bootObservers()
    {
        //
    }

    // 配置 Passport 令牌生命周期
    protected function configPassport(): void
    {
        // 默认情况下，Passport 发布的是长期有效的访问令牌，一年后到期
        Passport::tokensExpireIn(now()->addDays(30)); // 令牌有效期时间（天）
        Passport::refreshTokensExpireIn(now()->addDays(30)); // 刷新令牌有效期时间（天）
        Passport::personalAccessTokensExpireIn(now()->addMonths(6)); // 个人访问令牌有效期时间（月）
    }
}
