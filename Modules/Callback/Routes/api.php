<?php

use Illuminate\Support\Facades\Route;
use Modules\Callback\Http\Controllers\Api;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('callback')->name('callback.')->group(function () {
    // 移动端(app) 使用openid、access_token 等 交换登录用户信息
    Route::prefix('userinfo')->name('userinfo.')->group(function () {
        Route::any('qq', [Api\User\Tencent::class, 'tokenToUserInfo']);
        Route::any('weibo', [Api\User\Sina::class, 'tokenToUserInfo']);
    });
});
