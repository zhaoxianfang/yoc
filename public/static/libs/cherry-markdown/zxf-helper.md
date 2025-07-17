
# 下载和使用说明

下载日期: 2025-05-22
下载时的版本号：0.8.58

github:https://github.com/Tencent/cherry-markdown

> 提示 ：mac 同时按  Command + Shift + . 切换是否显示隐藏文件和文件夹

## 下载插件包后的操作：
    01、删除 全部.开头的隐藏文件
    02、删除 client(客户端) 文件夹
    03、删除 test 文件夹
    04、删除 vscodePlugin 文件夹
    05、删除 types 文件夹
    06、删除 babel.config.js 文件
    07、删除 gulpfile.js 文件
    08、删除 jest.config.ts 文件
    09、删除 tsconfig.addons.json 文件
    10、删除 tsconfig.json 文件
    11、删除 yarn.lock 文件
    12、删除 README.JP.md 文件
    13、删除 build 文件夹
    14、删除 examples/images 文件夹
    15、删除 examples/cherry-markdown-react-demo 文件夹
    15、删除 examples/cherry-markdown-publish 文件夹

## 替换 tex-svg.js 路径
原路径 `https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js`
现地址 TODO

## 为了减少echarts 的体积，进行echarts在线定制功能模块
保持和examples 示例里面的相同版本号，当前为 4.6.0
https://echarts.apache.org/zh/builder.html


##  MathJax 库
下载地址
```
https://github.com/mathjax/MathJax/tree/master/es5
```
然后把里面的`ui`和`input`文件夹复制到 `cherry-markdown` 文件夹下



## 此项目源码文件较大，无法直接下载

### 下载指定tags 的项目
```
git clone --branch v0.8.58 https://github.com/Tencent/cherry-markdown.git cherry-markdown-0.8.58
```

### Step 1：安装lfs
给出mac和linux俩版本的安装方法。
```
# mac下请使用homebrew安装：
brew install git-lfs
# linux(unbuntu)下安装：
curl -s https://packagecloud.io/install/repositories/github/git-lfs/script.deb.sh | sudo bash
apt-get install git-lfs
```


### Step 2：使用lfs
```
# 1. 安装完成后，首先先初始化；如果有反馈，一般表示初始化成功
git lfs install

# 2. 如果刚刚下载的那个项目没啥更改，重新下一遍，不算麻烦事（因为下载大文件，一般会比较慢）
git lfs clone https://github.com/Tencent/cherry-markdown.git
# 在下载的过程中，你也可以查看一下，你刚刚无法解析的那个pkl大文件，是不是在这个项目中，(进入项目目录)使用如下指令：
cd cherry-markdown
git lfs track

git lfs pull

# 3. 如果不想重新下载整个项目，可以使用如下命令，单独下载需要使用lfs下载的大文件。
git lfs fetch
git lfs checkout
#（备选：git lfs pull），不建议
```
