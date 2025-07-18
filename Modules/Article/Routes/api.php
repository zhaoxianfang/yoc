<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\Article\Http\Controllers\Api;

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

Route::prefix('article')->name('api.article.')->group(function () {
    // Route::get('user', function (Request $request) {
    //     return 'article Api';
    //     return $request->user();
    // });

    Route::get('', [Api\ArticleController::class, 'index'])->name('list');
});

// OR 资源路由
// Route::middleware([])->prefix('v1')->group(function () {
//     Route::apiResource('article', Api\ArticleController::class)->names('article');
// });
