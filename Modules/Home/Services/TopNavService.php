<?php

namespace Modules\Home\Services;

use Modules\Article\Models\ArticleClassifies;
use zxf\Tools\Tree;

/**
 * Class TopNavService
 */
class TopNavService
{
    // 顶部导航栏面板列表
    protected array $navBoardList = [
        'title' => '工具',
        'list' => [
            [
                'title' => '字符、文本',
                'list' => [
                    [
                        'title' => 'JS、CSS压缩',
                        'url' => '/tools/string/code_minify',
                    ], [
                        'title' => 'Unicode转换',
                        'url' => '/tools/string/unicode',
                    ], [
                        'title' => 'json格式化',
                        'url' => '/tools/string/json',
                    ], [
                        'title' => '系列化和反系列化',
                        'url' => '/tools/string/serialize',
                    ], [
                        'title' => 'RSA加密解密',
                        'url' => '/tools/string/rsa',
                    ],
                ],
            ], [
                'title' => '图片处理',
                'list' => [
                    [
                        'title' => '图片压缩与裁剪',
                        'url' => '/tools/images/compressor',
                    ], [
                        'title' => '二维码生成',
                        'url' => '/tools/images/qrcode#tab=1',
                    ], [
                        'title' => '条形码生成',
                        'url' => '/tools/images/qrcode#tab=2',
                    ], [
                        'title' => '字符串生成图片',
                        'url' => '/tools/images/create',
                    ], [
                        'title' => '图片转ico',
                        'url' => '/tools/images/ico',
                    ], [
                        'title' => '图片转换工具 imagick',
                        'url' => '/tools/images/magic',
                    ],
                ],
            ], [
                'title' => '文件处理',
                'list' => [
                    [
                        'title' => 'Excel导入导出.',
                        'url' => '/tools/file/excel',
                    ],
                ],
            ],
        ],
    ];

    /**
     * 渲染页面顶部水平导航
     */
    public function classifyTopNav(): string
    {
        $classify = ArticleClassifies::query()
            ->where('status', ArticleClassifies::STATUS_NORMAL)
            ->whereIn('show_nav', [
                ArticleClassifies::SHOW_NAV_ONLY_WEB,
                ArticleClassifies::SHOW_NAV_ALL,
            ])
            ->get();
        if ($classify->isEmpty()) {
            return '';
        }

        $data = $classify->toArray();
        $data = array_merge([
            [
                'id' => 99999,
                'pid' => 0,
                'level' => 1,
                'name' => '应用',
                'sort' => 0,
            ], [
                'id' => 999991,
                'pid' => 99999,
                'level' => 2,
                'name' => '在线文档',
                'sort' => 0,
                'url' => url('/docs'),
            ],
        ], $data);

        // 使用默认配置 初始化数据
        $treeClassify = Tree::instance($data)
            ->setId('id')
            ->setPid('pid')
            ->setSortType('sort')
            ->setChildlist('children')
            ->toTree();

        return $this->toNavHtml($treeClassify);
    }

    /**
     * 超级菜单/弹出大面积菜单
     */
    public function megaMenu(): string
    {
        // TODO: 临时关闭
        return '';

        if (empty($this->navBoardList)) {
            return '';
        }
        $html = '<div class="dropdown">';
        // title
        $html .= '<button class="topbar-link btn fw-medium btn-link dropdown-toggle drop-arrow-none p-0" data-bs-toggle="dropdown" data-bs-offset="0,16" type="button" aria-haspopup="false" aria-expanded="false">';
        $html .= $this->navBoardList['title'].'<span class="badge bg-success ms-1">免费</span><i class="ti ti-chevron-down ms-1"></i>';
        $html .= '</button>';
        $html .= '<div class="dropdown-menu dropdown-menu-xxl p-0">';
        $html .= '<div class="h-100" style="max-height: 380px;" data-simplebar>';
        // 提示标题
        $html .= '<div class="row g-0"><div class="col-12"><div class="p-1 text-center bg-light bg-opacity-50">';
        $html .= '<h5 class="mb-0 fs-lg fw-semibold">集合了众多<span class="text-success">绿色免费</span>的在线工具</h5>';
        $html .= '</div></div></div>';
        $html .= '<div class="row g-0">';

        $colum = count($this->navBoardList['list']);
        $columClass = $colum > 1 ? floor(12 / $colum) : 12;
        foreach ($this->navBoardList['list'] as $board) {
            $html .= '<div class="col-md-'.$columClass.'"><div class="p-3">';
            $html .= "<h5 class='mb-2 fw-semibold fs-sm dropdown-header'>{$board['title']}</h5>";
            $html .= '<ul class="list-unstyled megamenu-list">';
            foreach ($board['list'] as $item) {
                $html .= "<li><a class='dropdown-item' href='{$item['url']}'>{$item['title']}</a></li>";
            }
            $html .= '</ul></div></div>';
        }
        $html .= '</div></div></div></div>';

        return $html;
    }

    private function toNavHtml(array $treeClassify, int $level = 0): string
    {
        $html = '';
        foreach ($treeClassify as $item) {
            $hasChild = ! empty($item['children']);
            $dropdownClass = $hasChild ? 'dropdown-toggle drop-arrow-none' : '';
            $dropdownAttr = $hasChild ? 'data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"' : '';
            $dropdownHref = $hasChild ? 'javascript:;' : (! empty($item['url']) ? $item['url'] : route('article.classify', ['classify' => $item['id']]));

            if ($level < 1) {
                $dropdown = $hasChild ? 'dropdown' : '';

                $html .= "<li class='nav-item {$dropdown}'>";
                $html .= "<a class='nav-link {$dropdownClass}' {$dropdownAttr} href='{$dropdownHref}' id='topnav-{$item['id']}}'>";
                // icon 小图标
                // $html .= "<span class='menu-icon'><i class=''></i></span>";
                $html .= "<span class='menu-text' data-lang='xxx'> {$item['name']} </span>";
                // 标题后面的 badge 标签
                // $html .= '<span class="badge bg-success ms-1">new</span>';
                $html .= $hasChild ? '<div class="menu-arrow"></div>' : '';
                $html .= '</a>';
                // 下拉列表
                if ($hasChild) {
                    $html .= "<div class='dropdown-menu' aria-labelledby='topnav-{$item['id']}}'>";
                    $html .= $this->toNavHtml($item['children'], $level + 1);
                    $html .= '</div>';
                }
                $html .= '</li>';
            } else {
                $html .= $hasChild ? '<div class="dropdown">' : '';
                $html .= "<a class='dropdown-item {$dropdownClass}' {$dropdownAttr} href='{$dropdownHref}' id='topnav-{$item['id']}}'>";
                // icon 小图标
                // $html .= '<i class=""></i>';
                $html .= "<span data-lang='xxx'> {$item['name']} </span>";
                // 标题后面的 badge 标签
                // $html .= '<span class="badge bg-success ms-1">new</span>';
                $html .= $hasChild ? '<div class="menu-arrow"></div>' : '';
                $html .= '</a>';
                // 下拉列表
                if ($hasChild) {
                    $html .= "<div class='dropdown-menu' aria-labelledby='topnav-{$item['id']}}'>";
                    $html .= $this->toNavHtml($item['children'], $level + 1);
                    $html .= '</div></div>';
                }
            }
        }

        return $html;
    }
}
