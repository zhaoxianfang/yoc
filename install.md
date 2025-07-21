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

修改本地化语言为中文：zh_CN
修改时区为东八区 Asia/Shanghai

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
> 把 `config/database.php` 里面`sqlite`的 `'database'` 的值改为`'database' => database_path('database.sqlite')`


```
【重要】
先修改database/migrations/ 里面的 users 迁移文件自字段(字段太少了)

全部迁移
php artisan migrate
模块数据库迁移
php artisan module:migrate System
刷新 Users 模块的迁移
php artisan module:migrate-refresh Users
迁移所有模块
php artisan module:migrate
```


## 6. 默认日志处理

在`.env` 文件中定义默认日志渠道,使用`database`

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
创建一个全局核心模块 System
php artisan module:make System
```

引导文件`bootstrap/app.php`中接入异常处理 和 安全拦截中间件

```php
->withMiddleware(function (Middleware $middleware): void {
    // 公共中间件【放在前面】
    $middleware->append(\Modules\System\Http\Middleware\CommonBaseMiddleware::class);
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

## 9. 在`routes/web.php` 路由文件中定义全局的`login`路由，并进行按照模块分流
```php
// auth 登录页面
Route::get('login', function (Request $request) {
    $message = '请先登录';
    $code = 401;

    // 重定向 admin、docs 模块的登录地址
    if(!empty($prefix =source_local_website('prefix')) && in_array($prefix,['admin','docs'])){
        return to_route($prefix.'.login',[]);
    }

    if ($request->expectsJson() || $request->ajax()) {
        return app('trace')->respJson($message, $code)->send();
    } else {
        return app('trace')->respView($message, $code)->send();
    }
})->name('login');
```

## 10. 安装passport

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
```php
php artisan passport:keys
// 会在`storage/oauth-private.key` 和 `storage/oauth-public.key` 生成密钥对
// 可以复制或者重新生成配置文件
```

令牌生命周期
> 一般在`App\Providers\AuthServiceProvider`的`boot`方法中配置；
> 使用zxf/tools 后 可以在任意一个模块的 `XxxServiceProvider` 里面配置;
> 我们选择在 `SystemServiceProvider` 的 `boot`方法中配置，方便以后整个模块迁移；

创建客户端令牌
```php
php artisan passport:client
```


## 11.验证码

> https://github.com/mewebstudio/captcha

安装

```
composer require mews/captcha
```

发布,这会将配置文件发布到 config/captcha.php

```
php artisan vendor:publish --provider="Mews\Captcha\CaptchaServiceProvider"
```

## 11. 新增helpers.php 组手函数文件

## 12. 设置文件夹权限

```
chmod -R 775 /www/
chown -R nobody.nobody /www/
```


---

========== 其他可选操作 ==========

---

## 创建符号链接

> 如果public目录中已经有符号链接 images和storage，只需要删除后重新执行下面的命令即可

```
php artisan storage:link
```

## 发布语言文件(本地化)

```
php artisan lang:publish
```

### 检索翻译字符串

```
// 查找本地化文件(例如leng 下的 en 或 zh_CN 等语言文件夹下单的 messages.php 里面的 'hello' 字符串)翻译
__('messages.hello');

// 取消短键 messages, 直接翻译 hello; 会去找 leng 下的 en.json 或 zh_CN.json 等语言配置文件，不存在时返回传入__的字符串
__('hello');
```


## 定时任务「任务调度」

在bootstrap/app.php 的 withSchedule 添加自定义调度任务

```
->withSchedule(function (Schedule $schedule) {
    // 自定任务调度

    // 爬虫自定义定时任务
    SpiderTasksService::customCronTasks($schedule);
})
```

## 重新加载composer

```
composer dump-autoload
```

## 配置定时任务

crontab -uroot -e

```
# * * * * * /usr/bin/php /www/weisifang_com/artisan schedule:run >> /dev/null 2>&1
# 或者跟换你的php 路径
* * * * * /usr/local/php8/bin/php /www/weisifang_com/artisan schedule:run >> /dev/null 2>&1

# 每天凌晨3点执行重启任务队列
0 3 * * * systemctl restart laravel-queue.service >> /dev/null 2>&1

# 每天凌晨4点执行mysql备份
0 4 * * * /data/tasks_command/mysql_back.sh >> /dev/null 2>&1

```


## 队列

### 配置开机启动队列任务

```
cd /etc/systemd/system/
vim laravel-queue.service
```

内容

```
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www
Group=www
Restart=always
ExecStart=/usr/local/php8/bin/php /www/weisifang_com/artisan queue:work --sleep=3 --tries=3 --timeout=180

[Install]
WantedBy=multi-user.target
```

启用并启动服务：

```
systemctl enable laravel-queue.service
systemctl start laravel-queue.service
```

其他命令

```
启动服务：systemctl start laravel-queue.service
停止服务：systemctl stop laravel-queue.service
重启服务：systemctl restart laravel-queue.service
查看服务状态：systemctl status laravel-queue.service
启用服务开机启动：systemctl enable laravel-queue.service
取消服务开机启动：systemctl disable laravel-queue.service
```

### 运行队列

> 请注意一旦 queue:work 命令启动，它将持续运行直到被手动停止或关闭终端

```
php artisan queue:work

如果你希望处理的任务 ID 包含在命令的输出中，则可以在调用 queue:work 命令时包含 -v 标志：
php artisan queue:work -v

php artisan queue:work --queue=high,default

```

### 添加队列任务

```
// 把这个任务将被推送到默认队列
\Modules\Task\Jobs\SpiderJob::dispatch($task);
// 把这个任务将被推送到「emails」队列
\Modules\Task\Jobs\SpiderJob::dispatch($task)->onQueue('emails');


// 等到打开的父数据库事务提交后再实际调度作业。如果目前没有开启的数据库事务，作业将被立即调度
->afterCommit()

// 指定特定作业应立即调度，无需等待任何打开的数据库事务提交
->beforeCommit()
```

### 重新启动所有进程

```
php artisan queue:restart
```

### 从队列中清除任务

```
php artisan queue:clear
```

```
指定最大尝试次数 3
php artisan queue:work --tries=3

设置任务执行的超时时间(允许最大执行时间), 默认为60秒,超时则进入失败队列
php artisan queue:work --timeout=180
```
