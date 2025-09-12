<?php

use Illuminate\Support\Facades\Route;
use Modules\Test\Http\Controllers\Web;

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

Route::prefix('test')->name('test.')->group(function () {
    Route::get('', [Web\TestController::class, 'index'])->name('list');
});
// 资源路由
// Route::resource('test', Web\TestController::class)->names('test');
