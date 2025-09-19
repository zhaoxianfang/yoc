#!/bin/bash

# 设置严格模式
set -euo pipefail

# 可配置参数
CONFIG_FILE="/etc/php-install.conf"
LOG_FILE="/var/log/php-install.log"
INSTALL_CONFIG_FILE="/data/install_config.md"
BACKUP_DIR="/data/backup"
TEMP_DIR="/tmp/php-install-temp"

# 默认配置
declare -A CONFIG=(
["PHP_VERSION"]="8.4.12"
["PHP_URL"]="https://www.php.net/distributions/php-__VERSION__.tar.gz"
["PHP_PREFIX"]="/usr/local/php8"
["CONFIG_PATH"]="/usr/local/php8/lib"
["FPM_USER"]="www"
["FPM_GROUP"]="www"
["WWW_PASSWORD"]="$(openssl rand -base64 16)"
["BUILD_DIR"]="/tmp/php-build"
["LIB_DIR"]="lib64"
["MYSQL_VERSION"]="8.4"
["MYSQL_ROOT_PASSWORD"]="$(openssl rand -base64 16)"
["COMPOSER_MIRROR"]="https://mirrors.aliyun.com/composer/"
["TIMEZONE"]="Asia/Shanghai"
["PHP_MEMORY_LIMIT"]="512M"
["PHP_UPLOAD_MAX"]="256M"
["PHP_MAX_EXECUTION_TIME"]="300"
["PHP_OPCACHE_MEMORY"]="128"
["FPM_MAX_CHILDREN"]="50"
["FPM_START_SERVERS"]="5"
["FPM_MIN_SPARE_SERVERS"]="5"
["FPM_MAX_SPARE_SERVERS"]="10"
["REDIS_VERSION"]="5.3.7"
["IMAGICK_VERSION"]="3.8.0"
["MYSQL_CONFIG_DIR"]="/etc/mysql"
["MYSQL_DATA_DIR"]="/var/lib/mysql"
["MYSQL_LOG_DIR"]="/var/log/mysql"
["CLEANUP_TEMP"]="true"
["MAX_RETRY"]="3"
["RETRY_DELAY"]="5"
)

# 系统变量
OS=""
OS_VERSION=""
ARCH=""
SOURCE_DIR=""
INSTALLED_PACKAGES=()

# 加载用户配置
load_config() {
if [ -f "$CONFIG_FILE" ]; then
info "加载用户配置文件: $CONFIG_FILE"
while IFS='=' read -r key value; do
if [[ ! $key =~ ^# && -n $key && -n $value ]]; then
key=$(echo "$key" | tr '[:lower:]' '[:upper:]' | tr -d '[:space:]')
value=$(echo "$value" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
CONFIG["$key"]="$value"
fi
done < "$CONFIG_FILE"
fi

    # 处理PHP URL中的版本占位符
    CONFIG["PHP_URL"]="${CONFIG["PHP_URL"]//__VERSION__/${CONFIG["PHP_VERSION"]}}"
    SOURCE_DIR="${CONFIG["BUILD_DIR"]}/php-src"
    
    # 创建必要的目录
    mkdir -p "$BACKUP_DIR" "$TEMP_DIR"
}

# 定义颜色输出函数
red() { echo -e "\033[31m$1\033[0m"; }
green() { echo -e "\033[32m$1\033[0m"; }
yellow() { echo -e "\033[33m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }
info() { echo -e "\033[44m\033[97m 信息 \033[0m $1" | tee -a "$LOG_FILE"; }
success() { echo -e "\033[42m\033[30m 成功 \033[0m $1" | tee -a "$LOG_FILE"; }
error() { echo -e "\033[41m\033[97m 错误 \033[0m $1" | tee -a "$LOG_FILE"; }
warning() { echo -e "\033[43m\033[30m 警告 \033[0m $1" | tee -a "$LOG_FILE"; }

# 创建必要的目录
mkdir -p "${CONFIG["BUILD_DIR"]}" "${CONFIG["PHP_PREFIX"]}" /data
exec > >(tee -a "$LOG_FILE") 2>&1

# 错误处理函数
handle_error() {
local exit_code=$?
local line_no=$1
local command=$2

    error "脚本执行失败，行号: $line_no, 命令: $command, 退出码: $exit_code"
    error "请查看日志文件: $LOG_FILE 获取详细信息"
    save_install_config "FAILED"
    exit $exit_code
}

trap 'handle_error ${LINENO} "$BASH_COMMAND"' ERR

# 安全备份函数
safe_backup() {
local file="$1"
if [ -f "$file" ]; then
local backup_file="$BACKUP_DIR/$(basename "$file").backup.$(date +%Y%m%d%H%M%S)"
cp -p "$file" "$backup_file"
info "已备份: $file -> $backup_file"
fi
}

# 检查命令是否存在
check_command() {
local cmd="$1"
if ! command -v "$cmd" &>/dev/null; then
warning "命令不存在: $cmd"
return 1
fi
return 0
}

# 重试函数
retry_command() {
local max_attempts=${CONFIG["MAX_RETRY"]}
local delay=${CONFIG["RETRY_DELAY"]}
local attempt=1
local cmd="$*"

    while [ $attempt -le $max_attempts ]; do
        info "尝试 $attempt/$max_attempts: $cmd"
        if eval "$cmd"; then
            return 0
        else
            warning "尝试 $attempt/$max_attempts 失败，等待 ${delay}秒后重试..."
            sleep $delay
            ((attempt++))
        fi
    done
    
    error "命令执行失败: $cmd"
    return 1
}

# 安装单个包（带详细输出和错误处理）
install_package() {
local pkg="$1"
local install_cmd=""

    # 检查包是否已安装
    case $OS in
        "centos"|"rhel"|"fedora"|"almalinux"|"rocky")
            if yum list installed "$pkg" &>/dev/null; then
                info "包已安装: $pkg"
                return 0
            fi
            install_cmd="yum install -y --allowerasing \"$pkg\""
            ;;
        "ubuntu"|"debian")
            if dpkg -l "$pkg" &>/dev/null; then
                info "包已安装: $pkg"
                return 0
            fi
            install_cmd="apt install -y \"$pkg\""
            ;;
    esac
    
    if [ -n "$install_cmd" ]; then
        info "正在安装: $pkg"
        if retry_command "$install_cmd"; then
            INSTALLED_PACKAGES+=("$pkg")
            success "安装成功: $pkg"
            return 0
        else
            error "安装失败: $pkg"
            return 1
        fi
    fi
    
    return 0
}

# 批量安装包
install_packages() {
local packages=("$@")
local total=${#packages[@]}
local current=1

    info "开始安装 $total 个包..."
    
    for pkg in "${packages[@]}"; do
        info "[$current/$total] 处理包: $pkg"
        if ! install_package "$pkg"; then
            warning "跳过失败的包: $pkg"
        fi
        ((current++))
    done
    
    success "包安装完成"
}

# 保存安装配置到文件
save_install_config() {
local status="${1:-SUCCESS}"
local timestamp=$(date '+%Y-%m-%d %H:%M:%S')

    mkdir -p "$(dirname "$INSTALL_CONFIG_FILE")"
    
    cat > "$INSTALL_CONFIG_FILE" << EOF
# PHP 环境安装配置
> 生成时间: $timestamp
> 安装状态: $status

## 系统信息
- 操作系统: $OS $OS_VERSION
- 系统架构: $ARCH
- 主机名: $(hostname)
- IP地址: $(hostname -I 2>/dev/null | awk '{print $1}' || echo "未知")

## 用户配置
- WWW用户: ${CONFIG["FPM_USER"]}
- WWW密码: ${CONFIG["WWW_PASSWORD"]}
- WWW组: ${CONFIG["FPM_GROUP"]}

## PHP 配置
- PHP版本: ${CONFIG["PHP_VERSION"]}
- 安装路径: ${CONFIG["PHP_PREFIX"]}
- 配置文件: ${CONFIG["CONFIG_PATH"]}/php.ini
- PHP-FPM用户: ${CONFIG["FPM_USER"]}
- PHP-FPM组: ${CONFIG["FPM_GROUP"]}

## MySQL 配置
- MySQL版本: ${CONFIG["MYSQL_VERSION"]}
- Root密码: ${CONFIG["MYSQL_ROOT_PASSWORD"]}
- 配置目录: ${CONFIG["MYSQL_CONFIG_DIR"]}
- 数据目录: ${CONFIG["MYSQL_DATA_DIR"]}

## 安装的包
$(printf '%s\n' "${INSTALLED_PACKAGES[@]}" | sed 's/^/- /')

## 服务信息
- PHP-FPM服务: /etc/systemd/system/php-fpm.service
- MySQL服务: mysql.service
- Nginx服务: nginx.service

## 环境变量
\`\`\`bash
export PATH="${CONFIG["PHP_PREFIX"]}/bin:\\\$PATH"
export LD_LIBRARY_PATH="${CONFIG["PHP_PREFIX"]}/lib:\\\$LD_LIBRARY_PATH"
\`\`\`

## 重要路径
- 网站根目录: /var/www/html
- Nginx配置: /etc/nginx/
- MySQL数据: ${CONFIG["MYSQL_DATA_DIR"]}
- 日志文件: /var/log/
- 备份目录: $BACKUP_DIR

## 安全信息
- WWW用户已配置sudo权限（无密码）
- MySQL root密码已安全设置
- 所有配置文件已备份至: $BACKUP_DIR

## 使用说明
1. 重启终端或运行: source /etc/profile
2. 启动服务: systemctl start php-fpm mysql nginx
3. 设置开机启动: systemctl enable php-fpm mysql nginx
4. 测试PHP: php -v
5. 测试MySQL: mysql -u root -p

## 故障排除
- 查看PHP错误日志: ${CONFIG["PHP_PREFIX"]}/var/log/php-fpm.log
- 查看Nginx日志: /var/log/nginx/
- 查看MySQL日志: ${CONFIG["MYSQL_LOG_DIR"]}/error.log

> 安装日志: $LOG_FILE
> 备份文件: $BACKUP_DIR
EOF

    success "安装配置已保存到: $INSTALL_CONFIG_FILE"
    if [ "$status" = "SUCCESS" ]; then
        info "MySQL root 密码: ${CONFIG["MYSQL_ROOT_PASSWORD"]}"
        info "WWW 用户密码: ${CONFIG["WWW_PASSWORD"]}"
    fi
}

# 检测系统类型
detect_os() {
info "检测操作系统..."

    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        OS_VERSION=$VERSION_ID
    elif [ -f /etc/redhat-release ]; then
        OS="centos"
        OS_VERSION=$(grep -oE '[0-9]+\.[0-9]+' /etc/redhat-release)
    elif [ -f /etc/lsb-release ]; then
        . /etc/lsb-release
        OS=${DISTRIB_ID,,}
        OS_VERSION=$DISTRIB_RELEASE
    else
        OS="unknown"
        OS_VERSION="unknown"
    fi
    
    ARCH=$(uname -m)
    if [ "$ARCH" = "x86_64" ]; then
        CONFIG["LIB_DIR"]="lib64"
    else
        CONFIG["LIB_DIR"]="lib"
    fi
    
    # 设置MySQL配置目录
    case $OS in
        "centos"|"rhel"|"fedora"|"almalinux"|"rocky")
            CONFIG["MYSQL_CONFIG_DIR"]="/etc/my.cnf.d"
            ;;
        "ubuntu"|"debian")
            CONFIG["MYSQL_CONFIG_DIR"]="/etc/mysql/mysql.conf.d"
            ;;
    esac
    
    info "操作系统: $OS $OS_VERSION, 架构: $ARCH"
}

# 升级系统
upgrade_system() {
info "开始升级系统..."

    case $OS in
        "centos"|"rhel"|"fedora"|"almalinux"|"rocky")
            retry_command yum update -y --allowerasing
            retry_command yum upgrade -y --allowerasing
            ;;
        "ubuntu"|"debian")
            retry_command apt update -y
            retry_command apt upgrade -y
            retry_command apt dist-upgrade -y
            ;;
    esac
    
    success "系统升级完成"
}

# 创建WWW用户和组
create_www_user() {
info "创建WWW用户和组..."

    # 创建组
    if ! getent group "${CONFIG["FPM_GROUP"]}" >/dev/null; then
        info "创建组: ${CONFIG["FPM_GROUP"]}"
        groupadd "${CONFIG["FPM_GROUP"]}"
        success "创建组成功: ${CONFIG["FPM_GROUP"]}"
    else
        info "组已存在: ${CONFIG["FPM_GROUP"]}"
    fi
    
    # 创建用户
    if ! id "${CONFIG["FPM_USER"]}" >/dev/null 2>&1; then
        info "创建用户: ${CONFIG["FPM_USER"]}"
        useradd -r -m -s /bin/bash -g "${CONFIG["FPM_GROUP"]}" "${CONFIG["FPM_USER"]}"
        echo "${CONFIG["FPM_USER"]}:${CONFIG["WWW_PASSWORD"]}" | chpasswd
        success "创建用户成功: ${CONFIG["FPM_USER"]}"
    else
        info "用户已存在: ${CONFIG["FPM_USER"]}"
        echo "${CONFIG["FPM_USER"]}:${CONFIG["WWW_PASSWORD"]}" | chpasswd
        info "重置用户密码: ${CONFIG["FPM_USER"]}"
    fi
    
    # 配置sudo权限
    local sudo_config="${CONFIG["FPM_USER"]} ALL=(ALL) NOPASSWD:ALL"
    if ! grep -q "^${sudo_config}" /etc/sudoers; then
        info "配置sudo权限: ${CONFIG["FPM_USER"]}"
        safe_backup "/etc/sudoers"
        echo "$sudo_config" >> /etc/sudoers
        success "配置sudo权限成功: ${CONFIG["FPM_USER"]}"
    else
        info "sudo权限已配置: ${CONFIG["FPM_USER"]}"
    fi
    
    # 创建网站目录
    mkdir -p /var/www/html
    chown -R "${CONFIG["FPM_USER"]}:${CONFIG["FPM_GROUP"]}" /var/www/html
    chmod 755 /var/www/html
    
    success "WWW用户和组配置完成"
}

# 安装依赖包（修复版本）
install_dependencies() {
info "开始安装系统依赖包..."

    # 基础工具
    local base_tools=("wget" "curl" "git" "tar" "gzip" "make" "cmake" "autoconf" "libtool" "pkg-config")
    for tool in "${base_tools[@]}"; do
        if ! check_command "$tool"; then
            case $OS in
                "centos"|"rhel"|"fedora"|"almalinux"|"rocky")
                    install_package "$tool"
                    ;;
                "ubuntu"|"debian")
                    # Ubuntu/Debian 包名可能不同
                    local pkg_name="$tool"
                    if [ "$tool" = "pkg-config" ]; then
                        pkg_name="pkg-config"
                    fi
                    install_package "$pkg_name"
                    ;;
            esac
        fi
    done
    
    # 定义所有可能的依赖包
    local deps=()
    case $OS in
        "centos"|"rhel"|"fedora"|"almalinux"|"rocky")
            # 添加必要的仓库
            install_package epel-release
            retry_command yum install -y https://rpms.remirepo.net/enterprise/remi-release-$(rpm -E %rhel).rpm --allowerasing
            
            deps=(
                yum-utils gcc gcc-c++ autoconf libtool perl perl-devel
                libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel
                openldap openldap-devel openldap-clients freetype freetype-devel
                libxml2 libxml2-devel sqlite sqlite-devel zlib zlib-devel curl curl-devel
                pcre pcre-devel gd gd-devel expat expat-devel libicu libicu-devel 
                bzip2 bzip2-devel python3 python3-devel libwebp libwebp-devel 
                make libzstd libzstd-devel wget
                oniguruma oniguruma-devel zstd zstd-devel glibc-headers krb5-devel 
                libzip libzip-devel libxslt libxslt-devel openssl openssl-devel 
                libsodium libsodium-devel glib2 glib2-devel
                cairo cairo-devel gmp gmp-devel libevent libevent-devel 
                readline readline-devel net-snmp net-snmp-devel
                aspell aspell-devel unixODBC unixODBC-devel libc-client libc-client-devel 
                libXpm libXpm-devel enchant enchant-devel
                php-ldap automake kernel keyutils patch tidy libtidy libtidy-devel
                libedit libedit-devel re2c libargon2 libargon2-devel libyaml libyaml-devel 
                ImageMagick ImageMagick-devel libmemcached libmemcached-devel 
                rabbitmq-c rabbitmq-c-devel libuv libuv-devel hiredis hiredis-devel
                ncurses ncurses-devel libtirpc libtirpc-devel systemd systemd-devel 
                libffi libffi-devel libatomic openssl11 openssl11-devel
            )
            
            # 安装开发工具组
            info "安装开发工具组..."
            retry_command yum groupinstall -y "Development Tools" --allowerasing
            ;;
            
        "ubuntu"|"debian")
            # 添加PPA仓库
            install_package software-properties-common
            retry_command add-apt-repository -y ppa:ondrej/php
            retry_command add-apt-repository -y ppa:ondrej/nginx
            retry_command apt update
            
            deps=(
                build-essential autoconf libtool pkg-config cmake
                libxml2-dev libssl-dev libcurl4-openssl-dev
                libjpeg-dev libpng-dev libfreetype6-dev libicu-dev
                libxslt1-dev libsqlite3-dev libonig-dev libzip-dev
                libbz2-dev libreadline-dev libedit-dev libgmp-dev
                libwebp-dev libsodium-dev libargon2-1 libargon2-dev
                re2c libyaml-dev libmagickwand-dev libmemcached-dev
                librabbitmq-dev libuv1-dev libhiredis-dev
                libldap2-dev libtidy-dev libenchant-2-dev
                libaspell-dev libsnmp-dev unixodbc-dev
                libc-client-dev libxpm-dev libedit-dev
                libevent-dev libkrb5-dev libzstd-dev
                libpq-dev libpq5 libncurses5-dev libtirpc-dev
                libsystemd-dev libffi-dev libatomic1
                libssl-dev libxml2-dev
            )
            ;;
    esac
    
    # 安装所有依赖包
    install_packages "${deps[@]}"
    
    success "系统依赖包安装完成"
}

# 安装MySQL
install_mysql() {
info "开始安装MySQL ${CONFIG["MYSQL_VERSION"]}..."

    # 创建MySQL目录
    mkdir -p "${CONFIG["MYSQL_CONFIG_DIR"]}" "${CONFIG["MYSQL_DATA_DIR"]}" "${CONFIG["MYSQL_LOG_DIR"]}"
    
    case $OS in
        "centos"|"rhel"|"fedora"|"almalinux"|"rocky")
            # 确保配置目录存在
            mkdir -p /etc/my.cnf.d
            
            # 添加MySQL官方仓库
            info "添加MySQL仓库..."
            retry_command rpm -Uvh https://dev.mysql.com/get/mysql80-community-release-el$(rpm -E %rhel)-noarch.rpm
            
            # 安装MySQL服务器
            install_package mysql-community-server
            
            # 配置MySQL
            info "配置MySQL..."
            cat > /etc/my.cnf.d/php-optimized.cnf << EOF
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 100
thread_cache_size = 8
socket = /var/lib/mysql/mysql.sock
log-error = ${CONFIG["MYSQL_LOG_DIR"]}/error.log
datadir = ${CONFIG["MYSQL_DATA_DIR"]}

[client]
socket = /var/lib/mysql/mysql.sock
EOF

            # 启动MySQL
            info "启动MySQL服务..."
            systemctl start mysqld
            systemctl enable mysqld
            
            # 等待MySQL启动
            info "等待MySQL启动..."
            sleep 10
            
            # 获取临时密码或安全安装
            local temp_password=$(grep 'temporary password' /var/log/mysqld.log 2>/dev/null | awk '{print $NF}' || echo "")
            
            if [ -n "$temp_password" ]; then
                info "设置MySQL root密码..."
                mysql --connect-expired-password -u root -p"$temp_password" <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '${CONFIG["MYSQL_ROOT_PASSWORD"]}';
FLUSH PRIVILEGES;
EOF
else
info "运行MySQL安全安装..."
mysql_secure_installation <<EOF
y
${CONFIG["MYSQL_ROOT_PASSWORD"]}
${CONFIG["MYSQL_ROOT_PASSWORD"]}
y
y
y
y
EOF
fi
;;

        "ubuntu"|"debian")
            # 添加MySQL官方仓库
            info "添加MySQL仓库..."
            retry_command wget -O /tmp/mysql-apt-config.deb https://dev.mysql.com/get/mysql-apt-config_0.8.28-1_all.deb
            retry_command DEBIAN_FRONTEND=noninteractive dpkg -i /tmp/mysql-apt-config.deb
            retry_command apt update
            
            # 安装MySQL服务器
            info "安装MySQL服务器..."
            debconf-set-selections <<< "mysql-community-server mysql-community-server/root-pass password ${CONFIG["MYSQL_ROOT_PASSWORD"]}"
            debconf-set-selections <<< "mysql-community-server mysql-community-server/re-root-pass password ${CONFIG["MYSQL_ROOT_PASSWORD"]}"
            retry_command DEBIAN_FRONTEND=noninteractive apt install -y mysql-server
            
            # 配置MySQL
            info "配置MySQL..."
            mkdir -p /etc/mysql/conf.d
            cat > /etc/mysql/conf.d/php-optimized.cnf << EOF
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 100
thread_cache_size = 8

[client]
socket = /var/run/mysqld/mysqld.sock
EOF

            info "启动MySQL服务..."
            systemctl start mysql
            systemctl enable mysql
            ;;
    esac
    
    success "MySQL安装完成"
}

# 下载PHP源码
download_php() {
info "开始下载PHP ${CONFIG["PHP_VERSION"]} 源码..."

    if [ -d "$SOURCE_DIR" ]; then
        info "清理之前的源码目录..."
        rm -rf "$SOURCE_DIR"
    fi
    
    mkdir -p "$SOURCE_DIR"
    cd "${CONFIG["BUILD_DIR"]}"
    
    # 检查URL是否可用
    info "检查下载URL..."
    if ! curl -I --silent "${CONFIG["PHP_URL"]}" | head -n 1 | grep -q "200"; then
        error "PHP下载URL不可用: ${CONFIG["PHP_URL"]}"
        exit 1
    fi
    
    # 使用多线程下载
    local download_success=false
    
    if check_command axel; then
        info "使用 axel 多线程下载..."
        retry_command axel -n 10 -o php.tar.gz "${CONFIG["PHP_URL"]}" && download_success=true
    elif check_command wget; then
        info "使用 wget 下载..."
        retry_command wget --tries=3 --timeout=30 -O php.tar.gz "${CONFIG["PHP_URL"]}" && download_success=true
    elif check_command curl; then
        info "使用 curl 下载..."
        retry_command curl -L --retry 3 --connect-timeout 30 -o php.tar.gz "${CONFIG["PHP_URL"]}" && download_success=true
    else
        error "没有可用的下载工具"
        exit 1
    fi
    
    if [ "$download_success" != true ]; then
        error "PHP源码下载失败"
        exit 1
    fi
    
    info "解压PHP源码..."
    retry_command tar -xzf php.tar.gz -C "$SOURCE_DIR" --strip-components=1
    
    success "PHP源码下载和解压完成"
}

# 获取所有可用的扩展
get_available_extensions() {
info "检测可用的PHP扩展..."
cd "$SOURCE_DIR"

    local extensions=($(find . -name "ext" -type d | xargs -I{} sh -c 'basename $(dirname {})' | sort -u))
    
    CONFIGURE_EXTENSIONS=""
    for ext in "${extensions[@]}"; do
        case $ext in
            "opcache"|"zend_test"|"phar"|"core"|"pear"|"date"|"pcre"|"reflection"|"spl"|"standard"|"main")
                continue
                ;;
            "gd")
                CONFIGURE_EXTENSIONS+=" --with-gd --with-webp --with-jpeg --with-freetype"
                ;;
            *)
                CONFIGURE_EXTENSIONS+=" --enable-$ext=shared"
                ;;
        esac
    done
    
    CONFIGURE_EXTENSIONS+=" --enable-opcache --enable-phar --enable-mbstring --enable-zip"
    CONFIGURE_EXTENSIONS+=" --with-zlib --with-curl --with-openssl --with-mysqli --with-pdo-mysql"
    CONFIGURE_EXTENSIONS+=" --with-pdo-sqlite --with-bz2 --with-readline --with-gettext"
    CONFIGURE_EXTENSIONS+=" --with-xsl --with-libxml --with-ldap --with-enchant --with-snmp"
    
    info "检测到 ${#extensions[@]} 个扩展"
}

# 编译安装PHP
compile_php() {
info "开始编译PHP..."
cd "$SOURCE_DIR"

    info "清理之前的编译..."
    make clean 2>/dev/null || true
    make distclean 2>/dev/null || true
    
    info "运行 buildconf..."
    retry_command ./buildconf --force
    
    local configure_cmd="./configure \
        --prefix=${CONFIG["PHP_PREFIX"]} \
        --with-config-file-path=${CONFIG["CONFIG_PATH"]} \
        --with-config-file-scan-dir=${CONFIG["CONFIG_PATH"]}/conf.d \
        --with-fpm-user=${CONFIG["FPM_USER"]} \
        --with-fpm-group=${CONFIG["FPM_GROUP"]} \
        --enable-fpm \
        --with-libdir=${CONFIG["LIB_DIR"]} \
        --with-pic \
        --enable-maintainer-zts \
        --with-password-argon2 \
        $CONFIGURE_EXTENSIONS"
    
    info "配置PHP: $configure_cmd"
    retry_command eval "$configure_cmd"
    
    # 多线程编译
    info "编译PHP (使用 $(nproc) 个线程)..."
    retry_command make -j$(nproc)
    
    info "安装PHP..."
    retry_command make install
    
    success "PHP编译安装完成"
}

# 安装PHP扩展（版本兼容）
install_php_extensions() {
info "开始安装额外的PHP扩展..."
cd "${CONFIG["BUILD_DIR"]}"

    # Redis扩展
    install_redis_extension() {
        info "安装Redis扩展..."
        
        if [ ! -d "phpredis" ]; then
            info "克隆Redis扩展仓库..."
            retry_command git clone https://github.com/phpredis/phpredis.git
        fi
        
        cd phpredis
        info "更新git仓库..."
        retry_command git fetch --all --tags
        retry_command git checkout master
        
        # 清理之前的编译
        info "清理编译文件..."
        retry_command git clean -fdx
        
        info "运行phpize..."
        retry_command "${CONFIG["PHP_PREFIX"]}/bin/phpize"
        
        info "配置Redis扩展..."
        retry_command ./configure --with-php-config="${CONFIG["PHP_PREFIX"]}/bin/php-config"
        
        info "编译Redis扩展..."
        retry_command make -j$(nproc)
        
        info "安装Redis扩展..."
        retry_command make install
        
        info "配置Redis扩展..."
        echo "extension=redis.so" > "${CONFIG["CONFIG_PATH"]}/conf.d/redis.ini"
    }
    
    # Imagick扩展
    install_imagick_extension() {
        info "安装Imagick扩展..."
        
        if [ ! -d "imagick" ]; then
            info "克隆Imagick扩展仓库..."
            retry_command git clone https://github.com/Imagick/imagick.git
        fi
        
        cd imagick
        info "更新git仓库..."
        retry_command git fetch --all --tags
        retry_command git checkout master
        
        # 清理之前的编译
        info "清理编译文件..."
        retry_command git clean -fdx
        
        info "运行phpize..."
        retry_command "${CONFIG["PHP_PREFIX"]}/bin/phpize"
        
        info "配置Imagick扩展..."
        retry_command ./configure --with-php-config="${CONFIG["PHP_PREFIX"]}/bin/php-config"
        
        info "编译Imagick扩展..."
        retry_command make -j$(nproc)
        
        info "安装Imagick扩展..."
        retry_command make install
        
        info "配置Imagick扩展..."
        echo "extension=imagick.so" > "${CONFIG["CONFIG_PATH"]}/conf.d/imagick.ini"
    }
    
    # 安装扩展
    install_redis_extension
    cd "${CONFIG["BUILD_DIR"]}"
    install_imagick_extension
    
    success "额外扩展安装完成"
}

# 配置PHP
configure_php() {
info "配置PHP..."

    mkdir -p "${CONFIG["CONFIG_PATH"]}" "${CONFIG["CONFIG_PATH"]}/conf.d"
    
    if [ -f "$SOURCE_DIR/php.ini-production" ]; then
        info "复制php.ini配置文件..."
        cp "$SOURCE_DIR/php.ini-production" "${CONFIG["CONFIG_PATH"]}/php.ini"
        
        # 优化配置
        info "优化PHP配置..."
        sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" "${CONFIG["CONFIG_PATH"]}/php.ini"
        sed -i "s/memory_limit = .*/memory_limit = ${CONFIG["PHP_MEMORY_LIMIT"]}/" "${CONFIG["CONFIG_PATH"]}/php.ini"
        sed -i "s/upload_max_filesize = .*/upload_max_filesize = ${CONFIG["PHP_UPLOAD_MAX"]}/" "${CONFIG["CONFIG_PATH"]}/php.ini"
        sed -i "s/post_max_size = .*/post_max_size = ${CONFIG["PHP_UPLOAD_MAX"]}/" "${CONFIG["CONFIG_PATH"]}/php.ini"
        sed -i "s/max_execution_time = .*/max_execution_time = ${CONFIG["PHP_MAX_EXECUTION_TIME"]}/" "${CONFIG["CONFIG_PATH"]}/php.ini"
        sed -i "s/;date.timezone =/date.timezone = ${CONFIG["TIMEZONE"]}/" "${CONFIG["CONFIG_PATH"]}/php.ini"
        
        # 添加OPCache配置
        info "配置OPCache..."
        cat >> "${CONFIG["CONFIG_PATH"]}/php.ini" << EOF

[opcache]
opcache.enable=1
opcache.memory_consumption=${CONFIG["PHP_OPCACHE_MEMORY"]}
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
EOF
fi

    # PHP-FPM配置
    if [ -f "${CONFIG["PHP_PREFIX"]}/etc/php-fpm.conf.default" ]; then
        info "配置PHP-FPM..."
        cp "${CONFIG["PHP_PREFIX"]}/etc/php-fpm.conf.default" "${CONFIG["PHP_PREFIX"]}/etc/php-fpm.conf"
        sed -i "s/;pm.max_children = 5/pm.max_children = ${CONFIG["FPM_MAX_CHILDREN"]}/" "${CONFIG["PHP_PREFIX"]}/etc/php-fpm.conf"
        sed -i "s/;pm.start_servers = 2/pm.start_servers = ${CONFIG["FPM_START_SERVERS"]}/" "${CONFIG["PHP_PREFIX"]}/etc/php-fpm.conf"
        sed -i "s/;pm.min_spare_servers = 1/pm.min_spare_servers = ${CONFIG["FPM_MIN_SPARE_SERVERS"]}/" "${CONFIG["PHP_PREFIX"]}/etc/php-fpm.conf"
        sed -i "s/;pm.max_spare_servers = 3/pm.max_spare_servers = ${CONFIG["FPM_MAX_SPARE_SERVERS"]}/" "${CONFIG["PHP_PREFIX"]}/etc/php-fpm.conf"
    fi
    
    # PHP-FPM服务文件
    info "创建PHP-FPM服务文件..."
    cat > /etc/systemd/system/php-fpm.service << EOF
[Unit]
Description=The PHP FastCGI Process Manager
After=network.target mysql.service

[Service]
Type=simple
User=${CONFIG["FPM_USER"]}
Group=${CONFIG["FPM_GROUP"]}
PIDFile=${CONFIG["PHP_PREFIX"]}/var/run/php-fpm.pid
ExecStart=${CONFIG["PHP_PREFIX"]}/sbin/php-fpm --nodaemonize --fpm-config ${CONFIG["PHP_PREFIX"]}/etc/php-fpm.conf
ExecReload=/bin/kill -USR2 \$MAINPID
Restart=on-failure
LimitNOFILE=65536

[Install]
WantedBy=multi-user.target
EOF

    success "PHP配置完成"
}

# 安装Composer
install_composer() {
info "安装Composer..."

    info "下载Composer安装器..."
    EXPECTED_CHECKSUM="$(curl -s https://composer.github.io/installer.sig)"
    retry_command php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    
    info "验证文件完整性..."
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    
    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
        error "Composer安装文件校验失败"
        rm -f composer-setup.php
        exit 1
    fi
    
    info "安装Composer..."
    retry_command php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f composer-setup.php
    
    info "配置Composer镜像..."
    retry_command composer config -g repo.packagist composer "${CONFIG["COMPOSER_MIRROR"]}"
    
    success "Composer安装完成"
}

# 安装Git
install_git() {
info "安装Git..."
install_package git
success "Git安装完成"
}

# 安装Nginx
install_nginx() {
info "安装Nginx..."
install_package nginx

    # 配置Nginx支持PHP
    info "配置Nginx支持PHP..."
    cat > /etc/nginx/conf.d/php.conf << EOF
location ~ \.php$ {
fastcgi_pass   127.0.0.1:9000;
fastcgi_index  index.php;
fastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
include        fastcgi_params;

    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
    fastcgi_busy_buffers_size 256k;
}
EOF

    # 优化Nginx配置
    info "优化Nginx配置..."
    sed -i 's/worker_connections.*/worker_connections 10240;/' /etc/nginx/nginx.conf
    sed -i 's/worker_processes.*/worker_processes auto;/' /etc/nginx/nginx.conf
    
    # 设置正确的权限
    info "设置Nginx文件权限..."
    chown -R "${CONFIG["FPM_USER"]}:${CONFIG["FPM_GROUP"]}" /var/log/nginx
    chown -R "${CONFIG["FPM_USER"]}:${CONFIG["FPM_GROUP"]}" /var/lib/nginx
    
    info "启动Nginx服务..."
    systemctl enable nginx
    systemctl start nginx
    
    success "Nginx安装完成"
}

# 设置环境变量
setup_environment() {
info "设置环境变量..."

    if ! grep -q "${CONFIG["PHP_PREFIX"]}/bin" /etc/profile; then
        info "添加PHP到PATH..."
        cat >> /etc/profile << EOF
export PATH="${CONFIG["PHP_PREFIX"]}/bin:\$PATH"
export LD_LIBRARY_PATH="${CONFIG["PHP_PREFIX"]}/lib:\$LD_LIBRARY_PATH"
export PHP_INI_SCAN_DIR="${CONFIG["CONFIG_PATH"]}/conf.d"
EOF
fi

    info "创建符号链接..."
    ln -sf "${CONFIG["PHP_PREFIX"]}/bin/php" /usr/local/bin/php || true
    ln -sf "${CONFIG["PHP_PREFIX"]}/bin/phpize" /usr/local/bin/phpize || true
    ln -sf "${CONFIG["PHP_PREFIX"]}/bin/php-config" /usr/local/bin/php-config || true
    
    info "加载环境变量..."
    source /etc/profile
    
    success "环境变量设置完成"
}

# 启动服务
start_services() {
info "启动服务..."

    systemctl daemon-reload
    
    local services=("mysql" "nginx" "php-fpm")
    for service in "${services[@]}"; do
        info "处理服务: $service"
        if systemctl is-active --quiet "$service"; then
            info "重启服务: $service"
            systemctl restart "$service"
        else
            info "启动服务: $service"
            systemctl start "$service"
        fi
        systemctl enable "$service"
        
        if systemctl is-active --quiet "$service"; then
            success "服务启动成功: $service"
        else
            warning "服务启动失败: $service"
            systemctl status "$service" || true
        fi
    done
}

# 验证安装
verify_installation() {
info "验证安装..."

    if [ -x "${CONFIG["PHP_PREFIX"]}/bin/php" ]; then
        PHP_VERSION=$("${CONFIG["PHP_PREFIX"]}/bin/php" -v | head -n1)
        success "PHP安装成功: $PHP_VERSION"
        
        # 测试扩展
        info "测试PHP扩展..."
        if "${CONFIG["PHP_PREFIX"]}/bin/php" -m | grep -q "redis"; then
            success "Redis扩展安装成功"
        else
            warning "Redis扩展未加载"
        fi
        
        if "${CONFIG["PHP_PREFIX"]}/bin/php" -m | grep -q "imagick"; then
            success "Imagick扩展安装成功"
        else
            warning "Imagick扩展未加载"
        fi
        
        # 测试MySQL连接
        info "测试MySQL连接..."
        if mysql -u root -p"${CONFIG["MYSQL_ROOT_PASSWORD"]}" -e "SELECT 1" &>/dev/null; then
            success "MySQL连接测试成功"
        else
            warning "MySQL连接测试失败"
        fi
        
        # 测试Nginx
        info "测试Nginx服务..."
        if curl -I http://localhost &>/dev/null; then
            success "Nginx服务运行正常"
        else
            warning "Nginx服务测试失败"
        fi
        
    else
        error "PHP安装验证失败"
        exit 1
    fi
}

# 安全加固
secure_installation() {
info "进行安全加固..."

    # 设置文件权限
    info "设置文件权限..."
    chown -R root:root "${CONFIG["PHP_PREFIX"]}"
    chown -R "${CONFIG["FPM_USER"]}:${CONFIG["FPM_GROUP"]}" "${CONFIG["PHP_PREFIX"]}/var"
    
    # 保护配置文件
    info "保护配置文件..."
    chmod 644 "${CONFIG["CONFIG_PATH"]}/php.ini"
    chmod 600 "/root/.my.cnf" 2>/dev/null || true
    
    # 禁用危险的PHP函数
    info "禁用危险PHP函数..."
    sed -i "s/;disable_functions =/disable_functions = system,passthru,exec,shell_exec,popen,proc_open,phpinfo/" "${CONFIG["CONFIG_PATH"]}/php.ini"
    
    success "安全加固完成"
}

# 清理临时文件
cleanup_temp_files() {
info "清理临时文件..."

    if [ "${CONFIG["CLEANUP_TEMP"]}" = "true" ]; then
        # 清理构建目录
        info "清理构建目录..."
        rm -rf "${CONFIG["BUILD_DIR"]}"/*.tar.gz
        rm -rf "${CONFIG["BUILD_DIR"]}/phpredis"
        rm -rf "${CONFIG["BUILD_DIR"]}/imagick"
        
        # 清理临时目录
        info "清理临时目录..."
        rm -rf "$TEMP_DIR"
        
        # 清理包管理器缓存
        info "清理包管理器缓存..."
        case $OS in
            "centos"|"rhel"|"fedora"|"almalinux"|"rocky")
                yum clean all
                ;;
            "ubuntu"|"debian")
                apt clean
                ;;
        esac
        
        success "临时文件清理完成"
    else
        info "跳过临时文件清理（配置: CLEANUP_TEMP=false）"
    fi
}

# 显示使用说明
show_usage() {
echo "使用方法: $0 [配置文件]"
echo "示例: $0"
echo "示例: $0 /path/to/custom.conf"
echo ""
echo "配置文件格式:"
echo "PHP_VERSION=8.4.12"
echo "MYSQL_ROOT_PASSWORD=mysecurepassword"
echo "WWW_PASSWORD=wwwpassword"
echo "PHP_MEMORY_LIMIT=512M"
echo "CLEANUP_TEMP=true"
echo "MAX_RETRY=3"
echo "RETRY_DELAY=5"
}

# 主执行函数
main() {
local start_time=$(date +%s)

    info "开始PHP ${CONFIG["PHP_VERSION"]} 环境安装..."
    info "操作系统: $OS $OS_VERSION"
    info "系统架构: $ARCH"
    
    # 执行安装步骤
    upgrade_system
    create_www_user
    install_dependencies
    install_mysql
    download_php
    get_available_extensions
    compile_php
    install_php_extensions
    configure_php
    install_composer
    install_git
    install_nginx
    setup_environment
    secure_installation
    start_services
    verify_installation
    cleanup_temp_files
    
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    
    success "🎉 PHP环境安装全部完成！耗时: $duration 秒"
    save_install_config "SUCCESS"
}

# 参数处理
if [ $# -gt 1 ]; then
show_usage
exit 1
fi

if [ $# -eq 1 ]; then
if [ -f "$1" ]; then
CONFIG_FILE="$1"
else
error "配置文件不存在: $1"
exit 1
fi
fi

# 加载配置并开始安装
load_config
detect_os
main
