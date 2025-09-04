<?php

namespace Modules\Article\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\Article\Models\Article;
use Modules\Home\Http\Controllers\HomeBaseController;

class ArticleController extends HomeBaseController
{

    /**
     * 显示文章详情
     */
    public function show(Article $article)
    {
        abort_if(! show_news_module(), 423, '该内容暂不可见');

        $article->increment('read'); // 浏览次数+1
        $article->load(['classify.parent', 'user']);

        return view('article::web.article.detail', compact('article'));
    }
}
