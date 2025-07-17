<?php

namespace Modules\Files\Http\Controllers\Web\RichText;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Files\Services\FilesServices;
use Modules\Files\Services\ImagesServices;

/**
 * ckeditor4 上传文件
 * url: https://ckeditor.com/docs/ckeditor4/latest/index.html
 * 上传文件名：upload
 * 响应格式：
 * {
 *      "uploaded": 0 | 1,              // 0 表示上传失败，1 表示上传成功
 *      "fileName": "foo(2).jpg",       // 上传成功时才返回的文件名
 *      "url": "/files/foo(2).jpg",     // 上传成功时才返回的文件地址
 *      "error": {                      // 上传失败时才返回
 *          "message": "A file with the same name already exists. The uploaded file was renamed to \"foo(2).jpg\"."
 *      }
 * }
 */
class CkEditor extends Controller
{
    protected static string $fileName = 'upload';

    public static function img(Request $request, string $driver = 'img')
    {
        // 模拟失败
        return response()->json(['uploaded' => 0, 'error' => ['message' => '上传失败']]);

        $uploadInfo = ImagesServices::instance()->upload(self::$fileName, $driver);

        // ckeditor4 上传文件返回格式
        $res = [
            'uploaded' => $uploadInfo['code'] == 200 ? 1 : 0, // 1成功；0失败
            'fileName' => $uploadInfo['code'] == 200 ? $uploadInfo['filename'] : '', // 提示信息
            'url' => $uploadInfo['url'],
            'callback' => $_REQUEST['ckCsrfToken'] ?? '',
        ];
        if ($uploadInfo['code'] != 200) {
            $res['error'] = [
                'message' => $uploadInfo['message'] ?? '上传异常',
            ];
        }

        return response()->json($res);
    }

    // 上传文件
    public static function file(Request $request, string $driver = 'file')
    {
        // 模拟失败
        return response()->json(['uploaded' => 0, 'error' => ['message' => '上传失败']]);

        // 上传文件名：upload
        $uploadInfo = FilesServices::instance()->upload(self::$fileName, $driver);

        // ckeditor4 上传文件返回格式
        $res = [
            'uploaded' => $uploadInfo['code'] == 200 ? 1 : 0, // 1成功；0失败
            'fileName' => $uploadInfo['code'] == 200 ? $uploadInfo['filename'] : '', // 提示信息
            'url' => $uploadInfo['url'],
            'callback' => $_REQUEST['ckCsrfToken'] ?? '',
        ];
        if ($uploadInfo['code'] != 200) {
            $res['error'] = [
                'message' => $uploadInfo['message'] ?? '上传异常',
            ];
        }

        return response()->json($res);

    }
}
