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

Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    // 重定向到后台首页
    Route::redirect('/', '/admin/dashboard');
    // 后台首页、看板页面
    Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('home');
});
