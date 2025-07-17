<?php

namespace Modules\Files\Http\Controllers\Web\RichText;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Files\Services\ImagesServices;

/**
 * summernote 上传文件
 * 上传文件名：file
 * 响应格式：
 * {
 *      success : 200
 *      message : "提示的信息，上传成功或上传失败及错误信息等。",
 *      url     : "图片地址"        // 上传成功时才返回
 * }
 */
class Summernote extends Controller
{
    protected static string $fileName = 'file';

    public static function image(Request $request, string $driver = 'img')
    {
        // summernote 上传图片
        $uploadInfo = ImagesServices::instance()->upload(self::$fileName, $driver);

        return response()->json($uploadInfo);
    }
}
