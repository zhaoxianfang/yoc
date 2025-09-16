<?php

namespace Modules\User\Services\TopNav;

use Modules\System\Services\BaseService;

/**
 * 用户消息
 */
class MessagesService extends BaseService
{
    public function getMessages()
    {
        if (auth('web')->guest()) {
            return [];
        }

        // 只有 total 和 items 两个字段 都不为空时前端才展示
        return [
            'total' => 0, // 消息总条数
            'items' => [ // 消息列表
                [
                    'id' => 1,
                    'user_name' => '张三',
                    //  时间
                    'date' => '2025-01-01',
                    // 标题
                    'title' => '你有一个待办事项',
                    'content' => '您有新的消息，请及时查看！',
                    // 链接
                    'url' => '',
                ],
            ],
        ];
    }

    // 数据共享到视图
    public function toView(): void
    {
        view_share('top_nav_messages', $this->getMessages());
    }
}
