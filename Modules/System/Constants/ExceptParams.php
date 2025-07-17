<?php

namespace Modules\System\Constants;

/**
 * 表单中排除获取的字段
 */
class ExceptParams
{
    public static array $list = [
        'password', // 密码
        'log_already_recorded', // 标记为已经过日志「在黑名单中间件：BlacklistMiddleware 、 系统异常拦截等里面 添加的参数」
    ];
}
