<?php

namespace Modules\ShortLink\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\ShortLink\Models\Constants\LinkType;

class LinkController extends Controller
{
    /**
     * @param  string  $type  短链接类型：a-文章
     * @param  string  $short_code  短链接码
     * @return Factory|View
     */
    public function index(string $type, string $short_code)
    {
        // 先判断短链接类型$type是否在LinkType枚举类型中
        $getType = LinkType::tryFrom($type);
        if ($getType) {
            // dd($getType->name, $getType->value, $getType->text());

            return view('errors.tips', [
                'title' => "未开发的类型:{$getType->text()}",
                'message' => "暂未开发:{$getType->text()}类型的短链接",
            ]);
        }

        return match ($type) {
            'a' => get_resp_by_route(route('article.detail', ['article' => $short_code]), 'GET'), // 文章短链接
            default => abort(404, '不支持的请求'),
        };
    }

    /**
     * =================================================================
     *                     短链接对应的处理方法
     * =================================================================
     */
}
