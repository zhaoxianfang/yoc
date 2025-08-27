<?php

namespace Modules\Demo\Http\Controllers\Web\Components;

use Illuminate\Http\Request;
use Modules\Demo\Http\Controllers\DemoBaseController;

class ToolsController extends DemoBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('demo::components/tools.tools');
    }

}
