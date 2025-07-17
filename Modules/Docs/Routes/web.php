<?php

use Illuminate\Support\Facades\Route;
use Modules\Docs\Http\Controllers\Web;

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

Route::pattern('id', '[0-9]+');
Route::pattern('ids', '[,0-9]+');

Route::pattern('name', '[a-zA-Z]+');

Route::pattern('docsApp', '[0-9]+');
Route::pattern('docsDoc', '[0-9]+');
Route::pattern('docsAppMenu', '[0-9]+');
Route::pattern('user', '[0-9]+');

Route::prefix('docs')->name('docs.')->group(function () {
    // 文档首页
    Route::get('', [Web\DocsHomeController::class, 'index'])->name('home');
    // 我的文档
    Route::get('my', [Web\DocsHomeController::class, 'my'])->name('my');

    // App 应用管理 新增、编辑、删除
    Route::prefix('')->group(function () {
        // 搜索
        Route::any('search/{docsApp?}', [Web\DocsAppSearchController::class, 'index'])->name('search');

        // 文档app应用操作
        Route::get('{docsApp}', [Web\DocsAppController::class, 'index'])->name('app_home');
        Route::get('{docsApp}/help', [Web\DocsAppManageController::class, 'help'])->name('app_help');

        Route::get('create', [Web\DocsAppManageController::class, 'create'])->name('create_app');
        Route::post('create', [Web\DocsAppManageController::class, 'store'])->name('store_app');

        Route::get('{docsApp}/edit', [Web\DocsAppManageController::class, 'edit'])->name('app_edit');
        Route::post('{docsApp}/edit', [Web\DocsAppManageController::class, 'update']);

        Route::get('{docsApp}/users', [Web\DocsAppManageController::class, 'users'])->name('app_users');

    });

    // Doc 文档管理 新增、编辑、删除
    Route::prefix('doc')->name('doc.')->group(function () {
        // 文章内容详情
        Route::get('{docsApp}_{docsDoc}', [Web\DocsDocController::class, 'detail'])->name('show');
        // 仅仅处理ajax 请求的doc详情数据
        Route::get('doc_{docsDoc}', [Web\DocsDocController::class, 'onlyDoc'])->name('only_doc');

        // 编辑文章
        Route::get('{docsDoc}/update', [Web\DocsDocController::class, 'edit'])->name('edit');
        Route::post('{docsDoc}/update', [Web\DocsDocController::class, 'update']);
        // 删除文章
        Route::post('{docsDoc}/delete', [Web\DocsDocController::class, 'destroy']);

        // 创建文章
        Route::get('{docsApp}/create/{docsAppMenu}/{type}', [Web\DocsDocController::class, 'create'])->name('create_markdown');
        Route::post('{docsApp}/create/{docsAppMenu}/{type}', [Web\DocsDocController::class, 'store']);
    });

    // 文档目录管理
    Route::prefix('menus')->name('menus.')->group(function () {
        Route::post('{docsApp}/store', [Web\DocsMenusController::class, 'store']);
        Route::post('{menu}/store_child', [Web\DocsMenusController::class, 'storeSubMenu']);

        Route::post('{menu}/update', [Web\DocsMenusController::class, 'update']);
        Route::post('{menu}/delete', [Web\DocsMenusController::class, 'destroy']);
    });

    // 登录注册授权页
    Route::prefix('auth')->name('auth.')->group(function () {
        // 登录页
        Route::any('login', [Web\DocsAuthController::class, 'login'])->name('login');
        Route::any('register', [Web\DocsAuthController::class, 'register'])->name('register');
        Route::any('logout', [Web\DocsAuthController::class, 'logout'])->name('logout');

        // 第三方登录
        Route::get('qq_login', [Web\DocsAuthController::class, 'qq'])->name('qq_login');
        Route::get('weibo_login', [Web\DocsAuthController::class, 'weibo'])->name('weibo_login');

        Route::post('callback', [Web\DocsAuthController::class, 'callback'])->name('callback');
    });

    // 用户申请加入文档审核
    Route::prefix('apply')->name('apply.')->group(function () {
        // 申请加入文档页面
        Route::get('{docsApp}', [Web\DocsAppUserApplyController::class, 'apply'])->name('entry');
        Route::post('{docsApp}', [Web\DocsAppUserApplyController::class, 'store'])->name('store');
    });

    // 文档用户管理
    Route::prefix('user')->name('user.')->group(function () {
        // 申请加入文档时候 通过QQ登录、微博登录等登录方式后回调回来的用户信息接收
        Route::any('login/callback', [Web\DocsAppUsersController::class, 'loginCallback']);
        // 文档申请用户审核通过
        Route::post('{docsApp}/pass/{user}/role', [Web\DocsAppUsersController::class, 'agreeToJoin']);
        // 文档申请用户审核拒绝
        Route::post('{docsApp}/refuse/{user}', [Web\DocsAppUsersController::class, 'refuseToJoin']);
        // 踢出文档正式用户
        Route::post('{docsApp}/kick_out/{user}', [Web\DocsAppUsersController::class, 'kickOutUser']);
    });

});
