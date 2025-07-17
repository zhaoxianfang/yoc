<?php

use Illuminate\Support\Facades\Route;
use Modules\Spider\Http\Controllers\Admin;

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

Route::prefix('admin/spider')->name('admin.spider.')->group(function () {
    // 爬虫管理
    Route::prefix('')->group(function () {
        Route::any('list', [Admin\SpiderController::class, 'index'])->name('list');

        Route::get('list/create', [Admin\SpiderController::class, 'create'])->name('create');
        Route::post('list/create', [Admin\SpiderController::class, 'store'])->name('store');

        Route::get('list/{task}/edit', [Admin\SpiderController::class, 'edit'])->name('edit');
        Route::post('list/{task}/edit', [Admin\SpiderController::class, 'update'])->name('update');

        Route::post('list/{task}/delete', [Admin\SpiderController::class, 'destroy'])->name('destroy');

        // 爬虫规则测试
        Route::any('list/rule_test', [Admin\SpiderController::class, 'ruleTest'])->name('rule_test');
    });
});
