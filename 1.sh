#!/bin/bash

# 企业级 PHP8.4 + MySQL8.4 + Nginx + Redis + Composer 自动化安装脚本
# 兼容原生Linux系统和国产/云厂商魔改系统

set -euo pipefail

# 脚本配置
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="${SCRIPT_DIR}/install.log"
CONFIG_FILE="${SCRIPT_DIR}/install.conf"

# 初始化日志
> "$LOG_FILE"

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

# 输出函数
error() { echo -e "${RED}错误：$*${NC}" | tee -a "$LOG_FILE"; }
warn() { echo -e "${YELLOW}警告：$*${NC}" | tee -a "$LOG_FILE"; }
info() { echo -e "${BLUE}信息：$*${NC}" | tee -a "$LOG_FILE"; }
success() { echo -e "${GREEN}成功：$*${NC}" | tee -a "$LOG_FILE"; }
step() { echo -e "${PURPLE}步骤：$*${NC}" | tee -a "$LOG_FILE"; }


# 安装配置
load_config() {
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
        source "$CONFIG_FILE"
        info "加载配置文件: $CONFIG_FILE"
    fi
}

# 改进的spinner函数
spinner() {
    local pid=$1
    local message=$2
    # local delay=0.1
    local i=0

    # 显示初始状态
    printf "\r${SPINNER_FRAMES[i]} ${message}..."

    while kill -0 $pid 2>/dev/null; do
        i=$(( (i+1) % ${#SPINNER_FRAMES[@]} ))
        printf "\r${SPINNER_FRAMES[i]} ${message}..."
        # sleep $delay
    done

    # 检查进程退出状态
    wait $pid
    local exit_code=$?

    if [ $exit_code -eq 0 ]; then
        printf "\r✓ ${message}完成！\n"
    else
        printf "\r✗ ${message}失败！\n"
        return $exit_code
    fi
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

# 回滚函数
rollback_changes() {
    info "开始执行回滚操作..."

    # 逆序执行回滚操作
    for ((i=${#ROLLBACK_ACTIONS[@]}-1; i>=0; i--)); do
        local action="${ROLLBACK_ACTIONS[i]}"
        info "执行回滚: $action"
        eval "$action" >> "$LOG_FILE" 2>&1 || warn "回滚操作执行失败: $action"
    done

    success "回滚操作完成"
}

# 添加回滚操作
add_rollback() {
    ROLLBACK_ACTIONS+=("$1")
}

# 显示系统信息
show_system_info() {
    step "检测系统信息..."

    echo "=========================================="
    echo "          系统信息检测结果"
    echo "=========================================="
    echo "操作系统: $(cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d '\"')"
    echo "内核版本: $(uname -r)"
    echo "系统架构: $(uname -m)"
    echo "主机名称: $(hostname)"
    echo "IP地址: $(hostname -I 2>/dev/null | awk '{print $1}' || ip addr show | grep inet | grep -v 127.0.0.1 | head -1 | awk '{print $2}')"
    echo "内存大小: $(free -h | grep Mem | awk '{print $2}')"
    echo "磁盘空间: $(df -h / | tail -1 | awk '{print $4}') 可用"
    echo "当前用户: $(whoami)"
    echo "安装目录: $INSTALL_DIR"
    echo "日志文件: $LOG_FILE"
    echo "=========================================="
    echo
}

# 显示安装配置
show_install_config() {
    step "安装配置信息..."

    echo "=========================================="
    echo "          软件安装配置"
    echo "=========================================="
    echo "PHP版本: $PHP_VERSION"
    echo "PHP安装路径: $PHP_PREFIX"
    echo "MySQL版本: $MYSQL_VERSION"
    echo "MySQL安装路径: $MYSQL_PREFIX"
    echo "Nginx版本: $NGINX_VERSION"
    echo "Nginx安装路径: $NGINX_PREFIX"
    echo "Redis版本: $REDIS_VERSION"
    echo "Redis安装路径: $REDIS_PREFIX"
    echo "PHP Redis扩展版本: $REDIS_PHP_EXT_VERSION"
    echo "PHP Imagick扩展版本: $IMAGICK_PHP_EXT_VERSION"
    echo "PHP Swoole扩展版本: $SWOOLE_PHP_EXT_VERSION"
    echo "Web用户: $WWW_USER:$WWW_GROUP"
    echo "MySQL Root密码: ******"
    echo "Redis密码: ******"
    echo "=========================================="
    echo

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

    local current_swap=$(free -g | grep Swap | awk '{print $2}')

    if [[ $current_swap -lt 2 ]]; then
        info "当前SWAP空间不足2GB，正在配置${SWAP_SIZE}的SWAP..."

        # 检查是否已存在swap文件
        if [[ -f /swapfile ]]; then
            warn "已存在swap文件，先删除旧文件"
            swapoff /swapfile 2>/dev/null || true
            rm -f /swapfile
        fi

        # 创建swap文件
        dd if=/dev/zero of=/swapfile bs=1024 count=$(echo "${SWAP_SIZE//[!0-9]/} * 1024 * 1024" | bc) >> "$LOG_FILE" 2>&1 &
        spinner $! "创建SWAP文件"

        chmod 600 /swapfile >> "$LOG_FILE" 2>&1
        mkswap /swapfile >> "$LOG_FILE" 2>&1 &
        spinner $! "格式化SWAP文件"

        swapon /swapfile >> "$LOG_FILE" 2>&1 &
        spinner $! "启用SWAP"

        # 添加到fstab
        echo '/swapfile swap swap defaults 0 0' >> /etc/fstab

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
    if command -v yum &> /dev/null; then
        local pkg_manager="yum"
    elif command -v dnf &> /dev/null; then
        local pkg_manager="dnf"
    elif command -v apt &> /dev/null; then
        local pkg_manager="apt"
        # 转换包名为apt格式
        common_packages=($(echo "${common_packages[@]}" | sed 's/-devel/-dev/g'))
    else
        error "不支持的包管理器"
        return 1
    fi

    # 更新包管理器
    if [[ $pkg_manager == "yum" || $pkg_manager == "dnf" ]]; then
        $pkg_manager update -y --allowerasing >> "$LOG_FILE" 2>&1 &
        spinner $! "更新包管理器"
    fi

    # 安装依赖包
    for package in "${common_packages[@]}"; do
        $pkg_manager install -y --allowerasing "$package" >> "$LOG_FILE" 2>&1 &
        spinner $! "安装$package" || warn "安装$package失败，继续..."
    done

    $pkg_manager groupinstall -y "Development Tools" >> "$LOG_FILE" 2>&1 &
        spinner $! "安装开发包"

    success "依赖包安装完成"
}

# 创建用户和组
create_www_user() {
    step "创建Web服务用户..."

    if ! id "$WWW_USER" &>/dev/null; then
        groupadd "$WWW_GROUP" >> "$LOG_FILE" 2>&1
        useradd -g "$WWW_GROUP" -s /sbin/nologin -M "$WWW_USER" >> "$LOG_FILE" 2>&1
        echo "$WWW_USER:$WWW_PASSWORD" | chpasswd >> "$LOG_FILE" 2>&1

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

    cd "$INSTALL_DIR"

    # 下载PHP源码
    wget -c "$php_url" -O php.tar.gz >> "$LOG_FILE" 2>&1 &
    spinner $! "下载PHP源码"

    tar -xzf php.tar.gz >> "$LOG_FILE" 2>&1
    cd "$php_dir"

    # PHP编译配置
    PHP_CONFIGURE_OPTS=(
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
    ./configure "${PHP_CONFIGURE_OPTS[@]}" >> "$LOG_FILE" 2>&1 &
    spinner $! "配置PHP编译选项"

    # 编译安装
    make -j$(nproc) >> "$LOG_FILE" 2>&1 &
    spinner $! "编译PHP"

    make install >> "$LOG_FILE" 2>&1 &
    spinner $! "安装PHP"

    # 复制配置文件
    cp php.ini-production $PHP_PREFIX/etc/php.ini >> "$LOG_FILE" 2>&1
    cp sapi/fpm/php-fpm.conf $PHP_PREFIX/etc/ >> "$LOG_FILE" 2>&1
    cp sapi/fpm/www.conf $PHP_PREFIX/etc/php-fpm.d/ >> "$LOG_FILE" 2>&1

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

    # 添加回滚操作
    add_rollback "rm -rf $PHP_PREFIX; rm -f /etc/systemd/system/php-fpm.service; systemctl daemon-reload"

    success "PHP安装完成"
}

# 安装MySQL
install_mysql() {
    step "安装MySQL $MYSQL_VERSION..."

    # 下载MySQL Yum源
    wget -c "https://dev.mysql.com/get/mysql84-community-release-el8-1.noarch.rpm" -O mysql.rpm >> "$LOG_FILE" 2>&1 &
    spinner $! "下载MySQL源"

    # 安装MySQL源
    yum localinstall -y mysql.rpm >> "$LOG_FILE" 2>&1 &
    spinner $! "安装MySQL源"

    # 安装MySQL服务器
    yum install -y mysql-community-server >> "$LOG_FILE" 2>&1 &
    spinner $! "安装MySQL服务器"

    # 启动MySQL服务
    systemctl start mysqld >> "$LOG_FILE" 2>&1 &
    spinner $! "启动MySQL服务"

    systemctl enable mysqld >> "$LOG_FILE" 2>&1

    # 获取临时root密码
    local temp_password=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $NF}')

    # 修改root密码
    mysqladmin -u root -p"$temp_password" password "$MYSQL_ROOT_PASSWORD" >> "$LOG_FILE" 2>&1 &
    spinner $! "修改MySQL root密码"

    # 创建远程管理用户
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e \
        "CREATE USER '$MYSQL_REMOTE_ADMIN_USER'@'%' IDENTIFIED BY '$MYSQL_REMOTE_ADMIN_PASSWORD'; \
         GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_REMOTE_ADMIN_USER'@'%' WITH GRANT OPTION; \
         FLUSH PRIVILEGES;" >> "$LOG_FILE" 2>&1 &
    spinner $! "创建MySQL远程用户"

    # 添加回滚操作
    add_rollback "systemctl stop mysqld; yum remove -y mysql-community-server; rm -f /etc/yum.repos.d/mysql-community*"

    success "MySQL安装完成"
}

# 安装Redis
install_redis() {
    step "安装Redis $REDIS_VERSION..."

    local redis_url="https://github.com/redis/redis/archive/$REDIS_VERSION.tar.gz"

    cd "$INSTALL_DIR"

    # 下载Redis源码
    wget -c "$redis_url" -O redis.tar.gz >> "$LOG_FILE" 2>&1 &
    spinner $! "下载Redis源码"

    tar -xzf redis.tar.gz >> "$LOG_FILE" 2>&1
    cd "redis-$REDIS_VERSION"

    # 编译安装
    make -j$(nproc) >> "$LOG_FILE" 2>&1 &
    spinner $! "编译Redis"

    make PREFIX="$REDIS_PREFIX" install >> "$LOG_FILE" 2>&1 &
    spinner $! "安装Redis"

    # 创建配置目录
    mkdir -p "$REDIS_PREFIX/etc"

    # 修改配置文件
    cp redis.conf "$REDIS_PREFIX/etc/"
    sed -i "s/^daemonize no/daemonize yes/" "$REDIS_PREFIX/etc/redis.conf"
    sed -i "s/^# requirepass foobared/requirepass $REDIS_PASSWORD/" "$REDIS_PREFIX/etc/redis.conf"
    sed -i "s/^bind 127.0.0.1/bind 0.0.0.0/" "$REDIS_PREFIX/etc/redis.conf"

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

    # 设置权限
    chown -R $WWW_USER:$WWW_GROUP "$REDIS_PREFIX"

    # 启动服务
    systemctl daemon-reload
    systemctl start redis >> "$LOG_FILE" 2>&1 &
    spinner $! "启动Redis服务"

    systemctl enable redis >> "$LOG_FILE" 2>&1

    # 添加回滚操作
    add_rollback "systemctl stop redis; rm -f /etc/systemd/system/redis.service; rm -rf $REDIS_PREFIX"

    success "Redis安装完成"
}

# 安装Nginx
install_nginx() {
    step "安装Nginx $NGINX_VERSION..."

    local nginx_url="http://nginx.org/packages/centos/8/x86_64/RPMS/nginx-$NGINX_VERSION-1.el8.ngx.x86_64.rpm"

    # 下载Nginx RPM包
    wget -c "$nginx_url" -O nginx.rpm >> "$LOG_FILE" 2>&1 &
    spinner $! "下载Nginx安装包"

    # 安装Nginx
    yum localinstall -y nginx.rpm >> "$LOG_FILE" 2>&1 &
    spinner $! "安装Nginx"

    # 修改Nginx配置
    sed -i "s/user  nginx/user $WWW_USER/" /etc/nginx/nginx.conf

    # 启动服务
    systemctl start nginx >> "$LOG_FILE" 2>&1 &
    spinner $! "启动Nginx服务"

    systemctl enable nginx >> "$LOG_FILE" 2>&1

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

    cd "$INSTALL_DIR"

    wget -c "$ext_url" -O phpredis.tar.gz >> "$LOG_FILE" 2>&1 &
    spinner $! "下载Redis扩展"

    tar -xzf phpredis.tar.gz >> "$LOG_FILE" 2>&1
    cd "$ext_dir"

    $PHP_PREFIX/bin/phpize >> "$LOG_FILE" 2>&1
    ./configure --with-php-config=$PHP_PREFIX/bin/php-config >> "$LOG_FILE" 2>&1 &
    spinner $! "配置Redis扩展"

    make -j$(nproc) >> "$LOG_FILE" 2>&1 &
    spinner $! "编译Redis扩展"

    make install >> "$LOG_FILE" 2>&1 &
    spinner $! "安装Redis扩展"

    # 添加到php.ini
    echo "extension=redis.so" >> $PHP_PREFIX/etc/php.ini
}

# 安装Imagick扩展
install_imagick_extension() {
    info "安装PHP Imagick扩展..."

    local ext_url="https://github.com/Imagick/imagick/archive/refs/tags/$IMAGICK_PHP_EXT_VERSION.tar.gz"
    local ext_dir="imagick-$IMAGICK_PHP_EXT_VERSION"

    cd "$INSTALL_DIR"

    wget -c "$ext_url" -O imagick.tar.gz >> "$LOG_FILE" 2>&1 &
    spinner $! "下载Imagick扩展"

    tar -xzf imagick.tar.gz >> "$LOG_FILE" 2>&1
    cd "$ext_dir"

    $PHP_PREFIX/bin/phpize >> "$LOG_FILE" 2>&1
    ./configure --with-php-config=$PHP_PREFIX/bin/php-config >> "$LOG_FILE" 2>&1 &
    spinner $! "配置Imagick扩展"

    make -j$(nproc) >> "$LOG_FILE" 2>&1 &
    spinner $! "编译Imagick扩展"

    make install >> "$LOG_FILE" 2>&1 &
    spinner $! "安装Imagick扩展"

    # 添加到php.ini
    echo "extension=imagick.so" >> $PHP_PREFIX/etc/php.ini
}

# 安装Swoole扩展
install_swoole_extension() {
    info "安装PHP Swoole扩展..."

    local ext_url="https://github.com/swoole/swoole-src/archive/refs/tags/$SWOOLE_PHP_EXT_VERSION.tar.gz"
    local ext_dir="swoole-$SWOOLE_PHP_EXT_VERSION"

    cd "$INSTALL_DIR"

    wget -c "$ext_url" -O swoole.tar.gz >> "$LOG_FILE" 2>&1 &
    spinner $! "下载Swoole扩展"

    tar -xzf swoole.tar.gz >> "$LOG_FILE" 2>&1
    cd "$ext_dir"

    $PHP_PREFIX/bin/phpize >> "$LOG_FILE" 2>&1
    ./configure --enable-openssl --enable-sockets --enable-mysqlnd --enable-swoole-curl --enable-cares --enable-swoole-pgsql >> "$LOG_FILE" 2>&1 &
    spinner $! "配置Swoole扩展"

    make -j$(nproc) >> "$LOG_FILE" 2>&1 &
    spinner $! "编译Swoole扩展"

    make install >> "$LOG_FILE" 2>&1 &
    spinner $! "安装Swoole扩展"

    # 添加到php.ini
    echo "extension=swoole.so" >> $PHP_PREFIX/etc/php.ini
}

# 安装Composer
install_composer() {
    step "安装Composer..."

    cd "$INSTALL_DIR"

    # 下载Composer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" >> "$LOG_FILE" 2>&1 &
    spinner $! "下载Composer安装器"

    $PHP_PREFIX/bin/php composer-setup.php --install-dir=/usr/local/bin --filename=composer >> "$LOG_FILE" 2>&1 &
    spinner $! "安装Composer"

    php -r "unlink('composer-setup.php');" >> "$LOG_FILE" 2>&1

    success "Composer安装完成"
}

# 配置服务
configure_services() {
    step "配置系统服务..."

    # 启动PHP-FPM
    systemctl daemon-reload
    systemctl start php-fpm >> "$LOG_FILE" 2>&1 &
    spinner $! "启动PHP-FPM服务"

    systemctl enable php-fpm >> "$LOG_FILE" 2>&1

    # 重启相关服务
    systemctl restart nginx >> "$LOG_FILE" 2>&1 &
    spinner $! "重启Nginx服务"

    systemctl restart redis >> "$LOG_FILE" 2>&1 &
    spinner $! "重启Redis服务"

    systemctl restart mysqld >> "$LOG_FILE" 2>&1 &
    spinner $! "重启MySQL服务"

    success "服务配置完成"
}

# 显示安装摘要
show_installation_summary() {
    step "安装完成！"

    echo "=========================================="
    echo "          安装摘要"
    echo "=========================================="
    echo "✓ PHP $PHP_VERSION"
    echo "  安装路径: $PHP_PREFIX"
    echo "  配置文件: $PHP_PREFIX/etc/php.ini"
    echo "  服务状态: $(systemctl is-active php-fpm)"

    echo "✓ MySQL $MYSQL_VERSION"
    echo "  安装路径: /usr/bin/mysql"
    echo "  数据目录: /var/lib/mysql"
    echo "  Root密码: $MYSQL_ROOT_PASSWORD"
    echo "  远程用户: $MYSQL_REMOTE_ADMIN_USER"
    echo "  服务状态: $(systemctl is-active mysqld)"

    echo "✓ Nginx $NGINX_VERSION"
    echo "  安装路径: /usr/sbin/nginx"
    echo "  配置目录: /etc/nginx"
    echo "  服务状态: $(systemctl is-active nginx)"

    echo "✓ Redis $REDIS_VERSION"
    echo "  安装路径: $REDIS_PREFIX"
    echo "  配置文件: $REDIS_PREFIX/etc/redis.conf"
    echo "  密码: $REDIS_PASSWORD"
    echo "  服务状态: $(systemctl is-active redis)"

    echo "✓ PHP扩展"
    echo "  Redis: $REDIS_PHP_EXT_VERSION"
    echo "  Imagick: $IMAGICK_PHP_EXT_VERSION"
    echo "  Swoole: $SWOOLE_PHP_EXT_VERSION"

    echo "✓ Composer: $(composer --version 2>/dev/null | head -1 || echo '已安装')"

    echo "✓ Web用户: $WWW_USER:$WWW_GROUP"

    echo "=========================================="
    echo "重要信息:"
    echo "• MySQL root密码: $MYSQL_ROOT_PASSWORD"
    echo "• Redis密码: $REDIS_PASSWORD"
    echo "• 详细安装日志: $LOG_FILE"
    echo "• 请及时修改默认密码！"
    echo "=========================================="
}

# 主安装函数
main_installation() {
    info "开始自动化安装..."

    # 设置错误处理和信号捕获
    trap 'handle_error ${LINENO} "$BASH_COMMAND"' ERR
    trap 'handle_signal' INT TERM EXIT

    # 加载配置
    load_config

    # 显示系统信息
    show_system_info

    # 显示安装配置
    show_install_config

    # 创建安装目录
    mkdir -p "$INSTALL_DIR"
    cd "$INSTALL_DIR"

    # 执行安装步骤
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

    # 显示安装摘要
    show_installation_summary

    success "所有组件安装完成！"

    # 清除回滚操作（安装成功）
    ROLLBACK_ACTIONS=()
}

# 脚本入口
main() {
    clear

    echo -e "${GREEN}"
    echo "=========================================="
    echo "   企业级 Web 环境自动化安装脚本"
    echo "   支持: PHP + MySQL + Nginx + Redis"
    echo "   兼容: CentOS, RHEL, Alibaba Cloud Linux"
    echo "         OpenCloudOS, TencentOS, UOS, Kylin"
    echo "=========================================="
    echo -e "${NC}"

    # 检查root权限
    if [[ $EUID -ne 0 ]]; then
        error "请使用root权限运行此脚本"
        exit 1
    fi

    # 执行主安装流程
    main_installation
}

# 运行主函数
main "$@"
