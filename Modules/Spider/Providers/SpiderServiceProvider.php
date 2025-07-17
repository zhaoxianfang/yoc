<?php

namespace Modules\Spider\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Spider\Models\SpiderTask;
use Modules\Spider\Policies\SpiderTaskPolicy;

class SpiderServiceProvider extends ServiceProvider
{
    /**
     * 应用程序的策略映射。
     *
     * @var array
     */
    protected $policies = [
        SpiderTask::class => SpiderTaskPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 加载观察者
        $this->bootObservers();

    }

    // 加载观察者
    protected function bootObservers() {}
}
