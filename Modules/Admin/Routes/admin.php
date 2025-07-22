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
});

// 需要授权的路由
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    // 重定向到后台首页
    Route::redirect('/', '/admin/dashboard');
    // 后台首页、看板页面
    Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('home');
});
