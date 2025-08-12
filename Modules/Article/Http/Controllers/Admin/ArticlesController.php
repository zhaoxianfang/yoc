<?php

namespace Modules\Article\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Article\Http\Resources\ArticleResource;
use Modules\Article\Models\Article;
use Modules\Article\Models\ArticleClassifies;

class ArticlesController extends AdminBaseController
{
    /**
     * 文章管理
     */
    public function index(Request $request)
    {
        if (! $request->ajax()) {
            return view('article::admin/articles/index');
        }
        $req = $request->input();

        $query = Article::query()
            ->with(['classify'])
            ->when(! empty($req['id']), function ($query) use ($req) {
                $query->where('id', $req['id']);
            })
            ->when(! empty($req['title']), function ($query) use ($req) {
                $query->where('title', 'like', '%'.$req['title'].'%');
            })
            ->when(! empty($req['type']), function ($query) use ($req) {
                $query->where('type', $req['type']);
            })
            ->when(isset($req['status']), function ($query) use ($req) {
                $query->where('status', $req['status']);
            })
            ->when(! empty($req['created_at']), function ($query) use ($req) {
                $created_at = explode('~', $req['created_at']);
                $query->whereBetween('created_at', $created_at);
            })
            // 多字段排序
            ->when(! empty($req['multi_order']) && count($req['multi_order']) > 1, function ($query) use ($req) {
                // 传入多个排序字段
                $multi_order = $req['multi_order'];
                foreach ($multi_order as $order) {
                    ! empty($order['field']) && $query->orderBy($order['field'], $order['order'] ?? 'desc');
                }
            }, function ($query) use ($req) {
                // 默认排序 或单个字段排序
                $query->orderBy($req['sort'] ?? 'id', $req['order'] ?? 'desc');
            });

        return ArticleResource::collection($query->paginate($req['limit'] ?? 10));
    }

    public function create()
    {
        $classifyList = Article::query()->where('status', Article::STATUS_NORMAL)->get()->toArray();
        // 转换为Tree
        $classifyList = \zxf\Extend\Menu::instance()->init($classifyList)->setWeigh()->setTitle('name')->getTree();

        return view('article::admin/articles/add', [
            'classify_list' => $classifyList,
        ]);
    }

    public function store(Request $request)
    {
        // $this->gate::authorize('create', Article::class);
        $row = $request->input('row', []);
        $row['admin_id'] = auth('admin')->id();
        $classify = new Article;
        $classify->fill($row)->save();

        return $this->successPage([], route('admin.articles.list'));
    }

    public function edit(Article $article)
    {
        $this->gate::forUser(auth('admin')->user())->authorize('update', $article);
        $classifyList = ArticleClassifies::query()->where('status', ArticleClassifies::STATUS_NORMAL)->get()->toArray();
        // 转换为Tree
        $classifyList = \zxf\Extend\Menu::instance()->init($classifyList)->setWeigh()->setTitle('name')->getTree();

        return view('article::admin/articles/edit', [
            'classify_list' => $classifyList,
            'info' => $article,
        ]);
    }

    public function update(Article $article, Request $request)
    {
        $this->gate::forUser(auth('admin')->user())->authorize('update', $article);
        //        return $this->errorPage([
        //            'code'    => 403,
        //            'message' => '调试中...,禁止修改',
        //        ]);

        $row = $request->input('row', []);

        $article->fill($row)->save();

        return $this->successPage([], route('admin.articles.list'));
    }

    public function destroy(Article $article)
    {
        return $this->errorPage([
            'code' => 403,
            'message' => '禁止删除',
        ]);
        // $this->gate::forUser(auth('admin')->user())->authorize('destroy', $classify);
        $article->delete();

        return $this->successPage([], route('admin.articles.list'));
    }
}
