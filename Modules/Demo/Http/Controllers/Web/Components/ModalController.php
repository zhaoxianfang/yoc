<?php

namespace Modules\Demo\Http\Controllers\Web\Components;

use Illuminate\Http\Request;
use Modules\Demo\Http\Controllers\DemoBaseController;

class ModalController extends DemoBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('demo::components/modal/modal');
    }

    public function iframeContent()
    {
        return view('demo::components/modal/iframe-content');
    }
}
