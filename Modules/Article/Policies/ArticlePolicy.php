<?php

namespace Modules\Article\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Modules\Admin\Models\Admin;
use Modules\Article\Models\Article;

class ArticlePolicy
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
     * @param  Admin|null  $admin
     * @param  string  $ability  要检查的能力/方法名,例如 show
     */
    public function before(Admin $admin, string $ability): ?bool
    {
        // 如果 before 返回的是非 null 结果，则该返回将会被视为最终的检查结果
        return null;
    }

    public function update(?Admin $admin, Article $article): Response
    {
        // 仅超管可以操作
        return $admin->is_super ? Response::allow() : Response::deny('无权操作!');
    }

    public function delete(?Admin $admin, Article $article): Response
    {
        // 仅超管可以操作
        return $admin->is_super ? Response::allow() : Response::deny('无权操作!');
    }
}
