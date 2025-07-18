<?php

namespace Modules\Home\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Home\Services\HomePageService;
use Modules\Home\Services\TopNavService;
use Modules\System\Http\Controllers\BaseController;

class HomeBaseController extends BaseController
{
    public function initialize(Request $request, TopNavService $topNavService, HomePageService $homePageService)
    {
        view_share([
            'mega_menu' => $topNavService->megaMenu(),
            'classify_top_nav' => $topNavService->classifyTopNav(),
        ]);
        // 是否展示新闻模块 【show_news_module 这个配置放在最前面！！！】
        $showNews = setting('common.show_news_module');
        view_share('show_news_module', is_null($showNews) || (bool) $showNews);

        $homePageService->getHomeArticle();
    }
}
