<?php

namespace Modules\System\Constants;

/**
 * 系统邮箱
 */
class SystemEmails
{
    /**
     * 系统管理员邮箱列表
     *     用于通知系统管理、业务 相关信息
     */
    public static array $manager = [
        'EN 管理员' => 'weisifang_com@outlook.com',
        'CN 管理员' => '1748331509@qq.com',
    ];

    /**
     * 开发员邮箱列表
     *     用于通知系统异常、代码 相关信息
     */
    public static array $developer = [
        'EN 管理员' => 'weisifang_com@outlook.com',
        'CN 管理员' => '1748331509@qq.com',
    ];
}
