<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use zxf\Laravel\Modules\Traits\PathNamespace;

class CoreServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        // 加载观察者
        $this->bootObservers();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // 事件注册
        $this->app->register(EventServiceProvider::class);
    }

    // 加载观察者
    protected function bootObservers()
    {
        //
    }
}
