<?php

namespace Modules\Demo\Http\Controllers\Web\Editor;

use Illuminate\Http\Request;
use Modules\Demo\Http\Controllers\DemoBaseController;

/**
 * 腾讯 cherry-markdown 编辑器
 */
class Summernote extends DemoBaseController
{
    public function index(Request $request)
    {
        return view('demo::editor.summernote');
    }

}
