<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\Api;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
 */

Route::prefix('admin')->name('api.admin.')->group(function () {
    // Route::get('user', function (Request $request) {
    //     return 'admin Api';
    //     return $request->user();
    // });

    Route::get('', [Api\AdminController::class, 'index'])->name('list');
});

// OR 资源路由
// Route::middleware([])->prefix('v1')->group(function () {
//     Route::apiResource('admin', Api\AdminController::class)->names('admin');
// });
