<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\Demo\Http\Controllers\Api;

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

Route::prefix('demo')->name('api.demo.')->group(function () {
    // Route::get('user', function (Request $request) {
    //     return 'demo Api';
    //     return $request->user();
    // });

    Route::get('', [Api\DemoController::class, 'index'])->name('list');
});

// OR 资源路由
// Route::middleware([])->prefix('v1')->group(function () {
//     Route::apiResource('demo', Api\DemoController::class)->names('demo');
// });
