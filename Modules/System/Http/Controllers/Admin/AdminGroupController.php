<?php

namespace Modules\System\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Admin\Models\AdminGroup;
use Modules\Admin\Models\AdminMenu;

class AdminGroupController extends AdminBaseController
{
    /**
     * ==========================================================================
     * 以下为系统角色组管理
     * ==========================================================================
     */
    public function index(Request $request)
    {
        if (! is_ajax()) {
            return view('system::admin/admin_groups/index');
        }
        $req = $request->input();

        $list = AdminGroup::query()
            ->when(! empty($req['id']), function ($query) use ($req) {
                $query->where('id', $req['id']);
            })
            ->when(! empty($filter['group_name']), function ($query) use ($req) {
                $query->where('group_name', 'like', '%'.$req['group_name'].'%');
            })
            ->when(isset($req['status']), function ($query) use ($req) {
                $query->where('status', $req['status']);
            })
            ->when(! empty($req['create_by']), function ($query) use ($req) {
                $query->where('create_by', $req['create_by']);
            })
            ->when(! empty($req['created_at']), function ($query) use ($req) {
                $created_at = explode('~', $req['created_at']);
                $query->whereBetween('created_at', $created_at);
            })
            ->when(! empty($req['expiration_at']), function ($query) use ($req) {
                $expiration_at = explode('~', $req['expiration_at']);
                $query->whereBetween('expiration_at', $expiration_at);
            })
//            ->offset($req['offset'] ?? 0)
//            ->limit($req['limit'] ?? 10)
            ->orderBy($req['sort'] ?? 'id', $req['order'] ?? 'desc')
            ->get()
            ->toArray();

        // $count = AdminGroup::count();
        return $this->dataTables($list);
    }

    public function store(Request $request)
    {
        if (! is_ajax()) {
            $groups = AdminGroup::where('status', 1)->get()->toArray();
            // 菜单转换为视图
            $group_tree = \zxf\Extend\Menu::instance()->init($groups)->setWeigh()->getTree();

            return view('system::admin/admin_groups/add', [
                'group_list' => $group_tree,
            ]);
        }
        $req = $request->input('row', []);

        $req['create_by'] = auth('admin')->id();
        $req['expiration_at'] = Carbon::parse($req['expiration_at'])->endOfDay()->toDateTimeString();
        $rules = explode(',', trim($req['rules'], ','));
        $group = new AdminGroup($req);
        $group->save();
        $group->menus()->sync($rules ?? []);
        group_rules(true);

        return $this->success([], route('admin.system.admin_groups.list'));
    }

    public function update(AdminGroup $group, Request $request)
    {
        if (! is_ajax()) {
            $groups = AdminGroup::where('status', 1)->get()->toArray();
            // 菜单转换为视图
            $group_tree = \zxf\Extend\Menu::instance()->init($groups)->setWeigh()->getTree();
            $group->rules = implode(',', $group->menus->pluck('id')->toArray());
            $group->expiration_at = Carbon::parse($group->expiration_at)->toDateString();

            return view('system::admin/admin_groups/edit', [
                'info' => $group,
                'group_list' => $group_tree,
            ]);
        }
        $req = $request->input('row', []);
        $rules = explode(',', trim($req['rules'], ','));
        $req['create_by'] = auth('admin')->id();
        $req['expiration_at'] = Carbon::parse($req['expiration_at'])->endOfDay()->toDateTimeString();
        $group->fill($req)->save();
        $group->menus()->sync($rules ?? []);
        group_rules(true);

        return $this->success([], route('admin.system.admin_groups.list'));
    }

    public function delete(AdminGroup $group)
    {
        $group->menus()->sync([]);
        $group->delete();
        group_rules(true);

        return $this->success([], route('admin.system.admin_groups.list'));
    }

    /**
     * 获取管理员组节点树
     */
    public function getTree(Request $request)
    {
        $groupId = $request->input('id', 0);

        $groupInfo = $groupId ? AdminGroup::find($groupId) : null;

        // 获取管理员组 对应的权限列表
        $menuList = AdminMenu::where(['status' => 1])->get();
        // 已选节点id
        $checkArr = $groupId ? $groupInfo->menus->pluck('id')->toArray() : [];

        $rule = [];
        foreach ($menuList as $key => $value) {
            $rule[$key]['id'] = $value['id'];
            $rule[$key]['pId'] = $value['pid'];
            $rule[$key]['name'] = $value['title'].' ['.($value['ismenu'] ? '菜单' : '按钮').']';

            if (in_array($value['id'], $checkArr)) {
                $rule[$key]['checked'] = true;
            }
        }

        return $this->json(['status' => 1, 'data' => $rule, 'info' => '获取成功']);
    }
}
