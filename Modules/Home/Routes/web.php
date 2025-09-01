<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Home\Http\Controllers\Web;

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

Route::prefix('')->group(function () {
    Route::get('', [Web\HomeController::class, 'index'])->name('home');

    // 获取字符串的复数形式
    Route::get('/str/{string}', function (string $string) {
        return '字符串「'.$string.'」的各种形式：<br><br>'
               .'复数(Plural) : '.Str::plural($string).'<br>'
               .'单数(Singular) : '.Str::singular($string).'<br>'
               .'小写(Lower) : '.Str::lower($string).'<br>'
               .'大写(Upper) : '.Str::upper($string).'<br>'
               .'驼峰小写(Snake) : '.Str::snake($string).'<br>'
               .'驼峰大写(Studly) : '.Str::studly($string).'<br>'
               .'标题格式(Title) : '.Str::title($string).'<br>'
               .'长度(Length) : '.Str::length($string).'<br>';
    });
});

// 在线工具
Route::prefix('tools')->name('tools.')->group(function () {
    // 生成器
    Route::prefix('generate')->name('generate.')->group(function () {
        // 身份证生成
        Route::prefix('id_card')->name('id_card.')->group(function () {
            // 身份证生成
            Route::get('', [Web\Tools\Generate\IDCard::class, 'index'])->name('id_card');
            Route::post('', [Web\Tools\Generate\IDCard::class, 'generate'])->name('generate');
        });
    });
    // 字符处理
    Route::prefix('string')->name('string.')->group(function () {
        // css | js 代码压缩
        Route::any('code_minify', [Web\Tools\Code\CodeMinify::class, 'index'])->name('code_minify');
        // unicode 转码
        Route::get('unicode', [Web\Tools\Code\Unicode::class, 'index'])->name('unicode');
        // json 格式化
        Route::get('json', [Web\Tools\Code\JsonTools::class, 'index'])->name('json');
        // serialize 序列话和反序列化
        Route::get('serialize', [Web\Tools\Code\Serialize::class, 'index'])->name('serialize');
        // RSA 加密解密
        Route::any('rsa', [Web\Tools\Generate\RsaEncryption::class, 'index'])->name('rsa');
        // 时间/时区转换
        Route::any('timezone', [Web\Tools\Other\Timezone::class, 'index'])->name('timezone');
    });

    Route::prefix('images')->name('images.')->group(function () {
        // 图片压缩裁剪
        Route::any('compressor', [Web\Tools\Images\Compressor::class, 'index'])->name('img_compressor');
        // 条形码 || 二维码
        Route::any('qrcode', [Web\Tools\Generate\Qrcode::class, 'index'])->name('create_qrcode');

        // 字符串生成图片
        Route::any('create', [Web\Tools\Images\StrToImg::class, 'index'])->name('str2img');
        // 图片转ico
        Route::any('ico', [Web\Tools\Images\ImgToIco::class, 'index'])->name('img2ico');
        // 图片处理工具 imagick
        // Route::any('magic', [Web\Tools\Images\ImagickController::class, 'index'])->name('magic');
        // 下载文件
        // Route::get('download', [Web\Tools\Images\ImagickController::class, 'download'])->name('download');
    });

    // 其他路由

    // demo : /tools/text2png/ApiDoc2.0上线啦/1000/100/FFFFFF/7B00FF/0/qiuhong.html
    Route::get('/text2png/{text}/{width?}/{height?}/{color?}/{bgcolor?}/{rotate?}/{font?}', [Web\Tools\Images\StrToImg::class, 'create']); // ->where('text', '.*');

});

