<?php

namespace Modules\Docs\Http\Controllers\Web;

use Illuminate\Http\Request;
use Modules\Docs\Http\Controllers\DocsBaseController;
use Modules\Docs\Services\DocsAppService;

class DocsHomeController extends DocsBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DocsAppService $docsAppService)
    {
        $docsApps = $docsAppService->getHomeDocs();

        return view('docs::app_list', [
            'docs_apps' => $docsApps,
            'nav_type' => 'home',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function my(Request $request, DocsAppService $docsAppService)
    {
        $docsApps = $docsAppService->getMyDocs();

        return view('docs::app_list', [
            'docs_apps' => $docsApps,
            'nav_type' => 'mine',
        ]);
    }
}
