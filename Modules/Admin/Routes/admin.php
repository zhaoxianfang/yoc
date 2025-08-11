<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Admin;

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

// 不需要授权的路由
Route::prefix('admin/auth')->name('admin.auth.')->group(function () {
    // 登录
    Route::get('login', [Admin\AdminAuthController::class, 'login'])->name('login');
    Route::post('login', [Admin\AdminAuthController::class, 'store']);
    // 忘记密码
    Route::get('forget_password', [Admin\AdminAuthController::class, 'forgetPassword'])->name('forget_password');
    Route::post('forget_password', [Admin\AdminAuthController::class, 'retrievePassword']);
    // 登出
    Route::get('logout', [Admin\AdminAuthController::class, 'logout'])->name('logout');

    // 第三方登录
    Route::get('qq_login', [Admin\AdminAuthController::class, 'qqLogin'])->name('qq_login');
    Route::get('weibo_login', [Admin\AdminAuthController::class, 'weiboLogin'])->name('weibo_login');
    Route::get('wechat_login', [Admin\AdminAuthController::class, 'wechatLogin'])->name('wechat_login');

    // 第三方登录回调
    Route::any('callback', [Admin\AdminAuthController::class, 'callback'])->name('callback');

});

// 需要授权的路由
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    // 重定向到后台首页
    Route::redirect('/', '/admin/dashboard');
    // 后台首页、看板页面
    Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('home');

    // 系统管理
    Route::prefix('system')->name('system.')->group(function () {
        // 管理员管理
        Route::prefix('admins')->name('admins.')->group(function () {
            Route::any('', [Admin\AdminController::class, 'index'])->name('list');
            Route::any('create', [Admin\AdminController::class, 'store']);
            Route::any('{admin}/edit', [Admin\AdminController::class, 'update']);
            Route::post('{admin}/delete', [Admin\AdminController::class, 'destroy']);

            Route::post('check_field', [Admin\AdminController::class, 'checkField']);
        });
    });
});
