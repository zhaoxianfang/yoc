<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\App\Http\Controllers\Api;

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

// Route::middleware('auth:api')->get('/app', function (Request $request) {
//    return $request->user();
// });

// 不需要登录
Route::prefix('app')->name('app.')->group(function () {
    // 最新的app版本
    Route::post('version', [Api\AppVersionController::class, 'index'])->name('version');
    Route::any('submit_logs', [Api\AppLogsController::class, 'store'])->name('submit_logs');

    Route::prefix('home')->name('home.')->group(function () {
        // 获取app首页顶部nav导航
        Route::get('top_nav', [Api\Home\TopNavController::class, 'list'])->name('home_top_nav');
    });

    // App端 获取旋转图片验证码
    Route::get('rotate_verify/get_img/{random}', [Api\AppRotateVerifyController::class, 'getImg'])->name('rotate_verify');
    // 验证结果
    Route::post('rotate_verify/check', [Api\AppRotateVerifyController::class, 'check']);
});

// 需要登录
Route::prefix('app')->name('app.')->middleware('auth:api')->group(function () {
    // 用户信息
    Route::get('info', function (Request $request) {
        return $request->user();
    });
});
