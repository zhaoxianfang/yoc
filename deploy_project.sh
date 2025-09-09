#!/bin/sh

# 设置脚本可执行 chmod +x deploy_project.sh

# 定义参数
DEPLOY_BRANCH=${DEPLOY_BRANCH:-"master"}  # 如果未设置则默认为 "master"
PROJECT_PATH="/www/yoc_cn"

# 临时参数
COMPOSER_UPDATE=0   # 是否有依赖更新
MIGRATIONS_UPDATE=0 # 是否有数据迁移更新
TEST_UPDATE=0       # 是否有单元测试文件更新
STEP='null'         # 运行的步骤

PHP_BIN='/usr/local/php8/bin/php'         # php路径
COMPOSER_BIN='/usr/local/bin/composer'    # composer路径

# 定义一个函数来处理异常
error_handler() {
    local exit_code=$?
    local error_message

    # 捕获最后一条命令的错误输出
    error_message=$( { $BASH_COMMAND; } 2>&1 )

    # 判断错误信息是否包含 "not found"
    if [[ "$error_message" == *"_build' not found"* ]]; then
        echo "错误包含 'not found'，继续执行..."
    else
        echo "发生错误: $exit_code"
        echo "发生错误: $error_message" >&2

        if [[ "$STEP" == "migrate_testing" && "$error_message" == *" DONE"* ]]; then
            # 当有新的迁移操作时执行的操作
            echo "测试迁移回滚..."
            $PHP_BIN artisan migrate:rollback --database=sqlite --force
        fi

        if [[ "$STEP" == "migrate_deploy" && "$error_message" == *" DONE"* ]]; then
            # 当有新的迁移操作时执行的操作
            echo "部署迁移回滚..."
            $PHP_BIN artisan migrate:rollback --force
        fi

        # 输出错误信息并退出
        echo "❌ 脚本运行出错，退出状态: $?" >&2

        delete_testing_branch
        exit 1  # 使用非零状态码表示异常退出
    fi
}

delete_testing_branch() {
  echo "\n ➤➤➤ 强制删除测试分支: test_${DEPLOY_BRANCH}_build "
  git checkout ${DEPLOY_BRANCH}
  git branch -D test_${DEPLOY_BRANCH}_build
}

trap 'error_handler' ERR

# 执行的命令
cd /www/yoc_cn

$PHP_BIN -v
echo "➤➤➤➤➤➤➤➤➤➤➤➤"


echo "\n\n\n 【checkout 检查】"
echo "\n ➤➤➤ 拉取代码分支: ${DEPLOY_BRANCH} "

if [ -d .git ]; then
    git checkout ${DEPLOY_BRANCH}
else
    echo "❌ 发生错误[.git 文件夹不存在]，终止任务！" >&2
    exit 1
fi

# git rev-parse --abbrev-ref HEAD # 查看当前分支名称

echo "\n ➤➤➤ 拉取最新代码..."
git fetch origin ${DEPLOY_BRANCH}


echo "\n\n\n [测试]创建环境"

echo "\n ➤➤➤ 切换分支: ${DEPLOY_BRANCH} "
git checkout ${DEPLOY_BRANCH}

delete_testing_branch

echo "\n ➤➤➤ 创建并切换到临时测试分支：test_${DEPLOY_BRANCH}_build "

git checkout -b test_${DEPLOY_BRANCH}_build origin/${DEPLOY_BRANCH}


echo "\n\n\n [测试]安装依赖"


# 执行 git diff --quiet 检查 'composer.lock' 文件是否有变化
if git diff --quiet ${DEPLOY_BRANCH} origin/${DEPLOY_BRANCH} -- composer.lock; then
    echo "\n ➤➤➤ 'composer.lock' 无更新"
    COMPOSER_UPDATE=0
else
    echo "\n ➤➤➤ 检测到 'composer.lock' 文件有变化，但此时不处理更新依赖"
    COMPOSER_UPDATE=1
fi


echo "\n\n\n [测试]数据迁移"
# 获取差异文件列表 git diff --name-only 并捕获输出
diffOutput=$(git diff --name-only ${DEPLOY_BRANCH} origin/${DEPLOY_BRANCH})

if [ -z "$diffOutput" ]; then
    echo "✅ 没有文件变更，跳过测试检查。"

    delete_testing_branch
    exit 0
fi

# 检查是否有匹配的路径
if echo "$diffOutput" | grep -q "database/migrations/" || \
   echo "$diffOutput" | grep -E "Modules/[^/]+/Database/Migrations/" || \
   echo "$diffOutput" | grep -E "Modules/[^/]+/database/migrations/"
then
    echo "➤➤➤ 检测到数据迁移有变化："
    MIGRATIONS_UPDATE=1

    echo "\n\n 📊 执行测试数据迁移: "
    STEP='migrate_testing'

    # 执行 php artisan migrate --force 并捕获输出
    testingMigrateOutput=$($PHP_BIN artisan migrate --database=sqlite --force)

    # 检查输出是否为空或包含 "Nothing to migrate"
    if [ -z "$testingMigrateOutput" ] || echo "$testingMigrateOutput" | grep -qi "Nothing to migrate"; then
        echo "没有需要迁移的内容或输出为空。"
        MIGRATIONS_UPDATE=0
    fi

    STEP='null'
else
    echo "\n ➤➤➤ 无数据迁移"
fi


echo "\n\n\n [测试]单元测试"

# 过滤出测试相关文件
diff_test_output=$(echo "$diffOutput" | grep -E '^tests/|^Modules/[^/]+/Tests/|^Modules/[^/]+/tests/' || true)

# 检查输出是否为空
if [ -z "$diff_test_output" ]; then
    echo "\n ➤➤➤ 无单元测试"
    TEST_UPDATE=0
else
    echo "➤➤➤ 🧪 进行单元测试 "
    TEST_UPDATE=1

    $PHP_BIN artisan test
fi


echo "\n\n\n [部署]初始化"

echo "\n\n 🚀 开始运行部署 "
git checkout ${DEPLOY_BRANCH}
echo "📥 pull 更新代码 "

# 执行 git pull 并捕获输出
git_pull_output=$(git pull)
if [ -z "$git_pull_output" ] || echo "$git_pull_output" | grep -qi "Already up to date"; then
    echo "没有更新文件"

    delete_testing_branch
    exit 0 # 正常退出
fi


# 部署依赖
if [ "$COMPOSER_UPDATE" -eq 1 ]; then
     echo "\n\n 📦 安装环境依赖 "
     $PHP_BIN $COMPOSER_BIN install --no-dev --optimize-autoloader
fi


# 部署数据库迁移
if [ "$MIGRATIONS_UPDATE" -eq 1 ]; then
     echo "\n\n 📊 执行数据库迁移 "
     STEP='migrate_deploy'
     $PHP_BIN artisan migrate --force
fi


echo "\n\n\n [部署]配置优化"
echo "\n\n ⚡️Laravel 配置优化... "

$PHP_BIN artisan optimize:clear
$PHP_BIN artisan optimize
sudo chmod -R 755 ${PROJECT_PATH}
sudo chmod -R 777 ${PROJECT_PATH}/storage/
sudo chmod -R 777 ${PROJECT_PATH}/bootstrap/cache/
# 重启任务队列
$PHP_BIN artisan queue:restart


delete_testing_branch

