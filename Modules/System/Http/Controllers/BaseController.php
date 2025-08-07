<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
// use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\System\Trait\ControllerTrait;

/**
 * Class BaseController 如果是已经登录的用户，判断是否需要刷新 csrf_token
 */
class BaseController extends Controller
{
    use ControllerTrait;

    // auth 认证的名称 eg: admin、web 、api
    protected string $authName = '';

    /**
     * 策略判断(默认使用User模型) 例如： $this->gate::authorize('update', $photo);
     * 设置指定模型的用户判断：$this->gate::forUser(auth('admin')->user())->authorize('update', $article);
     */
    protected string|null|Gate $gate = null;

    public function __construct(Request $request)
    {
        // 此处未加载完中间件, 所以无法使用auth('admin')->check() 等操作

        // 添加一个最后执行的中间件，此时其他中间件已经加载完毕
        $this->middleware(function ($request, $next) {
            // 中间件基本加载完毕
            $this->initHandle($request);
            // 在路由调用方法之前，先调用初始化方法initialize
            // 给控制器新增 initialize 生命周期方法，可用于初始化
            // 甚至可以代替 构造函数，实现依赖注入
            before_calling_methods($this, 'initialize');

            return $next($request);
        });
    }

    // 检查token ，防止CSRF 验证失败
    private function initHandle(Request $request): void
    {
        // 初始化策略类
        $this->gate = Gate::class;
    }

    public function dataTablesXX($list = [], $total = 0, $errorMsg = '')
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
