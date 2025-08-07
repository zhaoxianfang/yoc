<?php

use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\Admin;

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

// 需要授权的路由
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    // 系统管理
    Route::prefix('system')->name('system.')->group(function () {
        // 系统配置
        Route::prefix('config')->name('config.')->group(function () {
            Route::get('', [Admin\SystemConfigController::class, 'index'])->name('config');
            Route::post('', [Admin\SystemConfigController::class, 'store']);
            // 系统配置验证 字段唯一性 ,使用type 字段区分
            Route::any('unique', [Admin\SystemConfigController::class, 'checkUnique'])->name('check_unique');
        });

        // 菜单管理
        Route::prefix('admin_menus')->name('admin_menus.')->group(function () {
            Route::any('', [Admin\AdminMenuController::class, 'index'])->name('list');
            Route::any('create', [Admin\AdminMenuController::class, 'store']);
            Route::any('{menus}/edit', [Admin\AdminMenuController::class, 'update']);
            Route::post('{menus}/delete', [Admin\AdminMenuController::class, 'delete']);
        });

        // 角色组管理
        Route::prefix('admin_groups')->name('admin_groups.')->group(function () {
            Route::get('', [Admin\AdminGroupController::class, 'index'])->name('list');
            Route::any('create', [Admin\AdminGroupController::class, 'store']);
            Route::any('{group}/edit', [Admin\AdminGroupController::class, 'update']);
            Route::post('{group}/delete', [Admin\AdminGroupController::class, 'delete']);
            Route::post('get_tree', [Admin\AdminGroupController::class, 'getTree']);
        });

        // 清理系统各种数据
        Route::prefix('clear')->name('admin_groups.')->group(function () {
            // 清理系统配置缓存
            Route::post('setting', [Admin\ClearDataController::class, 'setting']);
        });
    });
});
