<?php

namespace Modules\Docs\Http\Controllers\Web;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Docs\Http\Controllers\DocsBaseController;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsDoc;

class DocsAppSearchController extends DocsBaseController
{
    /**
     * App 文档内搜索
     *
     * @return JsonResponse
     */
    public function index(?DocsApp $docsApp, Request $request)
    {
        $keyword = $request->input('keyword', '');
        if (empty($keyword)) {
            return response()->json(['list' => []]);
        }
        $res = DocsDoc::search(urldecode($keyword), $docsApp);

        return response()->json(['list' => $res->toArray()]);
    }
}
