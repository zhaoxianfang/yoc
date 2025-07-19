<?php

namespace Modules\Docs\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Docs\Http\Controllers\DocsBaseController;
use Modules\Docs\Http\Resources\DocsUsersResource;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsAppUser;
use Modules\Docs\Models\DocsDoc;
use Modules\Docs\Services\DocsAppService;
use Modules\Files\Services\ImagesServices;
use zxf\QrCode\Common\EccLevel;
use zxf\QrCode\QRCodeGenerate;

/**
 * app文档管理
 */
class DocsAppManageController extends DocsBaseController
{
    // 初始化方法: 支持自定义依赖注入
    public function initialize(Request $request)
    {
        //
    }

    // 文档帮助页
    public function help(DocsApp $docsApp, Request $request)
    {
        return $this->showAppGuidePage($request, $docsApp, 'help', '帮助文档', 'app_help', [], 'faq', 'faq');
    }

    public function create(Request $request)
    {
        $this->gate::authorize('create', DocsApp::class);

        return $this->showAppGuidePage($request, null, 'create', '创建文档', 'app_edit', [], 'app');
    }

    public function store(Request $request, ImagesServices $imagesServices)
    {
        $this->gate::authorize('create', DocsApp::class);

        $validator = Validator::make($request->all(), [
            'app_name' => 'required|min:2',
            'team_name' => 'required|min:2',
            'urls' => 'array',
        ]);

        if ($validator->fails()) {
            return $this->backWithError($validator);
        }

        if ($request->hasFile('app_cover')) {
            try {
                $uploadInfo = $imagesServices->upload('app_cover', 'img');
                $app_cover = $uploadInfo['url'];
            } catch (\Exception $exception) {
                return $this->backWithError($exception);
            }
        } else {
            $app_cover = empty($request->app_cover) ? DocsApp::DEFAULT_COVER_PATH : $request->app_cover;
        }

        $userId = auth('web')->id(); // 创建人

        $urls = [];
        if (! empty($request->urls) && ! empty($request->urls['url_prefix'])) {
            foreach ($request->urls['url_prefix'] as $key => $item) {
                $urls[] = [
                    'alias' => $request->urls['alias'][$key],
                    'url_prefix' => $request->urls['url_prefix'][$key],
                ];
            }
        }
        DB::beginTransaction();
        try {
            $app = new DocsApp;
            $app->fill([
                'app_name' => $request->app_name,
                'app_cover' => $app_cover,
                'urls' => (array) $urls,
                'description' => $request->description,
                'open_type' => $request->open_type ?? DocsApp::OPEN_TYPE_OPEN,
                'create_by' => $userId,
                'team_name' => $request->team_name,
                'mark_days' => $request->mark_days ?? 3,
                'tag' => mb_substr($request->input('tag', ''), 0, 2),
                'status' => $request->status,
            ]);
            $app->save();

            // 设置文档创始人者角色
            $app->users()->syncWithoutDetaching([
                $userId => [
                    'audit_id' => $userId,
                    'doc_app_id' => $app->id,
                    'role' => DocsAppUser::ROLE_FOUNDER,
                    'status' => DocsAppUser::STATUS_PASS,
                ],
            ]);
            DB::commit();

            return redirect()->route('docs.app_edit', ['docsApp' => $app->id])->with('success', '创建成功'); // 成功后跳转到文档设置页面地址
        } catch (\Exception $exception) {
            DB::rollBack();

            return $this->backWithError($exception);
        }
    }

    // 文档设置页
    public function edit(DocsApp $docsApp, Request $request, DocsAppService $docsAppService)
    {
        $this->gate::authorize('update', $docsApp);

        $urls = [];
        foreach ($docsApp->urls as $key => $item) {
            $urls['alias'][$key] = $item['alias'];
            $urls['url_prefix'][$key] = $item['url_prefix'];
        }
        $docsApp->urls = $urls;

        return $this->showAppGuidePage($request, $docsApp, 'edit', '编辑文档', 'app_edit', [], 'app');
    }

    /**
     * 更新文档提交的设置
     */
    public function update(DocsApp $docsApp, Request $request, ImagesServices $imagesServices)
    {
        $this->gate::authorize('update', $docsApp);

        $validator = Validator::make($request->all(), [
            'app_name' => 'required|min:2',
            'team_name' => 'required|min:2',
            'urls' => 'array',
        ]);
        if ($validator->fails()) {
            return $this->backWithError($validator);
        }

        if ($request->hasFile('app_cover')) {
            try {
                $uploadInfo = $imagesServices->upload('app_cover', 'img');
                $app_cover = $uploadInfo['url'];
            } catch (\Exception $exception) {
                return $this->backWithError($exception);
            }
        } else {
            $app_cover = empty($request->app_cover) ? DocsApp::DEFAULT_COVER_PATH : $request->app_cover;
        }

        $urls = [];
        if (! empty($request->urls) && ! empty($request->urls['url_prefix'])) {
            foreach ($request->urls['url_prefix'] as $key => $item) {
                $urls[] = [
                    'alias' => $request->urls['alias'][$key],
                    'url_prefix' => $request->urls['url_prefix'][$key],
                ];
            }
        }
        DB::beginTransaction();
        try {
            $docsApp->fill([
                'app_name' => $request->app_name,
                'app_cover' => $app_cover,
                'urls' => (array) $urls,
                'description' => $request->description,
                'open_type' => $request->open_type ?? DocsApp::OPEN_TYPE_OPEN,
                'team_name' => $request->team_name,
                'mark_days' => $request->mark_days ?? 3,
                'tag' => mb_substr($request->input('tag', ''), 0, 2),
                'status' => $request->status,
            ]);
            $docsApp->save();

            DB::commit();

            return redirect()->route('docs.app_edit', ['docsApp' => $docsApp->id])->with('success', '修改成功'); // 成功后跳转到文档设置页面地址
        } catch (\Exception $exception) {
            DB::rollBack();

            return $this->backWithError($exception);
        }

    }

    // 文档成员管理
    public function users(DocsApp $docsApp, Request $request, DocsAppService $docsAppService)
    {
        if (auth('web')->guest() || ! $docsApp->isManager()) {
            return $this->tip_error('暂无权限!');
        }
        $docsApp->load(['users']);

        $qrcode = new QRCodeGenerate([
            'eccLevel' => EccLevel::H,
            'scale' => 5, // 每个模块的像素大小
        ]);

        $user = auth('web')->user();
        $data64 = $qrcode
            ->content(url("docs/apply/{$docsApp->id}?from={$user->uuid}"))
            ->withText('扫码加入「'.$docsApp->app_name.'」', '', 8) // 可选
            ->withLogo(public_path('static/images/logo/logo_mini.jpg')) // 可选
            ->toBase64();

        return $this->showAppGuidePage($request, $docsApp, 'users', '成员管理', 'app_users', [
            'apply_img' => $data64,
            'users' => DocsUsersResource::collection($docsApp->users)->toArray($request),
            'apply_users' => DocsUsersResource::collection($docsApp->applyUsers)->toArray($request),
        ]);
    }

    // 展示文件静态管理页面
    private function showAppGuidePage(Request $request, ?DocsApp $docsApp, int|string $docId, string $title, string $view_name, array $extData = [], string $dirName = 'guide', string $category = 'guide')
    {
        if (! empty($docsApp->id)) {
            $this->gate::authorize('guide', $docsApp);

            if ($request->pjax()) {
                $data = ['docs_app' => $docsApp];
                if (! empty($extData)) {
                    $data = array_merge($data, $extData);
                }

                return response()->json([
                    'content_html' => view("docs::{$dirName}/{$view_name}", $data)->render(),
                ], 200)->withHeaders([
                    'X-PJAX' => 'true', // 关键 PJAX 标识头
                    'X-Requested-With' => 'XMLHttpRequest', // 可选，模拟 AJAX
                ]);
            }

            $menus = $this->getAppMenus($docsApp);
        } else {
            $docsApp = new DocsApp;
            $menus = $this->getAppMenus($docsApp);
        }
        $docsDoc = $this->getMockDoc($docsApp, $docId, $title, $view_name, $extData, $dirName);

        return view('docs::show_docs', [
            'docs_app' => $docsApp,
            'docs_doc' => $docsDoc, // 文章内容
            'menus' => $menus, // 目录
            'category' => $category, // 顶部nav目录
            'base_url' => route('docs.doc.only_doc', ['docsDoc' => '_doc_id_']), // 基础url
        ]);
    }

    // 模拟一个文章数据基础字段
    private function getMockDoc(DocsApp $docsApp, int|string $docId, string $title, string $view_name, array $extData = [], string $dirName = 'guide'): DocsDoc
    {
        $data = ['docs_app' => $docsApp];
        if (! empty($extData)) {
            $data = array_merge($data, $extData);
        }
        // 生成一个随机数id，不够3位的左侧填充0
        $randId = $docsApp->id.'_'.str_pad(rand(1, 100), 3, '0', STR_PAD_LEFT);
        $docsDoc = new DocsDoc;
        $docsDoc->id = $docId ?? $randId;
        $docsDoc->_id = $docId ?? $randId;
        $docsDoc->title = $title;
        $docsDoc->doc_app_id = $docsApp->id;
        $docsDoc->content = '';
        $docsDoc->content_html = view("docs::{$dirName}/$view_name", $data)->render();

        return $docsDoc;
    }
}
