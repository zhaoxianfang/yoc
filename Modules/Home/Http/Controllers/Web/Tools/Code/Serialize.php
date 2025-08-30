<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Code;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;

/**
 * Serialize转换
 */
class Serialize extends HomeBaseController
{
    public function index(Request $request)
    {
        return view('home::tools.code.serialize', []);
    }
}
