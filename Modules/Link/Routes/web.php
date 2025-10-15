<?php

use Illuminate\Support\Facades\Route;
use Modules\Link\Http\Controllers\Web;

/*
 |--------------------------------------------------------------------------
 | Web Routes 系统短链接模块路由文件
 |--------------------------------------------------------------------------
 |
 | Here is where you can register web routes for your application. These
 | routes are loaded by the RouteServiceProvider within a group which
 | contains the "web" middleware group. Now create something great!
 |
 */

// Route::pattern('short_type', '^[a-zA-Z]{1,2}$');

// Route::prefix('link')->name('link.')->group(function() {
//    Route::get('', [Web\LinkController::class, 'index'])->name('list');
// });

// 文章短链接: eg: /a/abc123
Route::get('{type}/{short_code}', [Web\LinkController::class, 'index'])
    // ->where('type', '^[a-zA-Z]{1,2}$') // 2 位字母
    ->where('type', '^[a-zA-Z]{1}$') // 1 位字母
    ->name('link');
