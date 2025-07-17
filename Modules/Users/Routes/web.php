<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\Web;

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

Route::prefix('users')->name('users.')->group(function () {
    Route::get('', [Web\UsersController::class, 'index'])->name('list');
});
// 资源路由
// Route::resource('users', Web\UsersController::class)->names('users');
