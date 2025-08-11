<?php

use Illuminate\Support\Facades\Route;
use Modules\Article\Http\Controllers\Admin;

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

Route::prefix('admin/articles')->name('admin.articles.')->group(function () {

    // 文章管理
    Route::prefix('')->group(function () {
        // 文章列表
        Route::any('', [Admin\ArticlesController::class, 'index'])->name('list');
        Route::get('create', [Admin\ArticlesController::class, 'create'])->name('create');
        Route::post('create', [Admin\ArticlesController::class, 'store'])->name('store');

        Route::get('update/{article}', [Admin\ArticlesController::class, 'edit'])->name('edit');
        Route::post('update/{article}', [Admin\ArticlesController::class, 'update'])->name('update');
        Route::post('delete/{article}', [Admin\ArticlesController::class, 'destroy'])->name('destroy');
    });

    // 文章分类
    Route::prefix('classify')->name('classify.')->group(function () {
        // 文章分类
        Route::any('', [Admin\ArticleClassifyController::class, 'index'])->name('list');
        // 创建
        Route::get('create', [Admin\ArticleClassifyController::class, 'create'])->name('create');
        Route::post('create', [Admin\ArticleClassifyController::class, 'store'])->name('store');

        // 更新
        Route::get('{classify}/update', [Admin\ArticleClassifyController::class, 'edit'])->name('edit');
        Route::post('{classify}/update', [Admin\ArticleClassifyController::class, 'update'])->name('update');

        // 删除
        Route::post('{classify}/delete', [Admin\ArticleClassifyController::class, 'destroy'])->name('destroy');
    });
});
