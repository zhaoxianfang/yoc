<?php

namespace Modules\Docs\Http\Controllers\Web;

use Exception;
use Illuminate\Http\Request;
use Modules\Docs\Http\Controllers\DocsBaseController;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsAppMenu;

class DocsMenusController extends DocsBaseController
{
    /**
     * 新建 APP 一级目录
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DocsApp $docsApp, Request $request)
    {
        $this->gate::authorize('createTopMenu', $docsApp);

        $userId = auth('web')->id(); // 创建人

        $menu = new DocsAppMenu;
        $menu->fill([
            'user_id' => $userId,
            'doc_app_id' => $docsApp->id,
            'name' => $request->name,
            'open_type' => $request->open_type,
            'sort' => 0,
        ]);
        $menu->save();

        // 跳转到上个请求 URL
        return $this->success('创建成功', url()->previous());
    }

    /**
     * 新建子目录
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeSubMenu(DocsAppMenu $menu, Request $request)
    {
        $this->gate::authorize('editMenu', $menu);

        $userId = auth('web')->id(); // 创建人

        $childMenu = new DocsAppMenu;
        $childMenu->fill([
            'user_id' => $userId,
            'parent_id' => $menu->id,
            'doc_app_id' => $menu->doc_app_id,
            'name' => $request->name,
            'open_type' => $request->open_type,
            'sort' => 0,
        ]);
        $childMenu->save();

        // 成功后跳转到获取上个请求 URL
        return $this->success('创建成功', url()->previous());

    }

    /**
     * 编辑目录
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DocsAppMenu $menu, Request $request)
    {
        $this->gate::authorize('editMenu', $menu);

        $menu->fill([
            'user_id' => auth('web')->id(),
            'name' => $request->name,
            'open_type' => $request->open_type,
            'sort' => 0,
        ]);
        $menu->save();
        // 获取上个请求 URL
        $jump = url()->previous(); // 成功后跳转到文档设置页面地址

        return $this->success('编辑成功', $jump);
    }

    /**
     * 删除目录
     *
     *
     * @return \Illuminate\Http\JsonResponse|void
     *
     * @throws Exception
     */
    public function destroy(DocsAppMenu $menu)
    {
        $this->gate::authorize('editMenu', $menu);

        if (collect($menu->menus)->isNotEmpty()) {
            return $this->error('此目录下存在关联的子目录，不可直接删除');
        }
        if (collect($menu->docs)->isNotEmpty()) {
            return $this->error('此目录下存在关联的文档，不可直接删除');
        }
        $menu->delete();
        // 获取上个请求 URL
        $jump = url()->previous(); // 成功后跳转到文档设置页面地址

        return $this->success('删除成功', $jump);
    }
}
