<?php

namespace Modules\Task\Services;

use Modules\Article\Models\Article;
use Modules\Docs\Models\DocsDoc;
use Modules\Home\Services\SitemapService;

/**
 * 提交爬虫urls 到 索引引擎服务
 */
class SubmitSpiderUrlsService
{
    protected SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * 提交urls 到百度搜索引擎
     *
     * 调用参数:
     *      ['\Modules\Task\Services\SubmitSpiderUrlsService','baidu']
     *      或者
     *      [\Modules\Task\Services\SubmitSpiderUrlsService,baidu]
     *
     * @return void
     */
    public function baidu()
    {
        try {
            // 随机取出10条文章数据
            $articleRandom = Article::query()
                ->random(20, 'id')
                ->get();

            // 随机取出10条文档数据
            $docsRandom = DocsDoc::query()
                ->random(10, 'id')
                ->get();

            $urls = [];
            foreach ($articleRandom as $article) {
                $urls[] = route('article.detail', ['article' => $article->id]);
            }
            foreach ($docsRandom as $doc) {
                $urls[] = route('docs.doc.show', ['docsApp' => $doc->doc_app_id, 'docsDoc' => $doc->id]);
            }

            // 获取当前 url的domain
            $domain = request()->getSchemeAndHttpHost();

            // 统一替换 url前缀 把 http 替换成https，并把里面的元素随机打乱
            $urls = collect($urls)->map(function ($url) use ($domain) {
                return str_replace($domain, 'https://www.weisifang.com', $url);
            })->shuffle()->toArray();

            $api = 'http://data.zz.baidu.com/urls?site=https://www.weisifang.com&token=s4lrQEWD4wtrW17a';
            $ch = curl_init();
            $options = [
                CURLOPT_URL => $api,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => implode("\n", $urls),
                CURLOPT_HTTPHEADER => ['Content-Type: text/plain'],
            ];
            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            curl_close($ch);

            $data = is_string($result) ? json_decode($result, true) : $result;
            if (isset($data['not_same_site'])) {
                $data['not_same_site_url'] = count($data['not_same_site']);
                unset($data['not_same_site']);
            }
            debug_test(['submit_baidu_urls' => $urls, 'baidu_result' => $data], '提交百度搜索引擎 urls');
        } catch (\Exception $e) {
            debug_test([], '【异常】提交百度搜索引擎 urls：'.$e->getMessage());
        }
    }
}
