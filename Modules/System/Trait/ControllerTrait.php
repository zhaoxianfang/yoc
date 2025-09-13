<?php

namespace Modules\System\Trait;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

trait ControllerTrait
{
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

    public function success(string|array $resp = '', string $jumpUrl = ''): JsonResponse|RedirectResponse
    {
        $data = ['code' => 200, 'message' => '操作成功'];
        if (is_string($resp)) {
            $data['message'] = $resp;
        } elseif (is_array($resp)) {
            if (! isset($resp['code']) && ! isset($resp['message'])) {
                $data['data'] = $resp;
            } else {
                $data = array_merge($data, $resp);
            }
        }
        if (is_ajax()) {
            return $this->json($data, 200, $jumpUrl);
        }

        return $this->backWithSuccess($data['message']);
    }

    public function error(string|array $resp = '', string $jumpUrl = '')
    {
        $data = ['code' => 500, 'message' => '操作失败'];
        if (is_string($resp)) {
            $data['message'] = $resp;
        } elseif (is_array($resp)) {
            if (! isset($resp['code']) && ! isset($resp['message'])) {
                $data['data'] = $resp;
            } else {
                $data = array_merge($data, $resp);
            }
        }
        if (is_ajax()) {
            return $this->json($data, 500, $jumpUrl);
        }

        return $this->backWithError($data['message']);
    }

    /**
     * json 返回数据
     *
     *
     * @return JsonResponse|Response
     */
    public function successPage(string|array $resp = '', string $jumpUrl = '')
    {
        $code = 200;
        $wait = 3;
        $close = 0;
        $url = $jumpUrl;
        if (! is_string($resp)) {
            $code = ! empty($resp['code']) ? $resp['code'] : $code;
            $message = ! empty($resp['message']) ? $resp['message'] : '操作成功';
            unset($resp['code']);
            unset($resp['message']);
        } else {
            $message = $resp;
        }
        if (! empty($jumpUrl)) {
            $close = 1;
        }

        $data = ! is_string($resp) ? $resp : [];
        if (is_ajax()) {
            return $this->json(compact('code', 'message', 'url', 'wait', 'data', 'close'), $code);
        }
        $module = get_module_name(true); // 使用小写下划线模块名称

        $viewPath = $module.'::tips/success';
        if (! View::exists($viewPath)) {
            return response()->view('errors::2xx', [
                'message' => $message,
            ])->send();
        }

        return response()->view($viewPath, [
            'info' => $message,
            'desc' => '',
            'url' => ! empty($jumpUrl) ? $jumpUrl : 'javascript:;',
            'btn_text' => '',
            'module_name' => $module,
        ], 200)->header('Content-Type', 'text/html');
    }

    /**
     * 手动抛出异常
     *
     *
     * @return JsonResponse|Response
     */
    public function errorPage(string|array $resp = '', string $jumpUrl = '')
    {
        $code = 200;
        $wait = 3;
        $url = $jumpUrl;
        if (! is_string($resp)) {
            $code = ! empty($resp['code']) ? $resp['code'] : $code;
            $message = ! empty($resp['message']) ? $resp['message'] : '出错啦！';
            unset($resp['code']);
            unset($resp['message']);
        } else {
            $message = $resp;
        }

        // throw new Exception($message, $code);
        // return response()->json(compact('code', 'message', 'url', 'wait'), 200)->send();
        // return die(response()->json(compact('code', 'message', 'url', 'wait'), 200)->send());
        if (is_ajax()) {
            return $this->json(compact('code', 'message', 'url', 'wait'), 200);
        }

        $module = get_module_name(true); // 使用小写下划线模块名称

        $viewPath = $module.'::tips/info';
        if (! View::exists($viewPath)) {
            return response()->view('errors::5xx', [
                'message' => $message,
            ])->send();
        }

        return response()->view($viewPath, [
            'info' => $message,
            'desc' => '',
            'url' => ! empty($jumpUrl) ? $jumpUrl : 'javascript:;',
            'btn_text' => '',
            'module_name' => $module,
        ], 200)->header('Content-Type', 'text/html');
    }
}
