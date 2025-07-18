<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Web;

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

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('', [Web\AdminController::class, 'index'])->name('list');
});
// 资源路由
// Route::resource('admin', Web\AdminController::class)->names('admin');
