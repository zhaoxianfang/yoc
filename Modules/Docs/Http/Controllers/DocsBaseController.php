<?php

namespace Modules\Docs\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsDoc;
use Modules\Docs\Trait\DocsTipsTrait;
use Modules\System\Http\Controllers\BaseController;

class DocsBaseController extends BaseController
{
    use DocsTipsTrait;

    protected string $authName = 'web';

    // 初始化方法: 支持自定义依赖注入
    // public function initialize(Request $request)
    // {
    //     //
    // }

    /**
     * 获取应用的所有目录
     *
     * @return array[]
     */
    protected function getAppMenus(?DocsApp $docsApp): array
    {
        if ($docsApp) {
            $docsApp->load(['menus']);
            // $menuList = $docsApp->menus->toArray();
            $menuList = $docsApp->menus;
        } else {
            $menuList = [];
        }

        $isEditor = $docsApp?->isEditor(); // 是否为文档的编辑人员
        $groupMenuList = collect($menuList)->groupBy('group')->only(['guide', 'api', 'faq'])->toArray();

        // 把文档目录转换为前端目录结构 begin
        $docsGroupMenuList = [];
        foreach ($groupMenuList as $groupName => $menus) {
            $groupMenus = $this->getAppDocMenus($menus, $isEditor);
            $prefixMenu = match ($groupName) {
                'guide' => $this->getAppGuideMenu($docsApp), // guide:指南;
                'api' => $this->getAppApiMenu($docsApp), // api:api;
                'faq' => $this->getAppFaqMenu($docsApp->id), // faq:常见问题;
                default => [],
            };
            $docsGroupMenuList[$groupName] = ! empty($groupMenus) ? array_merge($prefixMenu, $groupMenus) : $prefixMenu;
            if ($docsApp?->isEditor()) {
                // 管理员角色 可以添加 底部操作按钮,固定添加键名 为 buttons 的数组
                $docsGroupMenuList['guide'][]['buttons'] = [
                    [
                        'type' => 'create_root_dir',
                        'category' => 'guide',
                        'icon' => '',
                        'group' => 'guide',
                        'title' => '创建根目录',
                        'app_id' => $docsApp?->id,
                    ],
                ];
                $docsGroupMenuList['api'][]['buttons'] = [
                    [
                        'type' => 'create_api_dir',
                        'category' => 'api',
                        'icon' => '',
                        'group' => 'api',
                        'title' => '创建接口',
                        'app_id' => $docsApp?->id,
                    ],
                ];
            }
        }
        if (empty($docsGroupMenuList['faq'])) {
            $docsGroupMenuList['faq'] = $this->getAppFaqMenu($docsApp?->id); // faq:常见问题;
            if ($docsApp?->isEditor()) {
                $docsGroupMenuList['faq'][]['buttons'] = [
                    [
                        'type' => 'create_faq_dir',
                        'category' => 'faq',
                        'icon' => '',
                        'group' => 'faq',
                        'title' => '创建帮助文档',
                        'app_id' => $docsApp?->id,
                    ],
                ];
            }
        }
        // 把文档目录转换为前端目录结构 end

        // docs 文档 顶部有没有 API 菜单分组
        view_share('docs_has_api_category', empty($docsGroupMenuList['api']) ? '' : 1);

        return $docsGroupMenuList;
    }

    // 获取应用的文档的菜单部份目录
    private function getAppDocMenus(array $menus, bool $isEditor = false)
    {
        $data = [];
        foreach ($menus as $menu) {
            $children = ! empty($menu['docs']) ? $menu['docs'] : [];
            $children = ! empty($menu['menus']) ? (! empty($children) ? array_merge($children, $menu['menus']) : $menu['menus']) : $children;
            // 菜单类型; doc:文档; dir:目录
            $menuType = ! empty($menu['title']) ? 'doc' : 'dir';
            $isDoc = $menuType == 'doc'; // 是否为文档
            $itemMenu = [
                // 字段属性中设置了 url 的，在菜单中就会自动 解析为 a 跳转标签
                'id' => $menu['id'], // 必须字段
                'title' => ! empty($menu['title']) ? $menu['title'] : $menu['name'], // 必须字段
                'icon' => $menu['icon'] ?? '',
                'menu_type' => $menuType, // doc:文档; dir:目录
                'app_id' => $menu['doc_app_id'],
                'open_type' => $menu['open_type'],
                'group' => $menu['group'], // guide:指南; api:api; faq:常见问题;
                'badge' => ($isDoc && $menu['type'] == DocsDoc::TYPE_API) ? (! empty($menu['method']) ? $menu['method'] : '') : '',
            ];

            $isEditor && ($itemMenu['edit-'.$menuType] = '');

            // children 有子菜单时必须使用 children 字段标识
            ! empty($children) && ($itemMenu['children'] = $this->getAppDocMenus($children, $isEditor));
            $data[] = $itemMenu;
        }

        return $data;
    }

    // 获取文档指南菜单
    private function getAppGuideMenu(?DocsApp $docsApp): array
    {
        // 未登录 || 不是文档管理员
        if (auth('web')->guest() || (! empty($docsApp->id) && ! $docsApp->isManager())) {
            return [];
        }
        $appId = $docsApp?->id;

        // 字段属性中设置了 url 的，在菜单中就会自动 解析为 a 跳转标签
        $guideMenus = [[
            'id' => 'manage',
            'title' => '文档管理',
            'icon' => '',
            'badge' => '管',
            'group' => 'guide',
            'menu_type' => 'app_manage_dir',
            'app_id' => $appId,
            'children' => [],
        ]];
        if (empty($appId)) {
            $guideMenus[0]['children'][] = [
                'id' => 'create',
                'title' => '创建文档',
                'icon' => '',
                'badge' => '',
                'group' => 'guide',
                'menu_type' => 'app_manage_doc', // 固定使用 app_manage_doc 或者 doc
                'app_id' => 0,
                'url' => route('docs.create_app'), // 启用url 字段表示直接跳转
            ];
        } else {
            $guideMenus[0]['children'] = [
                [
                    'id' => 'edit',
                    'title' => '编辑文档',
                    'icon' => '',
                    'badge' => '管',
                    'group' => 'guide',
                    'menu_type' => 'app_manage_doc', // 固定使用 app_manage_doc 或者 doc
                    'app_id' => $appId,
                    // 'url' => route('docs.app_edit', ['docsApp' => $appId]),
                ],
                [
                    'id' => 'users',
                    'title' => '成员管理',
                    'icon' => '',
                    'badge' => '管',
                    'group' => 'guide',
                    'menu_type' => 'app_manage_doc', // 固定使用 app_manage_doc 或者 doc
                    'app_id' => $appId,
                    // 'url' => route('docs.app_users', ['docsApp' => $appId]),
                ],
            ];
        }

        return $guideMenus;
    }

    // 获取文档常见问题菜单
    private function getAppFaqMenu(?int $appId = 0): array
    {
        return [[
            'id' => 'faq',
            'title' => '文档使用指南',
            'icon' => '',
            'badge' => '通用',
            'group' => 'faq',
            'menu_type' => 'app_faq_dir',
            'app_id' => $appId,
            'children' => [
                [
                    'id' => 'help',
                    'title' => '使用帮助',
                    'icon' => '',
                    'badge' => '',
                    'group' => 'faq',
                    'menu_type' => 'app_manage_doc', // 固定使用 app_manage_doc 或者 doc
                    'app_id' => $appId,
                    // 'url' => route('docs.app_help', ['docsApp' => $appId]),
                ],
            ],
        ]];
    }

    // 获取文档API菜单
    private function getAppApiMenu(?int $appId = 0): array
    {
        return [];
    }
}
