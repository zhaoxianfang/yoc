<?php

namespace Modules\Callback\Http\Controllers\Web\Wechat;

use Modules\Callback\Http\Controllers\Web\CallbackController;
use Modules\Test\Models\Test;
use zxf\Facade\Request;
use zxf\Pay\WechatPayFactory;

/**
 * 微信支付/退款回调
 */
class Pay extends CallbackController
{
    /**
     * 微信支付回调
     */
    public function payed(string $type = 'default')
    {
        try {
            Test::write('支付请求头：$header', Request::headers());
            Test::write('支付body', file_get_contents('php://input') ?? $GLOBALS['HTTP_RAW_POST_DATA']);
            WechatPayFactory::JsApi($type)->payed(function ($data) {
                Test::write('微信支付回调数据', $data ?? '');

                return true;
            });
        } catch (\Exception $e) {
            Test::write('微信支付 Err:'.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
                // 'trace' => $e->getTraceAsString(),
                // 'trace_arr' => $e->getTrace(),
            ]);

            return false;
        }
    }

    /**
     * 微信退款回调
     */
    public function refunded(string $type = 'default')
    {
        try {
            Test::write('退款请求头：$header', Request::headers());
            Test::write('退款body', file_get_contents('php://input') ?? $GLOBALS['HTTP_RAW_POST_DATA']);
            WechatPayFactory::JsApi($type)->refunded(function ($data) {
                Test::write('微信退款回调数据', $data ?? '');

                return true;
            });
        } catch (\Exception $e) {
            Test::write('微信退款 Err:'.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
                // 'trace' => $e->getTraceAsString(),
                // 'trace_arr' => $e->getTrace(),
            ]);

            return false;
        }
    }
}
