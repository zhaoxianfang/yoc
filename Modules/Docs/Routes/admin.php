<?php

use Illuminate\Support\Facades\Route;
use Modules\Docs\Http\Controllers\Admin;

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

Route::prefix('admin/docs')->name('admin.docs.')->group(function () {
    // 文档管理
    Route::prefix('')->group(function () {
        Route::get('list', [Admin\DocsController::class, 'index'])->name('list');
    });
});
