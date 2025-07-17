<?php

namespace Modules\Files\Http\Controllers\Web\RichText;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Files\Services\FilesServices;
use Modules\Files\Services\ImagesServices;

/**
 * editor.md 上传文件
 * 上传文件名：editormd-image-file
 * 响应格式：
 * {
 *      success : 0 | 1,           // 0 表示上传失败，1 表示上传成功
 *      message : "提示的信息，上传成功或上传失败及错误信息等。",
 *      url     : "图片地址"        // 上传成功时才返回
 * }
 */
class EditorMd extends Controller
{
    protected static string $fileName = 'editormd-image-file';

    public static function img(Request $request, string $driver = 'img')
    {
        // editormd 上传图片的默认文件名为 editormd-image-file
        $uploadInfo = ImagesServices::instance()->upload(self::$fileName, $driver);

        // editormd 上传文件返回格式
        $res = [
            'success' => $uploadInfo['code'] == 200 ? 1 : 0, // 1成功；0失败
            'message' => $uploadInfo['message'], // 提示信息
            'url' => $uploadInfo['url'],
            'dialog_id' => $request->input('guid'),
        ];

        return response()->json($res);
    }

    // 上传文件
    private static function file(Request $request, string $driver = 'file')
    {
        $uploadInfo = FilesServices::instance()->upload(self::$fileName, $driver);

        $res = [
            'code' => $uploadInfo['code'] == 200 ? 1 : 0, // 1成功；0失败
            'url' => $uploadInfo['url'] ?? '', // 1成功；0失败
            'fileName' => $uploadInfo['filename'], // 1成功；0失败
            'callback' => $_REQUEST['ckCsrfToken'] ?? '',
            'message' => $uploadInfo['code'] == 200 ? '' : $uploadInfo['message'],
            'dialog_id' => $request->input('guid'),
        ];

        return response()->json($res);

    }
}
