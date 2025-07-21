<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Modules\Admin\Http\Controllers\AdminBaseController;

class AdminHomeController extends AdminBaseController
{
    public function index()
    {
        return view('admin::index');
    }
}
