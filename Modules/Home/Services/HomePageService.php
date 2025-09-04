<?php

namespace Modules\Home\Services;

use Carbon\Carbon;
use Modules\Article\Models\Article;
use Modules\Article\Models\ArticleClassifies;
use Modules\Docs\Models\DocsApp;

class HomePageService
{
    public function getHomeArticle(): void
    {
        if (show_news_module()) {
            // 本周采集数量
            $weekArticleCount = Article::query()
                ->where('created_at', '>=', Carbon::now()->startOfWeek()->toDateTimeString())
                ->count();

            // 随机取出10条
            $articleTop = Article::query()
                ->with(['classify.parent'])
                ->when($weekArticleCount >= 10, function ($query) {
                    $query->where('updated_at', '>=', Carbon::now()->startOfWeek()->subDays(7)->toDateTimeString());
                })
                ->random(10, 'id')
                ->get();

            // 随机取出2个文章分类
            $randomClassify = ArticleClassifies::query()
                ->with('parent')
                ->withCount('articles') // 计算每个分类下的文章数量
                ->having('articles_count', '>', 0)  // 只获取文章数量大于0的分类
                // 获取三级分类
                ->where('level', 3)
                ->where('status', ArticleClassifies::STATUS_NORMAL)
                ->whereIn('show_nav', [
                    ArticleClassifies::SHOW_NAV_ONLY_WEB,
                    ArticleClassifies::SHOW_NAV_ALL,
                ])
                ->random(2)
                ->get();

            // 推荐文章列表，从每个分类中随机取出10条
            $recommendArticle = Article::query()
                // 使用 groupRandom 代替
                // ->fromSub(function ($query) use ($randomClassify) {
                //     $query->select('id', 'title', 'classify_id', 'publish_time', 'created_at', DB::raw('ROW_NUMBER() OVER (PARTITION BY classify_id ORDER BY RAND()) AS row_num'))
                //         ->from('articles')
                //         ->whereIn('classify_id', $randomClassify->pluck('id')->toArray());
                // }, 'subquery')
                // ->where('row_num', '<=', 10)
                ->with(['classify.parent'])
                ->whereIn('classify_id', $randomClassify->pluck('id')->toArray())
                ->groupRandom('classify_id', 10, 'id')
                ->get()
                ->groupBy('classify_id');

            foreach ($randomClassify as $classify) {
                $classify->recommend_article = $recommendArticle[$classify['id']] ?? [];
            }
        } else {
            $weekArticleCount = 0;
            $articleTop = [];
            $randomClassify = [];
        }

        // 在线文档 top10
        $docsTop = DocsApp::query()->open()
            ->withCount(['docs'])
            ->orderByDesc('sort')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        view_share([
            'week_article_count' => $weekArticleCount,
            'article_top' => $articleTop,
            'docs_top' => $docsTop,
            'random_classify' => $randomClassify,
        ]);
    }
}
