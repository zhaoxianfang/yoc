# 安装&部署

下载laravel

```php
composer create-project laravel/laravel yoc_cn
```

设置git 忽略文件

## 1. 安装tools

```
安装
composer require zxf/tools

发布多模块
php artisan vendor:publish --provider="zxf\Laravel\LaravelModulesServiceProvider"

配置composer.json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\": "Modules/", <-- 增加本行即可
    }
},
```

> 更新 所有的composer 可以使用 composer update

## 2. 配置.env 文件

> 复制`.env.example`文件为`.env`文件
> 修改`APP_URL` 等的配置信息

重新生成key

```
php artisan key:generate
```

## 3. 重新加载composer

```
composer dump-autoload
```

## 4. 统一自动格式代码

运行

```
./vendor/bin/pint
```

或者在`PhpStorm`中配置中搜索`Laravel Pint`并启用

## 5. laravel 迁移文件处理

> 重新定义 users 迁移文件

运行迁移`php artisan migrate`


## 6. 默认日志处理

在`.env` 文件中定义默认日志渠道

```
# LOG_CHANNEL=stack
LOG_CHANNEL=database
```

引导文件`config/logging.php`定义默认日志处理

```php
// 创建一个日志处理模块 Logs
php artisan module:make Logs
```

> 定义 system_logs 迁移文件

在`Logs`模块中定义数据库迁移文件

```php
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
```

## 7. 全局异常处理 和 安全拦截

```
创建一个全局核心模块 Core
php artisan module:make Core
```

引导文件`bootstrap/app.php`中接入异常处理 和 安全拦截中间件

```php
->withMiddleware(function (Middleware $middleware): void {
    // 公共中间件【放在前面】
    $middleware->append(\Modules\Core\Http\Middleware\CommonBaseMiddleware::class);
    // 安全拦截[高级用法见:https://weisifang.com/docs/doc/2_300]
    $middleware->append(\zxf\Laravel\Modules\Middleware\SecurityMiddleware::class);
})
->withExceptions(function (Exceptions $exceptions): void {
    // 定义异常处理类
    \zxf\Laravel\Trace\LaravelCommonException::initLaravelException($exceptions);
})
```

## 8. 分页 && 错误页

```
php artisan vendor:publish --tag=laravel-pagination

php artisan vendor:publish --tag=laravel-errors

// 注意：把 errors/ 整个复制
```


## 9. 安装passport

创建一个用户模块 Users
```php
php artisan module:make Users
```

```php
// 会自动运行迁移
php artisan install:api --passport
```

在`Users`模块下创建`User`模型，然后删除`app`下默认的`User`模型
s

部署 Passport
