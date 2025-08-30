<?php

namespace Modules\Demo\Http\Controllers\Web\Editor;

use Illuminate\Http\Request;
use Modules\Demo\Http\Controllers\DemoBaseController;

class Ckeditor extends DemoBaseController
{
    public function index(Request $request)
    {
        return view('demo::editor.ckeditor');
    }

}
