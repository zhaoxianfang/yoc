<?php

namespace Modules\Home\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Home\Services\TopNavService;
use Modules\System\Http\Controllers\BaseController;

class HomeBaseController  extends BaseController
{
    public function initialize(Request $request,TopNavService $topNavService)
    {
        view_share([
            'mega_menu' => $topNavService->megaMenu(),
            'classify_top_nav' => $topNavService->classifyTopNav(),
        ]);
    }
}
