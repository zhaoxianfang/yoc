<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\MiddlewareAbstract;
use Modules\Logs\Models\Crawler;
use Symfony\Component\HttpFoundation\Response;

class CommonBaseMiddleware extends MiddlewareAbstract
{
    /**
     * 通用基础中间件,在未报错时，会记录日志内容
     *
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // -------------------------
        // 获取渲染的视图路径     开始
        // -------------------------
        $viewPath = '';
        // 获取视图名称（仅适用于视图响应）
        if ($response instanceof \Illuminate\View\View) {
            // 存储到共享数据中
            $viewPath = $response->getName();
        }
        // 判断 $response 里面是否有 original属性
        if (property_exists($response, 'original')) {
            if ($response->original instanceof \Illuminate\View\View) {
                $viewPath = $response->original->getName();
            }
        }
        $request->attributes->set('resp_view_path', $viewPath); // 存储到请求
        // -------------------------
        // 获取渲染的视图路径     结束
        // -------------------------

        return $response;
    }

    /**
     * 在响应发送到浏览器后处理任务。
     * 该方法在应用程序的生命周期中最后执行, 通常用于清理任务
     */
    public function terminate(Request $request, Response $response): void
    {
        // 判断 $url 是否是 js、css、图片等资源文件
        if (is_resource_file($request->fullUrl())) {
            return;
        }

        $message = '普通';
        // 会执行，但是不会响应到浏览器
        $errorInfo = ! empty($response->exception) ? $response->exception->getMessage() : null; // 错误信息 null|string

        // 获取响应状态码
        $responseCode = $response->getStatusCode();

        if (empty($errorInfo)) {
            // 其他地方没有标记为已经记录过日志,就要记录访问日志；eg: $request->merge(['log_already_recorded' => true]);
            if (! $request->has('log_already_recorded')) {
                if (! empty($crawlerName = is_crawler(true))) {
                    $message = "[爬虫:{$crawlerName}]";
                } else {
                    if (is_qq_browser()) {
                        $message = '[QQ]'.$message;
                    }
                    if (is_wechat_browser()) {
                        $message = '[微信]'.$message;
                    }
                    if (is_weibo_browser()) {
                        $message = '[微博]'.$message;
                    }
                    if (is_alipay_browser()) {
                        $message = '[支付宝]'.$message;
                    }
                }

                $respString = $response->getContent(); // 响应内容

                $context = [
                    'request' => $request->input(),
                    'response' => is_json($respString) ? json_decode($respString, true) : 'HTML PAGE!',
                    'view_path' => $request->attributes->get('resp_view_path', ''), // 响应的视图路径
                ];
                $intercept = $request->attributes->get('intercept', ''); // 被拦截的信息
                // 当前环境是生产环境
                if (App::environment('production')) {
                    // 写入自定义渠道文件日志
                    Log::info("[{$responseCode}]".(! empty($intercept) ? "[{$intercept}]" : '').$message, $context);
                } else {
                    // 写入本地文件日志
                    Log::channel('stack')->info("[{$responseCode}]".(! empty($intercept) ? "[{$intercept}]" : '').$message.' : '.$request->fullUrl(), $context);
                }
            }
        }
        // 爬虫检测
//        Crawler::checkCrawlerAndWrite();
    }
}
