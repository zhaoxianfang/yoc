<?php

use Illuminate\Support\Facades\Route;
use Modules\App\Http\Controllers\Web;

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

Route::prefix('app')->name('app.')->group(function () {
    // 下载页面
    Route::prefix('download')->name('download.')->group(function () {
        Route::get('', [Web\AppController::class, 'download']);
        Route::get('android', [Web\AppController::class, 'android']);
    });
});
