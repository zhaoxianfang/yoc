<?php

namespace Modules\Docs\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Modules\Docs\Models\DocsApp;
use Modules\Users\Models\User;

class DocsAppPolicy
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
     * 查看文档引导页，例如 help、users、about 等页面
     */
    public function guide(?User $user, DocsApp $docsApp): Response
    {

        // 策略响应
        // 判断 DocsApp 权限

        if ($docsApp->open_type == DocsApp::OPEN_TYPE_OPEN) {
            // 公开文档
            return Response::allow();
        }

        // 仅文档成员可见
        return (! empty($user) && $docsApp->users->contains($user)) ? Response::allow() : Response::deny('无权查看!');
        // return $user->id === $photo->user_id ? Response::allow() : Response::deny('仅创建人可以修改');
    }

    /**
     * 是否可以创建 DocsApp 文档
     */
    public function create(?User $user)
    {
        return ! empty($user) ? Response::allow() : Response::deny('请先登录!');
    }

    /**
     * 是否可以编辑 DocsApp 文档
     */
    public function update(?User $user, DocsApp $docsApp)
    {
        return $docsApp->isManager() ? Response::allow() : Response::deny('无权操作!');
    }

    /**
     * 是否可以创建一级目录 DocsMenu
     */
    public function createTopMenu(?User $user, DocsApp $docsApp)
    {
        return $docsApp->isEditor() ? Response::allow() : Response::deny('无权操作!');
    }
}
