<?php

namespace Modules\System\Services;

use Illuminate\Http\Request;

/**
 * 安全拦截中间件附加服务
 */
class SecurityServices
{
    /**
     * 自定义正则匹配拦截请求body
     *
     * 返回说明：
     *      返回空数组表示使用预定义的表达式进行拦截;
     *      返回非空正则表达式数组表示使用此处定义的正则表达式进行拦截
     *
     * @see \zxf\Laravel\Modules\Middleware\SecurityMiddleware::MALICIOUS_BODY_PATTERNS
     */
    public static array $banRegExpBody = [
        // 例如：
        // '/<script\b[^>]*>(.*?)<\/script>/is',  // 基本脚本标签
        // '/delete\s+from/i',                    // SQL 删除
    ];

    /**
     * 自定义正则匹配拦截请求 URL
     *
     * 返回说明：
     *      返回空数组表示使用预定义的表达式进行拦截;
     *      返回非空正则表达式数组表示使用此处定义的正则表达式进行拦截
     *
     * @see \zxf\Laravel\Modules\Middleware\SecurityMiddleware::ILLEGAL_URL_PATTERNS
     */
    public static array $banRegExpUrl = [
        // 例如：
        // '~/(\.+[^/]*)(?=/|$)~',      // 匹配所有点(.)开头的文件或文件夹
    ];

    /**
     * 禁止上传的文件后缀
     *
     * 返回说明：
     *      返回空数组表示使用预定义的文件后缀进行拦截;
     *      返回非空正则表达式数组表示使用此处定义的文件后缀进行拦截
     *
     * @see \zxf\Laravel\Modules\Middleware\SecurityMiddleware::DISALLOWED_EXTENSIONS
     */
    public static array $forbidUploadFileExt = [
        // 例如：
        // 'php', 'exe',  'sh', 'bat'
    ];

    /**
     * 禁止包含的 User-Agent
     *
     * 返回说明：
     *      返回空数组表示使用预定义的表达式进行拦截;
     *      返回非空正则表达式数组表示使用此处定义的表达式进行拦截
     *
     * @see \zxf\Laravel\Modules\Middleware\SecurityMiddleware::SUSPICIOUS_USER_AGENTS
     */
    public static array $forbidUserAgent = [
        // 例如：
        // '/sqlmap/i',       // SQL注入工具
        // '/nikto/i',        // 漏洞扫描器
        // '/metasploit/i',   // 渗透测试框架
        // '/nessus/i',       // 漏洞扫描器
    ];

    /**
     * 允许的请求方法; 空表示允许所有的请求方法
     * @var array
     */
    public static array $allowMethods = [
        // 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'
    ];

    /**
     * 不需要验证请求body的请求uri 列表（仅path）; 空表示所有请求方法都验证
     * @var array
     */
    public static array $whitelistPathOfNotVerifyBody = [
        // 'api/test'
    ];

    /**
     * API 请求拦截时的返回参数格式
     *
     * 返回说明：
     *      返回空数组表示使用预定义的格式进行返回;
     *      返回非空正则表达式数组表示使用此处定义的格式进行返回;
     *
     * @example [
     *              'code'=>403,
     *              'message'=>'黑名单拦截'
     *              'data'=>[
     *                  'title'=>'黑名单/Ip拦截',
     *                  'type'=>'Blacklist'
     *              ]
     *          ]
     *
     * @see \zxf\Laravel\Modules\Middleware\SecurityMiddleware::createSecurityResponse
     */
    public static array $ajaxRespFormat = [
        'code' => 'code',
        'message' => 'message',
        'data' => 'data',
    ];

    /**
     * 黑名单IP处理
     *
     * @param  string  $ip  判断是否需要拦截的 ip
     * @return array [bool(是否拦截),'拦截信息']
     */
    public function blacklistIp(string $ip): array
    {
        return [false, '黑名单IP:'.$ip];
    }

    /**
     * 发送安全告警
     */
    public function sendSecurityAlarm(array $data): void
    {
        // 发送安全告警给管理员等
    }

    /**
     * 自定义拦截处理
     *
     * 返回说明：
     *  返回数据为空表示出处理，继续执行
     *  返回数据不为空表示拦截并提示返回数组
     */
    public function customHandle(Request $request): array
    {
        // 返回拦截参数格式
        // return [
        //    'type' => '拦截类型',
        //    'title' => '拦截标题',
        //    'message' => '拦截提示信息',
        //    'context' => ['info' => '拦截附加信息'],
        // ];
        return [];
    }
}
