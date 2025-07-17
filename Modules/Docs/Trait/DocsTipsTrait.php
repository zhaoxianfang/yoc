<?php

namespace Modules\Docs\Trait;

use Illuminate\Http\Response;

/**
 * 在线文档 docs 操作状态提示页面
 */
trait DocsTipsTrait
{

    /**
     * 提示页面
     *
     * @param string $message
     * @param string $describe
     * @param array  $btn
     * @param string $type
     *
     * @return Response
     */
    protected function tips(string $message = '', string $describe = '', array $btn = [], string $type = 'info'): Response
    {
        if (empty($btn) || ! isset($btn['url'])) {
            $btn = [
                'text' => '返回文档首页',
                'url' => '/docs',
            ];
        }

        return response()
            ->view("docs::tips/{$type}", compact('message', 'describe', 'btn'), 200)
            ->header('Content-Type', 'text/html')
            ->send();
    }

    /**
     * 错误提示页面
     *
     * @param string $message
     * @param string $describe
     * @param array  $btn
     *
     * @return Response
     */
    public function tip_error(string $message = '', string $describe = '', array $btn = []): Response
    {
        return $this->tips($message, $describe, $btn, 'error');
    }

    /**
     * 成功提示页面
     *
     * @param string $message
     * @param string $describe
     * @param array  $btn
     *
     * @return Response
     */
    public function tip_success(string $message = '', string $describe = '', array $btn = []): Response
    {
        return $this->tips($message, $describe, $btn, 'success');
    }

    /**
     * 警告提示页面
     *
     * @param string $message
     * @param string $describe
     * @param array  $btn
     *
     * @return Response
     */
    public function tip_warning(string $message = '', string $describe = '', array $btn = []): Response
    {
        return $this->tips($message, $describe, $btn, 'warning');
    }

    /**
     * 提示页面
     *
     * @param string $message
     * @param string $describe
     * @param array  $btn
     *
     * @return Response
     */
    public function tip_info(string $message = '', string $describe = '', array $btn = []): Response
    {
        return $this->tips($message, $describe, $btn, 'info');
    }
}
