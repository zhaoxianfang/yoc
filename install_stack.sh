#!/usr/bin/env bash
#
# industrial_php_stack.sh
# 工业级商业级 PHP 环境安装脚本
# 支持: PHP + 扩展 + MySQL + Redis + Nginx + 安全配置
#
set -euo pipefail
IFS=$'\n\t'
export LANG=C
export LC_ALL=C

############################
# ---------------- 可配置参数 ------------------
# 安装组件开关
INSTALL_PHP="${INSTALL_PHP:-yes}"
INSTALL_MYSQL="${INSTALL_MYSQL:-yes}"
INSTALL_REDIS="${INSTALL_REDIS:-yes}"
INSTALL_NGINX="${INSTALL_NGINX:-yes}"
INSTALL_COMPOSER="${INSTALL_COMPOSER:-yes}"
INSTALL_PHP_EXTENSIONS="${INSTALL_PHP_EXTENSIONS:-yes}"

# 版本配置
PHP_VERSION="${PHP_VERSION:-8.4.12}"
MYSQL_VERSION="${MYSQL_VERSION:-8.4.0}"
REDIS_VERSION="${REDIS_VERSION:-7.2.4}"
NGINX_VERSION="${NGINX_VERSION:-1.28.0}"

# 下载镜像源
PHP_MIRRORS=(
    "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"
    "https://mirrors.aliyun.com/php-distributions/php-${PHP_VERSION}.tar.gz"
    "https://mirrors.cloud.tencent.com/php-distributions/php-${PHP_VERSION}.tar.gz"
)

# 安装路径
PHP_PREFIX="${PHP_PREFIX:-/usr/local/php8}"
MYSQL_PREFIX="${MYSQL_PREFIX:-/usr/local/mysql}"
NGINX_PREFIX="${NGINX_PREFIX:-/usr/local/nginx}"
REDIS_PREFIX="${REDIS_PREFIX:-/usr/local/redis}"

# 安全配置
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-$(openssl rand -base64 24)}"
REMOTE_ADMIN_USER="${REMOTE_ADMIN_USER:-phpadmin}"
REMOTE_ADMIN_PASS="${REMOTE_ADMIN_PASS:-$(openssl rand -base64 24)}"
WWW_USER="${WWW_USER:-www}"
WWW_PASS="${WWW_PASS:-$(openssl rand -base64 24)}"

# 系统配置
SRC_DIR="${SRC_DIR:-/usr/local/src}"
LOG_DIR="${LOG_DIR:-/var/log/php-stack-install}"
LOG_FILE="${LOG_FILE:-$LOG_DIR/install.log}"
ERROR_LOG_FILE="${ERROR_LOG_FILE:-$LOG_DIR/error.log}"
INSTALL_SUMMARY="${INSTALL_SUMMARY:-/data/install_config.log}"
MAKE_JOBS="${MAKE_JOBS:-$(nproc 2>/dev/null || echo 1)}"
AUTO_START_SERVICES="${AUTO_START_SERVICES:-yes}"
MAX_RETRIES="${MAX_RETRIES:-5}"
DOWNLOAD_RETRIES="${DOWNLOAD_RETRIES:-3}"
CLEAN_TEMP="${CLEAN_TEMP:-yes}"
PROFILE_FILE="${PROFILE_FILE:-/etc/profile}"

# 性能优化
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
TMP_FILES=()

# 创建必要的目录
mkdir -p "$SRC_DIR" "$LOG_DIR" "$(dirname "$INSTALL_SUMMARY")"
: > "$LOG_FILE"
: > "$ERROR_LOG_FILE"

############################
# ---------------- 高级输出函数 ------------------
_red() { echo -e "\033[31m$*\033[0m"; }
_green() { echo -e "\033[32m$*\033[0m"; }
_yellow() { echo -e "\033[33m$*\033[0m"; }
_blue() { echo -e "\033[34m$*\033[0m"; }
_magenta() { echo -e "\033[35m$*\033[0m"; }
_cyan() { echo -e "\033[36m$*\033[0m"; }
_bold() { echo -e "\033[1m$*\033[0m"; }
_underline() { echo -e "\033[4m$*\033[0m"; }

# 图标
ICON_INFO="🔵"
ICON_SUCCESS="✅"
ICON_WARN="🟡"
ICON_ERROR="🔴"
ICON_START="🚀"
ICON_CONFIG="⚙️"
ICON_DOWNLOAD="📥"
ICON_BUILD="🔨"
ICON_SECURITY="🔒"

log() {
    echo "[$(date '+%F %T')] $*" | tee -a "$LOG_FILE"
}

info() {
    log "${ICON_INFO} 信息: $*"
    echo -e "${ICON_INFO} \033[34m信息:\033[0m $*"
}

success() {
    log "${ICON_SUCCESS} 成功: $*"
    echo -e "${ICON_SUCCESS} \033[32m成功:\033[0m $*"
}

warn() {
    log "${ICON_WARN} 警告: $*"
    echo -e "${ICON_WARN} \033[33m警告:\033[0m $*" >&2
}

error() {
    log "${ICON_ERROR} 错误: $*"
    echo -e "${ICON_ERROR} \033[31m错误:\033[0m $*" >&2
    echo "$(date '+%F %T') - ERROR: $*" >> "$ERROR_LOG_FILE"
}

fail() {
    local msg="${1:-未知错误}"
    local line="${2:-${BASH_LINENO[0]}}"
    error "$msg (行号: $line)"
    error "详细日志: $LOG_FILE"
    error "错误日志: $ERROR_LOG_FILE"
    exit 1
}

# 计时器
timer() {
    local start=$1
    local end=$(date +%s)
    local duration=$((end - start))
    local hours=$((duration / 3600))
    local minutes=$(( (duration % 3600) / 60 ))
    local seconds=$((duration % 60))

    if [ $hours -gt 0 ]; then
        echo "${hours}小时${minutes}分${seconds}秒"
    elif [ $minutes -gt 0 ]; then
        echo "${minutes}分${seconds}秒"
    else
        echo "${seconds}秒"
    fi
}

############################
# ---------------- 高级加载动画 ------------------
_spinner() {
    local pid=$1
    local msg="$2"
    local type="${3:-professional}"
    local frames=()

    case "$type" in
        "professional")
            frames=("▰▱▱▱▱▱▱" "▰▰▱▱▱▱▱" "▰▰▰▱▱▱▱" "▰▰▰▰▱▱▱" "▰▰▰▰▰▱▱" "▰▰▰▰▰▰▱" "▰▰▰▰▰▰▰" "▰▰▰▰▰▰▱" "▰▰▰▰▰▱▱" "▰▰▰▰▱▱▱" "▰▰▰▱▱▱▱" "▰▰▱▱▱▱▱")
            ;;
        "circle")
            frames=("◐" "◓" "◑" "◒")
            ;;
        "dots")
            frames=("⣾" "⣽" "⣻" "⢿" "⡿" "⣟" "⣯" "⣷")
            ;;
        "arrow")
            frames=("➞" "➟" "➠" "➡" "➠" "➟")
            ;;
        *)
            frames=("▏" "▎" "▍" "▌" "▋" "▊" "▉" "█" "▉" "▊" "▋" "▌" "▍" "▎")
            ;;
    esac

    local i=0
    printf "⏳ %s " "$msg"
    while kill -0 "$pid" 2>/dev/null; do
        i=$(( (i+1) % ${#frames[@]} ))
        printf "\r⏳ %s %s" "$msg" "${frames[i]}"
        sleep 0.08
    done
    printf "\r✅ %s 完成\n" "$msg"
}

############################
# ---------------- 高级命令执行 ------------------
run_cmd() {
    local cmd="$1"
    local desc="${2:-执行命令}"
    local spinner="${3:-professional}"
    local attempt="${4:-1}"
    local retry_delay="${5:-2}"

    local start_time=$(date +%s)
    log "执行命令 (尝试 $attempt): $cmd"

    # 执行命令
    eval "$cmd" >>"$LOG_FILE" 2>>"$ERROR_LOG_FILE" &
    local pid=$!

    # 显示加载动画
    _spinner "$pid" "$desc" "$spinner"

    wait "$pid"
    local rc=$?
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))

    if [ $rc -ne 0 ]; then
        log "命令失败 (耗时: ${duration}s, 退出码: $rc): $cmd"
        return 1
    else
        log "命令成功 (耗时: ${duration}s): $cmd"
        return 0
    fi
}

run_cmd_with_retry() {
    local cmd="$1"
    local desc="${2:-执行命令}"
    local spinner="${3:-professional}"
    local max_retries="${4:-$MAX_RETRIES}"
    local retry_delay="${5:-2}"

    local attempt=1
    while [ $attempt -le $max_retries ]; do
        if run_cmd "$cmd" "$desc (尝试 $attempt/$max_retries)" "$spinner" "$attempt" "$retry_delay"; then
            return 0
        fi

        if [ $attempt -lt $max_retries ]; then
            warn "命令失败，${retry_delay}秒后重试..."
            sleep $retry_delay
        fi

        attempt=$((attempt + 1))
    done

    error "命令在 $max_retries 次尝试后仍然失败: $cmd"
    return 1
}

############################
# ---------------- 系统检测 ------------------
detect_system() {
    info "检测系统环境..."

    # 系统信息
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS_ID="${ID:-unknown}"
        OS_NAME="${NAME:-unknown}"
        OS_VERSION="${VERSION_ID:-unknown}"
    else
        fail "无法检测操作系统"
    fi

    # 架构信息
    OS_ARCH=$(uname -m)
    if [ "$OS_ARCH" = "x86_64" ]; then
        OS_ARCH="x64"
    fi

    # 包管理器检测
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
# ---------------- 安全基础配置 ------------------
configure_security() {
    info "配置系统安全基础..."

    # 禁用 SELinux (生产环境请根据实际情况调整)
    if command -v setenforce >/dev/null 2>&1; then
        run_cmd "setenforce 0" "临时禁用 SELinux" "simple" || warn "禁用 SELinux 失败"
    fi

    # 配置防火墙
    if command -v firewall-cmd >/dev/null 2>&1; then
        run_cmd "firewall-cmd --permanent --add-service=http" "允许 HTTP" "simple"
        run_cmd "firewall-cmd --permanent --add-service=https" "允许 HTTPS" "simple"
        run_cmd "firewall-cmd --permanent --add-port=9000/tcp" "允许 PHP-FPM" "simple"
        run_cmd "firewall-cmd --reload" "重载防火墙" "simple"
    fi

    success "安全基础配置完成"
}

############################
# ---------------- 依赖安装 ------------------
install_dependencies() {
    local start=$(date +%s)
    info "安装系统依赖..."

    # 更新系统
    case "$PKG_MGR" in
        dnf)
            run_cmd "dnf -y update" "更新系统" "professional"
            run_cmd "dnf -y install epel-release" "安装 EPEL" "simple"
            ;;
        yum)
            run_cmd "yum -y update" "更新系统" "professional"
            run_cmd "yum -y install epel-release" "安装 EPEL" "simple"
            ;;
        apt)
            run_cmd "apt-get -y update" "更新包列表" "professional"
            run_cmd "apt-get -y upgrade" "升级系统" "professional"
            ;;
    esac

    # 安装 crypt 相关依赖
    info "安装 crypt 和 libgcrypt 相关依赖..."
    case "$PKG_MGR" in
        dnf|yum)
            run_cmd_with_retry "$PKG_MGR -y install crypt*" "安装 crypt 依赖" "dots"
            run_cmd_with_retry "$PKG_MGR -y install libgcrypt*" "安装 libgcrypt 依赖" "dots"
            ;;
        apt)
            run_cmd_with_retry "apt-get -y install libcrypt*" "安装 crypt 依赖" "dots"
            run_cmd_with_retry "apt-get -y install libgcrypt*" "安装 libgcrypt 依赖" "dots"
            ;;
    esac

    # 通用开发工具
    local dev_tools=(
        wget curl git make cmake automake autoconf libtool pkg-config
        # gcc gcc-c++ g++ kernel-devel kernel-headers
        # bison re2c flex patch unzip zip
    )

    # PHP 编译依赖
    local php_deps=()
    if [ "$PKG_MGR" = "apt" ]; then
        php_deps=(
            # libxml2-dev libssl-dev libcurl4-openssl-dev libjpeg-dev
            # libpng-dev libwebp-dev libfreetype6-dev libzip-dev zlib1g-dev
            # libsqlite3-dev libonig-dev libicu-dev libxslt1-dev libgmp-dev
            # libbz2-dev libreadline-dev libldap2-dev unixodbc-dev libtidy-dev
            # libsodium-dev libargon2-dev libpq-dev libpspell-dev libenchant-2-dev
            # libc-client-dev libkrb5-dev libsasl2-dev libsnmp-dev libedit-dev
            # libmm-dev libevent-dev librabbitmq-dev libgearman-dev libmemcached-dev
            # libyaml-dev libmongoc-dev libvirt-dev libcap-dev libffi-dev
            # libpng16-16 libjpeg62-turbo libwebp6 libfreetype6 libzip4
            yum-utils gcc gcc-c++ autoconf libtool perl perl-devel libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel openldap openldap-devel openldap-clients freetype freetype-devel libxml2 libxml2-devel sqlite-devel zlib zlib-devel curl curl-devel pcre pcre-devel gd gd-devel expat-devel libicu-devel bzip2 bzip2-devel python3 python3-devel libwebp-devel make libzstd-devel wget oniguruma oniguruma-devel zstd glibc-headers krb5-devel libzip libzip-devel libxslt libxslt-devel openssl openssl-devel libsodium-devel glib2-devel cairo-devel gmp-devel libevent-devel readline-devel net-snmp-devel aspell-devel unixODBC-devel libc-client-devel libXpm-devel enchant-devel php-ldap automake kernel keyutils patch tidy epel-release libtidy libtidy-devel
        )
    else
        php_deps=(
            # libxml2-devel openssl-devel curl-devel libjpeg-turbo-devel
            # libpng-devel libwebp-devel freetype-devel libzip-devel zlib-devel
            # sqlite-devel oniguruma-devel libicu-devel libxslt-devel gmp-devel
            # bzip2-devel readline-devel openldap-devel unixODBC-devel libtidy-devel
            # libsodium-devel argon2-devel postgresql-devel pspell-devel enchant-devel
            # libc-client-devel krb5-devel cyrus-sasl-devel net-snmp-devel libedit-devel
            # libmm-devel libevent-devel rabbitmq-c-devel gearmand-devel libmemcached-devel
            # libyaml-devel mongo-c-driver-devel libvirt-devel libcap-devel libffi-devel
            yum-utils gcc gcc-c++ autoconf libtool perl perl-devel libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel openldap openldap-devel openldap-clients freetype freetype-devel libxml2 libxml2-devel sqlite-devel zlib zlib-devel curl curl-devel pcre pcre-devel gd gd-devel expat-devel libicu-devel bzip2 bzip2-devel python3 python3-devel libwebp-devel make libzstd-devel wget oniguruma oniguruma-devel zstd glibc-headers krb5-devel libzip libzip-devel libxslt libxslt-devel openssl openssl-devel libsodium-devel glib2-devel cairo-devel gmp-devel libevent-devel readline-devel net-snmp-devel aspell-devel unixODBC-devel libc-client-devel libXpm-devel enchant-devel php-ldap automake kernel keyutils patch tidy epel-release libtidy libtidy-devel

        )
    fi

    # 安装所有依赖
    # pkg_install "${dev_tools[@]}" "${php_deps[@]}"
    # pkg_install "${php_deps[@]}"

    success "系统依赖安装完成 $(timer $start)"
}

pkg_install() {
    local pkgs=("$@")
    info "安装软件包: ${pkgs[*]}"

    case "$PKG_MGR" in
        dnf)
            run_cmd_with_retry "dnf -y install --allowerasing ${pkgs[*]}" "安装软件包" "dots"
            ;;
        yum)
            if ! run_cmd "yum -y install --allowerasing ${pkgs[*]}" "安装软件包" "dots"; then
                warn "使用 --allowerasing 失败，尝试普通安装"
                run_cmd_with_retry "yum -y install ${pkgs[*]}" "安装软件包" "dots"
            fi
            ;;
        apt)
            run_cmd "apt-get -y update" "更新包列表" "simple"
            run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install ${pkgs[*]}" "安装软件包" "dots"
            ;;
    esac
}

############################
# ---------------- PHP 安装 ------------------
download_php() {
    local start=$(date +%s)
    info "下载 PHP 源码..."

    mkdir -p "$SRC_DIR" && cd "$SRC_DIR"
    local php_archive="php-${PHP_VERSION}.tar.gz"

    # 尝试多个镜像源
    for mirror in "${PHP_MIRRORS[@]}"; do
        info "尝试从镜像下载: $(basename "$mirror")"

        if command -v wget >/dev/null 2>&1; then
            if run_cmd_with_retry "wget -c --tries=3 --timeout=30 '$mirror' -O '$php_archive'" "下载 PHP" "dots" "$DOWNLOAD_RETRIES" 3; then
                if [ -s "$php_archive" ]; then
                    success "PHP 下载成功 $(timer $start)"
                    return 0
                fi
            fi
        else
            if run_cmd_with_retry "curl -L --retry 3 --connect-timeout 30 '$mirror' -o '$php_archive'" "下载 PHP" "dots" "$DOWNLOAD_RETRIES" 3; then
                if [ -s "$php_archive" ]; then
                    success "PHP 下载成功 $(timer $start)"
                    return 0
                fi
            fi
        fi

        warn "下载失败: $mirror"
        rm -f "$php_archive" 2>/dev/null || true
    done

    fail "所有 PHP 下载源都失败"
}

extract_php() {
    info "解压 PHP 源码..."
    cd "$SRC_DIR"

    if [ ! -s "php-${PHP_VERSION}.tar.gz" ]; then
        fail "PHP 源码包不存在或为空"
    fi

    run_cmd "tar -xzf 'php-${PHP_VERSION}.tar.gz'" "解压 PHP" "professional"

    PHP_SRC_DIR="$SRC_DIR/php-${PHP_VERSION}"
    if [ ! -d "$PHP_SRC_DIR" ]; then
        local actual_dir=$(tar -tf "php-${PHP_VERSION}.tar.gz" | head -n1 | cut -d/ -f1)
        PHP_SRC_DIR="$SRC_DIR/$actual_dir"
    fi

    if [ ! -d "$PHP_SRC_DIR" ]; then
        fail "无法确定 PHP 源码目录"
    fi

    cd "$PHP_SRC_DIR" || fail "进入 PHP 源码目录失败"
    success "PHP 源码解压完成"
}

configure_php() {
    local start=$(date +%s)
    info "配置 PHP 编译选项..."

    cd "$PHP_SRC_DIR" || fail "进入 PHP 源码目录失败"

    # 运行 buildconf
    if [ -f buildconf ]; then
        run_cmd "./buildconf --force" "运行 buildconf" "professional"
    fi

    # 完整的 PHP 配置选项
    local CONFIGURE_OPTS=(
        "--prefix=$PHP_PREFIX"
        "--with-config-file-path=$PHP_PREFIX/lib"
        "--with-config-file-scan-dir=$PHP_PREFIX/lib/conf.d"
        "--enable-fpm"
        "--with-fpm-user=$WWW_USER"
        "--with-fpm-group=$WWW_USER"
        "--with-libxml"
        "--with-openssl"
        "--with-kerberos"
        "--with-system-ciphers"
        "--with-mysqli=mysqlnd"
        "--with-mysql-sock"
        "--enable-pdo"
        "--with-pdo-mysql=mysqlnd"
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
        "--with-gettext"
        "--with-mhash"
        "--with-iconv"
        "--with-imap-ssl"
        "--enable-intl"
        "--with-ldap"
        "--with-ldap-sasl"
        "--enable-mbstring"
        "--enable-mbregex"
        "--enable-opcache"
        "--enable-pcntl"
        "--enable-session"
        "--enable-simplexml"
        "--enable-shmop"
        "--enable-soap"
        "--enable-sockets"
        "--with-sodium"
        "--enable-sysvmsg"
        "--enable-sysvsem"
        "--enable-sysvshm"
        "--with-tidy"
        "--enable-tokenizer"
        "--enable-xml"
        "--with-xsl"
        "--with-zip"
        "--enable-mysqlnd"
        "--with-pear"
        "--with-jpeg"
        "--with-webp"
        "--with-libdir=lib64"
        "--enable-cli"
        "--enable-static"
        "--with-password-argon2"
        "--with-sodium"
        "--with-pgsql"
        "--with-pspell"
        "--with-enchant"
        "--with-readline"
        "--with-snmp"
        "--with-ffi"
        "--with-zlib-dir"
    )

    # 配置尝试
    local attempt=1
    while [ $attempt -le $MAX_RETRIES ]; do
        info "PHP 配置尝试 $attempt/$MAX_RETRIES"

        if run_cmd "./configure ${CONFIGURE_OPTS[*]}" "配置 PHP" "professional"; then
            success "PHP 配置成功 $(timer $start)"
            return 0
        fi

        warn "PHP 配置失败，尝试安装缺失依赖"
        install_missing_dependencies

        attempt=$((attempt + 1))
        if [ $attempt -le $MAX_RETRIES ]; then
            warn "等待 5 秒后重试..."
            sleep 5
        fi
    done

    fail "PHP 配置多次失败"
}

install_missing_dependencies() {
    info "安装可能缺失的开发依赖..."

    local missing_deps=()
    if grep -q "not found" "$LOG_FILE" || grep -q "error:" "$LOG_FILE"; then
        # 根据错误信息安装缺失的包
        case "$PKG_MGR" in
            apt)
                missing_deps=(
                    libxml2-dev libssl-dev libcurl4-openssl-dev
                    libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev
                    libzip-dev zlib1g-dev libsqlite3-dev libonig-dev
                    libicu-dev libxslt1-dev libgmp-dev libbz2-dev
                    libreadline-dev libldap2-dev unixodbc-dev libtidy-dev
                    libsodium-dev libargon2-dev libpq-dev libpspell-dev
                    libenchant-2-dev libc-client-dev libkrb5-dev
                    libsasl2-dev libsnmp-dev libedit-dev libevent-dev
                    libyaml-dev libffi-dev
                )
                ;;
            *)
                missing_deps=(
                    libxml2-devel openssl-devel curl-devel
                    libjpeg-turbo-devel libpng-devel libwebp-devel
                    freetype-devel libzip-devel zlib-devel sqlite-devel
                    oniguruma-devel libicu-devel libxslt-devel gmp-devel
                    bzip2-devel readline-devel openldap-devel unixODBC-devel
                    libtidy-devel libsodium-devel argon2-devel postgresql-devel
                    pspell-devel enchant-devel libc-client-devel krb5-devel
                    cyrus-sasl-devel net-snmp-devel libedit-devel libevent-devel
                    libyaml-devel libffi-devel
                )
                ;;
        esac

        pkg_install "${missing_deps[@]}"
    fi
}

build_php() {
    local start=$(date +%s)
    info "编译 PHP..."

    cd "$PHP_SRC_DIR" || fail "进入 PHP 源码目录失败"

    # 清理之前的编译
    run_cmd "make clean" "清理编译" "simple" || true

    # 编译
    run_cmd_with_retry "make -j '$MAKE_JOBS'" "编译 PHP" "professional" 2 || fail "PHP 编译失败"

    # 安装
    run_cmd "make install" "安装 PHP" "professional" || fail "PHP 安装失败"

    success "PHP 编译安装完成 $(timer $start)"
}

setup_php_config() {
    info "配置 PHP..."

    mkdir -p "$PHP_PREFIX/lib" "$PHP_PREFIX/etc" "$PHP_PREFIX/var/log" "$PHP_PREFIX/var/run"
    PHP_INI_FILE="$PHP_PREFIX/lib/php.ini"

    # 创建 php.ini
    if [ ! -f "$PHP_INI_FILE" ]; then
        cat > "$PHP_INI_FILE" <<EOF
[PHP]
engine = On
short_open_tag = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
serialize_precision = -1
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
log_errors = On
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
track_errors = Off
html_errors = Off
error_log = $PHP_PREFIX/var/log/php_errors.log

[Date]
date.timezone = Asia/Shanghai

[filter]

[iconv]

[intl]

[sqlite]

[sqlite3]

[Pcre]

[Pdo]

[Pdo_mysql]
pdo_mysql.default_socket =

[Phar]

[mail function]
SMTP = localhost
smtp_port = 25
sendmail_path = /usr/sbin/sendmail -t -i
mail.add_x_header = On

[SQL]
sql.safe_mode = Off

[ODBC]
odbc.allow_persistent = On
odbc.check_persistent = On
odbc.max_persistent = -1
odbc.max_links = -1
odbc.defaultlrl = 4096
odbc.defaultbinmode = 1

[MySQL]
mysql.allow_local_infile = On
mysql.allow_persistent = On
mysql.cache_size = 2000
mysql.max_persistent = -1
mysql.max_links = -1
mysql.default_port =
mysql.default_socket =
mysql.default_host =
mysql.default_user =
mysql.default_password =
mysql.connect_timeout = 60
mysql.trace_mode = Off

[MySQLi]
mysqli.max_persistent = -1
mysqli.allow_persistent = On
mysqli.max_links = -1
mysqli.default_port = 3306
mysqli.default_socket =
mysqli.default_host =
mysqli.default_user =
mysqli.default_pw =
mysqli.reconnect = Off

[mysqlnd]
mysqlnd.collect_statistics = On
mysqlnd.collect_memory_statistics = On

[PostgreSQL]
pgsql.allow_persistent = On
pgsql.auto_reset_persistent = Off
pgsql.max_persistent = -1
pgsql.max_links = -1
pgsql.ignore_notice = 0
pgsql.log_notice = 0

[Sybase]
sybase.allow_persistent = On
sybase.max_persistent = -1
sybase.max_links = -1
sybase.min_error_severity = 10
sybase.min_message_severity = 10
sybase.compatability_mode = Off

[bcmath]
bcmath.scale = 0

[Session]
session.save_handler = files
session.save_path = "$PHP_PREFIX/var/session"
session.use_strict_mode = 1
session.use_cookies = 1
session.use_only_cookies = 1
session.name = PHPSESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain =
session.cookie_secure = 1
session.cookie_httponly = 1
session.serialize_handler = php
session.gc_probability = 1
session.gc_divisor = 1000
session.gc_maxlifetime = 1440
session.referer_check =
session.entropy_length = 0
session.cache_limiter = nocache
session.cache_expire = 180

[Assertion]

[Tidy]
tidy.clean_output = 0

[soap]
soap.wsdl_cache_enabled = 1
soap.wsdl_cache_dir = /tmp
soap.wsdl_cache_ttl = 86400
soap.wsdl_cache_limit = 5

[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=1
opcache.jit=1255
opcache.jit_buffer_size=256M

[CLI Server]

[Redis]

[Zend Opcache]

; 性能优化
memory_limit = $PHP_MEMORY_LIMIT
max_execution_time = $PHP_MAX_EXECUTION_TIME
max_input_time = 300
max_input_vars = 5000
post_max_size = $PHP_POST_MAX_SIZE
upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE

; 安全设置
expose_php = Off
disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,shell_exec,system,exec,passthru,proc_open
disable_classes =
enable_dl = Off
file_uploads = On
allow_url_fopen = Off
allow_url_include = Off

; 路径设置
include_path = ".:$PHP_PREFIX/lib/php"
doc_root =
user_dir =
extension_dir = "$PHP_PREFIX/lib/php/extensions/no-debug-non-zts-20200930"

; 环境变量
variables_order = "GPCS"
request_order = "GP"

; 注册全局变量
register_argc_argv = Off
auto_globals_jit = On

; 其他设置
default_mimetype = "text/html"
default_charset = "UTF-8"
unicode.runtime_encoding = UTF-8
unicode.output_encoding = UTF-8
unicode.from_error_mode = U_INVALID_SUBSTITUTE
unicode.from_error_subst_char = 0xFFFD
EOF
        success "php.ini 已创建"
    else
        info "php.ini 已存在，跳过创建"
    fi

    # 创建 php-fpm 配置
    if [ ! -f "$PHP_PREFIX/etc/php-fpm.conf" ]; then
        cat > "$PHP_PREFIX/etc/php-fpm.conf" <<EOF
[global]
pid = $PHP_PREFIX/var/run/php-fpm.pid
error_log = $PHP_PREFIX/var/log/php-fpm.log
log_level = notice
emergency_restart_threshold = 10
emergency_restart_interval = 1m
process_control_timeout = 10s
daemonize = yes

[www]
user = $WWW_USER
group = $WWW_USER
listen = 127.0.0.1:9000
listen.allowed_clients = 127.0.0.1
listen.owner = $WWW_USER
listen.group = $WWW_USER
listen.mode = 0660
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
pm.process_idle_timeout = 10s
request_terminate_timeout = 300
request_slowlog_timeout = 0
slowlog = $PHP_PREFIX/var/log/php-fpm-slow.log
rlimit_files = 65536
rlimit_core = 0
catch_workers_output = yes
env[HOSTNAME] = \$HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp
php_admin_value[error_log] = $PHP_PREFIX/var/log/php-fpm-www.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = $PHP_MEMORY_LIMIT
php_admin_value[max_execution_time] = $PHP_MAX_EXECUTION_TIME
php_admin_value[upload_max_filesize] = $PHP_UPLOAD_MAX_FILESIZE
php_admin_value[post_max_size] = $PHP_POST_MAX_SIZE
EOF
        success "php-fpm.conf 已创建"
    fi

    # 创建符号链接
    for binary in php phpize pear pecl phar; do
        if [ -f "$PHP_PREFIX/bin/$binary" ]; then
            ln -sf "$PHP_PREFIX/bin/$binary" "/usr/local/bin/$binary" || warn "创建 $binary 符号链接失败"
        fi
    done

    # 添加到 PATH
    if ! grep -q "$PHP_PREFIX/bin" "$PROFILE_FILE" 2>/dev/null; then
        echo "" >> "$PROFILE_FILE"
        echo "# PHP $PHP_VERSION" >> "$PROFILE_FILE"
        echo "export PATH=$PHP_PREFIX/bin:\$PATH" >> "$PROFILE_FILE"
        echo "export PHP_HOME=$PHP_PREFIX" >> "$PROFILE_FILE"
    fi

    # 使环境变量生效
    export PATH="$PHP_PREFIX/bin:$PATH"
    export PHP_HOME="$PHP_PREFIX"

    success "PHP 配置完成"
}

create_phpfpm_service() {
    info "创建 PHP-FPM 系统服务..."

    local service_file="/etc/systemd/system/php-fpm.service"
    cat > "$service_file" <<EOF
[Unit]
Description=PHP FastCGI Process Manager (Custom Build)
After=network.target nss-lookup.target

[Service]
Type=simple
PIDFile=$PHP_PREFIX/var/run/php-fpm.pid
ExecStart=$PHP_PREFIX/sbin/php-fpm --nodaemonize --fpm-config $PHP_PREFIX/etc/php-fpm.conf
ExecReload=/bin/kill -USR2 \$MAINPID
ExecStop=/bin/kill -SIGINT \$MAINPID
Restart=on-failure
RestartSec=5s
TimeoutSec=300
LimitNOFILE=65536
LimitCORE=infinity
Environment=LD_LIBRARY_PATH=$PHP_PREFIX/lib
WorkingDirectory=/
ProtectSystem=full
ProtectHome=true
PrivateTmp=true
NoNewPrivileges=true

[Install]
WantedBy=multi-user.target
EOF

    run_cmd "systemctl daemon-reload" "重载 systemd" "simple"

    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then
        run_cmd "systemctl enable php-fpm.service" "启用 PHP-FPM" "simple"
        run_cmd "systemctl start php-fpm.service" "启动 PHP-FPM" "simple"
        run_cmd "systemctl status php-fpm.service" "检查 PHP-FPM 状态" "simple"
    fi

    success "PHP-FPM 服务已创建"
}

############################
# ---------------- PHP 扩展安装 ------------------
install_php_extensions() {
    if [ "${INSTALL_PHP_EXTENSIONS,,}" != "yes" ]; then
        info "跳过 PHP 扩展安装"
        return 0
    fi

    info "安装 PHP 扩展..."
    export PATH="$PHP_PREFIX/bin:$PATH"

    # 安装 Redis 扩展
    install_redis_extension() {
        info "安装 Redis PHP 扩展..."
        if command -v pecl >/dev/null 2>&1; then
            if run_cmd_with_retry "pecl install redis" "安装 Redis 扩展" "dots" 3; then
                echo "extension=redis.so" >> "$PHP_INI_FILE"
                success "Redis 扩展安装成功"
            else
                warn "Redis 扩展安装失败"
            fi
        else
            warn "pecl 不可用，跳过 Redis 扩展安装"
        fi
    }

    # 安装 Imagick 扩展
    install_imagick_extension() {
        info "安装 Imagick PHP 扩展..."
        # 确保 ImageMagick 开发包已安装
        if [ "$PKG_MGR" = "apt" ]; then
            pkg_install libmagickwand-dev libmagickcore-dev
        else
            pkg_install ImageMagick-devel ImageMagick
        fi

        if command -v pecl >/dev/null 2>&1; then
            if run_cmd_with_retry "pecl install imagick" "安装 Imagick 扩展" "dots" 3; then
                echo "extension=imagick.so" >> "$PHP_INI_FILE"
                success "Imagick 扩展安装成功"
            else
                warn "Imagick 扩展安装失败"
            fi
        fi
    }

    # 安装其他常用扩展
    install_other_extensions() {
        local extensions=(
            "mongodb"
            "xdebug"
            "memcached"
            "yaml"
            "event"
            "swoole"
        )

        for ext in "${extensions[@]}"; do
            info "尝试安装 $ext 扩展..."
            if command -v pecl >/dev/null 2>&1; then
                if run_cmd "pecl install $ext" "安装 $ext 扩展" "dots"; then
                    echo "extension=$ext.so" >> "$PHP_INI_FILE"
                    success "$ext 扩展安装成功"
                else
                    warn "$ext 扩展安装失败"
                fi
            fi
        done
    }

    # 执行扩展安装
    install_redis_extension
    install_imagick_extension
    install_other_extensions

    success "PHP 扩展安装完成"
}

############################
# ---------------- Composer 安装 ------------------
install_composer() {
    if [ "${INSTALL_COMPOSER,,}" != "yes" ]; then
        info "跳过 Composer 安装"
        return 0
    fi

    info "安装 Composer..."

    export PATH="$PHP_PREFIX/bin:$PATH"

    if command -v curl >/dev/null 2>&1; then
        run_cmd "curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php" "下载 Composer" "simple"
        run_cmd "php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer" "安装 Composer" "simple"
        run_cmd "rm /tmp/composer-setup.php" "清理临时文件" "simple"
        run_cmd "chmod +x /usr/local/bin/composer" "设置执行权限" "simple"

        # 配置 Composer
        run_cmd "composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/" "配置 Composer 镜像" "simple"
        run_cmd "composer config -g process-timeout 1200" "设置超时时间" "simple"

        success "Composer 安装完成"
    else
        warn "缺少 curl，无法安装 Composer"
    fi
}

############################
# ---------------- MySQL 安装 ------------------
install_mysql() {
    if [ "${INSTALL_MYSQL,,}" != "yes" ]; then
        info "跳过 MySQL 安装"
        return 0
    fi

    info "安装 MySQL..."

    case "$PKG_MGR" in
        apt)
            # Ubuntu/Debian
            run_cmd "apt-get install -y lsb-release gnupg" "安装依赖" "simple"
            run_cmd "wget -O /tmp/mysql-apt-config.deb https://dev.mysql.com/get/mysql-apt-config_0.8.28-1_all.deb" "下载 MySQL 配置" "simple"
            echo "mysql-apt-config mysql-apt-config/select-server select mysql-8.0" | debconf-set-selections
            run_cmd "DEBIAN_FRONTEND=noninteractive dpkg -i /tmp/mysql-apt-config.deb" "安装 MySQL 配置" "simple"
            run_cmd "apt-get update -y" "更新包列表" "simple"
            run_cmd "DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server mysql-client" "安装 MySQL" "professional"
            run_cmd "rm /tmp/mysql-apt-config.deb" "清理临时文件" "simple"
            ;;
        *)
            # CentOS/RHEL
            run_cmd "rpm -Uvh https://dev.mysql.com/get/mysql80-community-release-el7-11.noarch.rpm" "添加 MySQL 仓库" "simple"
            run_cmd "$PKG_MGR -y install mysql-community-server mysql-community-client" "安装 MySQL" "professional"
            ;;
    esac

    # 启动服务
    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then
        run_cmd "systemctl enable mysqld" "启用 MySQL" "simple"
        run_cmd "systemctl start mysqld" "启动 MySQL" "simple"

        # 获取临时密码并设置新密码
        local temp_pass=""
        local max_wait=30
        local wait_count=0

        info "等待 MySQL 启动..."
        while [ $wait_count -lt $max_wait ]; do
            if systemctl is-active --quiet mysqld; then
                if [ -f /var/log/mysqld.log ]; then
                    temp_pass=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $NF}' | tail -1)
                fi
                if [ -n "$temp_pass" ]; then
                    break
                fi
            fi
            sleep 1
            wait_count=$((wait_count + 1))
        done

        if [ -n "$temp_pass" ]; then
            run_cmd "mysql -uroot -p'$temp_pass' --connect-expired-password -e \"ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASS'; FLUSH PRIVILEGES;\"" "修改 root 密码" "simple"
        else
            # 尝试安全安装
            run_cmd "mysql_secure_installation <<EOF

y
$MYSQL_ROOT_PASS
$MYSQL_ROOT_PASS
y
y
y
y
EOF" "运行 MySQL 安全安装" "simple" || warn "MySQL 安全安装可能失败"
        fi

        # 创建远程管理用户
        run_cmd "mysql -uroot -p'$MYSQL_ROOT_PASS' -e \"CREATE USER IF NOT EXISTS '$REMOTE_ADMIN_USER'@'%' IDENTIFIED BY '$REMOTE_ADMIN_PASS'; GRANT ALL PRIVILEGES ON *.* TO '$REMOTE_ADMIN_USER'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;\"" "创建远程用户" "simple"
    fi

    success "MySQL 安装完成"
}

############################
# ---------------- Redis 安装 ------------------
install_redis() {
    if [ "${INSTALL_REDIS,,}" != "yes" ]; then
        info "跳过 Redis 安装"
        return 0
    fi

    info "安装 Redis..."

    if [ "$PKG_MGR" = "apt" ]; then
        pkg_install redis-server redis-tools
    else
        pkg_install redis
    fi

    # 配置 Redis
    local redis_conf=""
    if [ -f /etc/redis/redis.conf ]; then
        redis_conf="/etc/redis/redis.conf"
    elif [ -f /etc/redis.conf ]; then
        redis_conf="/etc/redis.conf"
    fi

    if [ -n "$redis_conf" ]; then
        # 备份原配置
        run_cmd "cp '$redis_conf' '${redis_conf}.bak'" "备份 Redis 配置" "simple"

        # 修改配置
        run_cmd "sed -i 's/^bind 127.0.0.1/bind 0.0.0.0/' '$redis_conf'" "允许远程访问" "simple"
        run_cmd "sed -i 's/^protected-mode yes/protected-mode no/' '$redis_conf'" "关闭保护模式" "simple"
        run_cmd "sed -i 's/^daemonize no/daemonize yes/' '$redis_conf'" "启用守护进程" "simple"
        run_cmd "echo 'requirepass $REMOTE_ADMIN_PASS' >> '$redis_conf'" "设置密码" "simple"
        run_cmd "echo 'maxmemory 2gb' >> '$redis_conf'" "设置内存限制" "simple"
        run_cmd "echo 'maxmemory-policy allkeys-lru' >> '$redis_conf'" "设置内存策略" "simple"
    fi

    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then
        run_cmd "systemctl enable redis" "启用 Redis" "simple"
        run_cmd "systemctl start redis" "启动 Redis" "simple"
    fi

    success "Redis 安装完成"
}

############################
# ---------------- Nginx 安装 ------------------
install_nginx() {
    if [ "${INSTALL_NGINX,,}" != "yes" ]; then
        info "跳过 Nginx 安装"
        return 0
    fi

    info "安装 Nginx..."

    if [ "$PKG_MGR" = "apt" ]; then
        pkg_install nginx nginx-extras
    else
        pkg_install nginx
    fi

    # 创建 nginx 配置
    if [ -d /etc/nginx/conf.d ]; then
        cat > /etc/nginx/conf.d/php-fpm.conf <<'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /usr/share/nginx/html;
    index index.php index.html index.htm;

    access_log /var/log/nginx/access.log main;
    error_log /var/log/nginx/error.log warn;

    # 安全头
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin" always;

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;

        # 安全设置
        fastcgi_param HTTP_PROXY "";
        fastcgi_param REMOTE_ADDR $remote_addr;
        fastcgi_param REMOTE_PORT $remote_port;
        fastcgi_param SERVER_ADDR $server_addr;
        fastcgi_param SERVER_PORT $server_port;
        fastcgi_param SERVER_NAME $server_name;

        # 超时设置
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 60s;
        fastcgi_read_timeout 60s;
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # 禁止访问常见敏感文件
    location ~* (\.env|\.git|\.svn|composer\.json|composer\.lock|Dockerfile|nginx\.conf|\.htaccess) {
        deny all;
        access_log off;
        log_not_found off;
    }
}
EOF
    fi

    # 测试配置
    run_cmd "nginx -t" "测试 Nginx 配置" "simple"

    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then
        run_cmd "systemctl enable nginx" "启用 Nginx" "simple"
        run_cmd "systemctl start nginx" "启动 Nginx" "simple"
    fi

    success "Nginx 安装完成"
}

############################
# ---------------- 用户创建 ------------------
create_users() {
    info "创建系统用户..."

    # www 用户
    if ! id "$WWW_USER" >/dev/null 2>&1; then
        run_cmd "useradd -r -m -s /bin/bash -U '$WWW_USER'" "创建用户 $WWW_USER" "simple"
        run_cmd "echo '$WWW_USER:$WWW_PASS' | chpasswd" "设置密码" "simple"
    else
        info "用户 $WWW_USER 已存在"
    fi

    # 创建网站目录
    run_cmd "mkdir -p /var/www/html" "创建网站目录" "simple"
    run_cmd "chown -R $WWW_USER:$WWW_USER /var/www/html" "设置目录权限" "simple"

    success "用户创建完成"
}

############################
# ---------------- 清理工作 ------------------
cleanup() {
    if [ "${CLEAN_TEMP,,}" = "yes" ]; then
        info "清理临时文件..."

        # 清理源码目录
        if [ -d "$SRC_DIR" ]; then
            run_cmd "rm -rf '$SRC_DIR/php-${PHP_VERSION}'" "清理 PHP 源码" "simple" || true
            run_cmd "rm -f '$SRC_DIR/php-${PHP_VERSION}.tar.gz'" "清理 PHP 压缩包" "simple" || true
        fi

        # 清理包管理器缓存
        case "$PKG_MGR" in
            dnf|yum)
                run_cmd "$PKG_MGR clean all" "清理包缓存" "simple" || true
                ;;
            apt)
                run_cmd "apt-get autoremove -y" "清理无用包" "simple" || true
                run_cmd "apt-get clean" "清理包缓存" "simple" || true
                ;;
        esac

        success "清理完成"
    else
        info "跳过清理（CLEAN_TEMP=no）"
    fi
}

############################
# ---------------- 安装总结 ------------------
installation_summary() {
    local end_time=$(date +%s)
    local total_time=$((end_time - START_TIME))

    cat > "$INSTALL_SUMMARY" <<EOF
# 🚀 PHP 环境安装总结
安装时间: $(date -d @$START_TIME '+%Y-%m-%d %H:%M:%S')
总耗时: $(timer $START_TIME)

## 📦 安装组件
- PHP: ${PHP_VERSION} (${INSTALL_PHP})
- MySQL: ${MYSQL_VERSION} (${INSTALL_MYSQL})
- Redis: ${REDIS_VERSION} (${INSTALL_REDIS})
- Nginx: ${NGINX_VERSION} (${INSTALL_NGINX})
- Composer: (${INSTALL_COMPOSER})
- PHP 扩展: (${INSTALL_PHP_EXTENSIONS})

## 📁 安装路径
- PHP: $PHP_PREFIX
- MySQL: $MYSQL_PREFIX
- Nginx: $NGINX_PREFIX
- Redis: $REDIS_PREFIX

## 🔧 服务状态
$(if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then
    echo "- PHP-FPM: $(systemctl is-active php-fpm 2>/dev/null || echo '未安装')"
    echo "- MySQL: $(systemctl is-active mysqld 2>/dev/null || echo '未安装')"
    echo "- Redis: $(systemctl is-active redis 2>/dev/null || echo '未安装')"
    echo "- Nginx: $(systemctl is-active nginx 2>/dev/null || echo '未安装')"
else
    echo "- 服务未自动启动 (AUTO_START_SERVICES=no)"
fi)

## 🔐 账户信息
- MySQL root 密码: $MYSQL_ROOT_PASS
- MySQL 远程用户: $REMOTE_ADMIN_USER / $REMOTE_ADMIN_PASS
- Redis 密码: $REMOTE_ADMIN_PASS
- PHP-FPM 用户: $WWW_USER / $WWW_PASS

## 📋 配置文件
- PHP: $PHP_INI_FILE
- PHP-FPM: $PHP_PREFIX/etc/php-fpm.conf
- Nginx: /etc/nginx/conf.d/php-fpm.conf
- MySQL: /etc/my.cnf 或 /etc/mysql/my.cnf
- Redis: $(if [ -f /etc/redis/redis.conf ]; then echo "/etc/redis/redis.conf"; elif [ -f /etc/redis.conf ]; then echo "/etc/redis.conf"; else echo "未知"; fi)

## 🌐 环境变量
- PHP 已添加到 PATH: $PHP_PREFIX/bin

## 🧪 测试命令
- PHP: $PHP_PREFIX/bin/php -v
- MySQL: mysql -u$REMOTE_ADMIN_USER -p'$REMOTE_ADMIN_PASS' -e 'SELECT VERSION();'
- Redis: redis-cli -a '$REMOTE_ADMIN_PASS' PING
- Nginx: curl -I http://localhost
- Composer: composer -V

## 📊 日志文件
- 安装日志: $LOG_FILE
- 错误日志: $ERROR_LOG_FILE
- PHP-FPM: $PHP_PREFIX/var/log/php-fpm.log
- Nginx: /var/log/nginx/error.log

## ⚠️ 重要提示
1. 请立即修改默认密码
2. 检查防火墙配置
3. 配置定期备份
4. 启用监控和日志轮转

安装完成时间: $(date '+%Y-%m-%d %H:%M:%S')
EOF

    # 设置安全权限
    chmod 600 "$INSTALL_SUMMARY"

    success "安装完成！总耗时: $(timer $START_TIME)"
    echo ""
    _bold "📋 安装总结已保存到: $INSTALL_SUMMARY"
    echo ""
    _bold "🔐 请立即查看上述文件并保存账户密码信息！"
    echo ""
    _bold "🚀 接下来您可以："
    _bold "  1. 检查服务状态: systemctl status php-fpm mysql redis nginx"
    _bold "  2. 测试 PHP: php -v"
    _bold "  3. 部署您的应用程序"
}

############################
# ---------------- 主函数 ------------------
main() {
    clear
    echo ""
    _bold "🚀 工业级 PHP 环境安装脚本"
    _bold "📅 开始时间: $(date '+%Y-%m-%d %H:%M:%S')"
    _bold "💻 系统: $(uname -a)"
    echo ""

    # 检查 root 权限
    if [ "$EUID" -ne 0 ]; then
        fail "请使用 root 权限运行此脚本"
    fi

    # 安装过程
    detect_system
    # configure_security 易错
    install_dependencies
    create_users

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
    cleanup
    installation_summary
}

############################
# ---------------- 异常处理 ------------------
handle_error() {
    local line="$1"
    local command="$2"
    local code="${3:-1}"

    error "脚本在行 $line 执行命令 '$command' 时失败 (退出码: $code)"
    error "详细错误请查看: $ERROR_LOG_FILE"
    error "完整日志请查看: $LOG_FILE"

    # 保存错误状态
    echo "[CRITICAL] 失败位置: 行 $line, 命令: $command, 退出码: $code" >> "$ERROR_LOG_FILE"
    echo "[CRITICAL] 系统信息: $OS_NAME $OS_VERSION $OS_ARCH" >> "$ERROR_LOG_FILE"
    echo "[CRITICAL] 时间: $(date '+%F %T')" >> "$ERROR_LOG_FILE"

    exit $code
}

# 设置错误处理
trap 'handle_error ${LINENO} "${BASH_COMMAND}" $?' ERR

# 处理中断信号
trap 'echo ""; warn "安装被用户中断"; exit 1' INT TERM

# 执行主函数
main "$@"
