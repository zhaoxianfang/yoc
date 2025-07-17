<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\Api;

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

Route::prefix('system')->name('api.system.')->group(function () {
    // Route::get('user', function (Request $request) {
    //     return 'system Api';
    //     return $request->user();
    // });

    Route::get('', [Api\SystemController::class, 'index'])->name('list');
});

// OR 资源路由
// Route::middleware([])->prefix('v1')->group(function () {
//     Route::apiResource('system', Api\SystemController::class)->names('system');
// });
