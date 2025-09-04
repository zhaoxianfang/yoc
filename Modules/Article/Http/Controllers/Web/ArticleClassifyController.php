<?php

namespace Modules\Article\Http\Controllers\Web;

use Modules\Article\Models\ArticleClassifies;
use Modules\Home\Http\Controllers\HomeBaseController;

class ArticleClassifyController extends HomeBaseController
{
    /**
     * 显示文章分类列表
     */
    public function show(ArticleClassifies $classify)
    {
        abort_if(! show_news_module(), 423, '该内容暂不可见');

        $articles = $classify->articles()
            ->select(['id', 'user_id', 'classify_id', 'title', 'summary', 'content', 'author', 'publish_time', 'sort', 'type', 'read', 'like', 'spider', 'source_type', 'source_url', 'created_at', 'updated_at', 'status'])
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->paginate(12);

        // return ArticleResource::collection($list);

        return view('article::web.article.classify', [
            'classify' => $classify,
            'articles' => $articles, // compact('articles')
        ]);
    }
}
