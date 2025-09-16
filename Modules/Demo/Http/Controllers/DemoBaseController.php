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
            // badge 显示标签，badge-*可用类型：eg:(badge badge-outline-success)
            //      default,
            //      outline-(dark,light,purple,danger,warning,info,success,secondary,primary)
            //      soft-(dark,light,purple,danger,warning,info,success,secondary,primary)
            'badge_text_style' => 'badge-outline-success',
        ],
        [
            'id' => 2,
            'pid' => 1,
            'title' => 'Summernote',
            'name' => '/demo/editor/summernote',
            'icon' => 'ti ti-medical-cross',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 3,
            'pid' => 1,
            'title' => 'CkEditor 4',
            'name' => '/demo/editor/ckeditor',
            'icon' => 'ti ti-medical-cross',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 4,
            'pid' => 1,
            'title' => 'Cherry Markdown',
            'name' => '/demo/editor/cherry',
            'icon' => 'ti ti-markdown',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        // [
        //     'id' => 5,
        //     'pid' => 1,
        //     'title' => 'editor.md',
        //     'name' => '/demo/editor/editor_md',
        //     'icon' => 'ti ti-markdown',
        //     'badge_text' => '',
        //     'badge_text_style' => '',
        // ],
        [
            'id' => 10,
            'pid' => 0,
            'title' => '表格',
            'name' => '',
            'icon' => 'ti ti-table',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 11,
            'pid' => 10,
            'title' => 'DataTables',
            'name' => '/demo/table/data_tables',
            'icon' => 'ti ti-table',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 20,
            'pid' => 0,
            'title' => '文件处理',
            'name' => '',
            'icon' => 'ti ti-file-function',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 21,
            'pid' => 20,
            'title' => 'Excel Import',
            'name' => '/demo/excel/import',
            'icon' => 'ti ti-upload',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 22,
            'pid' => 20,
            'title' => 'Excel Export',
            'name' => '/demo/excel/export',
            'icon' => 'ti ti-download',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 23,
            'pid' => 20,
            'title' => 'Word Write',
            'name' => '/demo/word/write',
            'icon' => 'ti ti-pencil-code',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 24,
            'pid' => 20,
            'title' => 'Word Template',
            'name' => '/demo/word/template',
            'icon' => 'ti ti-replace',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        // [
        //     'id' => 30,
        //     'pid' => 0,
        //     'title' => '在线工具',
        //     'name' => '',
        //     'icon' => 'ti ti-tools',
        //     'badge_text' => '',
        //     'badge_text_style' => '',
        // ],
        // [
        //     'id' => 31,
        //     'pid' => 30,
        //     'title' => '时间和时区',
        //     'name' => '/demo/tools/time_zone',
        //     'icon' => 'ti ti-timezone',
        //     'badge_text' => '',
        //     'badge_text_style' => '',
        // ],
        // [
        //     'id' => 32,
        //     'pid' => 30,
        //     'title' => '行政区选择',
        //     'name' => '/demo/other/region',
        //     'icon' => 'ti ti-sitemap',
        //     'badge_text' => '',
        //     'badge_text_style' => '',
        // ],
        [
            'id' => 40,
            'pid' => 0,
            'title' => '组件',
            'name' => '',
            'icon' => 'ti ti-components',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 41,
            'pid' => 40,
            'title' => '弹窗组件',
            'name' => '/demo/components/modal',
            'icon' => 'ti ti-layers-subtract',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 42,
            'pid' => 40,
            'title' => '右键菜单',
            'name' => '/demo/components/right-menu',
            'icon' => 'ti ti-pointer-check',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 43,
            'pid' => 40,
            'title' => 'Tools 组件',
            'name' => '/demo/components/tools',
            'icon' => 'ti ti-swords',
            'badge_text' => '',
            'badge_text_style' => '',
        ],
        [
            'id' => 44,
            'pid' => 40,
            'title' => 'WMS 仓库管理',
            'name' => '/demo/components/wms',
            'icon' => 'ti ti-building-warehouse',
            'badge_text' => 'new',
            // badge 显示标签，badge-*可用类型：eg:(badge badge-outline-success)
            //      default,
            //      outline-(dark,light,purple,danger,warning,info,success,secondary,primary)
            //      soft-(dark,light,purple,danger,warning,info,success,secondary,primary)
            'badge_text_style' => 'badge-outline-success',
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
