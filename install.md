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
