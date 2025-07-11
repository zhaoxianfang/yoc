<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Web;

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

Route::prefix('core')->name('core.')->group(function() {
    Route::get('', [Web\CoreController::class, 'index'])->name('list');
});
// 资源路由
// Route::resource('core', Web\CoreController::class)->names('core');
