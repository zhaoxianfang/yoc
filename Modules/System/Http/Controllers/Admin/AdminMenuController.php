<?php

namespace Modules\System\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Admin\Models\AdminMenu;

class AdminMenuController extends AdminBaseController
{
    /**
     * ==========================================================================
     * 以下为系统菜单管理
     * ==========================================================================
     */
    public function index(Request $request)
    {
        if (! $request->ajax()) {
            return view('system::admin/admin_menus/index');
        }
        $menu_list = \zxf\Extend\Menu::instance()->init(AdminMenu::all()->toArray())->setUrlPrefix('admin/')->getTree();

        // return $this->json(['rows' => $menu_list, 'total' => count($menu_list)]);
        return $this->dataTables($menu_list);
    }

    public function store(Request $request)
    {
        if (! $request->ajax()) {
            $menus = AdminMenu::where(['ismenu' => 1, 'status' => 1])->get();
            // 菜单转换为视图
            $menu_list = \zxf\Extend\Menu::instance()->init($menus->toArray())->setUrlPrefix('admin/')->getTree();

            return view('system::admin/admin_menus/add', [
                'menu_list' => $menu_list,
            ]);
        }
        $req = $request->input('row', []);
        $req['create_by'] = auth('admin')->id();
        AdminMenu::create($req);
        group_rules(true);

        return $this->success([], route('admin.system.admin_menus.list'));
    }

    public function update(AdminMenu $menus, Request $request)
    {
        if (! $request->ajax()) {
            $menuList = AdminMenu::where(['ismenu' => 1, 'status' => 1])->get();
            // 菜单转换为视图
            $menuList = \zxf\Extend\Menu::instance()->init($menuList->toArray())->setUrlPrefix('admin/')->getTree();

            return view('system::admin/admin_menus/edit', [
                'row' => $menus,
                'menu_list' => $menuList,
            ]);
        }
        $menus->fill($request->input('row', []))->save();
        group_rules(true);

        return $this->success([], route('admin.system.admin_menus.list'));
    }

    public function delete(AdminMenu $menus)
    {
        $menus->delete();
        group_rules(true);

        return $this->success([], route('admin.system.admin_menus'));
    }
}
