<?php

namespace Modules\Docs\Observers;

use Exception;
use Modules\Docs\Models\DocsApp;

/**
 * 在观察者中 返回 false ,那么操作就无法完成
 * Class DocsAppObserver
 */
class DocsAppObserver
{
    /**
     * 监听数据即将创建的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function creating(DocsApp $app)
    {
        $app->uni_code = uuid();
    }

    /**
     * 处理 User「created」事件
     *
     *
     * @return void
     */
    public function created(DocsApp $app) {}

    /**
     * 监听数据即将更新的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function updating(DocsApp $app) {}

    /**
     * 监听数据更新后的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function updated(DocsApp $app)
    {
        if (empty($app->uni_code)) {
            $app->uni_code = uuid();
            $app->save();
        }
    }

    /**
     * 监听数据即将保存的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function saving(DocsApp $app) {}

    /**
     * 监听数据保存后的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function saved(DocsApp $app)
    {
        if (empty($app->uni_code)) {
            $app->uni_code = uuid();
            $app->save();
        }
    }

    /**
     * 监听数据即将删除的事件。
     *
     *
     * @return void
     */
    public function deleting(DocsApp $app)
    {
        // return false;
    }

    /**
     * 监听数据删除后的事件。
     *
     *
     * @return void
     */
    public function deleted(DocsApp $app)
    {
        // 删除和用户相关的数据和表
    }
}
