<?php

use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\Web;

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

Route::prefix('system')->name('system.')->group(function () {
    Route::get('', [Web\SystemController::class, 'index'])->name('list');
});
// 资源路由
// Route::resource('system', Web\SystemController::class)->names('system');
