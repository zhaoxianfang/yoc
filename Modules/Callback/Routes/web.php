<?php

use Illuminate\Support\Facades\Route;
use Modules\Callback\Http\Controllers\Web;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('callback')->name('callback.')->group(function () {
    // Route::get('/', [Web\CallbackController::class, 'tips']);

    Route::prefix('wechat')->name('wechat.')->group(function () {
        // 微信登录
        Route::any('login', [Web\Wechat\OAuth::class, 'login'])->name('login');
        // 微信服务端回调
        Route::any('notify', [Web\Wechat\Notify::class, 'index']);
        // 微信支付/退款回调
        Route::any('payed/{type}', [Web\Wechat\Pay::class, 'payed']);
        Route::any('refunded/{type}', [Web\Wechat\Pay::class, 'refunded']);
    });

    Route::prefix('tencent')->name('tencent.')->group(function () {
        Route::any('login', [Web\Tencent\Connect::class, 'login'])->name('login');
        Route::any('callback', [Web\Tencent\Connect::class, 'callback']);
        Route::any('notify', [Web\Tencent\Connect::class, 'notify']);
    });

    Route::prefix('weibo')->name('weibo.')->group(function () {
        Route::any('login', [Web\Weibo\Sina::class, 'login'])->name('login');
        Route::any('callback', [Web\Weibo\Sina::class, 'callback']);
        Route::any('cancel', [Web\Weibo\Sina::class, 'notify']);
    });

    // 支付宝通知
    Route::prefix('alipay')->name('alipay.')->group(function () {
        Route::any('web_app', [Web\Alipay\WebApp::class, 'index'])->name('web_app');
    });
});
