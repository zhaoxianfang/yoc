<?php

namespace Modules\Docs\Http\Controllers\Web;

use Illuminate\Http\Request;
use Modules\Docs\Http\Controllers\DocsBaseController;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsDoc;
use Modules\Docs\Services\DocsAppMenuService;

/**
 * 文档应用
 */
class DocsAppController extends DocsBaseController
{
    // 初始化方法: 支持自定义依赖注入
    public function initialize(Request $request)
    {
        //
    }

    // 打开app文档的首页
    public function index(DocsApp $docsApp, Request $request, DocsAppMenuService $docsAppMenuService)
    {
        $this->gate::authorize('guide', $docsApp);

        $firstDocId = $docsAppMenuService->getAppFirstDocId($docsApp->menus);

        $menus = $this->getAppMenus($docsApp);

        if ($firstDocId) {
            $content = DocsDoc::query()->find($firstDocId);

            return view('docs::show_docs', [
                'docs_app' => $docsApp,
                'docs_doc' => $content, // 文章内容
                'menus' => $menus, // 目录
                'category' => 'guide', // 顶部nav目录
                'base_url' => route('docs.doc.only_doc', ['docsDoc' => '_doc_id_']), // 基础url
            ]);
        } else {
            return to_route('docs.app_help', ['docsApp' => $docsApp], 302);
        }
    }
}
