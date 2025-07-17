<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\Admin;

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

Route::prefix('admin/task')->name('admin.task.')->group(function () {
    // 定时任务管理
    Route::prefix('cron')->name('cron.')->group(function () {
        Route::get('', [Admin\CronTaskController::class, 'index'])->name('list');

        Route::get('create', [Admin\CronTaskController::class, 'create'])->name('create');
        Route::post('create', [Admin\CronTaskController::class, 'store'])->name('store');

        Route::get('{task}/edit', [Admin\CronTaskController::class, 'edit'])->name('edit');
        Route::post('{task}/edit', [Admin\CronTaskController::class, 'update'])->name('update');

        Route::post('{task}/delete', [Admin\CronTaskController::class, 'destroy'])->name('destroy');

        // Cron 介绍
        Route::get('cron_help', [Admin\CronTaskController::class, 'cronHelp'])->name('cron_help');
    });
});
