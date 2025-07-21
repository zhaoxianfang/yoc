<?php

namespace Modules\App\Http\Controllers\Api\Home;

use Modules\Article\Models\ArticleClassifies;
use Modules\System\Http\Controllers\Api\ApiBaseController;

class TopNavController extends ApiBaseController
{
    public function list()
    {
        $classify = ArticleClassifies::query()->whereIn('show_nav', [ArticleClassifies::SHOW_NAV_ONLY_APP, ArticleClassifies::SHOW_NAV_ALL])->where('status', 1)->get();
        $navBar = [];
        //        $navBar[] = [
        //            'id'   => 0,
        //            'title' => '热门',
        //            'type' => 'custom',
        //        ];
        foreach ($classify as $v) {
            $navBar[] = [
                'id' => $v['id'],
                'title' => $v['name'],
                'type' => 'article_classify',
            ];
        }

        return $this->api_json([
            'list' => $navBar,
        ]);
    }
}
