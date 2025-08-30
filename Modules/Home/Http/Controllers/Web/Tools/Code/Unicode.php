<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Code;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;

/**
 * unicode编码转换
 */
class Unicode extends HomeBaseController
{
    public function index(Request $request)
    {
        return view('home::tools.code.unicode', []);
    }
}
