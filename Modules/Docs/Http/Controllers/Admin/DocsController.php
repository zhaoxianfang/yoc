<?php

namespace Modules\Docs\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Docs\Models\DocsApp;

class DocsController extends AdminBaseController
{
    public function index(Request $request)
    {
        if (! is_ajax()) {
            return view('docs::admin.list');
        }
        $req = $request->input();

        $query = DocsApp::query()
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
}
