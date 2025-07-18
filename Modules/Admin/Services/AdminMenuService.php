<?php

namespace Modules\Admin\Services;

use Modules\Admin\Models\AdminMenu;

class AdminMenuService
{
    /**
     * 获取后台菜单
     */
    public static function render(): string
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

        return ! empty($menus[0]) ? \zxf\Extend\Menu::instance()->init($menus)->setTitle('title')->setActiveMenu($activeUrlLink)->setMenuType('inspinia')->createMenu() : '';
    }

    // 获取面包屑 导航
    public static function getBreadcrumb(): string
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

    private static function findBreadcrumbTree($tree)
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
}
