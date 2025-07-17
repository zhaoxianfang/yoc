<?php

namespace Modules\Files\Http\Controllers\Web\RichText;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Files\Services\FilesServices;
use Modules\Files\Services\ImagesServices;

/**
 * Tencent/cherry-markdown 上传文件
 * url: https://github.com/Tencent/cherry-markdown
 *
 * 响应格式：
 * {
 *      "code": 200 | 500,              // 500 表示上传失败，200 表示上传成功
 *      "message": "不支持的文件格式",    // 提示信息
 *      "url": "/files/foo(2).jpg",     // 上传成功时返回的文件地址
 *      "filename": "foo(2).jpg",// 回显文件名称
 *      "poster": "/files/foo(2).jpg",  // 视频封面图地址
 * }
 */
class Cherry extends Controller
{
    public static function img(Request $request, string $driver = 'img')
    {
        $uploadInfo = ImagesServices::instance()->upload('image', $driver);

        $res = [
            'code' => (int) $uploadInfo['code'], // 200成功；500失败
            'message' => ! empty($uploadInfo['message']) ? $uploadInfo['message'] : ($uploadInfo['code'] == 200 ? '上传成功' : '上传失败'), // 提示信息
            'url' => ! empty($uploadInfo['url']) ? $uploadInfo['url'] : '',
            'filename' => ! empty($uploadInfo['filename']) ? $uploadInfo['filename'] : '', // 回显文件名称
        ];

        return response()->json($res);
    }

    // 上传文件
    public static function file(Request $request, string $driver = 'file')
    {
        $uploadInfo = FilesServices::instance()->upload('files', $driver);

        $res = [
            'code' => (int) $uploadInfo['code'], // 200成功；500失败
            'message' => ! empty($uploadInfo['message']) ? $uploadInfo['message'] : ($uploadInfo['code'] == 200 ? '上传成功' : '上传失败'), // 提示信息
            'url' => ! empty($uploadInfo['url']) ? $uploadInfo['url'] : '',
            'filename' => ! empty($uploadInfo['filename']) ? $uploadInfo['filename'] : '', // 回显文件名称
        ];

        return response()->json($res);
    }

    // 上传文件
    public static function video(Request $request, string $driver = 'video')
    {
        $uploadInfo = FilesServices::instance()->upload('video', $driver);

        $res = [
            'code' => (int) $uploadInfo['code'], // 200成功；500失败
            'message' => ! empty($uploadInfo['message']) ? $uploadInfo['message'] : ($uploadInfo['code'] == 200 ? '上传成功' : '上传失败'), // 提示信息
            'url' => ! empty($uploadInfo['url']) ? $uploadInfo['url'] : '',
            'filename' => ! empty($uploadInfo['filename']) ? $uploadInfo['filename'] : '', // 回显文件名称
            'poster' => '',
        ];

        return response()->json($res);
    }
}
