#!/usr/bin/env bash
# 指定使用的解释器为 bash，确保脚本可移植
# -----------------------------------------------------------------------------
# industrial_php_stack_improved.sh
# 改进版：工业级 PHP 环境一键安装脚本（增强错误接管、回滚、备份与日志）
# 功能：安装 PHP / Nginx / MySQL / Redis / Composer / 常用扩展
# 主要改进点：
#  - 全面中文注释
#  - ERR/INT/TERM 信号接管并触发回滚
#  - 记录每一步的反向操作（rollback），异常时按逆序执行以回滚系统改动
#  - 智能判断哪些包是新安装的，回滚时仅移除这些包（尽量避免删除已有系统包）
#  - 对修改的配置文件先备份，回滚时恢复备份
#  - 优化 run_cmd 执行方式，使用 "bash -lc" 执行多行命令，解决命令包含换行符导致的执行问题
#  - 避免不必要的 eval，修复 make -j 等错误引用
#  - 增强日志（安装日志 + 错误日志 + 回滚日志）
#  - 增加临时文件管理，退出时自动清理
#  - 添加安全提示与使用说明
#  - 提取公共变量，优化代码结构
#  - 增强错误处理和回滚能力
# -----------------------------------------------------------------------------

set -euo pipefail  # 开启严格模式：出错退出、未定义变量视作错误、管道失败时整体失败
set -o errtrace   # 使 ERR trap 在函数/子 shell 中也生效
IFS=$'\n\t'  # 设置内部字段分隔符为换行和制表符，避免空格或换行导致参数被错误拆分

# ---------------- 可配置参数（保持与原脚本兼容） ----------------
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

# ---------------- 公共路径配置 ----------------
PHP_PREFIX="${PHP_PREFIX:-/usr/local/php8}"
MYSQL_PREFIX="${MYSQL_PREFIX:-/usr/local/mysql}"
NGINX_PREFIX="${NGINX_PREFIX:-/usr/local/nginx}"
REDIS_PREFIX="${REDIS_PREFIX:-/usr/local/redis}"
SRC_DIR="${SRC_DIR:-/usr/local/src}"

# ---------------- 安全凭证配置 ----------------
#MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-$(openssl rand -base64 24)}"
#REMOTE_ADMIN_USER="${REMOTE_ADMIN_USER:-phpadmin}"
#REMOTE_ADMIN_PASS="${REMOTE_ADMIN_PASS:-$(openssl rand -base64 24)}"
#GROUP_NAME="${GROUP_NAME:-www}"
#WWW_USER="${WWW_USER:-www}"
#WWW_PASS="${WWW_PASS:-$(openssl rand -base64 24)}"
#REDIS_PASS="${REDIS_PASS:-$(openssl rand -base64 24)}"
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-zhaoXfMysql001.}"
REMOTE_ADMIN_USER="${REMOTE_ADMIN_USER:-zhaoxianfang}"
REMOTE_ADMIN_PASS="${REMOTE_ADMIN_PASS:-zxfMysql001.}"
GROUP_NAME="${GROUP_NAME:-www}"
WWW_USER="${WWW_USER:-www}"
WWW_PASS="${WWW_PASS:-CdOs491592.}"
REDIS_PASS="${REDIS_PASS:-zxfRedis001.}"

# ---------------- 日志和临时文件配置 ----------------
LOG_DIR="${LOG_DIR:-/var/log/php-stack-install}"
LOG_FILE="${LOG_FILE:-$LOG_DIR/install.log}"
ERROR_LOG_FILE="${ERROR_LOG_FILE:-$LOG_DIR/error.log}"
ROLLBACK_LOG_FILE="${ROLLBACK_LOG_FILE:-$LOG_DIR/rollback.log}"
INSTALL_SUMMARY="${INSTALL_SUMMARY:-/data/install_config.log}"
INSTALLED_PACKAGES_LOG="${INSTALLED_PACKAGES_LOG:-$LOG_DIR/installed_packages.log}"

# ---------------- 性能配置 ----------------
MAKE_JOBS="${MAKE_JOBS:-$(nproc 1>/dev/null || echo 1)}"
AUTO_START_SERVICES="${AUTO_START_SERVICES:-yes}"
MAX_RETRIES="${MAX_RETRIES:-3}"
DOWNLOAD_RETRIES="${DOWNLOAD_RETRIES:-3}"
CLEAN_TEMP="${CLEAN_TEMP:-yes}"

# ---------------- PHP 配置 ----------------
PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-512M}"
PHP_MAX_EXECUTION_TIME="${PHP_MAX_EXECUTION_TIME:-300}"
PHP_UPLOAD_MAX_FILESIZE="${PHP_UPLOAD_MAX_FILESIZE:-256M}"
PHP_POST_MAX_SIZE="${PHP_POST_MAX_SIZE:-256M}"
PROFILE_FILE="${PROFILE_FILE:-/etc/profile}"

# ---------------- PHP 镜像配置 ----------------
PHP_MIRRORS=(
    "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"
    "https://mirrors.aliyun.com/php-distributions/php-${PHP_VERSION}.tar.gz"
    "https://mirrors.cloud.tencent.com/php-distributions/php-${PHP_VERSION}.tar.gz"
)

# ---------------- PHP 编译选项配置（提取为公共变量） ----------------
PHP_CONFIGURE_OPTS=(
    "--prefix=$PHP_PREFIX"  # 编译安装前缀
    "--with-config-file-path=$PHP_PREFIX/lib"  # php.ini 路径
    # "--with-config-file-scan-dir=$PHP_PREFIX/lib/conf.d"  # 扫描扩展配置目录
    "--with-fpm-user=$WWW_USER"  # fpm 用户
    "--with-fpm-group=$WWW_USER"  # fpm 用户组
    "--enable-fpm"
    "--with-libxml"
    "--with-openssl"
    # "--with-kerberos" 弃用
    "--with-system-ciphers"
    "--with-mysqli"
    "--with-mysql-sock"
    "--enable-pdo"
    "--with-pdo-sqlite"
    "--with-pdo-mysql"
    "--with-pdo-sqlite"
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
    # "--with-mhash" 弃用
    "--with-iconv"
    # "--with-imap-ssl" 弃用
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
    "--with-tidy"
    "--enable-tokenizer"
    "--enable-xml"
    "--with-xsl"
    "--with-zip"
    "--with-bz2"
    "--enable-mysqlnd"
    # "--with-pear" 弃用
    "--with-jpeg"
    "--with-libdir=lib64"
    "--enable-cli"
    "--enable-static"
)

# ---------------- 全局变量 ----------------
PKG_MGR=""
OS_ID=""
OS_NAME=""
OS_VERSION=""
OS_ARCH=""
PHP_SRC_DIR=""
PHP_INI_FILE=""
START_TIME=$(date +%s)
TMP_FILES=()

# rollback 命令栈（按顺序 push，回滚时逆序执行）
ROLLBACK_CMDS=()
# 记录由脚本新安装的软件包（回滚时会移除这些包）
INSTALLED_PKGS=()
# 记录创建的用户（回滚时会删除）
CREATED_USERS=()
# 记录备份的配置文件（回滚时恢复）
BACKUP_FILES=()

# 创建目录并初始化日志
mkdir -p "$SRC_DIR" "$LOG_DIR" "$(dirname "$INSTALL_SUMMARY")" "$(dirname "$INSTALLED_PACKAGES_LOG")"
: > "$LOG_FILE"
: > "$ERROR_LOG_FILE"
: > "$ROLLBACK_LOG_FILE"
: > "$INSTALLED_PACKAGES_LOG"

# ---------------- 输出（彩色） ----------------
_red() { echo -e "\033[31m$*\033[0m"; }
_green() { echo -e "\033[32m$*\033[0m"; }
_yellow() { echo -e "\033[33m$*\033[0m"; }
_blue() { echo -e "\033[34m$*\033[0m"; }
_bold() { echo -e "\033[1m$*\033[0m"; }

ICON_INFO="🔵"
ICON_SUCCESS="✅"
ICON_WARN="🟡"
ICON_ERROR="🔴"

log() {
    # echo "[$(date '+%F %T')] $*" | tee -a "$LOG_FILE"
    echo "$*" | tee -a "$LOG_FILE"
}

info() {
    log "${ICON_INFO} $*"
    echo -e "${ICON_INFO} $*"
}

success() {
    log "${ICON_SUCCESS} $*"
    echo -e "${ICON_SUCCESS} $*"
}

warn() {
    log "${ICON_WARN} $*"
    echo -e "${ICON_WARN} $*" >&2
}

error() {
    log "${ICON_ERROR} $*"
    echo -e "${ICON_ERROR} $*" >&2
    echo "$(date '+%F %T') - ERROR: $*" >> "$ERROR_LOG_FILE"
}

# --------------- 回滚记录与执行工具 ----------------
mark_rollback() {
    local desc="$1"
    local cmd="$2"
    ROLLBACK_CMDS+=("# $desc\n$cmd")
    # log "[ROLLBACK MARK] $desc -> $cmd"
}

rollback_all() {
    echo "" | tee -a "$ROLLBACK_LOG_FILE"
    echo "[$(date '+%F %T')] 开始回滚..." | tee -a "$ROLLBACK_LOG_FILE"

    local total_steps=${#ROLLBACK_CMDS[@]}
    local current_step=1

    for ((i=total_steps-1; i>=0; i--)); do
        local item="${ROLLBACK_CMDS[i]}"
        local desc="${item%%$'\n'*}"
        desc="${desc#\# }"

        echo "- 执行回滚步骤 ($current_step/$total_steps): $desc" | tee -a "$ROLLBACK_LOG_FILE"

        local cmd=$(printf '%s' "$item" | sed -n '2,999p')
        if [ -n "$cmd" ]; then
            set +e
            bash -lc "$cmd" >>"$ROLLBACK_LOG_FILE" 2>&1
            local rc=$?
            set -e

            if [ $rc -eq 0 ]; then
                echo "  -> 回滚步骤成功" | tee -a "$ROLLBACK_LOG_FILE"
            else
                echo "  -> 回滚步骤失败 (退出码:$rc)" | tee -a "$ROLLBACK_LOG_FILE"
            fi
        fi
        current_step=$((current_step + 1))
    done

    echo "[$(date '+%F %T')] 回滚完成" | tee -a "$ROLLBACK_LOG_FILE"
}

# 高级文件修改函数 - 支持批量多位置查找替换
# 参数:
#   $1: file_path - 要处理的文件路径
#   $2: operations - 操作数组（查找字符串:替换字符串:模式）
#   $3: global_mode - 全局模式 (默认: false) - true时所有操作使用相同模式
modify_file() {
    [[ $# -lt 2 ]] && { echo "错误: 参数不足"; return 1; }
    local f="$1" t b o=("${@:2}") g="false"
    [[ "${o[-1]}" == "global_mode=true" ]] && { g="true"; o=("${o[@]:0:${#o[@]}-1}"); }
    [[ ! -f "$f" ]] && { echo "错误: 文件不存在"; return 1; }
    [[ ! -w "$f" ]] && { echo "错误: 文件不可写"; return 1; }
    t=$(mktemp) || { echo "错误: 创建临时文件失败"; return 1; }
    cp "$f" "$t" || { echo "错误: 复制文件失败"; rm -f "$t"; return 1; }

    local c=0 s=0
    for op in "${o[@]}"; do
        ((c++))
        IFS=':' read -r fs ss m <<< "$op"
        m=${m:-insert}; [[ "$g" == "true" && "$m" == "insert" ]] && m="insert"
        fs=$(echo "$fs" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
        ss=$(echo "$ss" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
        m=$(echo "$m" | tr '[:upper:]' '[:lower:]')
        [[ -z "$fs" ]] && { echo "警告: 操作 $c 跳过"; continue; }

        echo "操作 $c: $m"
        local r=0
        case "$m" in
            "insert"|"i"|"false") awk -i inplace -v f="$fs" -v i="$ss" '
                BEGIN { split(f,a,"\n"); split(i,b,"\n"); x=length(a); y=length(b); z=""; w=0; p=0; }
                { if (p>0) { p--; next } if (w>0&&w<x) { z=z"\n"$0; w++;
                    if (w==x) { print z; for(j=1;j<=y;j++) print b[j]; z=""; w=0; p=x-1; } next }
                if (w==0) { if ($0~"^"a[1]) { z=$0; w=1;
                    if (x==1) { print $0; for(j=1;j<=y;j++) print b[j]; w=0; } } else print $0 } }
                END { if (z!="") print z }' "$t"; r=$? ;;

            "replace"|"r"|"true"|"cover") awk -i inplace -v f="$fs" -v r="$ss" '
                BEGIN { split(f,a,"\n"); split(r,b,"\n"); x=length(a); y=length(b); z=""; w=0; }
                { if (w>0&&w<x) { z=z"\n"$0; w++;
                    if (w==x) { for(j=1;j<=y;j++) print b[j]; z=""; w=0; } next }
                if (w==0) { if ($0~"^"a[1]) { z=$0; w=1;
                    if (x==1) { for(j=1;j<=y;j++) print b[j]; z=""; w=0; } } else print $0 } }
                END { if (z!="") print z }' "$t"; r=$? ;;

            "delete"|"d") awk -i inplace -v f="$fs" '
                BEGIN { split(f,a,"\n"); x=length(a); z=""; w=0; s=0; }
                { if (s>0) { s--; next } if (w>0&&w<x) { z=z"\n"$0; w++;
                    if (w==x) { z=""; w=0; s=x-1; } next }
                if (w==0) { if ($0~"^"a[1]) { z=$0; w=1;
                    if (x==1) { z=""; w=0; s=0; } } else print $0 } }
                END { if (z!="") print z }' "$t"; r=$? ;;

            "comment"|"c") [[ $(echo "$fs" | wc -l) -eq 1 ]] && {
                    sed -i "/^${fs//\//\\/}/s/^/# /" "$t"; r=$?;
                } || { echo "警告: 多行注释不支持"; r=1; } ;;
            "uncomment"|"u") [[ $(echo "$fs" | wc -l) -eq 1 ]] && {
                    sed -i "/^# *${fs//\//\\/}/s/^# *//" "$t"; r=$?;
                } || { echo "警告: 多行取消注释不支持"; r=1; } ;;
            *) sed -i "/^${fs//\//\\/}/a\\${ss//\//\\/}" "$t"; r=$? ;;
        esac
        [[ $r -eq 0 ]] && { ((s++)); echo "成功 $c"; } || echo "失败 $c"
    done

    if [[ $s -gt 0 ]]; then
        b="${f}.bak.$(date +%Y%m%d_%H%M%S)"
        cp "$f" "$b" 2>/dev/null && echo "备份: $b"
        mv "$t" "$f"
        echo "完成: $s/$c"
        return 0
    else
        echo "错误: 全部失败"
        rm -f "$t"
        return 1
    fi
}

# --------------- 临时文件管理 ----------------
register_tmp_file() {
    TMP_FILES+=("$1")
}

cleanup_tmpfiles() {
    for f in "${TMP_FILES[@]:-}"; do
        [ -e "$f" ] && rm -rf "$f" || true
    done
}

# ----------------- 运行命令的通用函数（支持多行） -----------------
run_cmd() {
    local cmd="$1"                         # 要执行的命令
    local desc="${2:-执行命令}"            # 命令描述
    local log_output="${3:-yes}"           # 是否输出到日志

    # 定义动画 frames（你原来的进度条+符号组合）
    local frames=("▰▱▱▱▱▱▱ -" "▰▰▱▱▱▱▱ \\"
                  "▰▰▰▱▱▱▱ |" "▰▰▰▰▱▱▱ /"
                  "▰▰▰▰▰▱▱ -" "▰▰▰▰▰▰▱ \\"
                  "▰▰▰▰▰▰▰ |" "▰▰▰▰▰▰▱ /"
                  "▰▰▰▰▰▱▱ -" "▰▰▰▰▱▱▱ \\"
                  "▰▰▰▱▱▱▱ |" "▰▰▱▱▱▱▱ /")

    # 输出命令日志
    log "[CMD-START] $desc : $cmd"

    # 根据 log_output 决定是否写日志文件
    if [ "$log_output" = "yes" ]; then
        bash -lc "$cmd" >>"$LOG_FILE" 2>>"$ERROR_LOG_FILE" &
    else
        bash -lc "$cmd" >/dev/null 2>&1 &
    fi

    local pid=$!   # 记录后台进程 PID
    local i=0

    # 显示进度动画
    printf "⏳ %s " "$desc"
    while kill -0 "$pid" 2>/dev/null; do
        i=$(((i+1) % ${#frames[@]}))
        printf "\r⏳ %s %s" "$desc" "${frames[i]}"
        sleep 0.1
    done

    # 等待命令完成
    wait "$pid"
    local rc=$?

    # 清理 spinner 行
    printf "\r%*s\r" "$(tput cols)" ""

    if [ $rc -ne 0 ]; then
        printf "❌ %s 失败（退出码:%s，详情见日志）\n" "$desc" "$rc"
        log "[CMD-FAIL] $desc (退出码:$rc)"
        return $rc
    else
        printf "✅ %s 完成\n" "$desc"
        log "[CMD-OK] $desc"
        return 0
    fi
}


run_cmd_with_retry() {
    local cmd="$1"
    local desc="${2:-执行命令}"
    local max_retries="${3:-$MAX_RETRIES}"
    local retry_delay="${4:-2}"
    local log_output="${5:-yes}"

    local attempt=1
    while [ $attempt -le $max_retries ]; do
        if run_cmd "$cmd" "$desc" "$log_output"; then
            return 0
        fi
        warn "$desc 失败 (尝试 $attempt/$max_retries)，${retry_delay}s 后重试..."
        sleep $retry_delay
        attempt=$((attempt+1))
    done

    error "$desc 在 $max_retries 次尝试后仍然失败: $cmd"
    return 1
}

# ----------------- 01、系统检测 -----------------
detect_system() {
    info "检测系统环境..."

    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS_ID="${ID:-unknown}"
        OS_NAME="${NAME:-unknown}"
        OS_VERSION="${VERSION_ID:-unknown}"
    else
        error "无法检测操作系统"
        return 1
    fi

    OS_ARCH=$(uname -m)
    [ "$OS_ARCH" = "x86_64" ] && OS_ARCH="x64" || true

    if command -v dnf >/dev/null 2>&1; then
        PKG_MGR="dnf"
    elif command -v yum >/dev/null 2>&1; then
        PKG_MGR="yum"
    elif command -v apt-get >/dev/null 2>&1; then
        PKG_MGR="apt"
    else
        error "未找到受支持的包管理器 (dnf|yum|apt-get)"
        return 1
    fi

    success "系统: $OS_NAME $OS_VERSION ($OS_ARCH), 包管理器: $PKG_MGR"
    log "系统信息: ID=$OS_ID, NAME=$OS_NAME, VERSION=$OS_VERSION, ARCH=$OS_ARCH"
    echo "-----------------------------------------------------------------"
}

# ----------------- 软件包安装工具 -----------------
pkg_is_installed() {
    local pkg="$1"
    if [ "$PKG_MGR" = "apt" ]; then
        dpkg -s "$pkg" >/dev/null 2>&1 && return 0 || return 1
    else
        rpm -q "$pkg" >/dev/null 2>&1 && return 0 || return 1
    fi
}

escape_pkg_list() {
    local -n arr=$1
    local out=()
    for p in "${arr[@]}"; do
        out+=("$(printf '%q' "$p")")
    done

    local joined=""
    for item in "${out[@]}"; do
        if [ -z "$joined" ]; then
            joined="$item"
        else
            joined="$joined $item"
        fi
    done
    printf '%s' "$joined"
}

pkg_install() {
    local pkgs=("$@")
    # info "准备安装软件包: ${pkgs[*]}"

    local to_install=()
    for p in "${pkgs[@]}"; do
        if ! pkg_is_installed "$p"; then
            to_install+=("$p")
        # else
        #    info "已存在: $p，跳过安装"
        fi
    done

    if [ ${#to_install[@]} -eq 0 ]; then
        # info "没有需要安装的新包"
        return 0
    fi

    local pkgstr=$(escape_pkg_list to_install)
    echo "新安装包: ${to_install[*]}" >> "$INSTALLED_PACKAGES_LOG"

    case "$PKG_MGR" in
        dnf)
            run_cmd_with_retry "dnf -y install --allowerasing $pkgstr" "安装软件包" || return 1
            INSTALLED_PKGS+=("${to_install[@]}")
            mark_rollback "卸载脚本新安装的软件包" "dnf -y remove --noautoremove ${pkgstr} || true"
            ;;
        yum)
            run_cmd_with_retry "yum -y install --allowerasing $pkgstr" "安装软件包" || return 1
            INSTALLED_PKGS+=("${to_install[@]}")
            mark_rollback "卸载脚本新安装的软件包" "yum -y remove ${pkgstr} || true"
            ;;
        apt)
            run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install ${pkgstr}" "安装软件包" || return 1
            INSTALLED_PKGS+=("${to_install[@]}")
            mark_rollback "卸载脚本新安装的软件包" "DEBIAN_FRONTEND=noninteractive apt-get -y remove --purge ${pkgstr} || true; apt-get -y autoremove || true"
            ;;
    esac

    # success "安装软件包完成: ${to_install[*]}"
     success "安装软件包完成"
}

# --------------- 备份配置文件 ----------------
safe_backup_file() {
    local file="$1"
    if [ -f "$file" ]; then
        local bak="${file}.bak.$(date +%s)"
        cp -a "$file" "$bak"
        BACKUP_FILES+=("$file:$bak")
        mark_rollback "恢复配置文件 $file" "if [ -f '$bak' ]; then mv -f '$bak' '$file' || true; fi"
        log "备份配置文件: $file -> $bak"
    fi
}

# --------------- 错误/中断处理 ----------------
handle_error() {
    local lineno="${1:-?}"
    local cmd="${2:-?}"
    local code="${3:-1}"

    error "脚本在行 $lineno 执行命令 '$cmd' 时失败 (退出码: $code)"
    error "详细日志请查看: $ERROR_LOG_FILE"
    error "完整安装日志请查看: $LOG_FILE"

    if [ -f "$ERROR_LOG_FILE" ]; then
        error "---- 最近的错误输出（最多 200 行） ----"
        tail -n 200 "$ERROR_LOG_FILE" | sed 's/^/  /' >&2 || true
        error "---- 错误输出结束 ----"
    fi

    warn "开始执行回滚操作..."
    rollback_all || warn "回滚过程中发生错误，请检查 $ROLLBACK_LOG_FILE"

    error "安装被中止 (行 $lineno, 命令: $cmd, 退出码: $code)"
    exit "$code"
}

on_interrupt() {
    warn "检测到中断信号，开始回滚并退出..."
    rollback_all || warn "回滚过程中发生错误，请检查 $ROLLBACK_LOG_FILE"
    exit 1
}

trap 'handle_error ${LINENO} "${BASH_COMMAND}" $?' ERR
trap 'on_interrupt' INT TERM

# ----------------- 02、系统依赖安装 -----------------
install_dependencies() {
    local start=$(date +%s)
    info "安装系统依赖..."

    case "$PKG_MGR" in
        dnf)
            run_cmd_with_retry "dnf -y update" "更新系统"
            if ! run_cmd_with_retry "dnf -y install --allowerasing epel-release" "安装 EPEL 通过 dnf" 3 2; then
                warn "通过 dnf 安装 epel-release 失败，尝试备用方式安装 EPEL..."
                install_epel_fallback
            fi
            ;;
        yum)
            run_cmd_with_retry "yum -y update" "更新系统"
            if ! run_cmd_with_retry "yum -y install --allowerasing epel-release" "安装 EPEL 通过 yum" 3 2; then
                install_epel_fallback
            fi
            ;;
        apt)
            run_cmd_with_retry "apt-get -y update" "更新包列表"
            run_cmd_with_retry "apt-get -y upgrade" "升级系统"
            ;;
    esac

    # 通用依赖
    local common_packages=(
        yum-utils gcc gcc-c++ autoconf libtool perl perl-devel
        libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel
        openldap openldap-devel openldap-clients freetype freetype-devel
        libxml2 libxml2-devel sqlite-devel zlib zlib-devel curl curl-devel
        pcre pcre-devel gd gd-devel expat-devel libicu-devel bzip2 bzip2-devel
        python3 python3-devel libwebp-devel make libzstd-devel wget
        oniguruma oniguruma-devel zstd glibc-headers krb5-devel libzip libzip-devel
        libxslt libxslt-devel openssl openssl-devel libsodium-devel glib2-devel
        cairo-devel gmp-devel libevent-devel readline-devel net-snmp-devel
        aspell-devel unixODBC-devel libc-client-devel libXpm-devel enchant-devel
        php-ldap automake kernel keyutils patch tidy epel-release libtidy libtidy-devel
    )

    pkg_install "${common_packages[@]}"

    success "系统依赖安装完成 (耗时: $(($(date +%s) - start)) 秒)"
}

install_epel_fallback() {
    warn "尝试从 Fedora 官方下载 epel rpm 并安装"
    local epel_rpm="/tmp/epel-release-latest.rpm"

    if command -v rpm >/dev/null 2>&1; then
        local rhelver="$(rpm -E '%{?rhel}' 2>/dev/null || echo '')"
        if [ -n "$rhelver" ]; then
            run_cmd_with_retry "wget -qO '$epel_rpm' 'https://dl.fedoraproject.org/pub/epel/epel-release-latest-$rhelver.noarch.rpm'" "下载 epel rpm" 3 2 || true
        fi

        run_cmd_with_retry "wget -qO '$epel_rpm' 'https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm'" "下载 epel rpm 备用" 3 2 || true

        if [ -f "$epel_rpm" ]; then
            run_cmd_with_retry "rpm -Uvh '$epel_rpm' --replacepkgs" "安装 epel rpm" 3 2 || warn "使用 rpm 安装 epel-release 失败"
            register_tmp_file "$epel_rpm"
        else
            warn "未能下载到 epel rpm，EPEL 安装被跳过，请手动处理"
        fi
    else
        warn "系统无 rpm 命令，无法用 rpm 安装 epel-release，EPEL 安装被跳过"
    fi
}

# ----------------- PHP 安装相关函数 -----------------
download_php() {
    local start=$(date +%s)
    info "下载 PHP 源码..."
    mkdir -p "$SRC_DIR" && cd "$SRC_DIR"
    local php_archive="php-${PHP_VERSION}.tar.gz"

    for mirror in "${PHP_MIRRORS[@]}"; do
        info "尝试从镜像下载: $mirror"
        if command -v wget >/dev/null 2>&1; then
            if run_cmd_with_retry "wget -c --tries=$DOWNLOAD_RETRIES --timeout=30 '$mirror' -O '$php_archive'" "下载 PHP ($mirror)" 3 3; then
                [ -s "$php_archive" ] && { success "PHP 下载成功"; register_tmp_file "$SRC_DIR/$php_archive"; return 0; }
            fi
        else
            if run_cmd_with_retry "curl -L --retry $DOWNLOAD_RETRIES --connect-timeout 30 '$mirror' -o '$php_archive'" "下载 PHP ($mirror)" 3 3; then
                [ -s "$php_archive" ] && { success "PHP 下载成功"; register_tmp_file "$SRC_DIR/$php_archive"; return 0; }
            fi
        fi
        warn "下载失败: $mirror"
        rm -f "$php_archive" 2>/dev/null || true
    done

    return 1
}

extract_php() {
    info "解压 PHP 源码..."
    cd "$SRC_DIR"
    local php_archive="php-${PHP_VERSION}.tar.gz"

    if [ ! -s "$php_archive" ]; then
        error "PHP 源码包不存在: $php_archive"
        return 1
    fi

    run_cmd "tar -xzf '$php_archive'" "解压 PHP 源码"

    PHP_SRC_DIR="$SRC_DIR/php-${PHP_VERSION}"
    if [ ! -d "$PHP_SRC_DIR" ]; then
        local actual_dir
        actual_dir=$(tar -tf "$php_archive" | head -n1 | cut -d/ -f1)
        PHP_SRC_DIR="$SRC_DIR/$actual_dir"
    fi

    if [ ! -d "$PHP_SRC_DIR" ]; then
        error "无法找到 PHP 源码目录"
        return 1
    fi

    success "PHP 源码解压完成"
}

configure_php() {
    info "配置 PHP 编译选项..."
    cd "$PHP_SRC_DIR"

    if [ -f buildconf ]; then
        run_cmd "./buildconf --force" "运行 buildconf" || warn "buildconf 运行失败"
    fi

    local opts_str=$(printf '%s ' "${PHP_CONFIGURE_OPTS[@]}" | tr -d '\n\r' | sed 's/ $//')
    local attempt=1

    while [ $attempt -le $MAX_RETRIES ]; do
        info "PHP 配置尝试 $attempt/$MAX_RETRIES"
        if run_cmd "./configure $opts_str" "配置 PHP"; then
            success "PHP 配置成功"
            # 删除？瞎搞嘛！！！
            mark_rollback "删除已安装的 PHP 文件夹 $PHP_PREFIX" "rm -rf '$PHP_PREFIX' || true"
            return 0
        fi
        warn "PHP 配置失败，尝试安装缺失依赖并重试"
        install_missing_dependencies || true
        attempt=$((attempt+1))
        sleep 3
    done

    return 1
}

install_missing_dependencies() {
    info "根据日志尝试安装缺失的依赖（若有）..."
    if [ "$PKG_MGR" = "apt" ]; then
        pkg_install libxml2-dev libssl-dev libcurl4-openssl-dev libjpeg-dev libpng-dev zlib1g-dev \
                   libonig-dev libsqlite3-dev libreadline-dev libzip-dev
    else
        pkg_install libxml2-devel openssl-devel curl-devel libjpeg-turbo-devel zlib-devel \
                   oniguruma-devel sqlite-devel readline-devel libzip-devel
    fi
}

build_php() {
    info "编译 PHP..."
    cd "$PHP_SRC_DIR"

    run_cmd "make clean || true" "清理上次编译"
    run_cmd_with_retry "make -j $MAKE_JOBS" "编译 PHP" 2 || return 1
    run_cmd "make install" "安装 PHP" || return 1

    success "PHP 编译安装完成"
}

setup_swap() {
    # 检查是否已有足够的SWAP空间
    local current_swap=$(free -m | awk '/Swap:/{print $2}')
    if [ "$current_swap" -lt 2048 ]; then  # 如果SWAP小于2GB
        info "配置SWAP空间(2GB)以防止编译内存不足..."
        local swap_file="/var/cache/swap/swap0"

        # 创建SWAP文件
        mkdir -p /var/cache/swap/
        if [ ! -f "$swap_file" ]; then
            run_cmd "dd if=/dev/zero of=$swap_file bs=64M count=128" "创建SWAP文件"
            run_cmd "chmod 600 $swap_file" "设置SWAP文件权限"
            run_cmd "mkswap $swap_file" "初始化SWAP"
        fi

        # 启用SWAP
        if ! swapon -s | grep -q "$swap_file"; then
            run_cmd "swapon $swap_file" "启用SWAP"
            # 添加到fstab永久生效
            if ! grep -q "$swap_file" /etc/fstab; then
                echo "$swap_file swap swap defaults 0 0" >> /etc/fstab
            fi
            mark_rollback "移除SWAP配置" "swapoff $swap_file 2>/dev/null || true; sed -i '\|$swap_file|d' /etc/fstab || true"
        fi

        # 显示内存信息
        run_cmd "free -h" "检查内存状态"
        success "SWAP配置完成"
    else
        info "现有SWAP空间充足(${current_swap}MB)，跳过配置"
    fi
}

setup_php_config() {
    info "配置 PHP..."

    mkdir -p "$PHP_PREFIX/lib" "$PHP_PREFIX/etc" "$PHP_PREFIX/var/log" \
           "$PHP_PREFIX/var/run" "$PHP_PREFIX/lib/conf.d" "$PHP_PREFIX/var/session"

    PHP_INI_FILE="$PHP_PREFIX/lib/php.ini"

    cd "$PHP_SRC_DIR"
    cp php.ini-production "$PHP_INI_FILE"
    cp "$PHP_PREFIX/etc/php-fpm.conf.default" "$PHP_PREFIX/etc/php-fpm.conf"
    cp "$PHP_PREFIX/etc/php-fpm.d/www.conf.default"  "$PHP_PREFIX/etc/php-fpm.d/www.conf"

    if [ -f "$PHP_INI_FILE" ]; then
        safe_backup_file "$PHP_INI_FILE"
    fi

    # 修改php.ini配置 暂不配置 [opcache]
    modify_file "$PHP_INI_FILE" \
            "upload_max_filesize.*:upload_max_filesize = 100M:replace" \
            "max_file_uploads.*:max_file_uploads = 100:replace" \
            "post_max_size.*:post_max_size = 150M:replace" \
            "memory_limit.*:memory_limit = 256M:replace" \
            "max_execution_time.*:max_execution_time = 300:replace" \
            "max_input_time.*:max_input_time = 300:replace" \
            ";date.timezone.*:date.timezone = Asia/Shanghai:insert" \

    setup_php_fpm_config
    create_php_symlinks
    setup_php_path

    # 让其他用户组可以执行 php
    run_cmd "sudo chown -R '$GROUP_NAME':'$WWW_USER' /usr/local/php8 '$PHP_PREFIX'" "修改 php 目录权限"
    run_cmd "sudo chmod -R 755 '$PHP_PREFIX'" "修改 php 目录权限"

    success "PHP 基本配置完成"
}

setup_php_fpm_config() {
    if [ -f "$PHP_PREFIX/etc/php-fpm.conf" ]; then
        safe_backup_file "$fpm_conf"
    fi

    # 修改php-fpm.conf配置
    modify_file "$PHP_PREFIX/etc/php-fpm.conf" \
        ";pid = run/php-fpm.pid:pid = $PHP_PREFIX/var/run/php-fpm.pid:insert" \
        ";error_log = log/php-fpm.log:error_log = $PHP_PREFIX/var/php-fpm.log:insert"

}

create_php_symlinks() {
    for binary in php phpize pear pecl phar; do
        if [ -f "$PHP_PREFIX/bin/$binary" ]; then
            ln -sf "$PHP_PREFIX/bin/$binary" "/usr/local/bin/$binary" || warn "创建 $binary 符号链接失败"
            mark_rollback "移除符号链接 /usr/local/bin/$binary" "rm -f '/usr/local/bin/$binary' || true"
        fi
    done
}

setup_php_path() {
    if ! grep -q "$PHP_PREFIX/bin" "$PROFILE_FILE" 2>/dev/null; then
        safe_backup_file "$PROFILE_FILE"
        modify_file "$PROFILE_FILE" \
            "export PATH.*:export PATH=$PHP_PREFIX/bin:\$PATH:insert"


        run_cmd "source $PROFILE_FILE" "保存配置..."

        mark_rollback "恢复 $PROFILE_FILE" "if [ -f '${PROFILE_FILE}.bak.' ]; then true; fi;"
    fi
}

create_phpfpm_service() {
    info "创建 PHP-FPM systemd 服务..."
    local service_file="/usr/lib/systemd/system/php-fpm.service"
    safe_backup_file "$service_file"

    cat > "$service_file" <<EOF
[Unit]
Description=The PHP FastCGI Process Manager
After=syslog.target network.target

[Service]
Type=forking
PIDFile=$PHP_PREFIX/var/run/php-fpm.pid
ExecStart=$PHP_PREFIX/sbin/php-fpm
ExecReload=/bin/kill -USR2 $MAINPID
PrivateTmp=true

[Install]
WantedBy=multi-user.target

EOF

    run_cmd "systemctl daemon-reload" "重载 systemd"

    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then
        run_cmd "systemctl enable php-fpm.service" "启用 php-fpm.service" || warn "启用 php-fpm 失败"
        run_cmd "systemctl start php-fpm.service" "启动 php-fpm.service" || warn "启动 php-fpm 失败"
        mark_rollback "禁用并停止 php-fpm 服务" "systemctl stop php-fpm.service || true; systemctl disable php-fpm.service || true; rm -f '$service_file' || true; systemctl daemon-reload || true"
    else
        mark_rollback "删除 php-fpm unit 文件" "rm -f '$service_file' || true; systemctl daemon-reload || true"
    fi

    success "PHP-FPM 服务已创建（若设置自动启动则已启动）"
}

# ----------------- PHP 扩展安装 -----------------
install_php_extensions() {
    if [ "${INSTALL_PHP_EXTENSIONS,,}" != "yes" ]; then
        info "跳过 PHP 扩展安装"
        return 0
    fi
    info "安装一些常用 PHP 扩展（通过 pecl / 系统包）..."
    export PATH="$PHP_PREFIX/bin:$PATH"

    install_pecl_extension "redis" "redis 扩展"
    # install_pecl_extension "mongodb" "mongodb 扩展"
    # install_pecl_extension "xdebug" "xdebug 扩展"
    install_imagick_extension

    success "PHP 扩展安装步骤完成"
}

install_pecl_extension() {
    local extension="$1"
    local desc="$2"

    if command -v pecl >/dev/null 2>&1; then
        if run_cmd_with_retry "pecl install $extension" "安装 $desc" 3 2; then
            echo "extension=$extension.so" >> "$PHP_INI_FILE"
            success "已安装 $desc"
        else
            warn "安装 $desc 失败，跳过"
        fi
    else
        warn "pecl 命令未找到，无法安装 $desc"
    fi
}

install_imagick_extension() {
    if pkg_install "ImageMagick-devel" "ImageMagick"; then
        install_pecl_extension "imagick" "imagick 扩展"
    else
        warn "ImageMagick 安装失败，跳过 imagick 扩展"
    fi
}

# ----------------- MySQL 安装 -----------------
install_mysql() {
    if [ "${INSTALL_MYSQL,,}" != "yes" ]; then
        info "跳过 MySQL 安装"
        return 0
    fi
    info "安装 MySQL $MYSQL_VERSION..."

    case "$PKG_MGR" in
        dnf|yum)
            pkg_install "mysql-server" "mysql" "mysql-devel"
            ;;
        apt)
            pkg_install "mysql-server" "mysql-client" "libmysqlclient-dev"
            ;;
    esac

    # setup_mysql_config
    start_mysql_service
    secure_mysql_installation

    success "MySQL 安装完成"
}

setup_mysql_config() {
    info "配置 MySQL..."
    local mysql_conf_dir="/etc/mysql"
    local mysql_conf_file="/etc/my.cnf"

    if [ -f "$mysql_conf_file" ]; then
        safe_backup_file "$mysql_conf_file"
    fi

    if [ -d "$mysql_conf_dir" ]; then
        safe_backup_file "$mysql_conf_dir/my.cnf"
    fi

    cat > "$mysql_conf_file" <<EOF
[mysqld]
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock
log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid
bind-address=0.0.0.0
default-authentication-plugin=mysql_native_password
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
max_connections=1000
max_allowed_packet=256M
innodb_buffer_pool_size=256M

[client]
default-character-set=utf8mb4
EOF

    mark_rollback "恢复 MySQL 配置" "if [ -f '${mysql_conf_file}.bak.' ]; then true; fi;"
}

start_mysql_service() {
    info "启动 MySQL 服务..."
    run_cmd "systemctl start mysqld.service || systemctl start mysql.service" "启动 MySQL 服务" || return 1
    run_cmd "systemctl enable mysqld.service || systemctl enable mysql.service" "启用 MySQL 自启动" || warn "启用 MySQL 自启动失败"
    mark_rollback "停止并禁用 MySQL 服务" "systemctl stop mysqld.service || systemctl stop mysql.service || true; systemctl disable mysqld.service || systemctl disable mysql.service || true"
}

secure_mysql_installation() {
    info "执行 MySQL 安全初始化..."
    local temp_pass=$(grep 'temporary password' /var/log/mysqld.log 2>/dev/null | tail -n1 | awk '{print $NF}' || true)
    temp_pass="${temp_pass:-}"

    if [ -n "$temp_pass" ]; then
        info "检测到 MySQL 临时密码: $temp_pass"
        mysql --connect-expired-password -u root -p"$temp_pass" -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASS';" 2>/dev/null || true
    fi

    mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE USER IF NOT EXISTS '$REMOTE_ADMIN_USER'@'%' IDENTIFIED BY '$REMOTE_ADMIN_PASS'; GRANT ALL PRIVILEGES ON *.* TO '$REMOTE_ADMIN_USER'@'%'; FLUSH PRIVILEGES;" 2>/dev/null || true

    success "MySQL 安全配置完成"
}

# ----------------- Nginx 安装 -----------------
install_nginx() {
    if [ "${INSTALL_NGINX,,}" != "yes" ]; then
        info "跳过 Nginx 安装"
        return 0
    fi
    info "安装 Nginx $NGINX_VERSION..."
    run_cmd "rpm -Uvh http://nginx.org/packages/centos/8/x86_64/RPMS/nginx-$NGINX_VERSION-1.el8.ngx.x86_64.rpm" "安装 Nginx"

    case "$PKG_MGR" in
        dnf|yum)
            pkg_install "nginx"
            ;;
        apt)
            pkg_install "nginx"
            ;;
    esac

    setup_nginx_config
    start_nginx_service

    success "Nginx 安装完成"
}

setup_nginx_config() {
    info "配置 Nginx..."
    local nginx_conf_dir="/etc/nginx"
    local nginx_conf_file="/etc/nginx/nginx.conf"

    if [ -f "$nginx_conf_file" ]; then
        safe_backup_file "$nginx_conf_file"
    fi

    cat > "$nginx_conf_file" <<EOF
user  nginx;
# user $WWW_USER;
# 自动根据CPU核心数调整Worker进程数量
worker_processes  auto;

error_log  /var/log/nginx/error.log notice;
pid        /var/run/nginx.pid;


events {
    worker_connections  1024;
}


http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    access_log  /var/log/nginx/access.log  main;


    # 开启高效传输模式
    sendfile        on;
    # 保持连接的时间，也叫超时时间，单位秒
    keepalive_timeout  65;

    # 新增配置
    tcp_nopush          on;   # 减少网络报文段的数量
    tcp_nodelay         on;
    types_hash_max_size 2048;

    ######################## 大文件上传处理 ########################
    # 上传文件的大小限制  默认1m
    client_max_body_size 500m;
    # 读取客户端请求头数据的超时时间 默认秒 默认60秒
    client_header_timeout 120;

    ######################## 压缩 ########################
    # 默认off，是否开启gzip
    gzip on;
    # 要采用 gzip 压缩的 MIME 文件类型，其中 text/html 被系统强制启用；
    gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript application/x-httpd-php image/jpeg image/gif image/png;

    # ---- 以上两个参数开启就可以支持Gzip压缩了 ---- #

    # 默认 off，该模块启用后，Nginx 首先检查是否存在请求静态文件的 gz 结尾的文件，如果有则直接返回该 .gz 文件内容；
    gzip_static on;

    # 默认 off，nginx做为反向代理时启用，用于设置启用或禁用从代理服务器上收到相应内容 gzip 压缩；
    # off：关闭Nginx对后台服务器的响应结果进行压缩。
    # expired：如果响应头中包含Expires信息，则开启压缩。
    # no-cache：如果响应头中包含Cache-Control:no-cache信息，则开启压缩。
    # no-store：如果响应头中包含Cache-Control:no-store信息，则开启压缩。
    # private：如果响应头中包含Cache-Control:private信息，则开启压缩。
    # no_last_modified：如果响应头中不包含Last-Modified信息，则开启压缩。
    # no_etag：如果响应头中不包含ETag信息，则开启压缩。
    # auth：如果响应头中包含Authorization信息，则开启压缩。
    # any：无条件对后端的响应结果开启压缩机制。
    gzip_proxied off;

    # 用于在响应消息头中添加 Vary：Accept-Encoding，使代理服务器根据请求头中的 Accept-Encoding 识别是否启用 gzip 压缩；
    gzip_vary on;

    # gzip 压缩比，压缩级别是 1-9，1 压缩级别最低，9 最高，级别越高压缩率越大，压缩时间越长，建议 4-6；
    gzip_comp_level 5;

    # 获取多少内存用于缓存压缩结果，16 8k 表示以 8k*16 为单位获得；
    gzip_buffers 16 8k;

    # 允许压缩的页面最小字节数，页面字节数从header头中的 Content-Length 中进行获取。默认值是 0，不管页面多大都压缩。建议设置成大于 1k 的字节数，小于 1k 可能会越压越大；
    # gzip_min_length 2k;

    # 默认 1.1，启用 gzip 所需的 HTTP 最低版本；
    gzip_http_version 1.1;

    # 禁用IE 6 gzip
    gzip_disable "MSIE [1-6]\.";


    ######################## Nginx缓冲区 ########################

    # 设置与后端服务器建立连接时的超时时间。默认为60s
    proxy_connect_timeout 600s;
    # 设置从后端服务器读取响应数据的超时时间 默认为60s
    proxy_read_timeout 600s;
    # 设置向后端服务器传输请求数据的超时时间 默认为60s
    proxy_send_timeout 600s;
    # 是否启用缓冲机制，默认为on关闭状态。
    proxy_buffering on;
    # 设置缓冲客户端请求数据的内存大小。
    client_body_buffer_size 512k;
    # 为每个请求/连接设置缓冲区的数量和大小，默认4 4k/8k。
    proxy_buffers 4 512k;
    # 设置用于存储响应头的缓冲区大小。
    proxy_buffer_size 512k;
    # 在后端数据没有完全接收完成时，Nginx可以将busy状态的缓冲返回给客户端，该参数用来设置busy状态的buffer具体有多大，默认为proxy_buffer_size*2
    proxy_busy_buffers_size 512k;
    # 设置每次写数据到临时文件的大小限制。
    proxy_temp_file_write_size 2m;
    # path是临时目录的路径
    proxy_temp_path /var/temp_buffer;


    ######################## Nginx缓存 ########################
    # 设置缓存路径并且使用一块最大100M的共享内存，用于硬盘上的文件索引，包括文件名和请求次数，每个文件在1天内若不活跃（无请求）则从硬盘上淘汰，硬盘缓存最大5G，满了则根据LRU算法自动清除缓存。
    proxy_cache_path /var/cache/nginx/cache levels=1:2 use_temp_path=on keys_zone=imgcache:100m inactive=1d max_size=5g;
    # 对于相同的请求，是否开启锁机制，只允许一个请求发往后端。on | off;
    # proxy_cache_lock on;
    # 配置锁超时机制，超出规定时间后会释放请求。默认为5s。
    # proxy_cache_lock_timeout 5;

    # 当上游响应的响应码'大于等于'300[常见"404"、"500"等]时; 按error_page指令处理
    proxy_intercept_errors on;
    # 创建自己的404.html页面 需要放在 nginx 的html路径下
    fastcgi_intercept_errors on;

    include /etc/nginx/conf.d/*.conf;
}

EOF

    mark_rollback "恢复 Nginx 配置" "if [ -f '${nginx_conf_file}.bak.' ]; then true; fi;"
}

start_nginx_service() {
    info "启动 Nginx 服务..."
    run_cmd "systemctl start nginx.service" "启动 Nginx 服务" || return 1
    run_cmd "systemctl enable nginx.service" "启用 Nginx 自启动" || warn "启用 Nginx 自启动失败"
    mark_rollback "停止并禁用 Nginx 服务" "systemctl stop nginx.service || true; systemctl disable nginx.service || true"
}

# ----------------- Redis 安装 -----------------
install_redis() {
    if [ "${INSTALL_REDIS,,}" != "yes" ]; then
        info "跳过 Redis 安装"
        return 0
    fi
    info "安装 Redis $REDIS_VERSION..."

    case "$PKG_MGR" in
        dnf|yum)
            pkg_install "redis"
            ;;
        apt)
            pkg_install "redis-server"
            ;;
    esac

    setup_redis_config
    start_redis_service

    success "Redis 安装完成"
}

setup_redis_config() {
    info "配置 Redis..."
    local redis_conf_file="/etc/redis.conf"

    if [ -f "$redis_conf_file" ]; then
        safe_backup_file "$redis_conf_file"
    fi

    modify_file "$redis_conf_file" \
        "daemonize.*:daemonize yes:replace" \
        "# requirepass.*:requirepass $REDIS_PASS:insert" \
        "bind.*:bind 0.0.0.0:replace"

    mark_rollback "恢复 Redis 配置" "if [ -f '${redis_conf_file}.bak.' ]; then true; fi;"
}

start_redis_service() {
    info "启动 Redis 服务..."
    run_cmd "systemctl start redis.service" "启动 Redis 服务" || return 1
    run_cmd "systemctl enable redis.service" "启用 Redis 自启动" || warn "启用 Redis 自启动失败"
    mark_rollback "停止并禁用 Redis 服务" "systemctl stop redis.service || true; systemctl disable redis.service || true"
}

# ----------------- Composer 安装 -----------------
install_composer() {
    if [ "${INSTALL_COMPOSER,,}" != "yes" ]; then
        info "跳过 Composer 安装"
        return 0
    fi
    info "安装 Composer..."

    local composer_installer="/tmp/composer-setup.php"
    register_tmp_file "$composer_installer"

    run_cmd_with_retry "curl -sS https://getcomposer.org/installer -o '$composer_installer'" "下载 Composer 安装器" || return 1
    run_cmd "php '$composer_installer' --install-dir=/usr/local/bin --filename=composer" "安装 Composer" || return 1
    run_cmd "chmod +x /usr/local/bin/composer" "设置 Composer 可执行权限" || return 1

    mark_rollback "移除 Composer" "rm -f /usr/local/bin/composer || true"

    success "Composer 安装完成"
}

# ----------------- 03、系统用户创建 -----------------
create_system_users() {
    info "创建系统用户和组..."

    # 如果用户组不存在，则创建
    if ! getent group "$GROUP_NAME" >/dev/null 2>&1; then
        run_cmd "sudo groupadd $GROUP_NAME" "创建用户组 $GROUP_NAME" || return 1
    fi

    # 如果用户不存在，则创建
    if ! id "$WWW_USER" >/dev/null 2>&1; then
        # 创建用户并将其加入用户组
        run_cmd "sudo useradd -g '$GROUP_NAME' '$WWW_USER'" "创建用户到组下 $WWW_USER" || return 1
        # 设置密码
        echo "${WWW_USER}:${WWW_PASS}" | chpasswd
        CREATED_USERS+=("$WWW_USER")
        mark_rollback "删除用户 $WWW_USER" "userdel '$WWW_USER' || true"
    fi

    success "系统用户创建完成"
}

# ----------------- 防火墙配置 -----------------
configure_firewall() {
    info "配置防火墙..."
    if command -v firewall-cmd >/dev/null 2>&1; then
        run_cmd "firewall-cmd --permanent --add-service=http" "开放 HTTP 端口" || true
        run_cmd "firewall-cmd --permanent --add-service=https" "开放 HTTPS 端口" || true
        run_cmd "firewall-cmd --permanent --add-port=9000/tcp" "开放 PHP-FPM 端口" || true
        run_cmd "firewall-cmd --reload" "重载防火墙" || true
        success "防火墙配置完成"
    elif command -v ufw >/dev/null 2>&1; then
        run_cmd "ufw allow http" "开放 HTTP 端口" || true
        run_cmd "ufw allow https" "开放 HTTPS 端口" || true
        run_cmd "ufw allow 9000/tcp" "开放 PHP-FPM 端口" || true
        success "UFW 防火墙配置完成"
    else
        warn "未找到支持的防火墙工具，跳过防火墙配置"
    fi
}

# ----------------- 安装总结与信息输出 -----------------
generate_install_summary() {
    local end_time=$(date +%s)
    local duration=$((end_time - START_TIME))

    cat > "$INSTALL_SUMMARY" <<EOF
# ==================== PHP 环境安装配置摘要 ====================
# 生成时间: $(date '+%F %T')
# 安装耗时: ${duration} 秒
# 系统信息: $OS_NAME $OS_VERSION ($OS_ARCH)

# -------------------- 安装路径信息 --------------------
PHP 安装路径: $PHP_PREFIX
MySQL 安装路径: $MYSQL_PREFIX
Nginx 安装路径: $NGINX_PREFIX
Redis 安装路径: $REDIS_PREFIX
源码目录: $SRC_DIR

# -------------------- 安全凭证信息 --------------------
MySQL root 密码: $MYSQL_ROOT_PASS
远程管理用户: $REMOTE_ADMIN_USER
远程管理密码: $REMOTE_ADMIN_PASS
Web服务用户组: $GROUP_NAME
Web 服务用户: $WWW_USER
Web 服务密码: $WWW_PASS
Redis密码: $REDIS_PASS

# -------------------- 服务状态 --------------------
PHP-FPM: $(systemctl is-active php-fpm.service 2>/dev/null || echo "未安装")
MySQL: $(systemctl is-active mysqld.service 2>/dev/null || systemctl is-active mysql.service 2>/dev/null || echo "未安装")
Nginx: $(systemctl is-active nginx.service 2>/dev/null || echo "未安装")
Redis: $(systemctl is-active redis.service 2>/dev/null || echo "未安装")

# -------------------- 连接信息 --------------------
PHP-FPM 监听: 127.0.0.1:9000
MySQL 监听: 0.0.0.0:3306
Redis 监听: 0.0.0.0:6379
Nginx 监听: 0.0.0.0:80, 0.0.0.0:443

# -------------------- 重要文件路径 --------------------
PHP 配置文件: $PHP_INI_FILE
PHP-FPM 配置文件: $PHP_PREFIX/etc/php-fpm.conf
MySQL 配置文件: /etc/my.cnf
Nginx 配置文件: /etc/nginx/nginx.conf
Redis 配置文件: /etc/redis/redis.conf

# -------------------- 环境变量 --------------------
PHP_HOME: $PHP_PREFIX
PATH: 已添加 $PHP_PREFIX/bin

# -------------------- 使用说明 --------------------
1. 启动所有服务: systemctl start php-fpm mysql nginx redis
2. 设置开机启动: systemctl enable php-fpm mysql nginx redis
3. 测试 PHP: php -v
4. 测试 MySQL: mysql -u root -p'$MYSQL_ROOT_PASS'
5. 测试 Redis: redis-cli -a '$REMOTE_ADMIN_PASS'

# -------------------- 安全建议 --------------------
1. 请立即修改上述所有密码
2. 配置适当的防火墙规则
3. 定期备份重要数据
4. 监控系统日志和安全事件

# -------------------- 日志文件位置 --------------------
安装日志: $LOG_FILE
错误日志: $ERROR_LOG_FILE
回滚日志: $ROLLBACK_LOG_FILE
PHP 错误日志: $PHP_PREFIX/var/log/php_errors.log
PHP-FPM 日志: $PHP_PREFIX/var/log/php-fpm.log
MySQL 日志: /var/log/mysqld.log
Nginx 访问日志: /var/log/nginx/access.log
Nginx 错误日志: /var/log/nginx/error.log
Redis 日志: /var/log/redis/redis.log
EOF

    chmod 600 "$INSTALL_SUMMARY"
    success "安装配置摘要已保存到: $INSTALL_SUMMARY"
    warn "请妥善保管此文件，特别是其中的密码信息！"
}

show_final_message() {
    echo ""
    echo "================================================================"
    echo "✅ PHP 环境安装完成！"
    echo "================================================================"
    echo "📋 安装摘要已保存到: $INSTALL_SUMMARY"
    echo "📝 安装日志: $LOG_FILE"
    echo "❌ 错误日志: $ERROR_LOG_FILE"
    echo "↩️  回滚日志: $ROLLBACK_LOG_FILE"
    echo ""
    echo "🔐 重要安全信息:"
    echo "   - MySQL root 密码: $MYSQL_ROOT_PASS"
    echo "   - 远程管理账号: $REMOTE_ADMIN_USER / $REMOTE_ADMIN_PASS"
    echo "   - 请立即修改这些密码！"
    echo ""
    echo "🚀 下一步操作:"
    echo "   1. 查看安装摘要文件获取详细信息"
    echo "   2. 测试各服务是否正常运行"
    echo "   3. 配置您的应用程序"
    echo "   4. 设置适当的防火墙规则"
    echo ""
    echo "💡 提示: 所有服务已配置为开机自动启动（如设置）"
    echo "================================================================"
}

# ----------------- 主安装流程 -----------------
main() {
    clear
    echo ""
    echo "================================================================"
    echo "🚀 开始安装 PHP $PHP_VERSION 环境栈"
    echo "================================================================"
    echo "📝 安装选项:"
    echo "   - PHP: $INSTALL_PHP"
    echo "   - MySQL: $INSTALL_MYSQL"
    echo "   - Redis: $INSTALL_REDIS"
    echo "   - Nginx: $INSTALL_NGINX"
    echo "   - Composer: $INSTALL_COMPOSER"
    echo "   - PHP 扩展: $INSTALL_PHP_EXTENSIONS"
    echo ""
    echo "⚙️  配置参数:"
    echo "   - PHP 路径: $PHP_PREFIX"
    echo "   - Web 用户: $WWW_USER"
    echo "   - 并发编译: $MAKE_JOBS 线程"
    echo "================================================================"

    # 安装前检查
    if [ "$(id -u)" -ne 0 ]; then
        error "此脚本必须以 root 用户身份运行"
        exit 1
    fi

    # 系统检测
    detect_system || exit 1

    # 主安装流程
    install_dependencies || handle_error ${LINENO} "安装依赖失败" $?
    create_system_users || handle_error ${LINENO} "创建用户失败" $?

    # 为了防止虚拟内存不足:先配置SWAP
    setup_swap

    if [ "${INSTALL_PHP,,}" = "yes" ]; then
        download_php || handle_error ${LINENO} "下载 PHP 失败" $?
        extract_php || handle_error ${LINENO} "解压 PHP 失败" $?
        configure_php || handle_error ${LINENO} "配置 PHP 失败" $?
        build_php || handle_error ${LINENO} "编译 PHP 失败" $?
        setup_php_config || handle_error ${LINENO} "配置 PHP 失败" $?
        create_phpfpm_service || handle_error ${LINENO} "创建 PHP-FPM 服务失败" $?
        install_php_extensions || warn "PHP 扩展安装有部分失败"
    fi

    if [ "${INSTALL_MYSQL,,}" = "yes" ]; then
        install_mysql || handle_error ${LINENO} "安装 MySQL 失败" $?
    fi

    if [ "${INSTALL_NGINX,,}" = "yes" ]; then
        install_nginx || handle_error ${LINENO} "安装 Nginx 失败" $?
    fi

    if [ "${INSTALL_REDIS,,}" = "yes" ]; then
        install_redis || handle_error ${LINENO} "安装 Redis 失败" $?
    fi

    if [ "${INSTALL_COMPOSER,,}" = "yes" ]; then
        install_composer || handle_error ${LINENO} "安装 Composer 失败" $?
    fi

    # configure_firewall || warn "防火墙配置有部分失败"

    # 清理和总结
    if [ "${CLEAN_TEMP,,}" = "yes" ]; then
        cleanup_tmpfiles || warn "临时文件清理失败"
    fi

    generate_install_summary
    show_final_message

    local end_time=$(date +%s)
    local duration=$((end_time - START_TIME))
    success "✅ 全部安装完成！总耗时: ${duration} 秒"
}

# 执行主函数
main "$@"
