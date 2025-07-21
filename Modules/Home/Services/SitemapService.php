<?php

namespace Modules\Home\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Blog\Models\Article;
use Modules\Docs\Models\DocsDoc;

class SitemapService
{
    // 获取动态 URL
    public function getDynamicUrls(): Collection
    {
        // 这里可以添加逻辑从数据库获取动态 URL
        // 例如，获取最新的文章 URL
        //    return [
        //        'https://www.example.com/article1',
        //        'https://www.example.com/article2',
        //        // ...更多动态 URL
        //    ];

        $urls = collect();

        try {
            // 置顶的 URL
            $urls = $urls->merge($this->getTopUrls());
        } catch (\Exception $e) {
        }

        try {
            // 获取文章的 URL
            $urls = $urls->merge($this->getArticleUrls());
        } catch (\Exception $e) {
        }

        try {
            // 获取文档的 URL
            $urls = $urls->merge($this->getDocsDocUrls());
        } catch (\Exception $e) {
        }

        try {
            // 获取在线工具的 URL
            $urls = $urls->merge($this->getOnlineToolUrls());
        } catch (\Exception $e) {
        }

        return $urls->unique('loc')->sortByDesc('lastmod');
    }

    public function getTopUrls()
    {
        $nowDate = now()->subMinutes(5)->format('Y-m-d');

        // 置顶的 URL
        return collect([
            // 首页
            [
                'loc' => route('home'),
                'lastmod' => $nowDate,
                'priority' => '1.0',
                'images' => [
                    [
                        'url' => asset('static/images/logo.jpg'),
                        'title' => config('app.name'),
                        'caption' => '威四方是一个为企业和个体客户提供信息综合服务的平台；包含客户关系管理系统(CRM)、仓库管理系统(WMS)、采购系统(SRM)、在线文档(DOCS)、在线相册(PHOTOS)、企业智能办公系统(OA)、企业资源计划管理(ERP)、在线工具(tools)、个性化定制等服务项目;以客户成功为我们的宗旨。',
                    ],
                ],
            ],
            // 在线文档
            [
                'loc' => route('docs.home'),
                'lastmod' => $nowDate,
                'priority' => '0.9',
                'images' => [
                    [
                        'url' => asset('static/images/wsf.jpg'),
                        'title' => '在线文档',
                        'caption' => '在线文档是团队协作、知识共享、知识保护、在线画图、快速制作流程图、流程图、脑图、UML类图、时序图、状态图、甘特图、饼图、柱状图 不可或缺的办公利器。',
                    ],
                ],
            ],
        ]);
    }

    /**
     * 获取文章 URLS
     */
    public function getArticleUrls()
    {
        // 获取最近7天的文章 URL
        $articles = Article::query()
            ->select('id', 'publish_time', 'created_at', 'updated_at')
            ->where('updated_at', '>=', now()->subDays(7))
            ->get();

        // 随机取出100条文章数据
        $articleRandom = Article::query()
            ->select('id', 'publish_time', 'created_at', 'updated_at')
            ->random(100, 'id')
            ->get();

        // 合并 $articles 和 $articleRandom 的数据
        $articleList = $articles->merge($articleRandom);

        $urls = collect();

        foreach ($articleList as $article) {
            $urls->push([
                'loc' => route('article.detail', ['article' => $article->id]),
                'lastmod' => Carbon::parse(! empty($article->publish_time) ? $article->publish_time : $article->created_at)->format('Y-m-d'),
            ]);
        }

        return $urls;
    }

    // 获取在线文档 URLS
    public function getDocsDocUrls()
    {
        // 获取最近4天的文档 URL
        $docs = DocsDoc::query()
            ->select('id', 'created_at', 'updated_at')
            ->where('updated_at', '>=', now()->subDays(4))
            ->get();

        // 随机取出50条文档数据
        $docsRandom = DocsDoc::query()
            ->select('id', 'created_at', 'updated_at')
            ->random(50, 'id')
            ->get();

        $docsList = $docs->merge($docsRandom);
        $urls = collect();

        foreach ($docsList as $doc) {
            $urls->push([
                'loc' => route('docs.doc.show', ['docsApp' => $doc->doc_app_id, 'docsDoc' => $doc->id]),
                'lastmod' => Carbon::parse($doc->updated_at)->format('Y-m-d'),
            ]);
        }

        return $urls;
    }

    // 在线工具地址
    public function getOnlineToolUrls()
    {
        $dateTime = now()->subMonths(2)->toDateTimeString();
        $list = [
            route('tools.string.code_minify'),
            route('tools.string.unicode'),
            route('tools.string.json'),
            route('tools.string.serialize'),
            route('tools.string.rsa'),
            route('tools.string.aes'),
            route('tools.images.img_compressor'),
            route('tools.images.create_qrcode'),
            route('tools.images.str2img'),
            route('tools.images.img2ico'),
            // route('tools.string.magic'),
        ];

        $urlList = [];
        foreach ($list as $url) {
            $urlList[] = [
                'loc' => $url,
                'lastmod' => $dateTime,
                'priority' => '0.5',
            ];
        }

        return collect($urlList);
    }

    public function generateSitemapXml(Collection $urls)
    {
        // 生成 XML 格式的 Sitemap 内容
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');

        foreach ($urls as $item) {
            $urlElement = $xml->addChild('url');
            // URL
            $urlElement->addChild('loc', htmlspecialchars($item['loc']));
            // 最后修改时间
            $urlElement->addChild('lastmod', $item['lastmod']);
            // 更新频率，可以是 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'
            $urlElement->addChild('changefreq', ! empty($item['changefreq']) ? $item['changefreq'] : 'monthly');
            // 优先级，取值范围为 0.0 至 1.0，默认为 0.5。
            $urlElement->addChild('priority', ! empty($item['priority']) ? $item['priority'] : '0.6');

            // 如果有图片
            if (isset($item['images'])) {
                foreach ($item['images'] as $image) {
                    // 创建 image:image 节点
                    $imageElement = $urlElement->addChild('image:image');
                    // 图片 URL
                    $imageElement->addChild('image:loc', htmlspecialchars($image['url']));
                    // 如果图片有标题，添加 image:title 标签
                    if (! empty($image['title'])) {
                        $imageElement->addChild('image:title', htmlspecialchars($image['title']));
                    }
                    // 如果图片有描述，添加 image:caption 标签
                    if (! empty($image['caption'])) {
                        $imageElement->addChild('image:caption', htmlspecialchars($image['caption']));
                    }
                }
            }
        }

        return $xml->asXML();
    }
}
