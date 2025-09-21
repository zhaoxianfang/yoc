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

# 日志函数
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

# 增强的错误输出函数
error() {
    local msg="$1"
    local line="${2:-${BASH_LINENO[0]}}"
    echo -e "\033[41;37m 错误 \033[0m $msg (行号: $line)" >&2
    echo "[ERROR] $(date '+%F %T') - $msg (行号: $line)" >> "$ERROR_LOG_FILE"
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
show_loading() {
    local pid=$1
    local msg="$2"
    local frames=("⣷" "⣯" "⣟" "⡿" "⢿" "⣻" "⣽" "⣾")
    local i=0

    printf "⏳ %s " "$msg"
    while kill -0 "$pid" 2>/dev/null; do
        i=$(( (i+1) % ${#frames[@]} ))
        printf "\r⏳ %s %s" "$msg" "${frames[i]}"
        sleep 0.1
    done
    printf "\r✅ %s 完成\n" "$msg"
}

############################
# ---------------- 高级命令执行 ------------------
run_cmd() {
    local cmd="$1"
    local desc="${2:-执行命令}"
    local attempt="${3:-1}"
    local retry_delay="${4:-2}"

    local start_time=$(date +%s)
    log "执行命令 (尝试 $attempt): $cmd"

    # 执行命令
    eval "$cmd" >>"$LOG_FILE" 2>>"$ERROR_LOG_FILE" &
    local pid=$!

    # 显示加载动画
    show_loading "$pid" "$desc"

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
    local max_retries="${3:-$MAX_RETRIES}"
    local retry_delay="${4:-2}"

    local attempt=1
    while [ $attempt -le $max_retries ]; do
        if run_cmd "$cmd" "$desc (尝试 $attempt/$max_retries)" "$attempt" "$retry_delay"; then
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
#    if command -v setenforce >/dev/null 2>&1; then
##        run_cmd "setenforce 0" "临时禁用 SELinux" || warn "禁用 SELinux 失败"
#    fi

    # 配置防火墙
#    if command -v firewall-cmd >/dev/null 2>&1; then
##        run_cmd "firewall-cmd --permanent --add-service=http" "允许 HTTP"
##        run_cmd "firewall-cmd --permanent --add-service=https" "允许 HTTPS"
##        run_cmd "firewall-cmd --permanent --add-port=9000/tcp" "允许 PHP-FPM"
##        run_cmd "firewall-cmd --reload" "重载防火墙"
#    fi

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
            run_cmd "dnf -y update" "更新系统"
            run_cmd "dnf -y install epel-release" "安装 EPEL"
            ;;
        yum)
            run_cmd "yum -y update" "更新系统"
            run_cmd "yum -y install epel-release" "安装 EPEL"
            ;;
        apt)
            run_cmd "apt-get -y update" "更新包列表"
            run_cmd "apt-get -y upgrade" "升级系统"
            ;;
    esac

    # 安装 crypt 相关依赖
    info "安装 crypt 和 libgcrypt 相关依赖..."
    case "$PKG_MGR" in
        dnf|yum)
            run_cmd_with_retry "$PKG_MGR -y install crypt*" "安装 crypt 依赖"
            run_cmd_with_retry "$PKG_MGR -y install libgcrypt*" "安装 libgcrypt 依赖"
            ;;
        apt)
            run_cmd_with_retry "apt-get -y install libcrypt*" "安装 crypt 依赖"
            run_cmd_with_retry "apt-get -y install libgcrypt*" "安装 libgcrypt 依赖"
            ;;
    esac

    # 修复数组定义问题 - 使用单行数组
    local dev_tools=(wget curl git make cmake automake autoconf libtool pkg-config gcc gcc-c++ g++ kernel-devel kernel-headers bison re2c flex patch unzip zip)

    # PHP 编译依赖 - 修复数组定义问题
    local php_deps=()
    if [ "$PKG_MGR" = "apt" ]; then
        php_deps=(libxml2-dev libssl-dev libcurl4-openssl-dev libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev libzip-dev zlib1g-dev libsqlite3-dev libonig-dev libicu-dev libxslt1-dev libgmp-dev libbz2-dev libreadline-dev libldap2-dev unixodbc-dev libtidy-dev libsodium-dev libargon2-dev libpq-dev libpspell-dev libenchant-2-dev libc-client-dev libkrb5-dev libsasl2-dev libsnmp-dev libedit-dev libmm-dev libevent-dev librabbitmq-dev libgearman-dev libmemcached-dev libyaml-dev libmongoc-dev libvirt-dev libcap-dev libffi-dev libpng16-16 libjpeg62-turbo libwebp6 libfreetype6 libzip4 yum-utils gcc gcc-c++ autoconf libtool perl perl-devel libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel openldap openldap-devel openldap-clients freetype freetype-devel libxml2 libxml2-devel sqlite-devel zlib zlib-devel curl curl-devel pcre pcre-devel gd gd-devel expat-devel libicu-devel bzip2 bzip2-devel python3 python3-devel libwebp-devel make libzstd-devel wget oniguruma oniguruma-devel zstd glibc-headers krb5-devel libzip libzip-devel libxslt libxslt-devel openssl openssl-devel libsodium-devel glib2-devel cairo-devel gmp-devel libevent-devel readline-devel net-snmp-devel aspell-devel unixODBC-devel libc-client-devel libXpm-devel enchant-devel php-ldap automake kernel keyutils patch tidy epel-release libtidy libtidy-devel)
    else
        php_deps=(libxml2-devel openssl-devel curl-devel libjpeg-turbo-devel libpng-devel libwebp-devel freetype-devel libzip-devel zlib-devel sqlite-devel oniguruma-devel libicu-devel libxslt-devel gmp-devel bzip2-devel readline-devel openldap-devel unixODBC-devel libtidy-devel libsodium-devel argon2-devel postgresql-devel pspell-devel enchant-devel libc-client-devel krb5-devel cyrus-sasl-devel net-snmp-devel libedit-devel libmm-devel libevent-devel rabbitmq-c-devel gearmand-devel libmemcached-devel libyaml-devel mongo-c-driver-devel libvirt-devel libcap-devel libffi-devel yum-utils gcc gcc-c++ autoconf libtool perl perl-devel libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel openldap openldap-devel openldap-clients freetype freetype-devel libxml2 libxml2-devel sqlite-devel zlib zlib-devel curl curl-devel pcre pcre-devel gd gd-devel expat-devel libicu-devel bzip2 bzip2-devel python3 python3-devel libwebp-devel make libzstd-devel wget oniguruma oniguruma-devel zstd glibc-headers krb5-devel libzip libzip-devel libxslt libxslt-devel openssl openssl-devel libsodium-devel glib2-devel cairo-devel gmp-devel libevent-devel readline-devel net-snmp-devel aspell-devel unixODBC-devel libc-client-devel libXpm-devel enchant-devel php-ldap automake kernel keyutils patch tidy epel-release libtidy libtidy-devel)
    fi

    # 安装所有依赖
    pkg_install "${dev_tools[@]}" "${php_deps[@]}"

    success "系统依赖安装完成 $(timer $start)"
}

pkg_install() {
    local pkgs=("$@")
    info "安装软件包: ${pkgs[*]}"

    case "$PKG_MGR" in
        dnf)
            run_cmd_with_retry "dnf -y install --allowerasing ${pkgs[*]}" "安装软件包"
            ;;
        yum)
            if ! run_cmd "yum -y install --allowerasing ${pkgs[*]}" "安装软件包"; then
                warn "使用 --allowerasing 失败，尝试普通安装"
                run_cmd_with_retry "yum -y install ${pkgs[*]}" "安装软件包"
            fi
            ;;
        apt)
            run_cmd "apt-get -y update" "更新包列表"
            run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install ${pkgs[*]}" "安装软件包"
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
            if run_cmd_with_retry "wget -c --tries=3 --timeout=30 '$mirror' -O '$php_archive'" "下载 PHP" "$DOWNLOAD_RETRIES" 3; then
                if [ -s "$php_archive" ]; then
                    success "PHP 下载成功 $(timer $start)"
                    return 0
                fi
            fi
        else
            if run_cmd_with_retry "curl -L --retry 3 --connect-timeout 30 '$mirror' -o '$php_archive'" "下载 PHP" "$DOWNLOAD_RETRIES" 3; then
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

    run_cmd "tar -xzf 'php-${PHP_VERSION}.tar.gz'" "解压 PHP"

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
        run_cmd "./buildconf --force" "运行 buildconf"
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

        if run_cmd "./configure ${CONFIGURE_OPTS[*]}" "配置 PHP"; then
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
                missing_deps=(libxml2-dev libssl-dev libcurl4-openssl-dev libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev libzip-dev zlib1g-dev libsqlite3-dev libonig-dev libicu-dev libxslt1-dev libgmp-dev libbz2-dev libreadline-dev libldap2-dev unixodbc-dev libtidy-dev libsodium-dev libargon2-dev libpq-dev libpspell-dev libenchant-2-dev libc-client-dev libkrb5-dev libsasl2-dev libsnmp-dev libedit-dev libevent-dev libyaml-dev libffi-dev)
                ;;
            *)
                missing_deps=(libxml2-devel openssl-devel curl-devel libjpeg-turbo-devel libpng-devel libwebp-devel freetype-devel libzip-devel zlib-devel sqlite-devel oniguruma-devel libicu-devel libxslt-devel gmp-devel bzip2-devel readline-devel openldap-devel unixODBC-devel libtidy-devel libsodium-devel argon2-devel postgresql-devel pspell-devel enchant-devel libc-client-devel krb5-devel cyrus-sasl-devel net-snmp-devel libedit-devel libevent-devel libyaml-devel libffi-devel)
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
    run_cmd "make clean" "清理编译" || true

    # 编译
    run_cmd_with_retry "make -j '$MAKE_JOBS'" "编译 PHP" 2 || fail "PHP 编译失败"

    # 安装
    run_cmd "make install" "安装 PHP" || fail "PHP 安装失败"

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
upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE
post_max_size = $PHP_POST_MAX_SIZE
max_input_time = 300
max_input_vars = 5000
default_socket_timeout = 60

; 安全配置
expose_php = Off
disable_functions = exec,system,shell_exec,passthru,proc_open,proc_close,proc_get_status,proc_nice,proc_terminate,show_source,phpinfo
disable_classes =
cgi.fix_pathinfo=0
file_uploads = On
allow_url_fopen = Off
allow_url_include = Off

; 其他配置
extension_dir = "$PHP_PREFIX/lib/php/extensions/no-debug-non-zts-20220829/"
EOF
    fi

    # 创建 PHP-FPM 配置
    mkdir -p "$PHP_PREFIX/etc/php-fpm.d"
    cat > "$PHP_PREFIX/etc/php-fpm.conf" <<EOF
[global]
pid = $PHP_PREFIX/var/run/php-fpm.pid
error_log = $PHP_PREFIX/var/log/php-fpm.log
log_level = warning
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
pm.min_spare_servers = 2
pm.max_spare_servers = 8
pm.max_requests = 500
pm.process_idle_timeout = 10s
request_terminate_timeout = 300
request_slowlog_timeout = 0
slowlog = $PHP_PREFIX/var/log/php-fpm-slow.log
rlimit_files = 65535
rlimit_core = 0
catch_workers_output = yes
env[HOSTNAME] = \$HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp
EOF

    # 创建配置文件目录
    mkdir -p "$PHP_PREFIX/lib/conf.d"

    success "PHP 配置完成"
}

create_php_service() {
    info "创建 PHP-FPM 服务..."

    # 创建 systemd 服务文件
    local service_file="/etc/systemd/system/php-fpm.service"
    cat > "$service_file" <<EOF
[Unit]
Description=PHP FastCGI Process Manager
After=syslog.target network.target

[Service]
Type=forking
PIDFile=$PHP_PREFIX/var/run/php-fpm.pid
ExecStart=$PHP_PREFIX/sbin/php-fpm --daemonize --fpm-config $PHP_PREFIX/etc/php-fpm.conf
ExecReload=/bin/kill -USR2 \$MAINPID
ExecStop=/bin/kill -SIGINT \$MAINPID
PrivateTmp=true
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF

    # 重新加载 systemd
    run_cmd "systemctl daemon-reload" "重新加载 systemd"

    if [ "$AUTO_START_SERVICES" = "yes" ]; then
        run_cmd "systemctl enable php-fpm" "启用 PHP-FPM 服务"
        run_cmd "systemctl start php-fpm" "启动 PHP-FPM 服务"
    fi

    success "PHP-FPM 服务配置完成"
}

############################
# ---------------- PHP 扩展安装 ------------------
install_php_extensions() {
    if [ "$INSTALL_PHP_EXTENSIONS" != "yes" ]; then
        info "跳过 PHP 扩展安装"
        return 0
    fi

    info "安装 PHP 扩展..."

    # 安装 Redis 扩展
    install_redis_extension

    # 安装 Imagick 扩展 (GitHub 方式)
    install_imagick_extension

    # 安装其他常用扩展
    install_other_extensions

    success "PHP 扩展安装完成"
}

install_redis_extension() {
    info "安装 Redis PHP 扩展..."

    cd "$SRC_DIR"
    run_cmd "git clone https://github.com/phpredis/phpredis.git" "克隆 Redis 源码" || fail "Redis 源码克隆失败"

    cd phpredis || fail "进入 Redis 源码目录失败"

    run_cmd "$PHP_PREFIX/bin/phpize" "准备 Redis 编译环境"
    run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 Redis"
    run_cmd "make -j$MAKE_JOBS" "编译 Redis"
    run_cmd "make install" "安装 Redis"

    # 添加到 php.ini
    echo "extension=redis.so" >> "$PHP_PREFIX/lib/conf.d/redis.ini"
    success "Redis 扩展安装成功"
}

install_imagick_extension() {
    info "安装 Imagick PHP 扩展 (GitHub 方式)..."

    # 安装 ImageMagick 开发依赖
    if [ "$PKG_MGR" = "apt" ]; then
        pkg_install libmagickwand-dev libmagickcore-dev
    else
        pkg_install ImageMagick-devel ImageMagick
    fi

    cd "$SRC_DIR"
    run_cmd "git clone https://github.com/Imagick/imagick.git" "克隆 Imagick 源码" || fail "Imagick 源码克隆失败"

    cd imagick || fail "进入 Imagick 源码目录失败"

    run_cmd "$PHP_PREFIX/bin/phpize" "准备 Imagick 编译环境"
    run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 Imagick"
    run_cmd "make -j$MAKE_JOBS" "编译 Imagick"
    run_cmd "make install" "安装 Imagick"

    # 添加到 php.ini
    echo "extension=imagick.so" >> "$PHP_PREFIX/lib/conf.d/imagick.ini"
    success "Imagick 扩展安装成功"
}

install_other_extensions() {
    info "安装其他 PHP 扩展..."

    # 安装常用扩展
    local extensions=(
        "mongodb https://github.com/mongodb/mongo-php-driver.git"
        "yaml https://github.com/php/pecl-file_formats-yaml.git"
        "memcached https://github.com/php-memcached-dev/php-memcached.git"
    )

    for ext_info in "${extensions[@]}"; do
        local ext_name=$(echo "$ext_info" | cut -d' ' -f1)
        local ext_url=$(echo "$ext_info" | cut -d' ' -f2)

        info "安装 $ext_name 扩展..."
        cd "$SRC_DIR"

        if [ -d "$ext_name" ]; then
            rm -rf "$ext_name"
        fi

        run_cmd "git clone \"$ext_url\" \"$ext_name\"" "克隆 $ext_name 源码" || {
            warn "$ext_name 源码克隆失败，跳过"
            continue
        }

        cd "$ext_name" || {
            warn "进入 $ext_name 源码目录失败，跳过"
            continue
        }

        run_cmd "$PHP_PREFIX/bin/phpize" "准备 $ext_name 编译环境" || {
            warn "$ext_name phpize 失败，跳过"
            continue
        }

        run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 $ext_name" || {
            warn "$ext_name 配置失败，跳过"
            continue
        }

        run_cmd "make -j$MAKE_JOBS" "编译 $ext_name" || {
            warn "$ext_name 编译失败，跳过"
            continue
        }

        run_cmd "make install" "安装 $ext_name" || {
            warn "$ext_name 安装失败，跳过"
            continue
        }

        echo "extension=$ext_name.so" >> "$PHP_PREFIX/lib/conf.d/$ext_name.ini"
        success "$ext_name 扩展安装成功"
    done
}

############################
# ---------------- MySQL 安装 ------------------
install_mysql() {
    if [ "$INSTALL_MYSQL" != "yes" ]; then
        info "跳过 MySQL 安装"
        return 0
    fi

    info "安装 MySQL..."

    # 下载 MySQL
    cd "$SRC_DIR"
    local mysql_pkg="mysql-$MYSQL_VERSION-linux-glibc2.28-x86_64.tar.xz"
    local mysql_url="https://dev.mysql.com/get/Downloads/MySQL-8.4/$mysql_pkg"

    if command -v wget >/dev/null 2>&1; then
        run_cmd_with_retry "wget -c --tries=3 --timeout=30 '$mysql_url' -O '$mysql_pkg'" "下载 MySQL" "$DOWNLOAD_RETRIES" 3
    else
        run_cmd_with_retry "curl -L --retry 3 --connect-timeout 30 '$mysql_url' -o '$mysql_pkg'" "下载 MySQL" "$DOWNLOAD_RETRIES" 3
    fi

    # 解压
    run_cmd "tar -xJf '$mysql_pkg'" "解压 MySQL"

    # 移动并创建符号链接
    local mysql_dir="mysql-$MYSQL_VERSION-linux-glibc2.28-x86_64"
    run_cmd "rm -rf '$MYSQL_PREFIX'" "清理旧 MySQL 目录"
    run_cmd "mv '$mysql_dir' '$MYSQL_PREFIX'" "移动 MySQL"
    run_cmd "ln -sf '$MYSQL_PREFIX' /usr/local/mysql" "创建 MySQL 符号链接"

    # 创建 MySQL 用户和组
    if ! id -u mysql >/dev/null 2>&1; then
        run_cmd "groupadd mysql" "创建 mysql 组"
        run_cmd "useradd -r -g mysql -s /bin/false mysql" "创建 mysql 用户"
    fi

    # 创建数据目录
    mkdir -p /data/mysql/data /data/mysql/logs /data/mysql/tmp
    chown -R mysql:mysql /data/mysql

    # 初始化 MySQL
    cd "$MYSQL_PREFIX"
    run_cmd "bin/mysqld --initialize-insecure --user=mysql --basedir=$MYSQL_PREFIX --datadir=/data/mysql/data" "初始化 MySQL"

    # 创建配置文件
    cat > /etc/my.cnf <<EOF
[mysqld]
user=mysql
basedir=$MYSQL_PREFIX
datadir=/data/mysql/data
socket=/tmp/mysql.sock
log-error=/data/mysql/logs/mysql-error.log
pid-file=/data/mysql/mysql.pid

character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
default-time-zone='+8:00'

max_connections=1000
max_connect_errors=10000
table_open_cache=2000
thread_cache_size=128

innodb_buffer_pool_size=1G
innodb_log_file_size=256M
innodb_flush_log_at_trx_commit=1
innodb_lock_wait_timeout=50

log-bin=mysql-bin
server-id=1
binlog_format=ROW
expire_logs_days=7

[client]
socket=/tmp/mysql.sock

[mysql]
socket=/tmp/mysql.sock
EOF

    # 创建 systemd 服务
    cat > /etc/systemd/system/mysql.service <<EOF
[Unit]
Description=MySQL Server
After=network.target

[Service]
User=mysql
Group=mysql
Type=forking
PIDFile=/data/mysql/mysql.pid
ExecStart=$MYSQL_PREFIX/bin/mysqld --daemonize --pid-file=/data/mysql/mysql.pid
ExecStop=/bin/kill -TERM \$MAINPID
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF

    # 重新加载 systemd
    run_cmd "systemctl daemon-reload" "重新加载 systemd"

    if [ "$AUTO_START_SERVICES" = "yes" ]; then
        run_cmd "systemctl enable mysql" "启用 MySQL 服务"
        run_cmd "systemctl start mysql" "启动 MySQL 服务"

        # 设置 root 密码
        sleep 5  # 等待 MySQL 启动
        run_cmd "$MYSQL_PREFIX/bin/mysqladmin -u root password \"$MYSQL_ROOT_PASS\"" "设置 MySQL root 密码"
    fi

    # 添加 MySQL 到 PATH
    echo "export PATH=\$PATH:$MYSQL_PREFIX/bin" >> "$PROFILE_FILE"

    success "MySQL 安装完成"
}

############################
# ---------------- Redis 安装 ------------------
install_redis() {
    if [ "$INSTALL_REDIS" != "yes" ]; then
        info "跳过 Redis 安装"
        return 0
    fi

    info "安装 Redis..."

    # 下载 Redis
    cd "$SRC_DIR"
    local redis_pkg="redis-$REDIS_VERSION.tar.gz"
    local redis_url="http://download.redis.io/releases/$redis_pkg"

    if command -v wget >/dev/null 2>&1; then
        run_cmd_with_retry "wget -c --tries=3 --timeout=30 '$redis_url' -O '$redis_pkg'" "下载 Redis" "$DOWNLOAD_RETRIES" 3
    else
        run_cmd_with_retry "curl -L --retry 3 --connect-timeout 30 '$redis_url' -o '$redis_pkg'" "下载 Redis" "$DOWNLOAD_RETRIES" 3
    fi

    # 解压
    run_cmd "tar -xzf '$redis_pkg'" "解压 Redis"

    # 编译安装
    cd "redis-$REDIS_VERSION"
    run_cmd "make -j$MAKE_JOBS" "编译 Redis"
    run_cmd "make PREFIX=$REDIS_PREFIX install" "安装 Redis"

    # 创建配置目录
    mkdir -p /etc/redis /var/log/redis /var/lib/redis
    chown -R redis:redis /var/log/redis /var/lib/redis

    # 创建 Redis 用户
    if ! id -u redis >/dev/null 2>&1; then
        run_cmd "groupadd redis" "创建 redis 组"
        run_cmd "useradd -r -g redis -s /bin/false redis" "创建 redis 用户"
    fi

    # 复制配置文件
    cp redis.conf /etc/redis/redis.conf

    # 修改配置文件
    sed -i 's/^daemonize no/daemonize yes/' /etc/redis/redis.conf
    sed -i 's/^logfile ""/logfile \/var\/log\/redis\/redis.log/' /etc/redis/redis.conf
    sed -i 's/^dir .\//dir \/var\/lib\/redis/' /etc/redis/redis.conf
    sed -i 's/^# bind 127.0.0.1/bind 127.0.0.1/' /etc/redis/redis.conf
    sed -i 's/^protected-mode yes/protected-mode yes/' /etc/redis/redis.conf

    # 创建 systemd 服务
    cat > /etc/systemd/system/redis.service <<EOF
[Unit]
Description=Redis In-Memory Data Store
After=network.target

[Service]
User=redis
Group=redis
Type=forking
ExecStart=$REDIS_PREFIX/bin/redis-server /etc/redis/redis.conf
ExecStop=$REDIS_PREFIX/bin/redis-cli shutdown
Restart=always

[Install]
WantedBy=multi-user.target
EOF

    # 重新加载 systemd
    run_cmd "systemctl daemon-reload" "重新加载 systemd"

    if [ "$AUTO_START_SERVICES" = "yes" ]; then
        run_cmd "systemctl enable redis" "启用 Redis 服务"
        run_cmd "systemctl start redis" "启动 Redis 服务"
    fi

    # 添加 Redis 到 PATH
    echo "export PATH=\$PATH:$REDIS_PREFIX/bin" >> "$PROFILE_FILE"

    success "Redis 安装完成"
}

############################
# ---------------- Nginx 安装 ------------------
install_nginx() {
    if [ "$INSTALL_NGINX" != "yes" ]; then
        info "跳过 Nginx 安装"
        return 0
    fi

    info "安装 Nginx..."

    # 安装依赖
    if [ "$PKG_MGR" = "apt" ]; then
        pkg_install libpcre3-dev zlib1g-dev libssl-dev
    else
        pkg_install pcre-devel zlib-devel openssl-devel
    fi

    # 下载 Nginx
    cd "$SRC_DIR"
    local nginx_pkg="nginx-$NGINX_VERSION.tar.gz"
    local nginx_url="https://nginx.org/download/$nginx_pkg"

    if command -v wget >/dev/null 2>&1; then
        run_cmd_with_retry "wget -c --tries=3 --timeout=30 '$nginx_url' -O '$nginx_pkg'" "下载 Nginx" "$DOWNLOAD_RETRIES" 3
    else
        run_cmd_with_retry "curl -L --retry 3 --connect-timeout 30 '$nginx_url' -o '$nginx_pkg'" "下载 Nginx" "$DOWNLOAD_RETRIES" 3
    fi

    # 解压
    run_cmd "tar -xzf '$nginx_pkg'" "解压 Nginx"

    # 编译安装
    cd "nginx-$NGINX_VERSION"
    run_cmd "./configure --prefix=$NGINX_PREFIX --with-http_ssl_module --with-http_v2_module --with-http_realip_module --with-http_stub_status_module --with-http_gzip_static_module --with-pcre --with-stream" "配置 Nginx"
    run_cmd "make -j$MAKE_JOBS" "编译 Nginx"
    run_cmd "make install" "安装 Nginx"

    # 创建 Nginx 用户
    if ! id -u "$WWW_USER" >/dev/null 2>&1; then
        run_cmd "groupadd $WWW_USER" "创建 $WWW_USER 组"
        run_cmd "useradd -r -g $WWW_USER -s /bin/false $WWW_USER" "创建 $WWW_USER 用户"
    fi

    # 创建网站目录
    mkdir -p /data/www /data/logs/nginx
    chown -R "$WWW_USER:$WWW_USER" /data/www /data/logs/nginx

    # 配置 Nginx
    cat > "$NGINX_PREFIX/conf/nginx.conf" <<EOF
user $WWW_USER $WWW_USER;
worker_processes auto;
error_log /data/logs/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    include mime.types;
    default_type application/octet-stream;

    log_format main '\$remote_addr - \$remote_user [\$time_local] "\$request" '
                    '\$status \$body_bytes_sent "\$http_referer" '
                    '"\$http_user_agent" "\$http_x_forwarded_for"';

    access_log /data/logs/nginx/access.log main;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;

    gzip on;
    gzip_min_length 1k;
    gzip_comp_level 2;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/atom+xml image/svg+xml;

    include /data/nginx/conf.d/*.conf;
}
EOF

    # 创建虚拟主机配置目录
    mkdir -p /data/nginx/conf.d

    # 创建默认虚拟主机配置
    cat > /data/nginx/conf.d/default.conf <<EOF
server {
    listen 80;
    server_name localhost;
    root /data/www;
    index index.php index.html index.htm;

    access_log /data/logs/nginx/default.access.log main;
    error_log /data/logs/nginx/default.error.log warn;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

    # 创建 systemd 服务
    cat > /etc/systemd/system/nginx.service <<EOF
[Unit]
Description=NGINX HTTP Server
After=network.target

[Service]
Type=forking
PIDFile=/var/run/nginx.pid
ExecStartPre=$NGINX_PREFIX/sbin/nginx -t
ExecStart=$NGINX_PREFIX/sbin/nginx
ExecReload=$NGINX_PREFIX/sbin/nginx -s reload
ExecStop=/bin/kill -s QUIT \$MAINPID
PrivateTmp=true
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF

    # 重新加载 systemd
    run_cmd "systemctl daemon-reload" "重新加载 systemd"

    if [ "$AUTO_START_SERVICES" = "yes" ]; then
        run_cmd "systemctl enable nginx" "启用 Nginx 服务"
        run_cmd "systemctl start nginx" "启动 Nginx 服务"
    fi

    # 添加 Nginx 到 PATH
    echo "export PATH=\$PATH:$NGINX_PREFIX/sbin" >> "$PROFILE_FILE"

    success "Nginx 安装完成"
}

############################
# ---------------- Composer 安装 ------------------
install_composer() {
    if [ "$INSTALL_COMPOSER" != "yes" ]; then
        info "跳过 Composer 安装"
        return 0
    fi

    info "安装 Composer..."

    # 下载 Composer
    cd /usr/local/bin
    run_cmd_with_retry "curl -sS https://getcomposer.org/installer -o composer-setup.php" "下载 Composer 安装器" "$DOWNLOAD_RETRIES" 3
    run_cmd "php composer-setup.php --install-dir=/usr/local/bin --filename=composer" "安装 Composer"
    run_cmd "rm composer-setup.php" "清理 Composer 安装文件"

    # 配置 Composer 中国镜像
    run_cmd "composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/" "配置 Composer 镜像"

    success "Composer 安装完成"
}

############################
# ---------------- 环境配置 ------------------
configure_environment() {
    info "配置系统环境..."

    # 添加 PHP 到 PATH
    echo "export PATH=\$PATH:$PHP_PREFIX/bin:$PHP_PREFIX/sbin" >> "$PROFILE_FILE"

    # 创建 PHP 配置文件目录
    mkdir -p "$PHP_PREFIX/lib/conf.d"

    # 创建测试 PHP 文件
    cat > /data/www/index.php <<EOF
<?php
phpinfo();
EOF

    chown -R "$WWW_USER:$WWW_USER" /data/www

    # 重新加载环境变量
    source "$PROFILE_FILE"

    success "环境配置完成"
}

############################
# ---------------- 安全加固 ------------------
harden_security() {
    info "加固系统安全..."

    # 配置 SSH (如果存在)
    if [ -f /etc/ssh/sshd_config ]; then
        sed -i 's/^#PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
        sed -i 's/^#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
        sed -i 's/^#PubkeyAuthentication yes/PubkeyAuthentication yes/' /etc/ssh/sshd_config

        # 重启 SSH 服务
        if systemctl is-active --quiet sshd; then
            run_cmd "systemctl restart sshd" "重启 SSH 服务"
        fi
    fi

    # 配置防火墙规则
    if command -v firewall-cmd >/dev/null 2>&1; then
        run_cmd "firewall-cmd --permanent --remove-service=ssh" "移除 SSH 服务"
        run_cmd "firewall-cmd --permanent --add-rich-rule='rule family=\"ipv4\" source address=\"0.0.0.0/0\" port port=\"22\" protocol=\"tcp\" accept'" "添加 SSH 规则"
        run_cmd "firewall-cmd --reload" "重载防火墙"
    fi

    # 创建受限的管理用户
    if ! id -u "$REMOTE_ADMIN_USER" >/dev/null 2>&1; then
        run_cmd "useradd -m -s /bin/bash $REMOTE_ADMIN_USER" "创建管理用户"
        run_cmd "echo '$REMOTE_ADMIN_USER:$REMOTE_ADMIN_PASS' | chpasswd" "设置管理用户密码"
        run_cmd "usermod -aG wheel $REMOTE_ADMIN_USER" "添加管理用户到 wheel 组"
    fi

    success "安全加固完成"
}

############################
# ---------------- 清理工作 ------------------
cleanup() {
    info "清理临时文件..."

    if [ "$CLEAN_TEMP" = "yes" ]; then
        # 清理源码目录
        cd "$SRC_DIR" && rm -rf php-* nginx-* redis-* mysql-* *-*-*

        # 清理编译缓存
        rm -rf /tmp/pear /tmp/php-*

        # 清理包管理器缓存
        case "$PKG_MGR" in
            dnf|yum)
                run_cmd "$PKG_MGR clean all" "清理包管理器缓存"
                ;;
            apt)
                run_cmd "apt-get clean" "清理包管理器缓存"
                run_cmd "apt-get autoremove -y" "清理不需要的包"
                ;;
        esac
    fi

    success "清理完成"
}

############################
# ---------------- 安装总结 ------------------
installation_summary() {
    local end_time=$(date +%s)
    local total_time=$(timer $START_TIME)

    info "生成安装总结..."

    cat > "$INSTALL_SUMMARY" <<EOF
# PHP 环境安装总结
安装时间: $(date '+%F %T')
总耗时: $total_time

## 系统信息
操作系统: $OS_NAME $OS_VERSION ($OS_ARCH)
包管理器: $PKG_MGR

## 安装组件
PHP: $PHP_VERSION ($PHP_PREFIX)
MySQL: $([ "$INSTALL_MYSQL" = "yes" ] && echo "$MYSQL_VERSION" || echo "未安装")
Redis: $([ "$INSTALL_REDIS" = "yes" ] && echo "$REDIS_VERSION" || echo "未安装")
Nginx: $([ "$INSTALL_NGINX" = "yes" ] && echo "$NGINX_VERSION" || echo "未安装")
Composer: $([ "$INSTALL_COMPOSER" = "yes" ] && echo "已安装" || echo "未安装")

## 服务状态
$(if [ "$AUTO_START_SERVICES" = "yes" ]; then
    echo "PHP-FPM: $(systemctl is-active php-fpm 2>/dev/null || echo '未启动')"
    echo "MySQL: $(systemctl is-active mysql 2>/dev/null || echo '未启动')"
    echo "Redis: $(systemctl is-active redis 2>/dev/null || echo '未启动')"
    echo "Nginx: $(systemctl is-active nginx 2>/dev/null || echo '未启动')"
else
    echo "服务未自动启动，请手动启动"
fi)

## 重要信息
MySQL root 密码: $MYSQL_ROOT_PASS
管理用户: $REMOTE_ADMIN_USER
管理用户密码: $REMOTE_ADMIN_PASS
Web 用户: $WWW_USER
Web 用户密码: $WWW_PASS

PHP 配置文件: $PHP_INI_FILE
PHP-FPM 配置文件: $PHP_PREFIX/etc/php-fpm.conf
MySQL 配置文件: /etc/my.cnf
Redis 配置文件: /etc/redis/redis.conf
Nginx 配置文件: $NGINX_PREFIX/conf/nginx.conf

网站根目录: /data/www
日志目录: /data/logs

## 环境变量
已添加到 $PROFILE_FILE:
- PHP: $PHP_PREFIX/bin
- MySQL: $MYSQL_PREFIX/bin
- Redis: $REDIS_PREFIX/bin
- Nginx: $NGINX_PREFIX/sbin

## 后续步骤
1. 请妥善保存上述密码信息
2. 根据需要调整配置文件
3. 重启相关服务使配置生效: systemctl restart php-fpm mysql redis nginx
4. 访问 http://服务器IP 测试 PHP 环境

## 错误日志
如果遇到问题，请检查以下日志文件:
- 安装日志: $LOG_FILE
- 错误日志: $ERROR_LOG_FILE
- PHP 错误日志: $PHP_PREFIX/var/log/php_errors.log
- PHP-FPM 日志: $PHP_PREFIX/var/log/php-fpm.log
- MySQL 错误日志: /data/mysql/logs/mysql-error.log
- Nginx 错误日志: /data/logs/nginx/error.log

安装完成时间: $(date '+%F %T')
总耗时: $total_time
EOF

    success "安装总结已保存到: $INSTALL_SUMMARY"
    echo ""
    echo "=================== 安装完成 ==================="
    echo "总耗时: $total_time"
    echo "安装总结: $INSTALL_SUMMARY"
    echo "================================================"
}

############################
# ---------------- 主函数 ------------------
main() {
    echo ""
    echo "🚀 开始安装工业级 PHP 环境栈"
    echo "================================================"

    # 检测系统
    detect_system

    # 安全基础配置
    configure_security

    # 安装依赖
    install_dependencies

    # PHP 安装
    if [ "$INSTALL_PHP" = "yes" ]; then
        download_php
        extract_php
        configure_php
        build_php
        setup_php_config
        create_php_service
    fi

    # PHP 扩展安装
    install_php_extensions

    # MySQL 安装
    install_mysql

    # Redis 安装
    install_redis

    # Nginx 安装
    install_nginx

    # Composer 安装
    install_composer

    # 环境配置
    configure_environment

    # 安全加固
    harden_security

    # 清理工作
    cleanup

    # 安装总结
    installation_summary

    echo ""
    success "PHP 环境栈安装完成!"
    warn "请查看 $INSTALL_SUMMARY 获取重要信息"
    echo ""
}

############################
# ---------------- 执行 ------------------
# 检查 root 权限
if [ "$(id -u)" != "0" ]; then
    error "此脚本必须以 root 权限运行"
    exit 1
fi

# 设置错误处理
trap 'error "脚本在行号 ${LINENO} 被中断"; exit 1' INT TERM

# 执行主函数
main "$@"