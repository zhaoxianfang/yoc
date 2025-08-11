<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Models\Admin;
use Modules\Admin\Models\AdminGroup;
use Modules\Admin\Http\Controllers\AdminBaseController;

class AdminController extends AdminBaseController
{
    /**
     * 管理员列表
     */
    public function index(Request $request)
    {
        if (! $request->ajax()) {
            return view('admin::admin/admins/index');
        }
        $req = $request->input();

        $query = Admin::query()
            ->when(! empty($req['id']), function ($query) use ($req) {
                $query->where('id', $req['id']);
            })
            ->when(! empty($req['nickname']), function ($query) use ($req) {
                $query->where('nickname', 'like', '%'.$req['nickname'].'%');
            })
            ->when(isset($req['status']), function ($query) use ($req) {
                $query->where('status', $req['status']);
            })
            ->when(isset($req['gender']), function ($query) use ($req) {
                $query->where('gender', $req['gender']);
            })
            ->when(! empty($req['id_card']), function ($query) use ($req) {
                $query->where('id_card', $req['id_card']);
            })
            ->when(! empty($req['email']), function ($query) use ($req) {
                $query->where('email', 'like', '%'.$req['email'].'%');
            })
            ->when(! empty($req['created_at']), function ($query) use ($req) {
                $created_at = explode('~', $req['created_at']);
                $query->whereBetween('created_at', $created_at);
            })
            ->when(! empty($req['mobile_verified_at']), function ($query) use ($req) {
                $mobile_verified_at = explode('~', $req['mobile_verified_at']);
                $query->whereBetween('mobile_verified_at', $mobile_verified_at);
            });

        $count = $query->count();

        $list = $query
            ->offset($req['offset'] ?? 0)
            ->limit($req['limit'] ?? 10)
            ->orderBy($req['sort'] ?? 'id', $req['order'] ?? 'desc')
            ->get()
            ->toArray();

        return $this->dataTables($list, $count);
    }

    /**
     * 创建管理员
     */
    public function store(Request $request)
    {
        if (! $request->ajax()) {
            $groups = AdminGroup::where('status', 1)->get()->toArray();
            // 菜单转换为视图
            $group_tree = \zxf\Extend\Menu::instance()->init($groups)->setWeigh()->getTree();

            return view('admin::admin/admins/add', [
                'group_list' => $group_tree,
            ]);
        }
        $request->validate([
            'row' => 'required|array',
            'row.group_ids' => 'required|array',
        ], [
            'row.required' => '请填写管理员信息',
            'row.array' => '管理员信息格式错误',
            'row.group_ids.required' => '请选择管理员组',
            'row.group_ids.array' => '管理员组格式错误',
        ]);

        $row = $request->input('row', []);
        $groupIds = $row['group_ids'];
        try {
            DB::beginTransaction();

            if (! empty($row['password'])) {
                $row['password'] = Hash::make($row['password']);
            } else {
                return $this->error('密码不能为空');
            }

            $model = new Admin;
            $model->fill($row);
            $model->save();

            $model->groups()->sync($groupIds);
            DB::commit();

            return $this->success([], route('admin.system.admins.list'));
        } catch (\Exception $exception) {
            DB::rollback();

            return $this->error([
                'code' => 500,
                'message' => $exception->getMessage(),
            ]);
            // throw new \Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * 编辑管理员
     */
    public function update(Admin $admin, Request $request)
    {
        if (! $request->ajax()) {
            $groups = AdminGroup::where('status', 1)->get()->toArray();
            // 菜单转换为视图
            $group_tree = \zxf\Extend\Menu::instance()->init($groups)->setWeigh()->getTree();

            return view('admin::admin/admins/edit', [
                'group_list' => $group_tree,
                'info' => $admin,
                'group_ids' => $admin->groups->pluck('id')->toArray(),
            ]);
        }
        $request->validate([
            'row' => 'required|array',
            'row.group_ids' => 'required|array',
        ], [
            'row.required' => '请填写管理员信息',
            'row.array' => '管理员信息格式错误',
            'row.group_ids.required' => '请选择管理员组',
            'row.group_ids.array' => '管理员组格式错误',
        ]);

        $row = $request->input('row', []);
        $groupIds = $row['group_ids'];
        try {
            DB::beginTransaction();

            if (! empty($row['password'])) {
                $row['password'] = Hash::make($row['password']);
            } else {
                unset($row['password']);
            }

            $model = $admin;
            $model->fill($row);
            $model->save();

            $model->groups()->sync($groupIds);
            DB::commit();

            return $this->success([], route('admin.system.admins.list'));
        } catch (\Exception $exception) {
            DB::rollback();

            return $this->error([
                'code' => 500,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * 删除管理员
     */
    public function destroy(Admin $admin)
    {
        $admin->groups()->sync([]);
        $admin->delete();

        return $this->success([], route('admin.system.admins.list'));
    }

    public function checkField(Request $request)
    {
        $row = $request->input('row', []);
        $where = [];
        foreach ($row as $field => $value) {
            $where[$field] = $value;
        }
        $id = $request->input('id', '');
        if (
            Admin::query()->when($id, function ($query, $id) {
                $query->where('id', '<>', $id);
            })->where($where)->exists()
        ) {
            return $this->json([
                'message' => '验证不通过:系统已存在该记录',
                'check' => false,
            ]);
        }

        return $this->json([
            'message' => '验证通过',
            'check' => true,
        ]);
    }
}
