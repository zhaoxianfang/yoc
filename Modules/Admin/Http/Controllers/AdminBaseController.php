<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Services\AdminMenuService;
use Modules\System\Http\Controllers\BaseController;

class AdminBaseController extends BaseController
{
    public function initialize(Request $request, AdminMenuService $adminMenuService)
    {
        //        dd($adminMenuService->getLeftMenu());
        view_share([
            'admin_menu' => $adminMenuService->getLeftMenu(),
        ]);
    }
}
