<?php

namespace Modules\Admin\Exceptions;

use Modules\Core\Exceptions\CustomHandler;
use Throwable;

class Handler
{
    /**
     * 自定义本模块下的异常处理
     */
    public function render($request, Throwable $e)
    {
        $code = CustomHandler::$code;
        $message = CustomHandler::$message;

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(compact('code', 'message'));
        } else {
            return response()->view('admin::tips/error', compact('code', 'message'), 200)->header('Content-Type', 'text/html');
        }
    }
}
