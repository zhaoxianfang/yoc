<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Code;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;

/**
 * 代码格式化
 */
class JsonTools extends HomeBaseController
{
    public function index(Request $request)
    {
        return view('home::tools.code.json', []);
    }
}
