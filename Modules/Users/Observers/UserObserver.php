<?php

namespace Modules\Users\Observers;

use Exception;
use Modules\Users\Models\User;

/**
 * 在观察者中 返回 false ,那么操作就无法完成
 * Class UserObserver
 */
class UserObserver
{
    /**
     * 监听数据即将创建的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function creating(User $user)
    {
        $user->uuid = uuid();
    }

    /**
     * 处理 User「created」事件
     *
     *
     * @return void
     */
    public function created(User $user)
    {
        // $user->uuid = uuid();
        // $user->save();
    }

    /**
     * 监听数据即将更新的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function updating(User $user) {}

    /**
     * 监听数据更新后的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function updated(User $user)
    {
        if (empty($user->uuid)) {
            $user->uuid = uuid();
            $user->save();
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
    public function saving(User $user)
    {
        // return false;
    }

    /**
     * 监听数据保存后的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function saved(User $user)
    {
        if (empty($user->uuid)) {
            $user->uuid = uuid();
            $user->save();
        }
    }

    /**
     * 监听数据即将删除的事件。
     *
     *
     * @return void
     */
    public function deleting(User $user)
    {
        // return false;
    }

    /**
     * 监听数据删除后的事件。
     *
     *
     * @return void
     */
    public function deleted(User $user)
    {
        // 删除和用户相关的数据和表
    }
}
