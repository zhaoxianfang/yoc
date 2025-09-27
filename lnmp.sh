#!/bin/bash

# 企业级 PHP8.4 + MySQL8.4 + Nginx + Redis + Composer 自动化安装脚本
# 兼容原生Linux系统和国产/云厂商魔改系统

set -euo pipefail

# 脚本配置
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="${SCRIPT_DIR}/install.log"
COMMAND_FILE="${SCRIPT_DIR}/command.log" # 所有执行的命令日志
CONFIG_FILE="${SCRIPT_DIR}/install.conf"

# 初始化日志
> "$LOG_FILE"
> "$COMMAND_FILE"

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# 进度动画帧
SPINNER_FRAMES=("⠋" "⠙" "⠹" "⠸" "⠼" "⠴" "⠦" "⠧" "⠇" "⠏")

# 回滚操作栈
ROLLBACK_ACTIONS=()

# 改进的命令执行函数 - 同时记录到命令日志和详细日志
execute_command() {
    local command="$1"
    local description="${2:-执行命令}"

    # 记录到命令日志（简洁格式）
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $command" >> "$COMMAND_FILE"

    # 记录到详细日志（带描述）
    echo "=== $description ===" >> "$LOG_FILE"
    echo "命令: $command" >> "$LOG_FILE"
    echo "开始时间: $(date)" >> "$LOG_FILE"
    echo "----------------------------------------" >> "$LOG_FILE"

    # 执行命令并同时输出到日志文件
    if eval "$command" >> "$LOG_FILE" 2>&1; then
        echo "结束时间: $(date)" >> "$LOG_FILE"
        echo "状态: 成功" >> "$LOG_FILE"
        echo "----------------------------------------" >> "$LOG_FILE"
        return 0
    else
        local exit_code=$?
        echo "结束时间: $(date)" >> "$LOG_FILE"
        echo "状态: 失败 (退出码: $exit_code)" >> "$LOG_FILE"
        echo "----------------------------------------" >> "$LOG_FILE"
        return $exit_code
    fi
}

# 安全执行函数 - 替代危险的eval用法
safe_execute() {
    local command_array=("$@")

    # 记录命令
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ${command_array[*]}" >> "$COMMAND_FILE"
    echo "=== 执行命令: ${command_array[*]} ===" >> "$LOG_FILE"
    echo "开始时间: $(date)" >> "$LOG_FILE"

    # 直接执行命令数组，避免eval
    if "${command_array[@]}" >> "$LOG_FILE" 2>&1; then
        echo "结束时间: $(date)" >> "$LOG_FILE"
        echo "状态: 成功" >> "$LOG_FILE"
        echo "----------------------------------------" >> "$LOG_FILE"
        return 0
    else
        local exit_code=$?
        echo "结束时间: $(date)" >> "$LOG_FILE"
        echo "状态: 失败 (退出码: $exit_code)" >> "$LOG_FILE"
        echo "----------------------------------------" >> "$LOG_FILE"
        return $exit_code
    fi
}

# 输出函数
error() {
    echo -e "${RED}错误：$*${NC}" | tee -a "$LOG_FILE"
    echo "[错误] $*" >> "$COMMAND_FILE"
}
warn() {
    echo -e "${YELLOW}警告：$*${NC}" | tee -a "$LOG_FILE"
    echo "[警告] $*" >> "$COMMAND_FILE"
}
info() {
    echo -e "${BLUE}信息：$*${NC}" | tee -a "$LOG_FILE"
    echo "[信息] $*" >> "$COMMAND_FILE"
}
success() {
    echo -e "${GREEN}成功：$*${NC}" | tee -a "$LOG_FILE"
    echo "[成功] $*" >> "$COMMAND_FILE"
}
step() {
    echo -e "${PURPLE}步骤：$*${NC}" | tee -a "$LOG_FILE"
    echo "[步骤] $*" >> "$COMMAND_FILE"
}

# 安装配置
load_config() {
    step "加载安装配置..."

    # 默认配置
    PHP_VERSION="${PHP_VERSION:-8.4.12}"
    MYSQL_VERSION="${MYSQL_VERSION:-8.4.0}"
    NGINX_VERSION="${NGINX_VERSION:-1.28.0}"
    REDIS_VERSION="${REDIS_VERSION:-7.2.4}"
    REDIS_PHP_EXT_VERSION="${REDIS_PHP_EXT_VERSION:-6.1.0}"
    IMAGICK_PHP_EXT_VERSION="${IMAGICK_PHP_EXT_VERSION:-3.7.0}"
    SWOOLE_PHP_EXT_VERSION="${SWOOLE_PHP_EXT_VERSION:-6.0.2}"

    # 安装路径
    PHP_PREFIX="${PHP_PREFIX:-/usr/local/php}"
    MYSQL_PREFIX="${MYSQL_PREFIX:-/usr/local/mysql}"
    NGINX_PREFIX="${NGINX_PREFIX:-/usr/local/nginx}"
    REDIS_PREFIX="${REDIS_PREFIX:-/usr/local/redis}"

    # 用户配置
    WWW_USER="${WWW_USER:-www}"
    WWW_GROUP="${WWW_GROUP:-www}"
    WWW_PASSWORD="${WWW_PASSWORD:-$(openssl rand -base64 24)}"

    # MySQL配置
    MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-$(openssl rand -base64 24)}"
    MYSQL_REMOTE_ADMIN_USER="${MYSQL_REMOTE_ADMIN_USER:-remote_admin}"
    MYSQL_REMOTE_ADMIN_PASSWORD="${MYSQL_REMOTE_ADMIN_PASSWORD:-$(openssl rand -base64 24)}"

    # Redis配置
    REDIS_PASSWORD="${REDIS_PASSWORD:-$(openssl rand -base64 24)}"

    # 系统配置
    SWAP_SIZE="${SWAP_SIZE:-2G}"
    INSTALL_DIR="${INSTALL_DIR:-/usr/local/src}"

    # 如果存在配置文件，则加载
    if [[ -f "$CONFIG_FILE" ]]; then
        info "加载配置文件: $CONFIG_FILE"
        # 使用source但限制权限
        if [[ $(stat -c %a "$CONFIG_FILE") -le 644 ]] && [[ $(stat -c %U "$CONFIG_FILE") == "root" ]]; then
            source "$CONFIG_FILE"
            echo "配置文件加载完成" >> "$COMMAND_FILE"
        else
            error "配置文件权限不安全，跳过加载"
        fi
    else
        info "未找到配置文件，使用默认配置"
    fi
}

# 改进的spinner函数
spinner() {
    local pid=$1
    local message=$2
    local i=0

    # 显示初始状态
    printf "\r${SPINNER_FRAMES[i]} ${message}..."

    while kill -0 "$pid" 2>/dev/null; do
        i=$(( (i+1) % ${#SPINNER_FRAMES[@]} ))
        printf "\r${SPINNER_FRAMES[i]} ${message}..."
    done

    # 检查进程退出状态
    wait "$pid"
    local exit_code=$?

    if [ $exit_code -eq 0 ]; then
        printf "\r✓ ${message}完成！\n"
    else
        printf "\r✗ ${message}失败！\n"
        return $exit_code
    fi
}

# 安全的后台任务执行函数
run_background_task() {
    local command="$1"
    local message="$2"

    # 记录命令
    echo "[后台任务] $command" >> "$COMMAND_FILE"
    echo "=== 后台任务: $message ===" >> "$LOG_FILE"
    echo "命令: $command" >> "$LOG_FILE"

    # 执行后台任务
    eval "$command" &
    local pid=$!

    # 使用spinner显示进度
    spinner "$pid" "$message"
    return $?
}

# 错误处理函数
handle_error() {
    local exit_code=$?
    local line_no=$1
    local command=$2

    error "脚本执行失败！行号: $line_no, 命令: $command, 退出码: $exit_code"

    # 执行回滚操作
    rollback_changes

    exit $exit_code
}

# 信号处理函数
handle_signal() {
    error "脚本被中断！开始回滚..."
    rollback_changes
    exit 1
}

# 安全的回滚函数 - 不使用eval
rollback_changes() {
    info "开始执行回滚操作..."
    local rollback_success=true

    # 逆序执行回滚操作
    for ((i=${#ROLLBACK_ACTIONS[@]}-1; i>=0; i--)); do
        local action="${ROLLBACK_ACTIONS[i]}"
        info "执行回滚: $action"

        # 安全地执行回滚命令
        if execute_command "$action" "回滚操作"; then
            echo "回滚成功: $action" >> "$COMMAND_FILE"
        else
            warn "回滚操作执行失败: $action"
            rollback_success=false
        fi
    done

    if $rollback_success; then
        success "回滚操作完成"
    else
        warn "部分回滚操作失败，请检查日志"
    fi
}

# 添加回滚操作
add_rollback() {
    ROLLBACK_ACTIONS+=("$1")
    echo "添加回滚操作: $1" >> "$COMMAND_FILE"
}

# 显示系统信息
show_system_info() {
    step "检测系统信息..."

    echo "==========================================" | tee -a "$LOG_FILE"
    echo "          系统信息检测结果" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"
    echo "操作系统: $(cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d '\"')"
    echo "内核版本: $(uname -r)" | tee -a "$LOG_FILE"
    echo "系统架构: $(uname -m)" | tee -a "$LOG_FILE"
    echo "主机名称: $(hostname)" | tee -a "$LOG_FILE"
    echo "IP地址: $(hostname -I 2>/dev/null | awk '{print $1}' || ip addr show | grep inet | grep -v 127.0.0.1 | head -1 | awk '{print $2}')" | tee -a "$LOG_FILE"
    echo "内存大小: $(free -h | grep Mem | awk '{print $2}')" | tee -a "$LOG_FILE"
    echo "磁盘空间: $(df -h / | tail -1 | awk '{print $4}') 可用" | tee -a "$LOG_FILE"
    echo "当前用户: $(whoami)" | tee -a "$LOG_FILE"
    echo "安装目录: $INSTALL_DIR" | tee -a "$LOG_FILE"
    echo "日志文件: $LOG_FILE" | tee -a "$LOG_FILE"
    echo "命令日志: $COMMAND_FILE" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"

    # 显示脚本信息
    cat << "EOF" | tee -a "$LOG_FILE"
     __  __      ____    ____        ____    ____
    /\ \/\ \    / __ \  / ___\      / ___\  / __ \
    \ \ \_\ \  /\ \_\ \/\ \___  __ /\ \__/ /\ \/\ \
     \ \____ \ \ \____/\ \____\/\_\\ \____\\ \_\ \_\
      \/___/\ \ \/___/  \/____/\/_/ \/____/ \/_/\/_/
         /\___/
         \/__/

           企业级LNMP环境自动化安装脚本:yoc.cn
EOF
}

# 显示安装配置
show_install_config() {
    step "安装配置信息..."

    echo "==========================================" | tee -a "$LOG_FILE"
    echo "          软件安装配置" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"
    echo "PHP版本: $PHP_VERSION" | tee -a "$LOG_FILE"
    echo "PHP安装路径: $PHP_PREFIX" | tee -a "$LOG_FILE"
    echo "MySQL版本: $MYSQL_VERSION" | tee -a "$LOG_FILE"
    echo "MySQL安装路径: $MYSQL_PREFIX" | tee -a "$LOG_FILE"
    echo "Nginx版本: $NGINX_VERSION" | tee -a "$LOG_FILE"
    echo "Nginx安装路径: $NGINX_PREFIX" | tee -a "$LOG_FILE"
    echo "Redis版本: $REDIS_VERSION" | tee -a "$LOG_FILE"
    echo "Redis安装路径: $REDIS_PREFIX" | tee -a "$LOG_FILE"
    echo "PHP Redis扩展版本: $REDIS_PHP_EXT_VERSION" | tee -a "$LOG_FILE"
    echo "PHP Imagick扩展版本: $IMAGICK_PHP_EXT_VERSION" | tee -a "$LOG_FILE"
    echo "PHP Swoole扩展版本: $SWOOLE_PHP_EXT_VERSION" | tee -a "$LOG_FILE"
    echo "Web用户: $WWW_USER:$WWW_GROUP" | tee -a "$LOG_FILE"
    echo "MySQL Root密码: $MYSQL_ROOT_PASSWORD" | tee -a "$LOG_FILE"
    echo "MySQL 远程用户: $MYSQL_REMOTE_ADMIN_USER" | tee -a "$LOG_FILE"
    echo "MySQL 远程用户密码: $MYSQL_REMOTE_ADMIN_PASSWORD" | tee -a "$LOG_FILE"
    echo "Redis密码: $REDIS_PASSWORD" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"

    read -p "是否继续安装？(y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        info "安装已取消"
        exit 0
    fi
}

# 检查并配置SWAP
configure_swap() {
    step "检查SWAP空间..."

    local current_swap
    current_swap=$(free -g | grep Swap | awk '{print $2}')

    if [[ $current_swap -lt 2 ]]; then
        info "当前SWAP空间不足2GB，正在配置${SWAP_SIZE}的SWAP..."

        # 检查是否已存在swap文件
        if [[ -f /swapfile ]]; then
            warn "已存在swap文件，先删除旧文件"
            execute_command "swapoff /swapfile 2>/dev/null || true" "禁用旧SWAP"
            execute_command "rm -f /swapfile" "删除旧SWAP文件"
        fi

        # 计算SWAP大小
        local swap_size_bytes
        swap_size_bytes=$(echo "${SWAP_SIZE//[!0-9]/} * 1024 * 1024" | bc)

        # 创建swap文件
        run_background_task "dd if=/dev/zero of=/swapfile bs=1024 count=$swap_size_bytes" "创建SWAP文件"

        execute_command "chmod 600 /swapfile" "设置SWAP文件权限"
        run_background_task "mkswap /swapfile" "格式化SWAP文件"
        run_background_task "swapon /swapfile" "启用SWAP"

        # 添加到fstab
        execute_command "echo '/swapfile swap swap defaults 0 0' >> /etc/fstab" "配置SWAP开机启动"

        # 添加回滚操作
        add_rollback "swapoff /swapfile 2>/dev/null; rm -f /swapfile; sed -i '/\\/swapfile/d' /etc/fstab"

        success "SWAP配置完成"
    else
        info "SWAP空间充足(${current_swap}GB)，无需配置"
    fi
}

# 安装依赖包
install_dependencies() {
    step "安装系统依赖包..."

    # 扩展的依赖包列表
    local common_packages=(
        yum-utils gcc gcc-c++ autoconf libtool make wget curl cmake
        libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel
        openldap openldap-devel freetype freetype-devel libwebp libwebp-devel
        libxml2 libxml2-devel sqlite sqlite-devel zlib zlib-devel pcre pcre-devel
        gd gd-devel expat-devel libicu libicu-devel bzip2 bzip2-devel
        oniguruma oniguruma-devel zstd glibc-headers krb5-devel
        libzip libzip-devel libxslt libxslt-devel openssl openssl-devel
        libsodium libsodium-devel glib2-devel cairo cairo-devel gmp gmp-devel
        libevent libevent-devel readline readline-devel net-snmp net-snmp-devel
        aspell aspell-devel unixODBC unixODBC-devel libc-client-devel
        libXpm libXpm-devel enchant enchant-devel automake
        libtidy libtidy-devel ImageMagick ImageMagick-devel
        cpp binutils glibc glibc-kernheaders glibc-common glibc-devel
        ncurses-devel systemd-devel libffi libffi-devel python3-devel
        perl perl-devel tcl tcl-devel tk tk-devel
    )

    # 检测包管理器
    local pkg_manager
    if command -v yum &> /dev/null; then
        pkg_manager="yum"
    elif command -v dnf &> /dev/null; then
        pkg_manager="dnf"
    elif command -v apt &> /dev/null; then
        pkg_manager="apt"
        # 转换包名为apt格式
        common_packages=($(echo "${common_packages[@]}" | sed 's/-devel/-dev/g'))
    else
        error "不支持的包管理器"
        return 1
    fi

    # 更新包管理器
    if [[ $pkg_manager == "yum" || $pkg_manager == "dnf" ]]; then
        run_background_task "$pkg_manager update -y --allowerasing" "更新包管理器"
    elif [[ $pkg_manager == "apt" ]]; then
        execute_command "apt update -y" "更新包管理器"
    fi

    # 安装开发工具组
    if [[ $pkg_manager == "yum" || $pkg_manager == "dnf" ]]; then
        run_background_task "$pkg_manager groupinstall -y 'Development Tools'" "安装开发工具组"
    elif [[ $pkg_manager == "apt" ]]; then
        execute_command "apt install -y build-essential" "安装开发工具"
    fi

    # 安装依赖包
    for package in "${common_packages[@]}"; do
        if execute_command "$pkg_manager install -y --allowerasing $package" "安装$package"; then
            echo "成功安装: $package" >> "$COMMAND_FILE"
        else
            warn "安装$package失败，继续..."
            echo "安装失败: $package" >> "$COMMAND_FILE"
        fi
    done

    success "依赖包安装完成"
}

# 创建用户和组
create_www_user() {
    step "创建Web服务用户..."

    if ! id "$WWW_USER" &>/dev/null; then
        execute_command "groupadd $WWW_GROUP" "创建用户组$WWW_GROUP"
        execute_command "useradd -g $WWW_GROUP -s /sbin/nologin -M $WWW_USER" "创建用户$WWW_USER"
        execute_command "echo '$WWW_USER:$WWW_PASSWORD' | chpasswd" "设置用户密码"

        # 添加回滚操作
        add_rollback "userdel -r $WWW_USER 2>/dev/null; groupdel $WWW_GROUP 2>/dev/null"

        success "创建用户 $WWW_USER 完成"
    else
        info "用户 $WWW_USER 已存在"
    fi
}

# 安装PHP
install_php() {
    step "安装PHP $PHP_VERSION..."

    local php_dir="php-$PHP_VERSION"
    local php_url="https://www.php.net/distributions/php-$PHP_VERSION.tar.gz"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    # 下载PHP源码
    execute_command "wget -c $php_url -O php.tar.gz" "下载PHP源码"
    execute_command "tar -xzf php.tar.gz" "解压PHP源码"

    cd "$php_dir" || { error "无法进入PHP源码目录"; return 1; }

    # PHP编译配置
    local configure_opts=(
        "--prefix=$PHP_PREFIX"
        "--with-config-file-path=$PHP_PREFIX/etc"
        "--with-fpm-user=$WWW_USER"
        "--with-fpm-group=$WWW_GROUP"
        "--enable-fpm"
        "--with-libxml"
        "--with-openssl"
        "--with-system-ciphers"
        "--with-mysqli"
        "--with-mysql-sock"
        "--enable-pdo"
        "--with-pdo-mysql"
        "--with-pdo-sqlite"
        "--with-zlib"
        "--enable-bcmath"
        "--with-bz2"
        "--enable-calendar"
        "--with-curl"
        "--enable-exif"
        "--enable-fileinfo"
        "--enable-gd"
        "--with-freetype"
        "--with-jpeg"
        "--with-webp"
        "--with-gettext"
        "--with-iconv"
        "--enable-intl"
        "--with-ldap"
        "--with-ldap-sasl"
        "--enable-mbstring"
        "--enable-mbregex"
        "--enable-opcache"
        "--enable-opcache-jit"
        "--enable-pcntl"
        "--enable-session"
        "--enable-simplexml"
        "--enable-shmop"
        "--enable-soap"
        "--enable-sockets"
        "--with-sodium"
        "--enable-sysvmsg"
        "--enable-sysvsem"
        "--with-tidy"
        "--enable-tokenizer"
        "--enable-xml"
        "--with-xsl"
        "--with-zip"
        "--with-bz2"
        "--enable-mysqlnd"
        "--with-jpeg"
        "--with-libdir=lib64"
        "--enable-cli"
        "--enable-static"
        "--with-ffi"
    )

    # 配置PHP
    execute_command "./configure ${configure_opts[*]}" "配置PHP编译选项"

    # 编译安装
    run_background_task "make -j\$(nproc)" "编译PHP"
    execute_command "make install" "安装PHP"

    # 创建配置目录
    execute_command "mkdir -p $PHP_PREFIX/etc/php-fpm.d" "创建PHP配置目录"

    # 复制配置文件
    execute_command "cp php.ini-production $PHP_PREFIX/etc/php.ini" "复制PHP配置文件"
    execute_command "cp sapi/fpm/php-fpm.conf $PHP_PREFIX/etc/" "复制PHP-FPM配置"
    execute_command "cp sapi/fpm/www.conf $PHP_PREFIX/etc/php-fpm.d/" "复制PHP-FPM进程池配置"

    # 创建服务文件
    cat > /etc/systemd/system/php-fpm.service << EOF
[Unit]
Description=PHP FastCGI Process Manager
After=network.target

[Service]
Type=simple
PIDFile=$PHP_PREFIX/var/run/php-fpm.pid
ExecStart=$PHP_PREFIX/sbin/php-fpm --nodaemonize --fpm-config $PHP_PREFIX/etc/php-fpm.conf
ExecReload=/bin/kill -USR2 \$MAINPID
ExecStop=/bin/kill -SIGINT \$MAINPID

[Install]
WantedBy=multi-user.target
EOF

    execute_command "chmod 644 /etc/systemd/system/php-fpm.service" "设置服务文件权限"

    # 添加回滚操作
    add_rollback "rm -rf $PHP_PREFIX; rm -f /etc/systemd/system/php-fpm.service; systemctl daemon-reload"

    success "PHP安装完成"
}

# 安装MySQL
install_mysql() {
    step "安装MySQL $MYSQL_VERSION..."

    # 下载MySQL Yum源
    execute_command "wget -c https://dev.mysql.com/get/mysql84-community-release-el8-1.noarch.rpm -O mysql.rpm" "下载MySQL源"

    # 安装MySQL源
    execute_command "yum localinstall -y mysql.rpm" "安装MySQL源"

    # 安装MySQL服务器
    run_background_task "yum install -y mysql-community-server" "安装MySQL服务器"

    # 启动MySQL服务
    execute_command "systemctl start mysqld" "启动MySQL服务"
    execute_command "systemctl enable mysqld" "设置MySQL开机启动"

    # 获取临时root密码
    local temp_password
    temp_password=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $NF}')

    if [[ -z "$temp_password" ]]; then
        error "无法获取MySQL临时密码"
        return 1
    fi

    # 修改root密码
    execute_command "mysqladmin -u root -p$temp_password password $MYSQL_ROOT_PASSWORD" "修改MySQL root密码"

    # 创建远程管理用户
    execute_command "mysql -u root -p$MYSQL_ROOT_PASSWORD -e \"CREATE USER '$MYSQL_REMOTE_ADMIN_USER'@'%' IDENTIFIED BY '$MYSQL_REMOTE_ADMIN_PASSWORD'; GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_REMOTE_ADMIN_USER'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;\"" "创建MySQL远程用户"

    # 添加回滚操作
    add_rollback "systemctl stop mysqld; yum remove -y mysql-community-server; rm -f /etc/yum.repos.d/mysql-community*"

    success "MySQL安装完成"
}

# 安装Redis
install_redis() {
    step "安装Redis $REDIS_VERSION..."

    local redis_url="https://github.com/redis/redis/archive/$REDIS_VERSION.tar.gz"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    # 下载Redis源码
    execute_command "wget -c $redis_url -O redis.tar.gz" "下载Redis源码"
    execute_command "tar -xzf redis.tar.gz" "解压Redis源码"
    cd "redis-$REDIS_VERSION" || { error "无法进入Redis源码目录"; return 1; }

    # 编译安装
    run_background_task "make -j\$(nproc)" "编译Redis"
    execute_command "make PREFIX=$REDIS_PREFIX install" "安装Redis"

    # 创建配置目录
    execute_command "mkdir -p $REDIS_PREFIX/etc" "创建Redis配置目录"

    # 修改配置文件
    execute_command "cp redis.conf $REDIS_PREFIX/etc/" "复制Redis配置文件"
    execute_command "sed -i 's/^daemonize no/daemonize yes/' $REDIS_PREFIX/etc/redis.conf" "配置Redis守护进程"
    execute_command "sed -i 's/^# requirepass foobared/requirepass $REDIS_PASSWORD/' $REDIS_PREFIX/etc/redis.conf" "设置Redis密码"
    execute_command "sed -i 's/^bind 127.0.0.1/bind 0.0.0.0/' $REDIS_PREFIX/etc/redis.conf" "配置Redis监听地址"

    # 创建服务文件
    cat > /etc/systemd/system/redis.service << EOF
[Unit]
Description=Redis persistent key-value database
After=network.target

[Service]
ExecStart=$REDIS_PREFIX/bin/redis-server $REDIS_PREFIX/etc/redis.conf
ExecReload=/bin/kill -USR2 \$MAINPID
TimeoutStopSec=0
Restart=always
User=$WWW_USER
Group=$WWW_GROUP

[Install]
WantedBy=multi-user.target
EOF

    execute_command "chmod 644 /etc/systemd/system/redis.service" "设置服务文件权限"

    # 设置权限
    execute_command "chown -R $WWW_USER:$WWW_GROUP $REDIS_PREFIX" "设置Redis目录权限"

    # 启动服务
    execute_command "systemctl daemon-reload" "重载系统服务"
    execute_command "systemctl start redis" "启动Redis服务"
    execute_command "systemctl enable redis" "设置Redis开机启动"

    # 添加回滚操作
    add_rollback "systemctl stop redis; rm -f /etc/systemd/system/redis.service; rm -rf $REDIS_PREFIX"

    success "Redis安装完成"
}

# 安装Nginx
install_nginx() {
    step "安装Nginx $NGINX_VERSION..."

    local nginx_url="http://nginx.org/packages/centos/8/x86_64/RPMS/nginx-$NGINX_VERSION-1.el8.ngx.x86_64.rpm"

    # 下载Nginx RPM包
    execute_command "wget -c $nginx_url -O nginx.rpm" "下载Nginx安装包"

    # 安装Nginx
    execute_command "yum localinstall -y nginx.rpm" "安装Nginx"

    # 修改Nginx配置
    execute_command "sed -i 's/user  nginx/user $WWW_USER/' /etc/nginx/nginx.conf" "配置Nginx运行用户"

    # 启动服务
    execute_command "systemctl start nginx" "启动Nginx服务"
    execute_command "systemctl enable nginx" "设置Nginx开机启动"

    # 添加回滚操作
    add_rollback "systemctl stop nginx; yum remove -y nginx"

    success "Nginx安装完成"
}

# 安装PHP扩展
install_php_extensions() {
    step "安装PHP扩展..."

    # 安装Redis扩展
    install_redis_extension

    # 安装Imagick扩展
    install_imagick_extension

    # 安装Swoole扩展
    install_swoole_extension

    success "PHP扩展安装完成"
}

# 安装Redis扩展
install_redis_extension() {
    info "安装PHP Redis扩展..."

    local ext_url="https://github.com/phpredis/phpredis/archive/$REDIS_PHP_EXT_VERSION.tar.gz"
    local ext_dir="phpredis-$REDIS_PHP_EXT_VERSION"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    execute_command "wget -c $ext_url -O phpredis.tar.gz" "下载Redis扩展"
    execute_command "tar -xzf phpredis.tar.gz" "解压Redis扩展"
    cd "$ext_dir" || { error "无法进入Redis扩展目录"; return 1; }

    execute_command "$PHP_PREFIX/bin/phpize" "准备Redis扩展编译环境"
    execute_command "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置Redis扩展"

    run_background_task "make -j\$(nproc)" "编译Redis扩展"
    execute_command "make install" "安装Redis扩展"

    # 添加到php.ini
    execute_command "echo 'extension=redis.so' >> $PHP_PREFIX/etc/php.ini" "启用Redis扩展"

    success "Redis扩展安装完成"
}

# 安装Imagick扩展
install_imagick_extension() {
    info "安装PHP Imagick扩展..."

    local ext_url="https://github.com/Imagick/imagick/archive/refs/tags/$IMAGICK_PHP_EXT_VERSION.tar.gz"
    local ext_dir="imagick-$IMAGICK_PHP_EXT_VERSION"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    execute_command "wget -c $ext_url -O imagick.tar.gz" "下载Imagick扩展"
    execute_command "tar -xzf imagick.tar.gz" "解压Imagick扩展"
    cd "$ext_dir" || { error "无法进入Imagick扩展目录"; return 1; }

    execute_command "$PHP_PREFIX/bin/phpize" "准备Imagick扩展编译环境"
    execute_command "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置Imagick扩展"

    run_background_task "make -j\$(nproc)" "编译Imagick扩展"
    execute_command "make install" "安装Imagick扩展"

    # 添加到php.ini
    execute_command "echo 'extension=imagick.so' >> $PHP_PREFIX/etc/php.ini" "启用Imagick扩展"

    success "Imagick扩展安装完成"
}

# 安装Swoole扩展
install_swoole_extension() {
    info "安装PHP Swoole扩展..."

    local ext_url="https://github.com/swoole/swoole-src/archive/refs/tags/v$SWOOLE_PHP_EXT_VERSION.tar.gz"
    local ext_dir="swoole-$SWOOLE_PHP_EXT_VERSION"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    execute_command "wget -c $ext_url -O swoole.tar.gz" "下载Swoole扩展"
    execute_command "tar -xzf swoole.tar.gz" "解压Swoole扩展"
    cd "$ext_dir" || { error "无法进入Swoole扩展目录"; return 1; }

    execute_command "$PHP_PREFIX/bin/phpize" "准备Swoole扩展编译环境"

    local configure_opts=(
        "--enable-openssl"
        "--enable-sockets"
        "--enable-mysqlnd"
        "--enable-swoole-curl"
        "--enable-cares"
        "--enable-swoole-pgsql"
    )
    execute_command "./configure ${configure_opts[*]}" "配置Swoole扩展"

    run_background_task "make -j\$(nproc)" "编译Swoole扩展"
    execute_command "make install" "安装Swoole扩展"

    # 添加到php.ini
    execute_command "echo 'extension=swoole.so' >> $PHP_PREFIX/etc/php.ini" "启用Swoole扩展"

    success "Swoole扩展安装完成"
}

# 安装Composer
install_composer() {
    step "安装Composer..."

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    # 下载Composer
    execute_command "php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\"" "下载Composer安装器"

    execute_command "$PHP_PREFIX/bin/php composer-setup.php --install-dir=/usr/local/bin --filename=composer" "安装Composer"
    execute_command "php -r \"unlink('composer-setup.php');\"" "清理Composer安装文件"

    success "Composer安装完成"
}

# 配置服务
configure_services() {
    step "配置系统服务..."

    # 启动PHP-FPM
    execute_command "systemctl daemon-reload" "重载系统服务"
    execute_command "systemctl start php-fpm" "启动PHP-FPM服务"
    execute_command "systemctl enable php-fpm" "设置PHP-FPM开机启动"

    # 重启Nginx
    execute_command "systemctl restart nginx" "重启Nginx服务"

    success "服务配置完成"
}

# 显示安装结果
show_installation_result() {
    step "安装完成！"

    echo "==========================================" | tee -a "$LOG_FILE"
    echo "          安装结果汇总" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"
    echo "PHP版本: $PHP_VERSION" | tee -a "$LOG_FILE"
    echo "PHP安装路径: $PHP_PREFIX" | tee -a "$LOG_FILE"
    echo "MySQL版本: $MYSQL_VERSION" | tee -a "$LOG_FILE"
    echo "MySQL Root密码: $MYSQL_ROOT_PASSWORD" | tee -a "$LOG_FILE"
    echo "MySQL远程用户: $MYSQL_REMOTE_ADMIN_USER" | tee -a "$LOG_FILE"
    echo "MySQL远程密码: $MYSQL_REMOTE_ADMIN_PASSWORD" | tee -a "$LOG_FILE"
    echo "Redis版本: $REDIS_VERSION" | tee -a "$LOG_FILE"
    echo "Redis密码: $REDIS_PASSWORD" | tee -a "$LOG_FILE"
    echo "Nginx版本: $NGINX_VERSION" | tee -a "$LOG_FILE"
    echo "Web用户: $WWW_USER" | tee -a "$LOG_FILE"
    echo "Web用户密码: $WWW_PASSWORD" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"

    # 显示服务状态
    echo "服务状态:" | tee -a "$LOG_FILE"
    execute_command "systemctl status php-fpm --no-pager" "PHP-FPM状态"
    execute_command "systemctl status nginx --no-pager" "Nginx状态"
    execute_command "systemctl status mysqld --no-pager" "MySQL状态"
    execute_command "systemctl status redis --no-pager" "Redis状态"

    # 显示关键文件位置
    echo "关键文件位置:" | tee -a "$LOG_FILE"
    echo "PHP配置文件: $PHP_PREFIX/etc/php.ini" | tee -a "$LOG_FILE"
    echo "PHP-FPM配置: $PHP_PREFIX/etc/php-fpm.conf" | tee -a "$LOG_FILE"
    echo "Nginx配置: /etc/nginx/nginx.conf" | tee -a "$LOG_FILE"
    echo "MySQL配置: /etc/my.cnf" | tee -a "$LOG_FILE"
    echo "Redis配置: $REDIS_PREFIX/etc/redis.conf" | tee -a "$LOG_FILE"
    echo "安装日志: $LOG_FILE" | tee -a "$LOG_FILE"
    echo "命令日志: $COMMAND_FILE" | tee -a "$LOG_FILE"

    success "LNMP环境安装完成！"
}

# 主安装函数
main() {
    # 设置错误处理
    trap 'handle_error $LINENO "$BASH_COMMAND"' ERR
    trap 'handle_signal' INT TERM

    # 显示欢迎信息
    echo "开始执行LNMP环境安装脚本..." | tee -a "$LOG_FILE"
    echo "开始时间: $(date)" | tee -a "$LOG_FILE"
    echo "命令日志文件: $COMMAND_FILE" | tee -a "$LOG_FILE"
    echo "详细日志文件: $LOG_FILE" | tee -a "$LOG_FILE"

    # 检查root权限
    if [[ $EUID -ne 0 ]]; then
        error "请使用root权限运行此脚本"
        exit 1
    fi
    # 加载配置
    load_config

    # 创建安装目录
    execute_command "mkdir -p $INSTALL_DIR" "创建安装目录"

    # 执行安装步骤
    show_system_info
    show_install_config
    configure_swap
    install_dependencies
    create_www_user
    install_php
    install_mysql
    install_redis
    install_nginx
    install_php_extensions
    install_composer
    configure_services
    show_installation_result

    echo "安装完成时间: $(date)" | tee -a "$LOG_FILE"
    success "所有组件安装完成！"
}

# 执行主函数
main "$@"
