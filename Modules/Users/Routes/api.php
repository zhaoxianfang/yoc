<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\Api;

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

Route::prefix('users')->name('api.users.')->group(function () {
    // Route::get('user', function (Request $request) {
    //     return 'users Api';
    //     return $request->user();
    // });

    Route::get('', [Api\UsersController::class, 'index'])->name('list');
});

// OR 资源路由
// Route::middleware([])->prefix('v1')->group(function () {
//     Route::apiResource('users', Api\UsersController::class)->names('users');
// });
