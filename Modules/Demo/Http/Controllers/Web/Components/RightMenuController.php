<?php

namespace Modules\Demo\Http\Controllers\Web\Components;

use Illuminate\Http\Request;
use Modules\Demo\Http\Controllers\DemoBaseController;

// 鼠标右键菜单组件
class RightMenuController extends DemoBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('demo::components/right_menu/right_menu');
    }
}
