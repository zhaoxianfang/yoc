<?php

use Illuminate\Support\Facades\Route;
use Modules\Files\Http\Controllers\Web;

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

Route::prefix('files')->group(function () {
    // Route::get('/', 'FilesController@index');

    Route::prefix('uploads')->name('uploads.')->group(function () {
        // eg:/files/uploads/editor/img/docs
        Route::post('{richText}/{fileType}/{driver?}', [Web\UploadsController::class, 'store']);
    });
});
