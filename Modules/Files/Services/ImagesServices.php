<?php

namespace Modules\Files\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Files\Models\File;
use Modules\System\Services\BaseService;
use zxf\Tools\Compressor as CompressorTool;

class ImagesServices extends BaseService
{
    /**
     * 上传图片
     *
     * @param  string  $fileName  上传图片传递的图片名称字段
     */
    public function upload(string $fileName = '', string $driver = 'img')
    {
        $request = request();

        if ($request->hasFile($fileName)) {
            $request->validate([
                $fileName => 'file|max:10240|image',
            ], [
                $fileName.'.max' => '上传图片大小不能超过10M',
                $fileName.'.image' => '不支持的图片格式',
            ]);

            $file = $request->file($fileName);
            if (
                ! in_array($file->getClientOriginalExtension(), ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'svg', 'webp'])
            ) {
                return [
                    'code' => 412, // 上传状态 200成功;412失败
                    'message' => '不支持的文件格式'.$file->getClientOriginalExtension(), // 提示信息
                    'filename' => '',
                    'url' => '', // 文件访问地址
                ];
            }
            // 验证是否上传成功
            if ($file->isValid()) {
                // 存储
                $fileName = $driver.'_img_'.uniqid().'.'.$file->getClientOriginalExtension(); // 自定义文件名
                $path = $file->storeAs(date('Ymd'), $fileName, $driver);

                // 图片绝对路径地址
                // Storage::disk($driver)->path($path);
                // 图片域名网络路径
                // Storage::disk($driver)->url($path);
                //                if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                //                    // 压缩图片尺寸 , 如果图片大于5M（5242880 kb）,压缩比例 为 0.8，小于5M的压缩比例为1
                //                    $proportion = Storage::disk($driver)->size($path) > 5242880 ? 0.8 : 1;
                //                    // 图片压缩有问题 imagecreatefrompng(): gd-png: libpng warning: iCCP: known incorrect sRGB， 解决:降低 libpng 版本 并安装 yum install freetype freetype-devel
                //                    $this->compressorImg(Storage::disk($driver)->path($path), $proportion);
                //                }

                // 记录文件信息
                $fileSaveInfo = $this->writeFileInfo($file, $driver, $fileName, $path);

                return [
                    'code' => 200, // 上传状态 200成功;412失败
                    'message' => '上传成功', // 提示信息
                    'filename' => $file->getClientOriginalName(), // 上传的原文件名称
                    'url' => $fileSaveInfo->getUrl(), // 文件访问地址
                ];
            } else {
                return [
                    'code' => 500, // 上传状态 200成功;412失败
                    'message' => $file->getError(), // 提示信息
                    'url' => '', // 文件访问地址
                ];
            }
        }

        return [
            'code' => 412, // 上传状态 200成功;412失败
            'message' => '文件上传失败', // 提示信息
            'url' => '', // 文件访问地址
        ];
    }

    /**
     * 下载图片
     *
     * @return void
     */
    public function download() {}

    /**
     * 记录文件上传痕迹
     *
     *
     * @return File
     */
    private function writeFileInfo($fileObj, $driver, $fileName, $path)
    {
        // 清除文件缓存状态 【特别重要】，因为 文件被覆盖导致文件尺寸大小等有变动
        clearstatcache();

        $file = new File;
        $file->fill([
            'user_id' => auth('web')->guest() ? (auth('admin')->id() ?? 0) : auth('web')->id(),
            'name' => $fileName, // 文件存储名,
            'ext' => $fileObj->getClientOriginalExtension(), // 扩展名 jpeg 的 后缀名为 jpg   $file->extension() 获取的文件后缀会把 js 等的文件格式 识别为 txt
            'original_name' => $fileObj->getClientOriginalName(), // 原文件名,
            'type' => $fileObj->getClientMimeType(), // 获取上传文件的 Mime 类型 （image/png）,
            'size' => Storage::disk($driver)->size($path), // $fileObj->getSize(),// 获取上传文件的大小, 因为文件可能被修改，需要重新获取文件大小
            'path' => $path,
            'driver' => $driver,
            'status' => File::STATUS_UNUSED,
        ]);
        $file->save();

        return $file;
    }

    /**
     * 保留图片尺寸宽高，仅压缩图片大小
     *
     * @param  string  $filePath  图片绝对地址 可用 Storage::disk($driver)->path($path); 获取
     * @param  string  $proportion  图片压缩比例 0~1
     * @return mixed
     */
    public function compressorImg(string $filePath = '', $proportion = 1)
    {
        if (empty($filePath)) {
            return false;
        }

        return CompressorTool::instance()->set($filePath, $filePath)->proportion($proportion)->get();
    }
}
