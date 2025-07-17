<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Modules\System\Models\SystemConfig;

if (! function_exists('source_local_website')) {
    /**
     * 判断跳转url的上一个地址（来源地址）是不是从本站跳转过来的
     *
     * @param  string  $returnType  返回类型，默认返回全部，可选值：status、url、all
     */
    function source_local_website(string $returnType = ''): bool|array|string|null
    {
        $referer = request()->header('referer', '');
        // 判断是否为外部来源 (空referer或非本站URL)
        $isExternal = empty($referer) || ! str_starts_with(
            parse_url($referer, PHP_URL_HOST) ?? '',
            parse_url(config('app.url'), PHP_URL_HOST) ?? ''
        );

        // 来源地址不是本站
        return match ($returnType) {
            'status' => (bool) ! $isExternal, // 返回来源是否是本站
            'url' => $isExternal ? $referer : '', // 当来源地址是本站时，返回来源地址，否则返回空
            default => [(bool) ! $isExternal, $referer], // 默认返回 [来源是否为本站,本站来源url]
        };
    }
}

if (! function_exists('cache_file')) {
    /**
     * 文件驱动缓存 助手函数
     *
     * @return mixed|\Illuminate\Cache\CacheManager
     */
    function cache_file()
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            return app('cache')->store('file');
        }

        if (is_string($arguments[0])) {
            return app('cache')->store('file')->get(...$arguments);
        }

        if (! is_array($arguments[0])) {
            return false;
            // throw new Exception('应该传入数组类型参数');
        }
        if (! empty($arguments[0])) {
            // zxf 自定义：传入的值为空数组的，直接进行删除操作
            $isForget = false; // 是否执行了删除操作
            foreach ($arguments[0] as $key => $value) {
                is_array($value) && empty($value) && app('cache')->store('file')->forget($key);
                is_array($value) && empty($value) && ($isForget = true);
            }
            if ($isForget) {
                return true;
            }
        }

        return app('cache')->store('file')->put(key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
    }
}

if (! function_exists('setting')) {
    /**
     * 获取系统配置
     *
     * $key:【字符串表示「获取」数据；数组表示更新(修改、插入、删除)数据】
     *
     * @param  bool|array|string|null  $key
     *                                       null|''   : [默认]获取全部 name=>value 配置参数;
     *                                       array  : 删除/修改 sys_config的缓存值; []表示删除对应配置，非空[]表示修改/创建对应配置
     *                                       string : 获取参数值，支持点分割的参数，例如 base,base.name
     *                                       true : 获取全部原始参数值
     *
     * @demo   例：setting()              获取全部 name=>value 键值对配置
     *         例：setting(true)          获取全部原始配置
     *         例：setting('base')        获取base组的值
     *         例：setting('base.name')   获取base.name的值
     *
     *         例：setting([])                删除sys_config的「缓存」值
     *         例：setting(['base'=>[]])      删除base组的所有配置
     *         例：setting(['base.name'=>[]]) 删除base组下的name配置
     *
     *         例：setting(['base.name' => '测试', 'base.title' => 'title'])        批量修改base.name和base.title的值
     *         例：setting(['base.title' => ['name'=>'xxx','type'=>'xxx',...]])   【必须携带type参数】修改base.title 对应行的值
     *         例：setting(['base' => ['name'=>'xxx','type'=>'xxx',...]])         【必须携带type参数】批量遍历更新 base 组内的所有配置项（会先进行base组数据清空）
     *
     * @return array|false|\Illuminate\Cache\CacheManager|mixed|null|bool
     */
    function setting(bool|array|string|null $key = null): mixed
    {
        if (is_array($key)) {
            // 先清空缓存
            cache_file(['sys_config' => []]);
            cache_file(['sys_config_all' => []]);
            if (! empty($key)) {
                foreach ($key as $field_str => $field_val) {
                    $fields = explode('.', $field_str);
                    if (empty($fields) || empty($fields[0])) {
                        continue;
                    }
                    $multiKey = count($fields) > 1 && ! empty($fields[1]); // 是否为多个键「group.name」
                    if (is_array($field_val) && (empty($field_val) || (! $multiKey && isset($field_val['0'])))) {
                        // （值为空数组 || 键仅为group的三维 ）表示删除该 项/组 配置
                        ! $multiKey && SystemConfig::where('group', $fields[0])->delete();
                        $multiKey && SystemConfig::where('group', $fields[0])->where('name', $fields[1])->delete();

                        continue;
                    }
                    // $field_val 为单行记录更新或插入
                    if (($valIsStr = ! is_array($field_val)) || isset($field_val['type'])) {
                        // 仅更新值，例如['base.name' => '测试']
                        $multiKey && $valIsStr && SystemConfig::where('group', $fields[0])->where('name', $fields[1])->update(['value' => json_array_to_string($field_val)]);
                        // 更新整行
                        isset($field_val['value']) && ($field_val['value'] = json_array_to_string($field_val['value']));
                        isset($field_val['content']) && ($field_val['content'] = json_array_to_string($field_val['content']));
                        isset($field_val['extend']) && ($field_val['extend'] = json_array_to_string($field_val['extend']));
                        isset($field_val['setting']) && ($field_val['setting'] = json_array_to_string($field_val['setting']));
                        $multiKey && ! $valIsStr && SystemConfig::updateOrCreate(['group' => $fields[0], 'name' => $fields[1]], $field_val);

                        continue;
                    }

                    // 更新多条记录
                    foreach ($field_val as $indexOrField => $item) {
                        $nameValue = $multiKey ? $fields[1] : $indexOrField;
                        if (is_array($item) && isset($item['type'])) {
                            // 更新整行
                            isset($item['value']) && ($item['value'] = json_array_to_string($item['value']));
                            isset($item['content']) && ($item['content'] = json_array_to_string($item['content']));
                            isset($item['extend']) && ($item['extend'] = json_array_to_string($item['extend']));
                            isset($item['setting']) && ($item['setting'] = json_array_to_string($item['setting']));
                            $multiKey && SystemConfig::updateOrCreate(['group' => $fields[0], 'name' => $nameValue], $item);
                        } else {
                            // 仅更新值
                            $multiKey && SystemConfig::where('group', $fields[0])->where('name', $fields[1])->update(['value' => json_array_to_string($item)]);
                            ! $multiKey && is_string($indexOrField) && SystemConfig::where('group', $fields[0])->where('name', $indexOrField)->update(['value' => json_array_to_string($item)]);
                        }
                    }
                }
            }

            return true;
        }
        if (empty($config = cache_file('sys_config')) || empty(cache_file('sys_config_all'))) {
            $sysConfig = SystemConfig::all();
            $all = empty($sysConfig) ? [] : collect($sysConfig)->groupBy('group')->toArray();

            $config = [];
            foreach ($all as $groupName => &$groupList) {
                empty($config[$groupName]) && $config[$groupName] = [];
                foreach ($groupList as &$group) {
                    $config[$groupName][$group['name']] = json_string_to_array($group['value']);
                    $group['value'] = json_string_to_array($group['value']);
                    $group['content'] = json_string_to_array($group['content']);
                    $group['extend'] = json_string_to_array($group['extend']);
                    $group['setting'] = json_string_to_array($group['setting']);
                }
            }
            // 部分app 配置
            $partApp = array_keys_search(config('app'), ['name', 'env', 'debug', 'url', 'asset_url', 'timezone', 'locale']);
            // 把app的部分配置信息合并到common中
            $config['common'] = ! empty($config['common']) ? array_merge($partApp, $config['common']) : $partApp;

            cache_file(['sys_config' => $config]);
            cache_file(['sys_config_all' => $all]);
        }
        // 获取所有的原始数据
        if ($key === true) {
            return cache_file('sys_config_all');
        }
        // 获取指定键对应的值 支持点分割的字符串，例如 base 、base.name 、base.copyright.start
        if (! empty($key) && is_string($key)) {
            $keys = explode('.', $key);
            foreach ($keys as $name) {
                $config = $config[$name] ?? null;
            }
        }

        return $config;
    }
}

if (! function_exists('clear_cache')) {
    /**
     * 清理系统缓存
     */
    function clear_cache()
    {
        // 更新管理员组缓存
        group_rules(true);
        // 清理系统配置项 文件缓存 sys_config
        setting([]);
        // artisan 命令清理缓存
        // 清理事件缓存
        Artisan::call('event:clear', []);
        // 清理视图缓存
        Artisan::call('view:clear', []);
        // 清理路由缓存
        Artisan::call('route:clear', []);
        // 清理配置缓存
        Artisan::call('config:clear', []);
        // 清理应用缓存
        Artisan::call('cache:clear', []);
        // 清理优化缓存
        Artisan::call('optimize:clear', []);
    }
}

if (! function_exists('open_cache')) {
    /**
     * 开启系统缓存
     */
    function open_cache()
    {
        // 开启事件缓存
        Artisan::call('event:cache', []);
        // 开启视图缓存
        Artisan::call('view:cache', []);
        // 开启路由缓存
        Artisan::call('route:cache', []);
        // 开启配置缓存
        Artisan::call('config:cache', []);
        // 开启优化缓存
        Artisan::call('optimize', []);
    }
}

if (! function_exists('send_email')) {
    /**
     * 发送邮件
     *
     * @param  array  $toEmails  接收者邮箱 eg: ['张三'=>'zhangsan@qq.com','李四=>'lisi@163.com'] 或者 ['zhangsan@qq.com','lisi@163.com']
     * @param  string  $title  邮件标题
     * @param  string  $html  邮件内容
     * @param  int  $throttleSecond  节流秒数；默认为0，不节流，否则根据节流秒数节流
     * @param  string  $throttleKey  节流key；默认为空，$throttleSecond大于0时，必须设置
     *
     * @throws Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    function send_email(array $toEmails, string $title, string $html, int $throttleSecond = 0, string $throttleKey = 'send_email'): void
    {
        try {
            if (! empty($throttleSecond) && empty($throttleKey)) {
                throw new \Exception('发送邮件参数错误');
            }
            if (! empty($throttleSecond) && $throttleSecond > 0 && ! empty($throttleKey)) {
                $throttleKey = trim($throttleKey);
                $cacheKey = "send_email_{$throttleKey}_throttle";

                if (! empty(Cache::store('file')->get($cacheKey, 0))) {
                    // 缓存没过期，不发送邮件
                    return;
                }
                // 设置当前时间戳
                Cache::store('file')->put($cacheKey, time(), $throttleSecond);
            }

            $sysName = config('app.name', '威四方');
            $mail = \zxf\PHPMailer\Mail::instance();
            $mail->title($title.'_'.$sysName)
                ->content($html);
            //    ->to('weisifang_com@outlook.com', 'EN 管理员')
            //    ->cc('1748331509@qq.com', 'CN 管理员') // 抄送
            //    // ->bcc('mail','name') // 密送
            //    // ->attachment('xxx.csv','xxx报表') // 附件
            //    ->send();

            foreach ($toEmails as $name => $email) {
                is_numeric($name) ? $mail->to($email) : $mail->to($email, $name);
            }
            $mail->send();
        } catch (\Exception $e) {
            // 捕捉异常信息
            // $mail->getErrors();
            // debug_test($e, 'send_email:Error');
            throw $e;
        }
    }
}
