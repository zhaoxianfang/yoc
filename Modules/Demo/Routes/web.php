<?php

use Illuminate\Support\Facades\Route;
use Modules\Demo\Http\Controllers\Web;

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

Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('', [Web\DemoController::class, 'index'])->name('list');

    // 表格
    Route::prefix('table')->name('table.')->group(function () {
        // Datatables 示例
        Route::get('data_tables', [Web\Table\DataTables::class, 'index'])->name('data_tables');
        Route::get('data', [Web\Table\DataTables::class, 'data'])->name('data');
    });
});
