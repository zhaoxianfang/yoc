<?php

namespace Modules\Logs\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Logs\Models\SystemLog;

class SystemLogsController extends AdminBaseController
{
    /**
     * ==========================================================================
     * 以下为系统日志管理
     * ==========================================================================
     */
    public function index(Request $request)
    {
        if (! $request->ajax()) {
            return view('logs::admin/logs/index');
        }
        $req = $request->input();

        $query = SystemLog::query()
            ->when(! empty($req['id']), function ($query) use ($req) {
                $query->where('id', $req['id']);
            })
            ->when(! empty($req['title']), function ($query) use ($req) {
                $title = $req['title'];
                // 判断 $title 里面是否包含 ->字符
                if (str_contains($title, '->')) {
                    // 匹配 blacklist->1 格式的参数搜索
                    $query->where('title', 'like', '%'.$title);
                } else {
                    $query->where('title', 'like', '%'.$title.'%');
                }
            })
            ->when(! empty($req['source_ip']), function ($query) use ($req) {
                $query->where('source_ip', 'like', '%'.$req['source_ip'].'%');
            })
            ->when(! empty($req['url']), function ($query) use ($req) {
                $query->where('url', 'like', '%'.$req['url'].'%');
            })
            ->when(! empty($req['user_id']), function ($query) use ($req) {
                $query->where('user_id', $req['user_id']);
            })
            ->when(! empty($req['is_crawler']), function ($query) use ($req) {
                $query->where('is_crawler', $req['is_crawler']);
            })
            ->when(! empty($req['created_at']), function ($query) use ($req) {
                $created_at = explode('~', $req['created_at']);
                $query->whereBetween('created_at', $created_at);
            });

        $count = $query->count();

        $list = $query
            ->with(['user:id,nickname,cover'])
            ->offset($req['offset'] ?? 0)
            ->limit($req['limit'] ?? 10)
            ->orderBy($req['sort'] ?? 'id', $req['order'] ?? 'desc')
            ->get()
            ->toArray();

        return $this->dataTables($list, $count);
    }

    public function info(SystemLog $log)
    {
        $log->load('user');

        return view('logs::admin/logs/showinfo', [
            'info' => $log->toArray(),
        ]);
    }
}
