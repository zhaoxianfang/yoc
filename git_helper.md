# git 使用简易说明

## 初始化项目

Git 全局设置:

```
git config --global user.name "xxx"
git config --global user.email "xxx@qq.com"
```

创建 git 仓库:

```
mkdir laravel_study
cd laravel_study
git init 
# 重新命名分支（把main重命名为master）
git branch -m main master
touch README.md
git add README.md
git commit -m "first commit"
git remote add origin https://gitee.com/xxx/laravel_study.git
git push -u origin "master"
```

已有仓库?

```
cd existing_git_repo
git remote add origin https://gitee.com/xxx/laravel_study.git
git push -u origin "master"
```

## 设置git 大小写敏感

```
git config core.ignorecase false
```
恢复git默认大小写不敏感
```
git config core.ignorecase true
```

## git 记住账号密码

```
1.进入命令行
2.输入如下命令
git config --global credential.helper store
3.使用git pull
此时输入了账号密码系统就可以记住
```

## git 强制覆盖拉取代码

```
方式一；
git fetch --all  运行 fetch 以将所有 origin/ 引用更新为最新：
git branch backup-master 【可选】备份当前分支：
git reset --hard origin/master 或者  git reset --hard

方式二；
git reset --hard HEAD
git pull

推荐
git fetch --all && git reset --hard origin/master && git pull
```

## git 全局配置

```
git config --global user.name "用户名"
git config --global user.email "邮箱号"
```

## 修改仓库源

1、查看远程库

```
git remote -v
```

2 设置git远程库的用户名密码

```
git remote set-url origin [url]
```

```
如：git remote set-url origin https://username:passwd@ip:port/test/name.git
```

3、先删除再修改地址

```
git remote rm origin
git remote add origin [url]
```

## gitee 使用token 推拉代码

```
git clone https://gitee用户名:私人令牌@gitee.com/gitee用户名/仓库名.git
```

拉取指定分支的代码

```
git clone -b 分支名称 https://gitee用户名:私人令牌@gitee.com/gitee用户名/仓库名.git
```

## git 一些基本操作

### 初始化

```
# 初始化git
git init
```

### 正常提交

```
# 添加到暂存区
git add .

# 提交信息
git commit -m 'feat: add moudles'

# 提交至远程仓库
git push --set-upstream origin master

```

### 将main分支修改为master

```
# 重命名
git branch -m main master

# 推送至远程仓库
git push -u origin master

# 删除远程 main 分支
git push origin --delete main

```

### 配置init

```
git config --global init.defaultBranch master
```

## 常见问题

问题：Git拉取的代码出现不管有没有修改的文件都变成了修改状态处理方法

解决：

```
// 项目目录下执行
git config core.filemode false

//全局设置
git config --global core.filemode false
```

### 修改git pull 等操作后的文件权限

```
https://itdream.blog.csdn.net/article/details/130973505
```

## 在git钩子中执行命令

1、进入项目根目录.git 目录

```
cd .git/hooks/
```

2、新建 post-merge 文件

```
vim post-merge
```

3、写入钩子内容

```
#!/bin/sh  

# 在写入你自定义操作命令
# echo "This is post-merge hook"

# composer install

# 缓存操作
echo "Processing cache"

php artisan cache:clear
php artisan route:clear
php artisan optimize:clear

# php artisan config:cache
# php artisan route:cache
# php artisan view:cache
php artisan optimize

# 注意: 请把权限操放在最后一步
sudo chmod -R 755 /www/yoc_cn/
# sudo chown -R nobody.nobody /www/yoc_cn/
sudo chmod -R 777 /www/yoc_cn/storage/
sudo chmod -R 777 /www/yoc_cn/bootstrap/cache/

echo "File permissions have been reset!"

# 重启任务队列
php artisan queue:restart

```

4、给予运行权限

```
sudo chmod +x post-merge
或 在hooks目录下
sudo chown -R nobody.nobody ./*
```
