<?php

use Illuminate\Support\Facades\Route;
use Modules\Article\Http\Controllers\Web;

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

Route::pattern('article', '[0-9]+');
Route::pattern('classify', '[0-9]+');

// 博客 文章和分类
Route::prefix('article')->name('article.')->group(function () {
    // 获取某个分类下的文章列表
    Route::get('classify/{classify}', [Web\ArticleClassifyController::class, 'show'])->name('classify');

    // 查看文章详情
    Route::get('{article}', [Web\ArticleController::class, 'show'])
        ->name('detail')
        // 正则匹配{article}参数，支持数字 或者 数字.html 两种方式; eg: article/1 或者 article/1.html
        ->where('article', '^\d+(?:\.html)?$');
});

// 资源路由
// Route::resource('article', Web\ArticleController::class)->names('article');
