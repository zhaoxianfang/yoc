<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\Admin;

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

Route::prefix('admin/users')->name('admin.users.')->group(function () {
    // 用户管理
    Route::prefix('member')->name('member.')->group(function () {
        Route::any('', [Admin\MemberController::class, 'index'])->name('list');
    });

    // 黑名单ip管理
    Route::prefix('blacklist')->name('blacklist.')->group(function () {
        Route::any('', [Admin\BlacklistController::class, 'index'])->name('list');

        Route::any('create', [Admin\BlacklistController::class, 'store']);
        Route::any('{blacklist}/edit', [Admin\BlacklistController::class, 'update']);
        Route::post('{blacklist}/delete', [Admin\BlacklistController::class, 'destroy']);
    });
});
