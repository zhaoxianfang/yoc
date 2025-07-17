<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//    return view('welcome');
// });

// Temp Api:auth 登录页面
Route::get('login', function (Request $request) {
    $message = '请先登录';
    $code = 401;
    if ($request->expectsJson() || $request->ajax()) {
        return app('trace')->respJson($message, $code)->send();
    } else {
        return app('trace')->respView($message, $code)->send();
    }
})->name('login');
