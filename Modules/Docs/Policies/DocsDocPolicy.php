<?php

namespace Modules\Docs\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsAppMenu;
use Modules\Docs\Models\DocsDoc;
use Modules\Users\Models\User;

class DocsDocPolicy
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
        if (auth('admin')->check() && auth('admin')->user()->is_super) {
            // 允许超管访问
            return true;
        }

        // 如果 before 返回的是非 null 结果，则该返回将会被视为最终的检查结果
        return null;
    }

    /**
     * 确定用户是否可以查看文档
     * 还需加强权限控制
     */
    public function show(?User $user, DocsDoc $docsDoc): Response
    {
        $docsApp = $docsDoc->app; // App: 文档应用
        $docsMenu = $docsDoc->menu; // Menu 文档目录

        // 1、全公开文档

        // 文档公开 && 应用公开 && 目录公开
        if ($docsDoc->open_type == DocsDoc::OPEN_TYPE_OPEN
            && $docsApp->open_type == DocsApp::OPEN_TYPE_OPEN
            && $docsMenu->open_type == DocsAppMenu::OPEN_TYPE_OPEN) {
            return Response::allow();
        }

        if ($docsDoc->open_type == DocsDoc::OPEN_TYPE_SENSITIVE) {
            // 敏感文档
            return Response::denyWithStatus(403, '内容包含敏感信息，暂不可见!');
        }
        // 2、需要登录或授权的情况

        // 游客
        if (empty($user)) {
            return Response::denyWithStatus(403, '未登录不可见');
        }
        if (
            // 文档仅自己可见
            ($docsDoc->open_type == DocsDoc::OPEN_TYPE_ONLY_SELF && $user->id != $docsDoc->user_id)
            // 目录仅自己可见
            || ($docsMenu->open_type == DocsAppMenu::OPEN_TYPE_ONLY_SELF && $user->id != $docsMenu->user_id)
        ) {
            // 仅自己可见
            return Response::denyWithStatus(403, '未授权!');
        }

        // 仅文档成员可见
        return $docsApp->users->contains($user->id) ? Response::allow() : Response::deny('未授权!');
        // return $user->id === $photo->user_id ? Response::allow() : Response::deny('仅创建人可以修改');
    }

    public function update(?User $user, DocsDoc $docsDoc): Response
    {
        return $docsDoc->canEdit() ? Response::allow() : Response::deny('未授权!');
    }

    public function delete(?User $user, DocsDoc $docsDoc): Response
    {
        return $docsDoc->canDelete() ? Response::allow() : Response::deny('未授权!');
    }
}
