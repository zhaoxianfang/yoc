<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // 自定义数据库日志
        'database' => [
            // 必须设置为 'monolog'，表示使用 Monolog 库
            'driver' => 'monolog',
            // 在创建日志实例前调用的类
            'tap' => [\Modules\Logs\Handler\BeforeDatabaseLogger::class],
            // 自定义处理器的类名，负责将日志写入数据库
            'handler' => \Modules\Logs\Handler\DatabaseLogHandler::class,
            // 使用的格式化类
            'formatter' => Monolog\Formatter\JsonFormatter::class, // JSON 格式
            // 'formatter' => Monolog\Formatter\LineFormatter::class, // 单行文本格式
            // 'formatter' => Monolog\Formatter\HtmlFormatter::class, //  HTML 格式
            // 传递给格式化器的额外参数
            'formatter_with' => [
                'dateFormat' => 'Y-m-d H:i:s',
                'includeStacktraces' => true, // 包含堆栈跟踪
            ],
            // 日志处理器的类数组，用于添加额外信息
            'processors' => [
                Monolog\Processor\UidProcessor::class, // 添加唯一ID
                Monolog\Processor\ProcessIdProcessor::class, // 添加进程ID
                Monolog\Processor\MemoryUsageProcessor::class, // 添加内存使用情况
                Monolog\Processor\WebProcessor::class, // 添加Web请求信息
                Monolog\Processor\IntrospectionProcessor::class, // 添加调用上下文信息
            ],
            // 传递给处理器的额外参数
            'handler_with' => [
                // 是否冒泡到其他渠道
                'bubble' => false,  // 数据库处理器通常设为 false 避免重复处理
                // 存储日志的数据库表名
                'table' => 'system_logs', // 日志表名
                // 批量插入的日志条数 (可选)：每积累 x 条日志触发一次批量写入
                // 'batch_size' => 10, // 默认50 // 不会传入到 处理器中
                // 当缓冲数据达到约1MB时会自动触发写入
                // 'buffer_limit' => 104857600, // 默认1M // 不会传入到 处理器中
            ],
            // 数据库特定配置
            'with' => [// 数据库处理器通常设为 false 避免重复处理
                // 使用的数据库连接 (来自 config/database.php)
                'connection' => env('DB_CONNECTION', 'mysql'), // 数据库连接名称
                // 存储日志的数据库表名
                'table' => 'system_logs', // 日志表名
            ],

        ],

    ],

];
