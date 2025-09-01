<?php

namespace Modules\Admin\Exceptions;

use Throwable;
use zxf\Laravel\Trace\Handle;

class Handler
{
    /**
     * 自定义本模块下的异常处理
     */
    public function render($request, Throwable $e)
    {
        $code = Handle::$code;
        $message = Handle::$message;

        if (is_ajax()) {
            // 404 错误获取不到登录信息
            if (auth('admin')->guest()) {
                return app('trace')->respJson($message, $code)->send();
            }

            return response()->json(compact('code', 'message'));
        } else {
            // 404 错误获取不到登录信息
            if (auth('admin')->guest()) {
                return app('trace')->respView($message, $code);
            }

            return response()->view('admin::tips/error', compact('code', 'message'), 200)->header('Content-Type', 'text/html');
        }
    }
}
