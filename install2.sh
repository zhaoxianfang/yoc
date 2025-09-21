#!/usr/bin/env bash
#
# industrial_php_stack.sh
# 工业级PHP环境安装脚本 - 重构优化版
#
set -euo pipefail
IFS=$'\n\t'
export LANG=C
export LC_ALL=C

############################
# ---------------- 配置参数 ------------------
INSTALL_PHP="${INSTALL_PHP:-yes}"
INSTALL_MYSQL="${INSTALL_MYSQL:-yes}"
INSTALL_REDIS="${INSTALL_REDIS:-yes}"
INSTALL_NGINX="${INSTALL_NGINX:-yes}"
INSTALL_COMPOSER="${INSTALL_COMPOSER:-yes}"
INSTALL_PHP_EXTENSIONS="${INSTALL_PHP_EXTENSIONS:-yes}"

PHP_VERSION="${PHP_VERSION:-8.4.12}"
MYSQL_VERSION="${MYSQL_VERSION:-8.4.0}"
REDIS_VERSION="${REDIS_VERSION:-7.2.4}"
NGINX_VERSION="${NGINX_VERSION:-1.28.0}"

PHP_MIRRORS=(
    "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"
    "https://mirrors.aliyun.com/php-distributions/php-${PHP_VERSION}.tar.gz"
)

PHP_PREFIX="${PHP_PREFIX:-/usr/local/php8}"
MYSQL_PREFIX="${MYSQL_PREFIX:-/usr/local/mysql}"
NGINX_PREFIX="${NGINX_PREFIX:-/usr/local/nginx}"

MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-$(openssl rand -base64 16)}"
REMOTE_ADMIN_USER="${REMOTE_ADMIN_USER:-phpadmin}"
REMOTE_ADMIN_PASS="${REMOTE_ADMIN_PASS:-$(openssl rand -base64 16)}"
WWW_USER="${WWW_USER:-www}"
WWW_PASS="${WWW_PASS:-$(openssl rand -base64 16)}"

SRC_DIR="${SRC_DIR:-/usr/local/src}"
LOG_DIR="${LOG_DIR:-/var/log/php-stack-install}"
LOG_FILE="${LOG_FILE:-$LOG_DIR/install.log}"
ERROR_LOG_FILE="${ERROR_LOG_FILE:-$LOG_DIR/error.log}"
INSTALL_SUMMARY="${INSTALL_SUMMARY:-/data/install_config.log}"
MAKE_JOBS="${MAKE_JOBS:-$(nproc 2>/dev/null || echo 1)}"
AUTO_START_SERVICES="${AUTO_START_SERVICES:-yes}"
MAX_RETRIES="${MAX_RETRIES:-3}"
DOWNLOAD_RETRIES="${DOWNLOAD_RETRIES:-2}"
CLEAN_TEMP="${CLEAN_TEMP:-yes}"

PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-512M}"
PHP_MAX_EXECUTION_TIME="${PHP_MAX_EXECUTION_TIME:-300}"
PHP_UPLOAD_MAX_FILESIZE="${PHP_UPLOAD_MAX_FILESIZE:-256M}"
PHP_POST_MAX_SIZE="${PHP_POST_MAX_SIZE:-256M}"

############################
# ---------------- 初始化 ------------------
PKG_MGR=""
OS_ID=""
OS_NAME=""
OS_VERSION=""
OS_ARCH=""
PHP_SRC_DIR=""
PHP_INI_FILE=""
START_TIME=$(date +%s)

mkdir -p "$SRC_DIR" "$LOG_DIR" "$(dirname "$INSTALL_SUMMARY")"
exec > >(tee -a "$LOG_FILE") 2> >(tee -a "$ERROR_LOG_FILE" >&2)

############################
# ---------------- 输出函数 ------------------
_red() { echo -e "\033[31m$*\033[0m"; }
_green() { echo -e "\033[32m$*\033[0m"; }
_yellow() { echo -e "\033[33m$*\033[0m"; }
_blue() { echo -e "\033[34m$*\033[0m"; }
_bold() { echo -e "\033[1m$*\033[0m"; }

log() { echo "[$(date '+%F %T')] $*" | tee -a "$LOG_FILE"; }
info() { log "INFO: $*"; echo -e "\033[34mℹ  $*\033[0m"; }
success() { log "SUCCESS: $*"; echo -e "\033[32m✓ $*\033[0m"; }
warn() { log "WARN: $*"; echo -e "\033[33m⚠ $*\033[0m" >&2; }
error() { log "ERROR: $*"; echo -e "\033[31m✗ $*\033[0m" >&2; }

fail() {
    error "$1 (Line: ${2:-${BASH_LINENO[0]}})"
    error "详细日志: $LOG_FILE"
    exit 1
}

timer() {
    local duration=$(($(date +%s) - $1))
    echo "$((duration / 60))分$((duration % 60))秒"
}

############################
# ---------------- 命令执行函数 ------------------
run_cmd() {
    local cmd="$1"
    local desc="${2:-执行命令}"
    local attempt="${3:-1}"

    log "执行命令 (尝试 $attempt): $cmd"

    if eval "$cmd"; then
        log "命令成功: $cmd"
        return 0
    else
        local rc=$?
        log "命令失败 (退出码: $rc): $cmd"
        return $rc
    fi
}

run_cmd_with_retry() {
    local cmd="$1"
    local desc="${2:-执行命令}"
    local max_retries="${3:-$MAX_RETRIES}"
    local retry_delay="${4:-2}"

    local attempt=1
    while [ $attempt -le $max_retries ]; do
        if run_cmd "$cmd" "$desc ($attempt/$max_retries)" "$attempt"; then
            return 0
        fi

        if [ $attempt -lt $max_retries ]; then
            sleep $retry_delay
        fi
        attempt=$((attempt + 1))
    done

    return 1
}

############################
# ---------------- 包管理函数 ------------------
pkg_install() {
    local packages="$*"
    info "安装软件包: $packages"

    case "$PKG_MGR" in
        dnf|yum)
            run_cmd_with_retry "$PKG_MGR -y install $packages" "安装软件包" 3 5
            ;;
        apt)
            run_cmd "apt-get -y update" "更新包列表"
            run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install $packages" "安装软件包" 3 5
            ;;
        *)
            fail "不支持的包管理器: $PKG_MGR"
            ;;
    esac
}

pkg_group_install() {
    local group_name="$1"
    shift
    local packages="$*"

    info "安装 $group_name 依赖包"
    pkg_install "$packages"
}

############################
# ---------------- 系统检测 ------------------
detect_system() {
    info "检测系统环境..."

    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS_ID="${ID:-unknown}"
        OS_NAME="${NAME:-unknown}"
        OS_VERSION="${VERSION_ID:-unknown}"
    else
        fail "无法检测操作系统"
    fi

    OS_ARCH=$(uname -m)
    [ "$OS_ARCH" = "x86_64" ] && OS_ARCH="x64"

    if command -v dnf >/dev/null 2>&1; then
        PKG_MGR="dnf"
    elif command -v yum >/dev/null 2>&1; then
        PKG_MGR="yum"
    elif command -v apt-get >/dev/null 2>&1; then
        PKG_MGR="apt"
    else
        fail "未找到支持的包管理器"
    fi

    success "系统: $OS_NAME $OS_VERSION ($OS_ARCH), 包管理器: $PKG_MGR"
}

############################
# ---------------- 依赖安装 ------------------
install_dependencies() {
    local start=$(date +%s)
    info "安装系统依赖..."

    # 更新系统
    case "$PKG_MGR" in
        dnf|yum)
            run_cmd "$PKG_MGR -y update" "更新系统"
            run_cmd "$PKG_MGR -y install epel-release" "安装EPEL仓库"
            ;;
        apt)
            run_cmd "apt-get -y update" "更新包列表"
            run_cmd "apt-get -y upgrade" "升级系统"
            ;;
    esac

    # 安装crypt相关依赖
    info "安装crypt和libgcrypt依赖"
    case "$PKG_MGR" in
        dnf|yum)
            pkg_install "crypt* libgcrypt*"
            ;;
        apt)
            pkg_install "libcrypt* libgcrypt*"
            ;;
    esac

    # 基础工具 - 单行定义避免换行问题
    pkg_group_install "基础工具" "wget curl git make cmake automake autoconf libtool pkg-config gcc gcc-c++ g++ bison re2c flex patch unzip zip"

    # 内核开发包（仅RedHat系）
    if [ "$PKG_MGR" = "dnf" ] || [ "$PKG_MGR" = "yum" ]; then
        pkg_group_install "内核开发" "kernel-devel kernel-headers"
    fi

    # PHP编译依赖 - 按功能分组安装
    if [ "$PKG_MGR" = "apt" ]; then
        pkg_group_install "PHP基础依赖" "libxml2-dev libssl-dev libcurl4-openssl-dev libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev"
        pkg_group_install "PHP扩展依赖" "libzip-dev zlib1g-dev libsqlite3-dev libonig-dev libicu-dev libxslt1-dev libgmp-dev libbz2-dev"
        pkg_group_install "PHP高级依赖" "libreadline-dev libldap2-dev unixodbc-dev libtidy-dev libsodium-dev libargon2-dev libpq-dev libpspell-dev"
    else
        pkg_group_install "PHP基础依赖" "libxml2-devel openssl-devel curl-devel libjpeg-turbo-devel libpng-devel libwebp-devel freetype-devel"
        pkg_group_install "PHP扩展依赖" "libzip-devel zlib-devel sqlite-devel oniguruma-devel libicu-devel libxslt-devel gmp-devel bzip2-devel"
        pkg_group_install "PHP高级依赖" "readline-devel openldap-devel unixODBC-devel libtidy-devel libsodium-devel"
    fi

    success "系统依赖安装完成 $(timer $start)"
}

############################
# ---------------- PHP安装函数 ------------------
download_php() {
    local start=$(date +%s)
    info "下载PHP源码..."

    mkdir -p "$SRC_DIR" && cd "$SRC_DIR"
    local php_archive="php-${PHP_VERSION}.tar.gz"

    for mirror in "${PHP_MIRRORS[@]}"; do
        info "尝试下载: $(basename "$mirror")"

        rm -f "$php_archive" 2>/dev/null || true

        if command -v wget >/dev/null 2>&1; then
            if run_cmd "wget -c --tries=3 --timeout=30 '$mirror' -O '$php_archive'" "下载PHP"; then
                [ -s "$php_archive" ] && break
            fi
        elif command -v curl >/dev/null 2>&1; then
            if run_cmd "curl -L --retry 3 --connect-timeout 30 '$mirror' -o '$php_archive'" "下载PHP"; then
                [ -s "$php_archive" ] && break
            fi
        fi

        warn "下载失败: $mirror"
    done

    [ -s "$php_archive" ] || fail "所有下载源都失败"
    success "PHP下载成功 $(timer $start)"
}

extract_php() {
    info "解压PHP源码..."
    cd "$SRC_DIR"

    [ -s "php-${PHP_VERSION}.tar.gz" ] || fail "PHP源码包不存在"
    run_cmd "tar -xzf 'php-${PHP_VERSION}.tar.gz'" "解压PHP"

    PHP_SRC_DIR="$SRC_DIR/php-${PHP_VERSION}"
    [ -d "$PHP_SRC_DIR" ] || PHP_SRC_DIR="$SRC_DIR/$(tar -tf "php-${PHP_VERSION}.tar.gz" | head -1 | cut -d/ -f1)"
    [ -d "$PHP_SRC_DIR" ] || fail "无法确定PHP源码目录"

    cd "$PHP_SRC_DIR" || fail "进入PHP源码目录失败"
    success "PHP源码解压完成"
}

configure_php() {
    local start=$(date +%s)
    info "配置PHP编译选项..."

    cd "$PHP_SRC_DIR" || fail "进入PHP源码目录失败"

    [ -f buildconf ] && run_cmd "./buildconf --force" "运行buildconf"

    local configure_cmd="./configure \
        --prefix=$PHP_PREFIX \
        --with-config-file-path=$PHP_PREFIX/lib \
        --with-config-file-scan-dir=$PHP_PREFIX/lib/conf.d \
        --enable-fpm \
        --with-fpm-user=$WWW_USER \
        --with-fpm-group=$WWW_USER \
        --with-libxml \
        --with-openssl \
        --with-kerberos \
        --with-system-ciphers \
        --with-mysqli=mysqlnd \
        --with-mysql-sock \
        --enable-pdo \
        --with-pdo-mysql=mysqlnd \
        --with-pdo-sqlite \
        --with-zlib \
        --enable-bcmath \
        --with-bz2 \
        --enable-calendar \
        --with-curl \
        --enable-exif \
        --enable-fileinfo \
        --enable-gd \
        --with-freetype \
        --with-gettext \
        --with-mhash \
        --with-iconv \
        --with-imap-ssl \
        --enable-intl \
        --with-ldap \
        --with-ldap-sasl \
        --enable-mbstring \
        --enable-mbregex \
        --enable-opcache \
        --enable-pcntl \
        --enable-session \
        --enable-simplexml \
        --enable-shmop \
        --enable-soap \
        --enable-sockets \
        --with-sodium \
        --enable-sysvmsg \
        --enable-sysvsem \
        --enable-sysvshm \
        --with-tidy \
        --enable-tokenizer \
        --enable-xml \
        --with-xsl \
        --with-zip \
        --enable-mysqlnd \
        --with-pear \
        --with-jpeg \
        --with-webp \
        --with-libdir=lib64 \
        --enable-cli \
        --enable-static"

    run_cmd_with_retry "$configure_cmd" "配置PHP" 3 5
    success "PHP配置成功 $(timer $start)"
}

build_php() {
    local start=$(date +%s)
    info "编译PHP..."

    cd "$PHP_SRC_DIR" || fail "进入PHP源码目录失败"

    run_cmd "make clean" "清理编译" || true
    run_cmd_with_retry "make -j '$MAKE_JOBS'" "编译PHP" 2 10
    run_cmd "make install" "安装PHP"

    success "PHP编译安装完成 $(timer $start)"
}

setup_php_config() {
    info "配置PHP环境..."

    mkdir -p "$PHP_PREFIX/lib" "$PHP_PREFIX/etc" "$PHP_PREFIX/var/log" "$PHP_PREFIX/var/run"
    PHP_INI_FILE="$PHP_PREFIX/lib/php.ini"

    # 创建php.ini
    if [ ! -f "$PHP_INI_FILE" ]; then
        cat > "$PHP_INI_FILE" <<EOF
[PHP]
engine = On
precision = 14
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
log_errors = On
date.timezone = Asia/Shanghai

[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16

memory_limit = $PHP_MEMORY_LIMIT
max_execution_time = $PHP_MAX_EXECUTION_TIME
post_max_size = $PHP_POST_MAX_SIZE
upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE
EOF
        success "php.ini已创建"
    fi

    # 创建php-fpm配置
    if [ ! -f "$PHP_PREFIX/etc/php-fpm.conf" ]; then
        cat > "$PHP_PREFIX/etc/php-fpm.conf" <<EOF
[global]
pid = $PHP_PREFIX/var/run/php-fpm.pid
error_log = $PHP_PREFIX/var/log/php-fpm.log

[www]
user = $WWW_USER
group = $WWW_USER
listen = 127.0.0.1:9000
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
EOF
        success "php-fpm.conf已创建"
    fi

    # 创建符号链接
    for binary in php phpize pear pecl; do
        [ -f "$PHP_PREFIX/bin/$binary" ] && ln -sf "$PHP_PREFIX/bin/$binary" "/usr/local/bin/$binary"
    done

    # 添加到PATH
    if ! grep -q "$PHP_PREFIX/bin" "/etc/profile"; then
        echo "export PATH=$PHP_PREFIX/bin:\$PATH" >> "/etc/profile"
    fi

    export PATH="$PHP_PREFIX/bin:$PATH"
    success "PHP配置完成"
}

create_phpfpm_service() {
    info "创建PHP-FPM服务..."

    cat > "/etc/systemd/system/php-fpm.service" <<EOF
[Unit]
Description=PHP FastCGI Process Manager
After=network.target

[Service]
Type=simple
PIDFile=$PHP_PREFIX/var/run/php-fpm.pid
ExecStart=$PHP_PREFIX/sbin/php-fpm --nodaemonize --fpm-config $PHP_PREFIX/etc/php-fpm.conf
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF

    run_cmd "systemctl daemon-reload" "重载systemd"

    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then
        run_cmd "systemctl enable php-fpm" "启用PHP-FPM"
        run_cmd "systemctl start php-fpm" "启动PHP-FPM"
    fi

    success "PHP-FPM服务已创建"
}

install_php_extensions() {
    [ "${INSTALL_PHP_EXTENSIONS,,}" != "yes" ] && return 0

    info "安装PHP扩展..."
    export PATH="$PHP_PREFIX/bin:$PATH"

    # Redis扩展
    if command -v pecl >/dev/null 2>&1; then
        run_cmd "pecl install redis" "安装Redis扩展" && echo "extension=redis.so" >> "$PHP_INI_FILE"
    fi

    # Imagick扩展
    if [ "$PKG_MGR" = "apt" ]; then
        pkg_install "libmagickwand-dev libmagickcore-dev"
    else
        pkg_install "ImageMagick-devel"
    fi
    command -v pecl >/dev/null && run_cmd "pecl install imagick" "安装Imagick扩展" && echo "extension=imagick.so" >> "$PHP_INI_FILE"

    success "PHP扩展安装完成"
}

############################
# ---------------- 其他组件安装 ------------------
install_composer() {
    [ "${INSTALL_COMPOSER,,}" != "yes" ] && return 0

    info "安装Composer..."
    export PATH="$PHP_PREFIX/bin:$PATH"

    if command -v curl >/dev/null 2>&1; then
        run_cmd "curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php" "下载Composer"
        run_cmd "php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer" "安装Composer"
        run_cmd "chmod +x /usr/local/bin/composer" "设置权限"
        success "Composer安装完成"
    fi
}

install_mysql() {
    [ "${INSTALL_MYSQL,,}" != "yes" ] && return 0

    info "安装MySQL..."
    case "$PKG_MGR" in
        apt) pkg_install "mysql-server mysql-client" ;;
        *) pkg_install "mysql-community-server" ;;
    esac

    [ "${AUTO_START_SERVICES,,}" = "yes" ] && run_cmd "systemctl enable mysqld" "启用MySQL" && run_cmd "systemctl start mysqld" "启动MySQL"
    success "MySQL安装完成"
}

install_redis() {
    [ "${INSTALL_REDIS,,}" != "yes" ] && return 0

    info "安装Redis..."
    [ "$PKG_MGR" = "apt" ] && pkg_install "redis-server" || pkg_install "redis"

    [ "${AUTO_START_SERVICES,,}" = "yes" ] && run_cmd "systemctl enable redis" "启用Redis" && run_cmd "systemctl start redis" "启动Redis"
    success "Redis安装完成"
}

install_nginx() {
    [ "${INSTALL_NGINX,,}" != "yes" ] && return 0

    info "安装Nginx..."
    pkg_install "nginx"

    [ "${AUTO_START_SERVICES,,}" = "yes" ] && run_cmd "systemctl enable nginx" "启用Nginx" && run_cmd "systemctl start nginx" "启动Nginx"
    success "Nginx安装完成"
}

############################
# ---------------- 主函数 ------------------
main() {
    echo ""
    _bold "🚀 开始安装PHP环境栈"
    _bold "⏰ 开始时间: $(date '+%Y-%m-%d %H:%M:%S')"
    echo ""

    [ "$EUID" -ne 0 ] && fail "请使用root权限运行"

    detect_system
    install_dependencies

    if [ "${INSTALL_PHP,,}" = "yes" ]; then
        download_php
        extract_php
        configure_php
        build_php
        setup_php_config
        create_phpfpm_service
        install_php_extensions
    fi

    install_composer
    install_mysql
    install_redis
    install_nginx

    success "🎉 所有组件安装完成！"
    _bold "📊 安装日志: $LOG_FILE"
    _bold "🔧 使用 systemctl status php-fpm 检查服务状态"
}

############################
# ---------------- 异常处理 ------------------
trap 'error "脚本在行 ${LINENO} 被中断"; exit 1' INT TERM
trap 'error "脚本执行错误: ${BASH_COMMAND} (行: ${LINENO})"; exit 1' ERR

# 执行主函数
main "$@"
