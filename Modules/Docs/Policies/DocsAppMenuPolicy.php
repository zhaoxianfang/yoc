<?php

namespace Modules\Docs\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Modules\Docs\Models\DocsAppMenu;
use Modules\Users\Models\User;

class DocsAppMenuPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 执行预先授权检查
     *
     * @param  User|null  $user  当前登录用户实例
     * @param  string  $ability  要检查的能力/方法名,例如 show
     */
    public function before(?User $user, string $ability): ?bool
    {
        // 如果 before 返回的是非 null 结果，则该返回将会被视为最终的检查结果
        if (auth('admin')->check() && auth('admin')->user()->is_super) {
            // 允许超管访问
            return true;
        }

        return null;
    }

    /**
     * 是否可以 创建/编辑 目录
     */
    public function editMenu(?User $user, DocsAppMenu $docsAppMenu)
    {
        return $docsAppMenu->canEdit() ? Response::allow() : Response::deny('无权操作!');
    }
}
