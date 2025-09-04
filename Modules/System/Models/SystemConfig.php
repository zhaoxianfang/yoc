<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $guarded = ['id'];

    // 前台是否展示新闻模块
    public const SHOW_NEW_MODULE_CLOSE = 'close';
    public const SHOW_NEW_MODULE_ONLY_SPIDER = 'only_spider';
    public const SHOW_NEW_MODULE_ONLY_USER = 'only_user';
    public const SHOW_NEW_MODULE_ALL = 'all';

    public static array $showNewModuleMaps = [
        self::SHOW_NEW_MODULE_CLOSE => '不展示(关闭)',
        self::SHOW_NEW_MODULE_ONLY_SPIDER => '仅向爬虫展示(用户不可见)',
        self::SHOW_NEW_MODULE_ONLY_USER => '仅向访问者展示(爬虫不可见)',
        self::SHOW_NEW_MODULE_ALL => '全部展示(访问者和爬虫都可见)',
    ];

    /**
     * 类型转换
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
        ];
    }

    /**
     * 读取配置类型
     *
     * @return array
     */
    public static function getTypeList()
    {
        return [
            'string' => '字符串(string|text)',
            'password' => '密码(password)',
            'text' => '文本(textarea)',
            'number' => '数字(number)',
            'select' => '下拉(select)',
            'selects' => '下拉多选(selects)',
            'checkbox' => '复选(checkbox)',
            'radio' => '单选(radio)',
            'date' => '日期[年月日](date)',
            'date_y' => '日期[年](date_y)',
            'date_y_m' => '日期[年月](date_y_m)',
            'time' => '时间(time)',
            'daterange' => '日期区间(daterange)',
            // 'datetime'      => '日期时间(暂不支持)',
            // 'datetimerange' => '日期时间区间(暂不支持)',
            // 'array'         => '数组',
            // 'image'         => '图片',
            // 'images'        => '图片(多)',
            // 'file'          => '文件',
            // 'files'         => '文件(多)',
            // 'switch'        => '开关', 用select代替
            // 'editor'        => '富文本', 用text代替
            // 'custom'        => '自定义',
        ];
    }

    // 字段规则
    public static function getRegexList()
    {
        $regexList = [
            'required' => '必填',
            'number' => '数字',
            'date' => '日期',
            'time' => '时间',
            'email' => '邮箱',
            'url' => '网址',
            'id_card' => '身份证',
            'mobile' => '手机号',
            'zipcode' => '邮编',
            'ip' => 'IP地址',
            'zh_cn' => '中文',
            'en' => '字母',
            'en_underline' => '字母或下划线',
            'cn_en_num' => '中英文或数字',
            'cn_en_num_underline' => '中英文、数字或下划线',
            'en_num' => '字母或数字',
            'username' => '用户名',
            'password' => '密码',
            'strong_pwd' => '强密码(数字、大写字母、小写字母、特殊字符 至少四选三)至少8位',
            'json' => 'JSON字符串',
            // 'remote(/admin/system/config/unique)' => 'url验证唯一性',
            'length(1~5)' => '长度(1~5)',
            'length(2~15)' => '长度(2~15)',
        ];

        return $regexList;
    }

    public function getExtendHtmlAttr($value, $data)
    {
        $result = preg_replace_callback("/\{([a-zA-Z]+)\}/", function ($matches) use ($data) {
            if (isset($data[$matches[1]])) {
                return $data[$matches[1]];
            }
        }, $data['extend']);

        return $result;
    }

    /**
     * 将字符串解析成键值数组
     *
     * @param  string  $text
     * @return array
     */
    public static function decode($text, $split = "\r\n")
    {
        $content = explode($split, $text);
        $arr = [];
        foreach ($content as $k => $v) {
            if (stripos($v, '|') !== false) {
                $item = explode('|', $v);
                $arr[$item[0]] = $item[1];
            }
        }

        return $arr;
    }

    /**
     * 将键值数组转换为字符串
     *
     * @param  array  $array
     * @return string
     */
    public static function encode($array, $split = "\r\n")
    {
        $content = '';
        if ($array && is_array($array)) {
            $arr = [];
            foreach ($array as $k => $v) {
                $arr[] = "{$k}|{$v}";
            }
            $content = implode($split, $arr);
        }

        return $content;
    }

    // 检查tabs中是否有common,没有则添加到首位
    // 检查tabs中是否有email ,没有则添加
    public static function checkTabsData(&$config = [])
    {
        empty($config) && $config = [];
        $tabs = $config['tabs'] ?? [];
        $hasCommon = false;
        foreach ($tabs as $tab) {
            foreach ($tab['value'] as $item) {
                if ($item == 'common') {
                    $hasCommon = true;
                    break;
                }
            }
        }
        if (! $hasCommon) {
            array_unshift($tabs, [
                'name' => 'zero',
                'group' => 'tabs',
                'title' => 'Tabs 公共配置',
                'tip' => '一行一个Tab项',
                'type' => 'array',
                'visible' => null,
                'value' => [
                    'key' => 'common',
                    'name' => '公共配置',
                ],
                'content' => null,
                'rule' => 'required',
                'extend' => null,
                'setting' => null,
            ]);
        }
        $config['common'] = $config['common'] ?? [];
        $config['tabs'] = $tabs;
        $config['email'] = $config['email'] ?? [
            [
                'host' => 'smtp.qq.com',
                'port' => '465',
                'mail' => '',
                'password' => '',
            ],
            [
                'host' => 'smtp.qq.com',
                'port' => '465',
                'mail' => '',
                'password' => '',
            ],
        ];

        foreach ($tabs as $tab) {
            foreach ($tab['value'] as $item) {
                if (! isset($config[$item])) {
                    $config[$item] = [];
                }
            }
        }
    }
}
