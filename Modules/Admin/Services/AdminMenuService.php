<?php

namespace Modules\Admin\Services;

use Modules\Admin\Models\AdminMenu;
use zxf\Tools\Tree;

class AdminMenuService
{
    /**
     * 获取左侧菜单 Html
     */
    public function getLeftMenu(): string
    {
        $menus = $this->getData();

        return $this->menuToHtml($menus);
    }

    /**
     * 获取后台菜单数据
     */
    private function getData(): array
    {
        $menus = [];
        $activeUrlLink = request()->path(); // $activeUrlLink = 'admin/home';
        // ->setUrlPrefix('admin/')

        if (auth('admin')->guest()) {
            $dashboard = AdminMenu::where(['identify' => 'admin_home'])->first()?->toArray();
            $menus[] = (array) $dashboard;
        } else {
            $admin = auth('admin')->user();

            // 根据不同的角色，显示不同的菜单
            if ($admin->is_super || $admin->login_group_id == '*') {
                // 超管
                $menus = AdminMenu::where(['ismenu' => 1, 'status' => 1])->get()->toArray();
            } else {
                $groupRules = group_rules();
                // 如果没有当前登录人员对应的管理员组权限数据，则返回false
                if (! isset($groupRules[$admin->login_group_id]) || empty($adminGroup = $groupRules[$admin->login_group_id])) {
                    // 没有后台权限的直接 跳转到整站首页
                    /**
                     * 敲黑板！！！
                     * laravel 析构函数 和 构造函数中，不能使用 redirect() 方法 ，需要使用 redirect()->send() 方法 进行跳转
                     */
                    redirect('/')->send();
                } else {
                    $names = array_merge(['/admin/dashboard'], array_keys($adminGroup));
                    $menus = AdminMenu::query()->where(['ismenu' => 1, 'status' => 1])->whereIn('name', $names)->get()->toArray();
                }
            }
        }
        if (empty($menus[0])) {
            return [];
        }

        // 使用默认配置 初始化数据
        return Tree::instance($menus)
            ->setId('id')
            ->setPid('pid')
            ->setSortType('weigh')
            ->setChildlist('children')
            ->toTree();
    }

    private function menuToHtml(array $menus, int $level = 0): string
    {
        $html = '';
        foreach ($menus as $menu) {
            $hasChild = ! empty($menu['children']);

        }

        return $html;
    }

    // 获取面包屑 导航
    public function getBreadcrumb(): string
    {
        $str = '';
        $currentLink = request()->path();
        $link = trim($currentLink, '/');
        $activeMenu = AdminMenu::where('ismenu', 1)->where(function ($query) use ($link) {
            $query->where('name', $link)->orWhere('name', '/'.$link);
        })->first();
        if ($activeMenu) {
            $activeMenu = $activeMenu->toArray();
            $menus = AdminMenu::where(['ismenu' => 1, 'status' => 1])->get()->toArray();
            $treeObj = \zxf\Tools\Tree::instance();
            $treeList = $treeObj->setData($menus)->getParentAndMeNodes($activeMenu['id']);
            $treeList = $treeObj->reset()->setData($treeList)->toTree();

            $list = self::findBreadcrumbTree($treeList);
            $count = count($list);
            if ($count > 1) {
                foreach ($list as $key => $item) {
                    $str .= '<li class="breadcrumb-item">';
                    if ($key == $count - 1) {
                        $str .= '<strong>'.$item['title'].'</strong>';
                    } else {
                        // $str .= '<a href="' . $item['url'] . '">' . $item['title'] . '</a>';
                        $str .= '<a href="javascript:;">'.$item['title'].'</a>';
                    }
                }
            }
        }

        return $str;
    }

    private function findBreadcrumbTree($tree)
    {
        $list = [];
        foreach ($tree as $item) {
            $list[] = [
                'title' => $item['title'],
                'url' => url($item['name']),
            ];
            if (isset($item['children'])) {
                $child = self::findBreadcrumbTree($item['children']);
                if ($child) {
                    $list = array_merge($list, $child);
                }
            }
        }

        return $list;
    }

    public function temp()
    {
        $one = <<< 'HTML'
        <li class="side-nav-item">
            <a href="chat.html" class="side-nav-link gap-1">
                <span class="menu-icon"><i class="ti ti-message-dots"></i></span>
                <span class="menu-text" data-lang="chat"> Chat </span>
                <span class="badge text-bg-danger">Icon</span>
            </a>
        </li>
HTML;

        $two = <<< 'HTML'
        <li class="side-nav-item">
            <a class="side-nav-link gap-1" data-bs-toggle="collapse" href="pages-empty.html#sidebarDashboards" aria-expanded="false" aria-controls="sidebarDashboards">
                <span class="menu-icon"><i class="ti ti-layout-dashboard"></i></span>
                <span class="menu-text" data-lang="dashboards">Dashboards</span>
                <span class="badge bg-success">5</span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarDashboards">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="index.html" class="side-nav-link">
                            <span class="menu-text" data-lang="dashboard-one">Dashboard v.1</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-empty.html#!" class="side-nav-link disabled">
                            <span class="menu-text" data-lang="dashboard-four">Dashboard v.4</span>
                            <span class="badge text-bg-light opacity-50" data-lang="dashboard-soon">soon</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
HTML;

        $three = <<< 'HTML'
        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarMenuLevels" aria-expanded="false" aria-controls="sidebarMenuLevels" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-sitemap"></i></span>
                <span class="menu-text" data-lang="menu-levels"> Menu Levels </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarMenuLevels">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarSecondLevel" aria-expanded="false" aria-controls="sidebarSecondLevel" class="side-nav-link">
                            <span class="menu-text" data-lang="second-level"> Second Level </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarSecondLevel">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="javascript: void(0);" class="side-nav-link">
                                        <span class="menu-text">Item 2.1</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="javascript: void(0);" class="side-nav-link">
                                        <span class="menu-text">Item 2.2</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </li>
HTML;

        $fore = <<< 'HTML'
        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarMenuLevels" aria-expanded="false" aria-controls="sidebarMenuLevels" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-sitemap"></i></span>
                <span class="menu-text" data-lang="menu-levels"> Menu Levels </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarMenuLevels">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarThirdLevel" aria-expanded="false" aria-controls="sidebarThirdLevel" class="side-nav-link">
                            <span class="menu-text" data-lang="third-level"> Third Level </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarThirdLevel">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="javascript: void(0);" class="side-nav-link">Item 1</a>
                                </li>
                                <li class="side-nav-item">
                                    <a data-bs-toggle="collapse" href="pages-empty.html#sidebarFourthLevel" aria-expanded="false" aria-controls="sidebarFourthLevel" class="side-nav-link">
                                        <span class="menu-text"> Item 2 </span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="sidebarFourthLevel">
                                        <ul class="sub-menu">
                                            <li class="side-nav-item">
                                                <a href="javascript: void(0);" class="side-nav-link">
                                                    <span class="menu-text">Item 3.1</span>
                                                </a>
                                            </li>
                                            <li class="side-nav-item">
                                                <a href="javascript: void(0);" class="side-nav-link">
                                                    <span class="menu-text">Item 3.2</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </li>
HTML;

    }
}
