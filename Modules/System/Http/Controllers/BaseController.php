<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\System\Trait\ControllerTrait;
use Modules\User\Services\TopNav\MessagesService;
use Modules\User\Services\TopNav\NotificationService;

/**
 * 全局基础控制器
 * 继承了此类的控制器，可以在构造函数执行之后，在被调用方法之前，执行初始化方法initialize，initialize方法支持依赖注入
 */
class BaseController extends \zxf\Laravel\Controller\BaseController
{
    use ControllerTrait;

    // auth 认证的名称 eg: admin、web 、api
    protected string $authName = '';

    /**
     * 策略判断(默认使用User模型) 例如： $this->gate::authorize('update', $photo);
     * 设置指定模型的用户判断：$this->gate::forUser(auth('admin')->user())->authorize('update', $article);
     */
    protected string|null|Gate $gate = null;

    // 一级初始化
    protected function initHandle(Request $request): void
    {
        // 初始化策略类
        $this->gate = Gate::class;

        // 共享消息和通知数据到视图
        NotificationService::instance()->toView();
        MessagesService::instance()->toView();
    }
}
