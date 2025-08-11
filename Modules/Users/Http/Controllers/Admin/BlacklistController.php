<?php

namespace Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Users\Models\Blacklist;

class BlacklistController extends AdminBaseController
{
    /**
     * 黑名单ip管理
     */
    public function index(Request $request)
    {
        if (! $request->ajax()) {
            return view('users::admin.blacklist');
        }
        $req = $request->input();

        $query = Blacklist::query()
            ->when(! empty($req['id']), function ($query) use ($req) {
                $query->where('id', $req['id']);
            })
            ->when(! empty($req['ip']), function ($query) use ($req) {
                $query->where('ip', 'like', '%'.$req['ip'].'%');
            })
            ->when(isset($req['type']) && in_array($req['type'], [0, 1]), function ($query) use ($req) {
                $query->where('type', $req['type']);
            })
            ->when(! empty($req['visits'][0]) && ! empty($req['visits'][1]), function ($query) use ($req) {
                $query->whereBetween('visits', $req['visits']);
            })
            ->when(! empty($req['created_at']), function ($query) use ($req) {
                $created_at = explode('~', $req['created_at']);
                $query->whereBetween('created_at', $created_at);
            })
            ->when(! empty($req['updated_at']), function ($query) use ($req) {
                $updated_at = explode('~', $req['updated_at']);
                $query->whereBetween('updated_at', $updated_at);
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
     * 创建
     */
    public function store(Request $request)
    {
        if (! $request->ajax()) {
            return view('users::admin.blacklist.add');
        }
        $request->validate([
            'row' => 'required|array',
            'row.ip' => 'required',
        ], [
            'row.required' => '请填写管理员信息',
            'row.array' => '管理员信息格式错误',
            'row.ip.required' => '请填写IP',
        ]);

        $row = $request->input('row', []);

        try {
            DB::beginTransaction();
            $model = new Blacklist;
            $model->fill($row);
            $model->save();
            DB::commit();

            return $this->success([], route('admin.users.blacklist.list'));
        } catch (\Exception $exception) {
            DB::rollback();

            return $this->error([
                'code' => 500,
                // 'message' => $exception->getMessage(),
                'message' => '系统异常：请稍后再试！',
            ]);
        }
    }

    /**
     * 编辑
     */
    public function update(Blacklist $blacklist, Request $request)
    {
        if (! $request->ajax()) {
            return view('users::admin/blacklist/edit', [
                'info' => $blacklist,
            ]);
        }
        $request->validate([
            'row' => 'required|array',
            'row.ip' => 'required',
        ], [
            'row.required' => '请填写管理员信息',
            'row.array' => '管理员信息格式错误',
            'row.ip.required' => '请填写IP',
        ]);

        $row = $request->input('row', []);
        try {
            DB::beginTransaction();

            $blacklist->fill($row);
            $blacklist->save();

            DB::commit();

            return $this->success([], route('admin.users.blacklist.list'));
        } catch (\Exception $exception) {
            DB::rollback();

            return $this->error([
                'code' => 500,
                // 'message' => $exception->getMessage(),
                'message' => '系统异常：请稍后再试！',
            ]);
        }
    }

    /**
     * 删除
     */
    public function destroy(Blacklist $blacklist)
    {
        $blacklist->delete();

        return $this->success([], route('admin.users.blacklist.list'));
    }
}
