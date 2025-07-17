<?php

use Illuminate\Support\Facades\Route;
use Modules\Spider\Http\Controllers\Web;

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

Route::prefix('spider')->group(function () {
    // Route::get('/', [Web\SpiderController::class, 'index']);
});
