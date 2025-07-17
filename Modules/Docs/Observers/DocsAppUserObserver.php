<?php

namespace Modules\Docs\Observers;

use Modules\Docs\Models\DocsAppUser;

/**
 * 在观察者中 返回 false ,那么操作就无法完成
 * Class DocsAppUserObserver
 */
class DocsAppUserObserver
{
    /**
     * 监听数据即将创建的事件。
     */
    public function creating(DocsAppUser $docUser): bool
    {
        // 创始人不能修改 || 不能修改为创始人
        if ($docUser->role == DocsAppUser::ROLE_FOUNDER) {
            return DocsAppUser::where('doc_app_id', $docUser->doc_app_id)->where('role', DocsAppUser::ROLE_FOUNDER)->doesntExist();
        }

        return true;
    }

    /**
     * 处理 User「created」事件
     *
     *
     * @return void
     */
    public function created(DocsAppUser $docUser) {}

    /**
     * 监听数据即将更新的事件。
     *
     *
     * @return false|void
     */
    public function updating(DocsAppUser $docUser)
    {
        // 创始人不能修改 || 不能修改为创始人
        if (in_array(DocsAppUser::ROLE_FOUNDER, [$docUser->getOriginal('role'), $docUser->role])) {
            return false;
        }
    }

    /**
     * 监听数据更新后的事件。
     */
    public function updated(DocsAppUser $docUser) {}

    /**
     * 监听数据即将保存的事件。
     *
     *
     * @return false|void
     */
    public function saving(DocsAppUser $docUser)
    {
        $original = $docUser->getOriginal(); // 获取原始数据
        if (empty($original)) {
            // 新增
            return $this->creating($docUser);
        } else {
            // 修改
            // 创始人不能修改 || 不能修改为创始人
            if (in_array(DocsAppUser::ROLE_FOUNDER, [$docUser->getOriginal('role'), $docUser->role])) {
                return false;
            }
        }
    }

    /**
     * 监听数据保存后的事件。
     */
    public function saved(DocsAppUser $docUser) {}

    /**
     * 监听数据即将删除的事件。
     *
     *
     * @return false|void
     */
    public function deleting(DocsAppUser $docUser)
    {
        // 创始人不能修改 || 不能修改为创始人
        if (in_array(DocsAppUser::ROLE_FOUNDER, [$docUser->getOriginal('role'), $docUser->role])) {
            return false;
        }
    }

    /**
     * 监听数据删除后的事件。
     *
     *
     * @return void
     */
    public function deleted(DocsAppUser $docUser)
    {
        // 删除和用户相关的数据和表
    }

    /**
     * 处理用户「还原」事件。
     */
    public function restored(DocsAppUser $docUser): void
    {
        // ...
    }

    /**
     * 处理用户「强制删除」事件。
     */
    public function forceDeleted(DocsAppUser $docUser): void
    {
        // ...
    }
}
