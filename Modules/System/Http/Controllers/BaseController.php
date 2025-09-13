<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\System\Trait\ControllerTrait;
use Modules\Users\Services\TopNav\MessagesService;
use Modules\Users\Services\TopNav\NotificationService;

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

    public function dataTables($list = [], $total = 0, $errorMsg = ''): \Illuminate\Http\JsonResponse
    {
        // draw 相当于是 datatables 插件需要展示的页码编号，[相当重要][必须有]
        $draw = (int) request()->input('draw', 1);
        if ($errorMsg) {
            return $this->json(['rows' => $list, 'total' => $total, 'draw' => $draw, 'error' => $errorMsg]);
        }

        // DataTables 渲染数据放在 data 或 list 或 rows 里面
        return $this->json(['rows' => $list, 'total' => $total, 'draw' => $draw]);
        // return $this->json([
        //     'list'            => $list, // 数据列表
        //     'recordsTotal'    => $total,// 数据总条数
        //     "draw"            => $draw, // (int)响应计数器
        //     "recordsFiltered" => $total, // (int)筛选后的总记录数
        //     'error'           => $errorMsg, // 注意：仅有错误信息时才返回error字段，请不要返回此字段
        // ]);
    }
}
