<?php

namespace Modules\Spider\Services;

use Exception;
use Modules\Spider\Models\SpiderLink;
use Modules\Spider\Models\SpiderTask;
use Modules\Spider\Models\SpiderTasksLog;
use zxf\Dom\Document;
use zxf\Dom\Exceptions\InvalidSelectorException;
use zxf\Dom\Query;

/**
 * 执行爬虫采集任务
 */
class SpiderHandleService
{
    // 采集内容时候，需要提取 时间格式 的规则字段; 支持格式:2022-05-30,2022/05/30,2022.05.30,2022年05月30日,2022-05-30 12:12:12
    protected array $getTimeOrDate = ['publish_time', 'time', 'date'];

    // 需要把"链接"属性中的地址换成完整地址的属性名，例如 替换href 里面的地址为完整地址
    protected array $linkAttr = ['href', 'src', 'durl'];

    // 踢出文本中以开头的字符串
    protected array $kickOutTextStartString = ['来源', '来自'];

    // 当前正在执行的任务
    protected $currentTask;

    // 当前正在采集的url
    protected string $currentUrl = '';

    // 采集任务开始时间
    protected $startTime;

    // 是否为调试模式，主要是给test方法测试用的
    protected bool $isDebug = false;

    // 成功执行次数
    protected static int $successTotal = 0;

    // 失败执行次数
    protected static int $failTotal = 0;

    // 获取文章数
    protected static int $articleTotal = 0;

    /**
     * 采集字段清洗处理规则类型； 0:保留原格式 1:清洗html 2:取出时间字符  3: 纯text文字 4:正则原格式 5:正则text 6:清洗text并匹配特殊的kickOut字符串
     */
    const CLEAN_ORIGINAL = 0; // 0:保留原格式

    const CLEAN_HTML = 1; // 1:清洗html

    const CLEAN_TIME = 2; // 2:取出时间字符

    const CLEAN_TEXT = 3; // 3:纯text文字

    const CLEAN_REGEX_ORIGINAL = 4; // 4:正则原格式

    const CLEAN_REGEX_TEXT = 5; // 5:正则text

    const CLEAN_TEXT_AND_KICK_OUT = 6; // 6:清洗text并匹配特殊的kickOut字符串

    /**
     * 执行爬虫采集数据 入口
     */
    public function entry(SpiderTask $task)
    {
        set_time_limit(0);
        // 告诉PHP使用UTF-8编码直到脚本执行结束
        mb_internal_encoding('UTF-8');

        $this->currentTask = $task;
        // 执行爬虫任务 扩展属性
        $this->event('start');

        try {
            if ($task->type == SpiderTask::TYPE_LIST) {
                $this->getList($task);
            } else {
                $this->getContent($task);
            }
        } catch (Exception $err) {
            $this->error($err, '[主任务]采集异常');
        }

        $this->currentTask = $task;
        // 执行爬虫任务 扩展属性
        $this->event('finally');
    }

    /**
     * 测试爬虫规则
     *
     * @param  string|SpiderTask  $task  指定已有采集任务模型类或空字符
     * @param  string  $url  采集地址，可以指定当前采集那个url地址，可以单独配合$task或者$rule使用
     * @param  string|array  $rule  采集规则，可以指定当前采集那个规则，仅在$task为空时候有效，配合$url使用
     * @param  bool  $isDebug  是否为调试模式(true:调试模式，false,补采模式)：调试模式下，不会存储数据，只会返回采集到的数据；补采模式下，会存储数据,补采模式仅在$task为不为空时候有效
     * @return array|bool|mixed|null
     *
     * @throws Exception
     */
    public function test(string|SpiderTask $task, string $url = '', string|array $rule = [], bool $isDebug = true, int $type = SpiderTask::TYPE_CONTENT)
    {
        if (empty($task) || $task->status != SpiderTask::STATUS_NORMAL) {
            $isDebug = true;
        }
        if (empty($task)) {
            if (empty($rule)) {
                return false; // 缺少采集规则
            }
            $task = new SpiderTask;
            $url = ! empty($url) ? $url : $task->url ?? '';
            $task->status = SpiderTask::STATUS_NORMAL;
            $task->timer = '* * * * *';
            $task->name = 'TEST TASK!';
            $task->url_can_repeated = 1;
            $task->next_tasks_id = 0;
            $task->sub_tasks = 0;
            $task->domain_prefix = '';
            $task->extend = [];
            $task->before = [];
            $task->after = [];
            $task->fail = [];
            $task->success = [];
            $task->run_status = 0;
            // 上面的为默认值，下面的为传入的测试值
            $task->url = $url;
            $task->type = $type;
            $task->rules = [
                'test_field' => [
                    'route' => $rule,
                    'field_handle' => 1, // 采集到的内容是否需要手动清洗数据 0:保留原格式 1:清洗html 2:取出时间字符  3: 纯text文字
                ],
            ];
        }

        $this->isDebug = (bool) $isDebug;
        $res = [];
        try {
            $res = $type == SpiderTask::TYPE_CONTENT ? $this->getContent($task, $url) : $this->getList($task, $url);
            if (! $isDebug) {
                $this->currentTask = $task;
                // 执行爬虫任务 扩展属性
                $this->event('finally');
            }
        } catch (Exception $err) {
            ! $this->isDebug && $this->error($err, '[手动补采任务]采集异常');
        }
        $this->isDebug = false;

        return $res;
    }

    /**
     * 获取 文章列表类 数据
     *
     * @param  string|null  $url  可为空，为空时候去$task里面去取
     * @return bool|mixed|null
     *
     * @throws InvalidSelectorException
     */
    private function getList(SpiderTask $task, ?string $url = '')
    {
        if ($task->type != SpiderTask::TYPE_LIST) {
            return $this->getContent($task, $url);
        }

        return $this->spiderHtml($task, $url);
    }

    /**
     * 获取非列表(文章正文)类数据
     *
     * @param  string|null  $url  可为空，为空时候去$task里面去取
     * @return bool|mixed|void|null
     *
     * @throws InvalidSelectorException
     */
    private function getContent(SpiderTask $task, ?string $url = '')
    {
        if ($task->type == SpiderTask::TYPE_LIST) {
            return $this->getList($task, $url);
        }

        return $this->spiderHtml($task, $url);
    }

    // 执行html采集
    private function spiderHtml(SpiderTask $task, ?string $url = '')
    {
        if ($task->status != SpiderTask::STATUS_NORMAL && ! $this->isDebug) {
            return false;
        }
        $this->currentTask = $task;
        // 先初始化为成功
        $task->run_status = SpiderTask::RUN_STATUS_SUCCESS;
        $task->run_at = now()->toDateTimeString();

        // 判断url 地址
        $url = ! empty($url) ? $url : ($task->url ?? '');

        // 没有采集地址 或者没有采集规则，进入下一个任务
        if (empty($url) || empty($task->rules)) {
            self::$failTotal++;
            $task->run_status = SpiderTask::RUN_STATUS_FAIL;
            ! $this->isDebug && $task->save();
            // 执行爬虫任务 扩展属性
            $this->event('fail', ['task_id' => $task->id, 'type' => 'list'], '[config-err]缺少采集地址url或者采集规则rules');

            $this->next($task);

            return false;
        }
        // 当前url的路径前缀，用于拼接相对路径，http://a.com/a/b/c/d.html 和 http://a.com/a/b/c/ 的路径前缀都是 https://a.com/a/b/c
        $currentUrlDomainPrefix = str_ends_with(rtrim($url), '/') ? trim($url) : dirname($url);
        // 相对路径的替换前缀
        $domainPrefix = rtrim(! empty($task->domain_prefix) ? $task->domain_prefix : $currentUrlDomainPrefix, '/').'/';

        $url = $this->urlConversion($url, $domainPrefix);

        $this->currentUrl = $url;

        // 不允许重复采集 && 已经采集过了
        if (! $this->isDebug && ! $task->url_can_repeated && SpiderLink::query()->where('url', $url)->exists()) {
            // 不采集，直接进入下一个任务
            $this->next($task);

            return false;
        }

        // 执行爬虫任务 扩展属性
        $this->event('before');

        try {
            // 开始采集内容
            $document = new Document($url, true);
        } catch (Exception $err) {
            // $url 采集地址打不开或者不存在此url地址了
            $this->error($err, 'url 异常');
            $this->next($task);

            return false;
        }

        $rules = is_array($task->rules) ? $task->rules : json_decode($task->rules, true);
        $emptyFieldAndRule = []; // 没有采集到数据的字段和规则
        $data = []; // 采集结果
        $listData = []; // 采集结果
        $links = []; // 采集到的链接

        foreach ($rules as $field => $rule) {
            // 会存在 一个 内容页面 有多种页面展示（即 多种规格），需要 遍历 获取数据，只要有任意一个规则有效就视为抓取成功
            // $list 表示 通过 $rule 规则采集到的结果
            // $fieldHandle 表示 采集到 $result 的规则是否需要手动清洗数据 0:保留原格式 1:清洗html 2:取出时间字符
            [$list, $fieldHandle] = $this->exploratoryFindAndReturnResult($document, $rule);
            if (empty($list) || empty($list[0])) {
                // 没有采集到数据内容字段
                $emptyFieldAndRule[] = [
                    'task_id' => $task->id,
                    'filed' => $field,
                    'rule' => $rule,
                ];

                continue;
            }

            $tempString = '';
            foreach ($list as $row) {
                $link = '';
                if (is_string($row)) {
                    // 采集结果为字符串
                    $content = $row;
                } else {
                    $isRegExp = false; // 是否是正则采集
                    if (in_array($fieldHandle, [self::CLEAN_REGEX_ORIGINAL, self::CLEAN_REGEX_TEXT])) {
                        $isRegExp = true;
                        // 正则采集内容
                        $link = ! empty($row['href']) ? trim($row['href']) : '';
                    } else {
                        // 判断 $row 对象中是否有attr 方法
                        $link = method_exists($row, 'attr') ? trim($row->attr('href')) : '';
                    }
                    // 相对路径替换为绝对路径
                    $link = $this->urlConversion($link, $domainPrefix);
                    $links[] = $link;
                    // 是否是正则采集
                    if ($isRegExp) {
                        $content = $row['first'];
                    } else {
                        // $fieldHandle < 1 ? 原格式 : 清洗html
                        $content = is_object($row) ? ($fieldHandle == self::CLEAN_ORIGINAL ? $row->html() : $row->text()) : $row;
                    }
                }

                // $fieldHandle 采集内容是否需要清洗html格式；0:保留原格式 1:清洗html 2:取出时间字符  3: 纯text文字 4:正则原格式 5:正则text 6:清洗text并匹配特殊的kickOut字符串
                if (in_array($fieldHandle, [self::CLEAN_HTML, self::CLEAN_TEXT, self::CLEAN_REGEX_TEXT, self::CLEAN_TEXT_AND_KICK_OUT])) {
                    $content = $this->trimAndKickOutText(detach_html($content), $fieldHandle == self::CLEAN_TEXT_AND_KICK_OUT);
                }

                // 2:取出时间字符
                $needClearDate = $fieldHandle == self::CLEAN_TIME;
                // 正则匹配时间格式 日期格式
                if ($needClearDate || (! empty($this->getTimeOrDate) && in_array($field, $this->getTimeOrDate))) {
                    $content = $this->getTimeOrDate($content);
                }

                // 正则匹配替换图片等 ./ 和 ../ 开头的资源地址
                $content = $this->urlConversion($content, $domainPrefix);

                ! isset($data[$field]) && $task->type == SpiderTask::TYPE_CONTENT && $data[$field] = [];

                if ($task->type == SpiderTask::TYPE_LIST) {
                    $data[$field][] = $content;
                } else {
                    $tempString .= $content;
                    $data[$field] = $tempString;
                }
                if ($task->type == SpiderTask::TYPE_LIST) {
                    $listData[] = [
                        'text' => $content,
                        'href' => $link,
                    ];
                }
            }
        }

        if (empty($data)) {
            // 没有采集到任何数据
            self::$failTotal++;
            $task->run_status = SpiderTask::RUN_STATUS_FAIL;
            $this->event('fail', ['empty_field' => $emptyFieldAndRule, 'get_field' => array_keys($data)], '[fail]没有采集到任何数据');
        } else {
            // 不判断 $emptyFieldAndRule 是否为空，因为可能存在 采集到部分数据没采集到，但是这部分数据不是主要数据
            // if (!empty($emptyFieldAndRule)) { ... }

            // 采集到所有字段数据 或者 采集到部分数据
            self::$successTotal++;
            self::$articleTotal++;
            $task->run_status = SpiderTask::RUN_STATUS_SUCCESS;
            try {
                // 记录采集url 记录 ,防止重复添加，所以加上 try
                ! $this->isDebug && ($task->type != SpiderTask::TYPE_LIST) && SpiderLink::create(['url' => $url]);
            } catch (\Exception $e) {
            }
            // 采集到内容 执行爬虫任务 扩展属性
            ! $this->isDebug && ($task->type == SpiderTask::TYPE_LIST ? $this->event('success', $listData) : $this->event('success', $data));
        }
        if ($this->isDebug) {
            return ['list' => $data, 'list_links' => $listData, 'empty_filed' => $emptyFieldAndRule];
        }

        $task->save();
        // 执行爬虫任务 扩展属性
        $this->event('after');

        if ($task->type == SpiderTask::TYPE_LIST) {
            // 如果是列表任务,则进入下一个任务
            foreach ($links as $href) {
                $this->next($task, $href);
            }
        } else {
            // 如果是内容任务,则进入下一个任务
            $this->next($task);
        }

        return true;
    }

    /**
     * 判断并执行下一个任务
     *
     *
     * @return false|void
     */
    private function next(SpiderTask $task, ?string $url = '')
    {
        if ($this->isDebug) {
            return false;
        }
        try {
            // 如果有下一个任务
            $nextTask = (! empty($task->next_tasks_id) && is_numeric($task->next_tasks_id)) ? SpiderTask::where('id', $task->next_tasks_id)->first() : null;
            if ($nextTask) {
                $this->currentTask = $nextTask;
                $this->getList($nextTask, $url);
            }
        } catch (Exception $err) {
            if (! empty($nextTask)) {
                $this->error($err, '任务执行失败,id:'.$nextTask->id);
            }
        }
    }

    /**
     * 把 ./ 和 ../ 开头的资源地址转换为绝对地址
     *
     * @param  string  $string  需要转换的字符串
     * @param  string  $prefixString  拼接的前缀字符
     */
    public function urlConversion(string $string = '', string $prefixString = ''): string
    {
        if (empty($string) || empty($prefixString)) {
            return $string;
        }
        // 判断$string是否是 / 、./ 或者 ../ 开头的url字符串
        if (mb_substr($string, 0, 1, 'utf-8') == '/' || mb_substr($string, 0, 2, 'utf-8') == './' || mb_substr($string, 0, 3, 'utf-8') == '../') {
            return $this->urlConversionToPrefixPath($string, $prefixString);
        }
        $linkAttr = $this->linkAttr ?? ['href', 'src', 'durl'];
        $linkAttrString = implode('|', $linkAttr); // 数组转为字符串 用 (竖线)`|` 分割，例如：href|src|durl
        // 正则查找 $linkAttr 属性中 以 ./、../、/ 和文件夹名称开头的图片或超链接的相对路径 URL 地址字符串,要求src、href等前面至少带一个空格，避免操作 src 和 oldsrc 都识别到src的情况
        // $pattern = '/\s+(href|src)\s*=\s*"(?:\.\/|\.\.|\/)?([^"|^\']+)"/';
        $pattern = '/\s+('.$linkAttrString.')\s*=\s*"(?:\.\/|\.\.|\/)?([^"|^\']+)"/';
        preg_match_all($pattern, $string, $matches);

        $relativeURLs = $matches[0];
        $originalPath = []; // 原始的相对路径数组
        $replacePath = []; // 替换成的前缀路径数组
        $plusReplacePath = []; // 加强版替换路径数组
        foreach ($relativeURLs as $findStr) {
            // 删除 $findStr 字符串中的 href= 或者 src= durl= 字符串
            $findStr = preg_replace('/\s+('.$linkAttrString.')\s*=\s*["\']/i', '', $findStr);
            $originalPath[] = $findStr;
            $replacePath[] = $this->urlConversionToPrefixPath($findStr, $prefixString);
        }
        if (! empty($originalPath) && ! empty($replacePath)) {
            // 批量替换地址;直接在此处替换会导致 出现相同的'link'字符串时候会被替换多次，导致出现错误的结果
            // $string = str_replace($originalPath, $replacePath, $string);

            // 加强版开始开始表演：找出 'link' 相关字符串的前缀(例如src、href等)最为批量替换的前缀，防止被多次替换
            // 强化前缀字符串
            $strengthenAttr = $matches[1];
            foreach ($originalPath as $index => $item) {
                // 判断最后一个引号是单引号还是双引号
                $lastQuotationMark = substr($relativeURLs[$index], -1);
                // 把替换结果拼上 $linkAttr 对应的前缀，例如 ` src="` 或者 ` href="等
                $plusReplacePath[$index] = ' '.$strengthenAttr[$index].'='.$lastQuotationMark.$replacePath[$index];
            }
            // 批量替换地址
            $string = str_replace($relativeURLs, $plusReplacePath, $string);
        }

        return $string;
    }

    /**
     * 把 $url 中的 相对路径 转换为$prefix前缀路径
     */
    private function urlConversionToPrefixPath(string $url = '', string $prefix = ''): string
    {
        if (empty($url) || empty($prefix)) {
            return $url;
        }
        if (mb_substr($url, 0, 4, 'utf-8') != 'http') {
            // 用 / 把 $prefix  拆分为数组
            $domain_prefix_arr = explode('/', trim($prefix, '/'));
            if (mb_substr($url, 0, 1, 'utf-8') == '/') {
                // 处理 / 开头的路径
                if (mb_substr($prefix, 0, 4, 'utf-8') == 'http') {
                    // 解析URL
                    $urlInfo = parse_url($prefix);
                    $domain = $urlInfo['scheme'].'://'.$urlInfo['host'].(! empty($urlInfo['port']) ? ':'.$urlInfo['port'] : '');

                    return $domain.$url;
                } else {
                    return $domain_prefix_arr[0].$url;
                }
            }
            // 查找 $url 字符串中出现了几次 ../ ,例如：../../ ,不要查找 ./ ，因为 ./ 表示0次
            $count = mb_substr_count($url, '../', 'utf-8');
            // 从 $domain_prefix_arr 中删除 $count 个元素
            $count > 0 && array_splice($domain_prefix_arr, -$count);
            // 用 / 把 $domain_prefix_arr  拼接为字符串
            $prefix = implode('/', $domain_prefix_arr);
            // 去掉 $url 字符串中的 ../ 和 ./
            $url = str_replace(['../', './'], '', $url);
            $url = rtrim($prefix, '/').'/'.ltrim($url, '/');
        }

        return $url;
    }

    /**
     * 报告在执行某个采集任务时候遇到的异常
     *
     * @param  string  $title  提示标题
     * @return void
     */
    private function error(string|Exception $err, string $title = '采集异常')
    {
        if (is_string($err)) {
            $err = new Exception($err);
        }
        self::$failTotal++;
        try {
            $task = $this->currentTask;
            $url = $this->currentUrl ?? $task->url;
            ! $this->isDebug && SpiderTasksLog::writeLog($task, $title.'; id:'.$task->id, [
                '异常信息' => $err->getMessage(), // 返回用户自定义的异常信息
                '异常代码' => $err->getCode(),   // 返回用户自定义的异常代码
                '异常文件' => str_replace(base_path(), '', $err->getFile()),   // 返回发生异常的PHP程序文件名
                '异常行号' => $err->getLine(),   // 返回发生异常的代码所在行的行号
                // '异常路线' => $err->getTrace(),  // 以数组形式返回跟踪异常每一步传递的路线
            ], $url, SpiderTasksLog::STATUS_FAIL);
        } catch (Exception $e) {
        }
    }

    /**
     * 获取dom 查找规则是 css选择器还是 xpath
     */
    private function getRuleType(?string $str): string
    {
        //  判断字符串$str是dom选择器还是xpath
        $str = trim($str);

        return empty($str) ? Query::TYPE_CSS : (mb_substr($str, 0, 1, 'utf-8') == '/' ? Query::TYPE_XPATH : Query::TYPE_CSS);
        // return empty($str) ? Query::TYPE_CSS : (in_array(mb_substr($str, 0, 1, "utf-8"), ['.', '#']) ? Query::TYPE_CSS : Query::TYPE_XPATH);
    }

    /**
     * 去除所有空格和换行 和 提取特殊字符串
     *
     * @param  string|null  $str  需要去除空格和换行的字符串
     * @param  bool  $isKickOutText  是否匹配去除特殊字符串
     */
    private function trimAndKickOutText(?string $str, bool $isKickOutText = false): string
    {
        $string = empty($str) ? '' : trim(str_replace(PHP_EOL, '', $str));

        // 将 HTML 实体符号转换为普通字符
        $string = html_entity_decode(preg_replace('/\p{Zs}/u', ' ', $string), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 使用正则表达式替换回车、换行、特殊 Unicode 字符为空格
        $string = preg_replace('/[\r\n\t]+/', ' ', $string); // 替换回车、换行、制表符

        if ($isKickOutText) {
            // 处理来源等字符串
            foreach ($this->kickOutTextStartString as $keyword) {
                if (($pos = strpos($string, $keyword)) !== false) {
                    $string = substr($string, $pos + strlen($keyword));
                    // 去除前后多余的标点和空白字符
                    $string = trim($string, " \t\n\r\0\x0B:：");
                    // $string = trim(trim(trim(trim($string), ':'), '：'));
                    // 删除空格及其之后的内容
                    if (($spacePos = strpos($string, ' ')) !== false) {
                        $string = substr($string, 0, $spacePos);
                    }
                    break;
                }
            }
        } else {
            // 去除前后多余的标点和空白字符
            $string = trim($string, " \t\n\r");
        }

        return trim($string);
    }

    /**
     * 如果获取 某字段 的规则有多个「数组」，则逐一尝试去验证规则，只要有一个规则采集到数据，就视为采集成功
     * 此方法只负责处理 通过指定规则获取采集数据
     *
     * @param  Document  $document  采集到的html 页面对象
     * @param  array|string  $rules  采集规则 数组或者字符串
     *
     * @throws \zxf\Dom\Exceptions\InvalidSelectorException
     */
    private function exploratoryFindAndReturnResult(Document $document, array|string $rules): array
    {
        // $fieldHandle 采集内容是否需要清洗html格式；0:保留原格式 1:清洗html 2:取出时间字符  3: 纯text文字 4:正则原格式 5:正则text
        $fieldHandle = ! isset($rules['field_handle']) ? self::CLEAN_HTML : (int) $rules['field_handle'];     // 采集到数据时候使用的规则，是否需要手动清理一次,默认清洗
        $result = [];                                                              // 没有采集到的数据返回空

        if (empty($document) || empty($rules)) {
            return [$result, $fieldHandle];
        }
        if (is_array($rules)) {
            // 采集规则，支持的格式 1、 ['规则一','规则二','...']; 2、['route'=>['规则一','规则二','...']]; 3、['field_handle'=>0|1|2|3,'route'=>['规则一','规则二','...']]
            $routeList = ! empty($rules['route']) ? (array) $rules['route'] : (empty(array_filter($rules, function ($k) {
                return ! is_numeric($k);
            }, ARRAY_FILTER_USE_KEY)) ? $rules : []);
            // 会存在 一个 内容页面 有多种页面展示（即 多种规格），需要 遍历 获取数据，只要有任意一个规则有效就视为抓取成功
            // 逐个规格 探索性 的 去采集
            foreach ($routeList as $item) {
                if (in_array($fieldHandle, [self::CLEAN_REGEX_ORIGINAL, self::CLEAN_REGEX_TEXT])) {
                    // 使用正则采集
                    if (empty($rules['extend']) || empty($regFirst = $rules['extend']['first'])) {
                        continue;
                    }
                    $regExpHref = empty($rules['extend']['href']) ? '' : $rules['extend']['href'];

                    $decodedHtml = html_entity_decode($document->html());
                    preg_match_all($item, $decodedHtml, $matches, PREG_SET_ORDER, 0);
                    $result = [];
                    foreach ($matches as $match) {
                        if (! isset($match[$regFirst]) || (! empty($regExpHref) && ! isset($match[$regExpHref]))) {
                            break;
                        }
                        $result[] = empty($regExpHref) ? [
                            'first' => $match[$regFirst],
                            'href' => '',
                        ] : [
                            'first' => $match[$regFirst],
                            'href' => $match[$regExpHref],
                        ];
                    }
                } else {
                    // 把连续多个空格转为一个空格
                    $item = ! empty($item) ? preg_replace('/\s+/', ' ', $item) : $item;
                    // 判断是 xpath 还是css 选择器
                    $type = $this->getRuleType($item);
                    $result = $document->find($item, $type);
                }

                if (! empty($result) && ! empty($result[0])) {
                    break;
                }
            }
        } else {
            // 采集规则，$rules 直接就表示单个 '规则地址' 字符串
            // 把连续多个空格转为一个空格
            $rules = preg_replace('/\s+/', ' ', $rules);
            // 判断是 xpath 还是css 选择器
            $type = $this->getRuleType($rules);
            $result = $document->find($rules, $type);
        }

        return [$result, $fieldHandle];
    }

    // 正则匹配时间格式 日期格式 preg_match_all | preg_match, 支持格式:2022-05-30,2022/05/30,2022.05.30,2022年05月30日,2022-05-30 12:12:12
    private function getTimeOrDate(?string $string)
    {
        if (! empty($string) && preg_match("/(\d{2,4})(-|\/|.|,|、|年|\s)(\d{1,2})(-|\/|.|,|、|月|\s)(\d{1,2})(日)?(\s+(\d{1,2})\:(\d{1,2})\:(\d{1,2}))?/", $string, $parts)) {
            if (! empty($parts[0])) {
                $string = $parts[0];
            }
        }

        return trim(str_replace(['年', '月', '日', '/', '时', '分', '秒'], ['-', '-', ' ', '-', ':', ':', ''], $string));
    }

    private function event($eventName = '', $data = [], $message = '')
    {
        $eventName = strtolower($eventName);
        $map = [
            'start' => Events\StartEvent::class, // 开始进入到爬虫任务
            'fail' => Events\FailEvent::class, // 爬虫执行失败时
            'before' => Events\BeforeEvent::class, // 爬虫执行前
            'after' => Events\AfterEvent::class, // 爬虫执行后
            'success' => Events\SuccessEvent::class, // 爬虫执行成功时
            'finally' => Events\FinallyEvent::class, // 爬虫执行结束时
        ];
        if (empty($eventName) || empty($map[$eventName])) {
            return false;
        }
        if ($eventName == 'start') {
            $this->eventStart();
            $data['start_time'] = $this->startTime;
            ! $this->isDebug && SpiderTasksLog::writeLog($this->currentTask, '[start]「'.$this->currentTask->name.'」开始执行采集任务(id:'.$this->currentTask->id.') ');
        }
        if ($eventName == 'fail') {
            ! $this->isDebug && SpiderTasksLog::writeLog($this->currentTask, ('采集异常:task_id-'.$this->currentTask->id.'||msg-'.($message ?? '采集失败')), $data, $this->currentUrl, SpiderTasksLog::STATUS_FAIL);
        }
        if ($eventName == 'finally') {
            $statistics = $this->eventFinally();
            $data = empty($data) ? $statistics : (empty($statistics) ? $data : array_merge($data, $statistics));
        }
        $data['spider_url'] = $this->currentUrl;
        try {
            $map[$eventName]::handle($this->currentTask, $data, $message);
        } catch (\Throwable $e) {
            $this->error($e, $eventName.'事件处理失败');
        }

        return $data;
    }

    private function eventStart()
    {
        // 初始化计数
        self::$successTotal = 0;
        self::$failTotal = 0;
        self::$articleTotal = 0;

        // 任务开始时间
        $this->startTime = microtime(true);
    }

    // 采集结束的场景
    private function eventFinally(): array
    {
        // 结束时间(秒)
        $second = round(bcsub(microtime(true), $this->startTime, 0), 3);
        $data = [
            'article_num' => self::$articleTotal.'-篇',
            'success' => self::$successTotal.'-次',
            'fail' => self::$failTotal.'-次',
            'time' => $second.'秒',
        ];

        ! $this->isDebug && SpiderTasksLog::writeLog($this->currentTask, '[end]「'.$this->currentTask->name.'」:采集结束,id:'.$this->currentTask->id, $data);

        // 全部处理完后重新初始化
        self::$successTotal = 0;
        self::$failTotal = 0;
        self::$articleTotal = 0;
        $this->currentTask = '';
        $this->currentUrl = '';

        return $data;
    }
}
