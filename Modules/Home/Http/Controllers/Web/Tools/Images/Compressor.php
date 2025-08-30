<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Images;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;

class Compressor extends HomeBaseController
{
    /**
     * 图片压缩
     */
    public function index(Request $request)
    {
        return view('home::tools.images.compressor');
    }
}
