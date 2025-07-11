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

## 5. 创建一个全局核心模块

```
创建一个全局核心模块 Core
php artisan module:make Core

制作全局中间件
php artisan module:make-middleware CommonBaseMiddleware Core

// 在bootstrap/app.php 的 withMiddleware 里面注册全局中间件
$middleware->append(\Modules\Core\Http\Middleware\CommonBaseMiddleware::class);

// 手动注册 admin 中间件组
```

## 6. 创建自定义全局异常处理类

```php
php artisan module:make-exception CommonException Core 
```
