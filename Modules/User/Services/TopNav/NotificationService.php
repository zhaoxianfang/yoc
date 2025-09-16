<?php

namespace Modules\User\Services\TopNav;

use Modules\System\Services\BaseService;

/**
 * 系统通知
 */
class NotificationService extends BaseService
{
    public function getNotifications()
    {
        if (auth('web')->guest()) {
            return [];
        }

        // 只有 total 和 items 两个字段 都不为空时前端才展示
        return [
            'total' => 0, // 通知总条数
            'items' => [ // 通知列表
                [
                    'id' => 1,
                    // lucide 图标=》
                    // server-crash:服务器异常；
                    // alert-triangle:警告；
                    // check-circle:圆形成功；
                    // check-square:方形成功；
                    // user-plus:新增用户；
                    // bug:bug;
                    // message-circle:消息对话；
                    // battery-warning:低电量警告；
                    // cloud-upload:上传文件；
                    // calendar:日历；
                    // download:下载；
                    // lock:锁定；
                    // bell-ring:闹铃；
                    // database-zap:数据库；
                    'lucide' => 'server-crash',
                    // 颜色 danger、warning、info、success、primary、secondary
                    'color' => 'danger',
                    // 标题
                    'title' => '【系统】您有新的消息',
                    'content' => '您有新的消息，请及时查看！',
                    //  时间
                    'date' => '30 分钟前',
                    // 链接
                    'url' => '',
                ],
            ],
        ];
    }

    // 数据共享到视图
    public function toView(): void
    {
        view_share('top_nav_notification', $this->getNotifications());
    }
}
