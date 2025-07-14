<?php

namespace Modules\Logs\Handler;

use Illuminate\Log\Logger;
use Monolog\Handler\HandlerInterface;

/**
 * 在日志渠道创建后、使用前进行最后的自定义
 */
class BeforeDatabaseLogger
{
    /**
     * 自定义给定的日志实例
     *
     * @param  Logger  $logger  Monolog日志实例
     */
    public function __invoke(Logger $logger)
    {

        //    // 获取所有已注册的处理器
        //    foreach ($logger->getHandlers() as $handler) {
        //        // 只处理DatabaseLogHandler实例
        //        if ($handler instanceof HandlerInterface) {
        //            // 示例1: 添加额外的处理器
        //            $handler->pushProcessor(function ($record) {
        //                $record['extra']['ip'] = request()->ip();
        //                return $record;
        //            });
        //
        //            // 示例2: 修改日志级别
        //            // $handler->setLevel(Logger::ERROR);
        //
        //            // 示例3: 添加自定义字段
        //            // $handler->setFormatter(new CustomFormatter());
        //        }
        //    }

        // 可以添加全局处理器
        $logger->pushProcessor(function ($record) {
            // 检测爬虫
            $crawlerName = is_crawler(true);

            $record['extra']['ip'] = request()->ip(); // 用户ip
            $record['extra']['user_id'] = (int) $this->getUserInfo('id'); // 用户id
            $record['extra']['is_crawler'] = ! empty($crawlerName);
            $record['extra']['crawler_name'] = $crawlerName;
            $record['extra']['module_name'] = get_module_name(true); // 使用小写下划线模块名称,
            $record['extra']['user_agent'] = mb_substr(request()->userAgent(), 0, 240);

            // 密码隐藏
            isset($record['context']['password']) && ($record['context']['password'] = '***');
            isset($record['extra']['password']) && ($record['extra']['password'] = '***');

            return $record;
        });
    }

    private function getUserInfo(?string $field = null)
    {
        try {
            return get_user_info($field);
        } catch (\Exception $e) {
            // 读取项目文件(夹)等没有相应的读/写权限 会导致无法记录错误信息
            try {
                // 测试能不能 访问数据库
                \Illuminate\Support\Facades\DB::connection()->getPdo();
                throw new \Exception(! empty($title) ? $title : '猜测可能是某个项目文件(夹)等没有响应的读/写权限');
            } catch (\Exception $e) {
                // 无法连接数据库
                // dd('Could not connect to the database. Error: ' . $e->getMessage());
                throw new \Exception(! empty($title) ? $title : '猜测可能是数据库无法连接');
            }
        }
    }
}
