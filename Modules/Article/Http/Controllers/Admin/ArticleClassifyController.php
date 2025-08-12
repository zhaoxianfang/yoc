<?php

namespace Modules\Article\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Article\Http\Resources\ArticleClassifyResource;
use Modules\Article\Models\ArticleClassifies;

class ArticleClassifyController extends AdminBaseController
{
    /**
     * 文章分类
     */
    public function index(Request $request)
    {
        if (! $request->ajax()) {
            return view('article::admin/article_classify/index');
        }
        $req = $request->input();

        $query = ArticleClassifies::query()
            ->with(['admin', 'parent'])
            ->when(! empty($req['id']), function ($query) use ($req) {
                $query->where('id', $req['id']);
            })
            ->when(! empty($req['name']), function ($query) use ($req) {
                $query->where('name', 'like', '%'.$req['name'].'%');
            })
            ->when(! empty($req['type']), function ($query) use ($req) {
                $query->where('type', $req['type']);
            })
            ->when(isset($req['show_nav']), function ($query) use ($req) {
                $query->where('show_nav', $req['show_nav']);
            })
            ->when(isset($req['status']), function ($query) use ($req) {
                $query->where('status', $req['status']);
            })
            ->when(! empty($req['created_at']), function ($query) use ($req) {
                $created_at = explode('~', $req['created_at']);
                $query->whereBetween('created_at', $created_at);
            })
            ->orderBy($req['sort'] ?? 'id', $req['order'] ?? 'desc');

        return ArticleClassifyResource::collection($query->paginate($req['limit'] ?? 10));
    }

    public function create()
    {
        $classifyList = ArticleClassifies::query()->where('status', ArticleClassifies::STATUS_NORMAL)->get()->toArray();
        // 转换为Tree
        $classifyList = \zxf\Extend\Menu::instance()->init($classifyList)->setWeigh()->setTitle('name')->getTree();

        return view('article::admin/article_classify/add', [
            'classify_list' => $classifyList,
        ]);
    }

    public function store(Request $request)
    {
        // $this->gate::authorize('create', ArticleClassifies::class);
        $row = $request->input('row', []);
        $row['admin_id'] = auth('admin')->id();
        $classify = new ArticleClassifies;
        $classify->fill($row)->save();

        return $this->successPage([], route('admin.articles.classify.list'));
    }

    public function edit(ArticleClassifies $classify)
    {
        $classifyList = ArticleClassifies::query()->where('status', ArticleClassifies::STATUS_NORMAL)->get()->toArray();
        // 转换为Tree
        $classifyList = \zxf\Extend\Menu::instance()->init($classifyList)->setWeigh()->setTitle('name')->getTree();

        return view('article::admin/article_classify/edit', [
            'classify_list' => $classifyList,
            'info' => $classify,
        ]);
    }

    public function update(ArticleClassifies $classify, Request $request)
    {
        // $this->gate::authorize('update', $classify);
        $row = $request->input('row', []);
        $row['admin_id'] = auth('admin')->id();
        $classify->fill($row)->save();

        return $this->successPage([], route('admin.articles.classify.list'));
    }

    public function destroy(ArticleClassifies $classify)
    {
        // $this->gate::authorize('destroy', $classify);
        $classify->delete();

        return $this->successPage([], route('admin.articles.classify.list'));
    }
}
