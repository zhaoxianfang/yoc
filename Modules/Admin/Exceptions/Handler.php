<?php

namespace Modules\Admin\Exceptions;

use Throwable;
use zxf\Laravel\Trace\LaravelCommonException;

class Handler
{
    /**
     * 自定义本模块下的异常处理
     */
    public function render($request, Throwable $e)
    {
        $code = LaravelCommonException::$code;
        $message = LaravelCommonException::$message;

        if ($request->ajax() || $request->expectsJson()) {
            if(auth('admin')->guest()){
                return app('trace')->respJson($message, $code)->send();
            }
            return response()->json(compact('code', 'message'));
        } else {
            if(auth('admin')->guest()){
                return app('trace')->respView($message, $code)->send();
            }
            return response()->view('admin::tips/error', compact('code', 'message'), 200)->header('Content-Type', 'text/html');
        }
    }
}
