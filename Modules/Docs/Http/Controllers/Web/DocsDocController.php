<?php

namespace Modules\Docs\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Docs\Http\Controllers\DocsBaseController;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsAppMenu;
use Modules\Docs\Models\DocsDoc;
use Modules\Docs\Services\DocsAppMenuService;

/**
 * 文档模板等
 */
class DocsDocController extends DocsBaseController
{
    protected array $editorMap = [
        // markdown 编辑器
        'editor_md' => 'editor.md',
        'cherry' => 'Tencent/cherry-markdown',
        'vditor' => 'vditor',
        // 富文本编辑器
        'ckeditor' => 'ckeditor4',
    ];

    // 默认markdown编辑器
    protected string $defaultMdEditor = 'cherry';

    // 默认富文本编辑器
    protected string $defaultHtmlEditor = 'ckeditor';

    // 初始化方法: 支持自定义依赖注入
    public function initialize(Request $request)
    {
        $defaultMdEditor = setting('common.default_md_editor');
        if (! empty($defaultMdEditor)) {
            $this->defaultMdEditor = $defaultMdEditor;
        }
    }

    /**
     * 文章详情
     */
    public function detail(DocsApp $docsApp, DocsDoc $docsDoc, Request $request)
    {
        if ($request->pjax()) {
            return $this->onlyDoc($docsDoc, $request);
        }

        $this->gate::authorize('show', $docsDoc);

        if ($docsDoc->doc_app_id != $docsApp->id) {
            $this->tip_error('文档不存在');
        }
        $this->updateViewCount($docsDoc);

        $menus = $this->getAppMenus($docsApp);

        return view('docs::show_docs', [
            'docs_app' => $docsApp,
            'docs_doc' => $docsDoc, // 文章内容
            'menus' => $menus, // 目录
            'category' => 'guide', // 顶部nav目录
            'base_url' => route('docs.doc.only_doc', ['docsDoc' => '_doc_id_']), // 基础url
        ]);
    }

    // 仅仅处理ajax 请求的 DocsDoc 数据
    public function onlyDoc(DocsDoc $docsDoc, Request $request)
    {
        $this->gate::authorize('show', $docsDoc);

        if (! $request->pjax() || ! $request->ajax()) {
            abort(424, '不合理的请求');
        }
        $this->updateViewCount($docsDoc);

        return response()->json($docsDoc, 200)->withHeaders([
            'X-PJAX' => 'true', // 关键 PJAX 标识头
            'X-Requested-With' => 'XMLHttpRequest', // 可选，模拟 AJAX
        ]);
    }

    // 更新文章被浏览次数
    private function updateViewCount(DocsDoc $docsDoc): void
    {
        // 更新文章被浏览次数时 临时设置timestamps为false，避免更新时间
        // 关闭时间戳更新
        $docsDoc->timestamps = false;
        $docsDoc->increment('view'); // 浏览次数+1
        // 恢复时间戳更新
        $docsDoc->timestamps = true;
    }

    // 文章编辑
    public function edit(DocsDoc $docsDoc, Request $request, DocsAppMenuService $docsAppMenuService)
    {
        $this->gate::authorize('update', $docsDoc);
        $docsDoc->load('app', 'menu');
        $docsApp = $docsDoc->app;
        $menus = $this->getAppMenus($docsApp);

        return view('docs::edit/create_edit_doc', [
            'editor_name' => $docsDoc->type == DocsDoc::TYPE_MARKDOWN ? $this->defaultMdEditor : $this->defaultHtmlEditor,
            'menus_tree' => $docsAppMenuService->tree($docsApp->menus),
            'docs_menu' => $docsDoc->menu,

            'docs_app' => $docsApp,
            'docs_doc' => $docsDoc,
            'menus' => $menus, // 目录
            'category' => 'guide', // 顶部nav目录
            'base_url' => route('docs.doc.only_doc', ['docsDoc' => '_doc_id_']), // 基础url
        ]);
    }

    public function update(DocsDoc $docsDoc, Request $request, DocsAppMenuService $docsAppMenuService)
    {
        $this->gate::authorize('update', $docsDoc);

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:2',
            'content' => 'required|min:2',
            'open_type' => 'required|in:1,2,3,9',
        ], [
            'title.required' => '标题不能为空',
            'title.min' => '标题不能少于2个字符',
            'content.required' => '内容不能为空',
            'content.min' => '内容不能少于2个字符',
            'open_type.required' => '开放类型不能为空',
            'open_type.in' => '开放类型不正确',
        ]);

        if ($validator->fails()) {
            return $this->backWithError($validator);
        }

        $docsApp = $docsDoc->app;

        $content = $docsDoc->type == DocsDoc::TYPE_MARKDOWN ? $request->input('content') : null;
        $contentHtml = $docsDoc->type == DocsDoc::TYPE_MARKDOWN ? $request->input('content_html') : $request->input('content');
        if (
            empty($contentHtml) ||
            ($docsDoc->type == DocsDoc::TYPE_MARKDOWN && empty($content))
        ) {
            return $this->backWithError('请重新点击提交并保存按钮进行提交！');
        }

        $docsDoc->fill([
            'doc_menu_id' => $request->input('doc_menu_id'),
            'title' => $request->input('title'),
            'content' => $content,
            'content_html' => $contentHtml,
            'open_type' => $request->input('open_type', DocsDoc::OPEN_TYPE_OPEN),
        ]);
        $docsDoc->save();

        $jump = route('docs.doc.show', ['docsApp' => $docsApp->id, 'docsDoc' => $docsDoc->id]);

        return redirect($jump)->with('success', '编辑成功');
    }

    public function destroy(DocsDoc $docsDoc)
    {
        $this->gate::authorize('delete', $docsDoc);
        // 删除doc 文档

        // TODO 删除文档中的图片、文档等资源

        $docsDoc->delete();

        return $this->success('删除成功', route('docs.app_home', ['docsApp' => $docsDoc->app->id]));
    }

    // 创建文档
    public function create(DocsApp $docsApp, DocsAppMenu $docsAppMenu, string $type, DocsAppMenuService $docsAppMenuService)
    {
        $this->gate::authorize('editMenu', $docsAppMenu);

        $docsApp->load('menus');
        $menus = $this->getAppMenus($docsApp);
        $isMd = $type == 'markdown'; // 是否为markdown : markdown|editor

        return view('docs::edit/create_edit_doc', [
            'editor_name' => $isMd ? $this->defaultMdEditor : $this->defaultHtmlEditor,
            'menus_tree' => $docsAppMenuService->tree($docsApp->menus),
            'docs_menu' => $docsAppMenu,

            'docs_app' => $docsApp,
            'docs_doc' => null,
            'menus' => $menus, // 目录
            'category' => 'guide', // 顶部nav目录
            'base_url' => route('docs.doc.only_doc', ['docsDoc' => '_doc_id_']), // 基础url
        ]);
    }

    public function store(DocsApp $docsApp, DocsAppMenu $docsAppMenu, string $type, Request $request)
    {
        $this->gate::authorize('editMenu', $docsAppMenu);

        $isMd = $type == 'markdown'; // 是否为markdown : markdown|editor
        $userId = auth('web')->id(); // 创建人
        $validatorData = [
            'rule' => [
                'title' => 'required|min:2',
                'content' => 'required|min:2',
                'open_type' => 'required|in:1,2,3,9',
            ],
            'messages' => [
                'title.required' => '标题不能为空',
                'title.min' => '标题不能少于2个字符',
                'content.required' => '内容不能为空',
                'content.min' => '内容不能少于2个字符',
                'open_type.required' => '开放类型不能为空',
                'open_type.in' => '开放类型不正确',
            ],
        ];
        if ($isMd) {
            $validatorData['rule']['content_html'] = 'required|min:2';
            $validatorData['messages']['content_html.required'] = '内容不能为空';
            $validatorData['messages']['content_html.min'] = '内容不能少于2个字符';
        }

        $validator = Validator::make($request->all(), $validatorData['rule'], $validatorData['messages']);

        if ($validator->fails()) {
            return $this->backWithError($validator);
        }

        $doc = new DocsDoc;
        $doc->fill([
            'user_id' => $userId,
            'doc_app_id' => $docsApp->id,
            'doc_menu_id' => $docsAppMenu->id,

            'type' => $isMd ? DocsDoc::TYPE_MARKDOWN : DocsDoc::TYPE_EDITOR,
            'title' => $request->input('title'),
            'content' => $isMd ? $request->input('content') : null,
            'content_html' => $isMd ? $request->input('content_html') : $request->input('content'),
            'sort' => 0,
            'open_type' => $request->input('open_type', DocsDoc::OPEN_TYPE_OPEN),
        ]);
        $doc->save();

        $jump = route('docs.doc.show', ['docsApp' => $docsApp->id, 'docsDoc' => $doc->id]);

        return redirect($jump)->with('success', '创建成功');
    }
}
