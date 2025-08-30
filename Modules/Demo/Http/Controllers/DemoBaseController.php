<?php

namespace Modules\Demo\Http\Controllers;

use Illuminate\Http\Request;
use Modules\System\Http\Controllers\BaseController;
use zxf\Tools\Tree;

class DemoBaseController extends BaseController
{
    protected array $leftMenu = [
        [
            'id' => 1,
            'pid' => 0,
            'title' => '编辑器',
            'name' => '',
            'icon' => 'ti ti-medical-cross',
            'badge_text' => '',
        ],
        [
            'id' => 2,
            'pid' => 1,
            'title' => 'Summernote',
            'name' => '/demo/editor/summernote',
            'icon' => 'ti ti-medical-cross',
            'badge_text' => '',
        ],
        [
            'id' => 3,
            'pid' => 1,
            'title' => 'CkEditor 4',
            'name' => '/demo/editor/ckeditor',
            'icon' => 'ti ti-medical-cross',
            'badge_text' => '',
        ],
        [
            'id' => 4,
            'pid' => 1,
            'title' => 'Cherry Markdown',
            'name' => '/demo/editor/cherry',
            'icon' => 'ti ti-markdown',
            'badge_text' => '',
        ],
        [
            'id' => 5,
            'pid' => 1,
            'title' => 'editor.md',
            'name' => '/demo/editor/editor_md',
            'icon' => 'ti ti-markdown',
            'badge_text' => '',
        ],
        [
            'id' => 10,
            'pid' => 0,
            'title' => '表格',
            'name' => '',
            'icon' => 'ti ti-table',
            'badge_text' => '',
        ],
        [
            'id' => 11,
            'pid' => 10,
            'title' => 'DataTables',
            'name' => '/demo/table/data_tables',
            'icon' => 'ti ti-table',
            'badge_text' => '',
        ],
        [
            'id' => 20,
            'pid' => 0,
            'title' => '文件处理',
            'name' => '',
            'icon' => 'ti ti-file-function',
            'badge_text' => '',
        ],
        [
            'id' => 21,
            'pid' => 20,
            'title' => 'Excel Import',
            'name' => '/demo/excel/import',
            'icon' => 'ti ti-upload',
            'badge_text' => '',
        ],
        [
            'id' => 22,
            'pid' => 20,
            'title' => 'Excel Export',
            'name' => '/demo/excel/export',
            'icon' => 'ti ti-download',
            'badge_text' => '',
        ],
        [
            'id' => 23,
            'pid' => 20,
            'title' => 'Word Write',
            'name' => '/demo/word/write',
            'icon' => 'ti ti-pencil-code',
            'badge_text' => '',
        ],
        [
            'id' => 24,
            'pid' => 20,
            'title' => 'Word Template',
            'name' => '/demo/word/template',
            'icon' => 'ti ti-replace',
            'badge_text' => '',
        ],
        [
            'id' => 30,
            'pid' => 0,
            'title' => '在线工具',
            'name' => '',
            'icon' => 'ti ti-tools',
            'badge_text' => '',
        ],
        [
            'id' => 31,
            'pid' => 30,
            'title' => '时间和时区',
            'name' => '/demo/tools/time_zone',
            'icon' => 'ti ti-timezone',
            'badge_text' => '',
        ],
        [
            'id' => 32,
            'pid' => 30,
            'title' => '行政区选择',
            'name' => '/demo/tools/region_select',
            'icon' => 'ti ti-sitemap',
            'badge_text' => '',
        ],
        [
            'id' => 40,
            'pid' => 0,
            'title' => '组件',
            'name' => '',
            'icon' => 'ti ti-components',
            'badge_text' => '',
        ],
        [
            'id' => 41,
            'pid' => 40,
            'title' => '弹窗组件',
            'name' => '/demo/components/modal',
            'icon' => 'ti ti-layers-subtract',
            'badge_text' => '',
        ],
        [
            'id' => 42,
            'pid' => 40,
            'title' => '右键菜单',
            'name' => '/demo/components/right-menu',
            'icon' => 'ti ti-pointer-check',
            'badge_text' => '',
        ],
        [
            'id' => 43,
            'pid' => 40,
            'title' => 'Tools 组件',
            'name' => '/demo/components/tools',
            'icon' => 'ti ti-swords',
            'badge_text' => '',
        ],
    ];

    public function initialize(Request $request)
    {
        if ($request->isMethod('get')) {
            $this->generateMenuTree();

            view_share('user', auth('web')->user());
        }
    }

    // 生成左侧菜单
    private function generateMenuTree()
    {
        $activeUrlLink = request()->path(); // $activeUrlLink = 'admin/home';
        // 使用默认配置 初始化数据
        $treeData = Tree::instance($this->leftMenu)
            ->setId('id')
            ->setPid('pid')
            ->setSortType('weigh')
            ->setChildlist('children')
            // addField：为所有满足筛选条件的数据都添加is_active字段属性
            ->addFieldWithParentIds([['name', '=', $activeUrlLink]], function () {
                return ['is_active' => true];
            })
            ->toTree();

        view_share('demo_menu_html', array_to_admin_menu($treeData));
    }
}
