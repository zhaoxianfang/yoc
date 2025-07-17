<?php

namespace Modules\Spider\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Modules\Admin\Models\Admin;
use Modules\Spider\Models\SpiderTask;

class SpiderTaskPolicy
{
    use HandlesAuthorization;

    protected $admin;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $this->admin = auth('admin')->user();
    }

    /**
     * 判断是否有权创建
     */
    public function create(?Admin $admin): Response
    {
        return $this->admin->id === 1 ? Response::allow() : Response::denyWithStatus(403, '无权操作！');
    }

    /**
     * 确定用户是否可以更新
     */
    public function update(?Admin $admin, SpiderTask $task): Response
    {
        // 策略判断
        // 策略响应
        return $this->admin->id === 1 ? Response::allow() : Response::denyWithStatus(403, '无权操作！');
    }

    /**
     * 确定用户是否可以删除
     */
    public function destroy(?Admin $admin, SpiderTask $task): Response
    {
        return $this->admin->id === 1 ? Response::allow() : Response::denyWithStatus(403, '无权操作！');
    }
}
