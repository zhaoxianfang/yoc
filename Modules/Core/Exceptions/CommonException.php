<?php

namespace Modules\Core\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Core\Trait\ExceptionShowDebugHtmlTrait;
use Modules\Core\Trait\ExceptionTrait;
use Throwable;

class CommonException // extends Handler
{
    use ExceptionShowDebugHtmlTrait,ExceptionTrait;

    // 定义不需要被报告的异常类
    public function getDontReport(): array
    {
        return [
            // \...\xxException::class,
        ];
    }

    /**
     * 报告异常：负责记录异常（后台操作）
     */
    public function report(Throwable $e): void
    {
        // 初始化错误信息
        $this->initError($e);
        // 记录日志
        $this->writeLog($e);
        // dd($e);
    }

    /**
     * 渲染异常为 HTTP 响应：负责显示异常（用户可见的响应）
     */
    public function render($request, Throwable $e): Response|JsonResponse
    {
        // 如果模块下定义了自定义的异常接管类 Handler，则交由模块下的异常类自己处理
        if ($this->hasModuleCustomException()) {
            return $this->handleModulesCustomException($e, $request);
        }

        // 调试模式
        if (config('app.debug')) {
            return $this->debug($e);
        }

        // 判断路径 : 不是get的api 或 json 请求
        if (($request->is('api/*') || ! $request->isMethod('get')) || $request->expectsJson()) {
            return $this->respJson(self::$message, self::$code)->send();
        } else {
            return $this->respView(self::$message, self::$code)->send();
        }
    }

    private function debug(Throwable $e): Response|JsonResponse
    {
        $content = [
            '异常提示' => self::$isSysErr ? $e->getMessage() : self::$message,   // 返回用户自定义的异常信息
            '状态码' => self::$code,      // 返回用户自定义的异常代码
            '异常文件' => str_replace(base_path(), '', $e->getFile()),      // 返回发生异常的PHP程序文件名
            '异常行号' => $e->getLine(),        // 返回发生异常的代码所在行的行号
            'code_string' => $this->getExceptionContent($e),      // 异常代码片段
        ];
        return $this->outputDebugHtml($content);
    }
}
