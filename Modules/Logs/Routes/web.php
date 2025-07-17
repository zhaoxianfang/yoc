<?php

use Illuminate\Support\Facades\Route;
use Modules\Logs\Http\Controllers\Web;

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

Route::prefix('logs')->name('logs.')->group(function () {
    Route::get('', [Web\LogsController::class, 'index'])->name('list');
});
// 资源路由
// Route::resource('logs', Web\LogsController::class)->names('logs');
