<?php

use Illuminate\Support\Facades\Route;
use Modules\Home\Http\Controllers\Web;

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

Route::prefix('')->group(function() {
    Route::get('', [Web\HomeController::class, 'index'])->name('home');
});
// 资源路由
// Route::resource('home', Web\HomeController::class)->names('home');
