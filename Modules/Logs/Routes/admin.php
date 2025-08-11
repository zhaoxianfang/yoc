<?php

use Illuminate\Support\Facades\Route;
use Modules\Logs\Http\Controllers\Admin;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // 系统管理
    Route::prefix('system')->name('system.')->group(function () {
        // 系统日志管理
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::any('', [Admin\SystemLogsController::class, 'index'])->name('logs');
            Route::any('{log}/detail', [Admin\SystemLogsController::class, 'info'])->name('log_info');
        });
    });
});
