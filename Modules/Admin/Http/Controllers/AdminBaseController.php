<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Services\AdminMenuService;
use Modules\System\Http\Controllers\BaseController;

class AdminBaseController extends BaseController
{
    public function initialize(Request $request, AdminMenuService $adminMenuService)
    {
        if (auth('admin')->guest()) {
            return;
        }
        if ($request->isMethod('get')) {
            view_share([
                'admin_menu_html' => $adminMenuService->getLeftMenu(),
                'admin' => auth('admin')->user(),
            ]);
        }
    }
}
