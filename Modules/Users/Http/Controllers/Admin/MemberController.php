<?php

namespace Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Users\Models\User;

class MemberController extends AdminBaseController
{
    /**
     * 用户管理
     */
    public function index(Request $request)
    {
        if (! is_ajax()) {
            return view('users::admin.member');
        }
        $req = $request->input();

        $query = User::query()
            ->when(! empty($req['id']), function ($query) use ($req) {
                $query->where('id', $req['id']);
            })
            ->when(! empty($req['real_name']), function ($query) use ($req) {
                $query->where('real_name', 'like', '%'.$req['real_name'].'%');
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
}
