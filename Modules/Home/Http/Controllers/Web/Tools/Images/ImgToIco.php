<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Images;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;

class ImgToIco extends HomeBaseController
{
    /**
     * 图片转ICO
     */
    public function index(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('home::tools.images.ico');
        }
        if ($request->hasFile('images')) {
            $request->validate([
                'images' => 'file|max:10240|mimes:jpeg,png,jpg',
            ], [
                'images.max' => '上传图片大小不能超过10M',
                'images.mimes' => '不支持的图片格式',
            ]);

            $file = $request->file('images');

            $base64 = \zxf\Tools\ImgToIco::instance()->set($file->getRealPath(), (int) $request->size)->generate();

            return $this->success([
                'base64_str' => $base64,
            ]);
        }
        return $this->error('上传图片失败');
    }
}
