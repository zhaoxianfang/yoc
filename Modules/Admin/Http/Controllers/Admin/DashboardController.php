<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Modules\Admin\Http\Controllers\AdminBaseController;

class DashboardController extends AdminBaseController
{
    public function index()
    {
        return view('admin::dashboard');
    }
}
