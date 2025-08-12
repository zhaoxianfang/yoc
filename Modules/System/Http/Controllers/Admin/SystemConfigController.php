<?php

namespace Modules\System\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Admin\Models\AdminMenu;
use Modules\System\Models\SystemConfig;

class SystemConfigController extends AdminBaseController
{
    /**
     * ==========================================================================
     * 以下为系统配置管理
     * ==========================================================================
     */

    /**
     * 系统配置
     *
     * @return Renderable
     */
    public function index()
    {
        $config = setting(true);
        $config['type_list'] = SystemConfig::getTypeList();
        $config['rule_list'] = SystemConfig::getRegexList();

        SystemConfig::checkTabsData($config);

        // 获取系统配置
        return view('system::admin/config', $config);
    }

    /**
     * 保存系统配置
     */
    public function store(Request $request)
    {
        $config = $request->input('config', []);

        $email = []; // 邮箱配置 host:val, port:val,mail:val,password:val
        $tabs = []; // Tabs配置 key:name
        $add_field = $config['add_field'] ?? []; // 新增字段

        // 有新增字段
        if (! empty($add_field['name']) && ! empty($add_field['title'])) {
            $content = $add_field['content'] ?? '';
            $content = ! empty($content) ? (in_array($add_field['type'], ['select', 'selects', 'checkbox', 'radio', 'array']) ? json_encode(SystemConfig::decode($content)) : '') : '';
            setting([
                $add_field['group'].'.'.$add_field['name'] => [
                    'name' => $add_field['name'],
                    'group' => $add_field['group'],
                    'title' => $add_field['title'],
                    'tip' => $add_field['tip'] ?? '',
                    'type' => $add_field['type'],
                    'visible' => $add_field['visible'] ?? '',
                    'value' => in_array($add_field['type'], ['selects', 'checkbox', 'array']) ? json_array_to_string($add_field['value'] ?? '') : ($add_field['value'] ?? ''),
                    'content' => $content,
                    'rule' => ! empty($add_field['rule']) ? implode('|', $add_field['rule']) : '',
                    'extend' => $add_field['extend'] ?? '',
                    'setting' => $add_field['setting'] ?? '',
                ],
            ]);
        } else {
            if (! empty($config['email'])) {
                // 验证邮箱配置
                foreach ($config['email']['host'] as $key => $host) {
                    $mail = [];
                    $mail['host'] = $host ?? 'smtp.qq.com';
                    $mail['port'] = $config['email']['port'][$key] ?? 465;
                    $mail['mail'] = $config['email']['mail'][$key] ?? '';
                    $mail['password'] = $config['email']['password'][$key] ?? '';

                    $wordKey = number_to_word($key + 1);
                    $email['email.'.$wordKey] = [
                        'title' => '邮件配置-'.$wordKey,
                        'value' => json_encode($mail),
                        'tip' => '一行一个邮件配置项',
                        'type' => 'array',
                    ];
                }
                setting($email);
            }

            if (! empty($config['tabs'])) {
                // 验证Tabs配置
                foreach ($config['tabs']['key'] as $key => $tabKey) {
                    $tab = [];
                    $tab['key'] = $tabKey;
                    $tab['name'] = $config['tabs']['name'][$key];

                    $wordKey = number_to_word($key + 1);
                    $tabs['tabs.'.$wordKey] = [
                        'title' => 'Tabs 配置-'.$wordKey,
                        'value' => json_encode($tab),
                        'tip' => '一行一个Tab项',
                        'type' => 'array',
                        'rule' => 'required',
                    ];
                }
                setting($tabs);
            }

            // 处理其他常规键值对数据
            foreach ($config as $group => $item) {
                if (in_array($group, ['email', 'tabs', 'add_field'])) {
                    continue;
                }
                setting([$group => $item]);
            }
        }

        // 清空缓存
        clear_cache();

        // 当前环境是 local 或 testing ...
        // if (App::environment(['local', 'testing'])) {
        // 当前环境不是本地环境
        if (! App::environment('local')) {
            // 重新写入缓存
            open_cache();
        }

        return $this->success([], $request->getRequestUri());
    }

    // 系统配置验证 字段唯一性
    public function checkUnique(Request $request)
    {
        $req = $request->only([
            'name',
            'type',
            'group',
        ]);
        $where = [
            'name' => $req['name'],
        ];
        $id = $request->input('id');
        if (! empty($id)) {
            $where[] = ['id', '<>', $id];
        }
        if ($req['type'] == 'add_system_config') {
            $where['group'] = $req['group'];
            $isPass = SystemConfig::query()->where($where)->doesntExist();
        }
        if ($req['type'] == 'add_menu_check_name') {
            $isPass = AdminMenu::query()->where($where)->doesntExist();
        }

        return $this->json([
            'message' => $isPass ? '验证通过' : '['.$req['name'].']字段已存在',
            'check' => (bool) $isPass,
        ]);
    }
}
