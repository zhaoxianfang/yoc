<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\Api;

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

Route::prefix('home')->name('api.home.')->group(function () {
    // Route::get('user', function (Request $request) {
    //     return 'home Api';
    //     return $request->user();
    // });

    Route::get('', [Api\HomeController::class, 'index'])->name('list');
});

// OR 资源路由
// Route::middleware([])->prefix('v1')->group(function () {
//     Route::apiResource('home', Api\HomeController::class)->names('home');
// });
