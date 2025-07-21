<?php

namespace Modules\Article\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\Article\Models\Article;
use Modules\Home\Http\Controllers\HomeBaseController;

class ArticleController extends HomeBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(Request $request)
    {
        return view('home::index');
    }

    /**
     * Show the specified resource.
     */
    public function show(Article $article)
    {
        $article->increment('read'); // 浏览次数+1
        $article->load(['classify.parent', 'user']);

        return view('article::web.article.detail', compact('article'));
    }
}
