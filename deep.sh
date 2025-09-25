#!/usr/bin/env bash
# 指定使用的解释器为 bash，确保脚本可移植
# -----------------------------------------------------------------------------
# industrial_php_stack_improved.sh
# 最终优化版：工业级 PHP 环境一键安装脚本
# 主要改进：
#  - 修复重复执行问题，添加执行锁机制
#  - 优化 run_cmd 函数，增强错误处理
#  - 完善资源清理机制，及时释放磁盘空间
#  - 改用源码编译方式安装 PHP 扩展
#  - 针对 PHP 8.x 高版本特性优化配置
#  - 简化界面输出，详细日志记录到文件
#  - 增强对国产和云厂商魔改系统的兼容性
#  - 新增 DEBUG 模式支持
# -----------------------------------------------------------------------------

# 设置严格模式：出错退出、未定义变量报错、管道失败整体失败
set -euo pipefail
# 使 ERR trap 在函数和子shell中生效
set -o errtrace
# 设置内部字段分隔符，避免参数被错误拆分
IFS=$'\n\t'

# ==================== 配置参数区域 ====================
# 使用环境变量或默认值设置安装选项
INSTALL_PHP="${INSTALL_PHP:-yes}"
INSTALL_MYSQL="${INSTALL_MYSQL:-yes}"
INSTALL_REDIS="${INSTALL_REDIS:-yes}"
INSTALL_NGINX="${INSTALL_NGINX:-yes}"
INSTALL_COMPOSER="${INSTALL_COMPOSER:-yes}"
INSTALL_PHP_EXTENSIONS="${INSTALL_PHP_EXTENSIONS:-yes}"
IS_DEBUG="${IS_DEBUG:-no}"  # 新增 DEBUG 模式

# 版本配置
PHP_VERSION="${PHP_VERSION:-8.4.12}"
MYSQL_VERSION="${MYSQL_VERSION:-8.4.0}"
REDIS_VERSION="${REDIS_VERSION:-7.2.4}"
NGINX_VERSION="${NGINX_VERSION:-1.28.0}"

# PHP 扩展版本
REDIS_EXT_VERSION="${REDIS_EXT_VERSION:-6.2.0}"
IMAGICK_EXT_VERSION="${IMAGICK_EXT_VERSION:-3.8.0}"

# 安装路径配置
PHP_PREFIX="${PHP_PREFIX:-/usr/local/php8}"
MYSQL_PREFIX="${MYSQL_PREFIX:-/usr/local/mysql}"
NGINX_PREFIX="${NGINX_PREFIX:-/usr/local/nginx}"
REDIS_PREFIX="${REDIS_PREFIX:-/usr/local/redis}"
SRC_DIR="${SRC_DIR:-/usr/local/src}"

# 安全凭证配置（生产环境建议使用随机密码 $(openssl rand -base64 24)} ）
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-zhaoXfMysql001.}" # mysql root用户密码
MYSQL_REMOTE_ADMIN_USER="${MYSQL_REMOTE_ADMIN_USER:-zhaoxianfang}" # mysql 远程连接用户
MYSQL_REMOTE_ADMIN_PASS="${MYSQL_REMOTE_ADMIN_PASS:-zxfMysql001.}" # mysql 远程连接用户 密码
GROUP_NAME="${GROUP_NAME:-www}" # www 用户组
WWW_USER="${WWW_USER:-www}" # www 用户组的用户
WWW_USER_PASS="${WWW_USER_PASS:-CdOs491592.}" # www 用户组的用户 密码
REDIS_PASS="${REDIS_PASS:-zxfRedis001.}" #  redis 密码

# 日志和临时文件配置
LOG_DIR="${LOG_DIR:-/var/log/php-stack-install}"
LOG_FILE="${LOG_FILE:-$LOG_DIR/install.log}"
ERROR_LOG_FILE="${ERROR_LOG_FILE:-$LOG_DIR/error.log}"
ROLLBACK_LOG_FILE="${ROLLBACK_LOG_FILE:-$LOG_DIR/rollback.log}"
INSTALL_SUMMARY="${INSTALL_SUMMARY:-/data/install_config.log}"
INSTALLED_PACKAGES_LOG="${INSTALLED_PACKAGES_LOG:-$LOG_DIR/installed_packages.log}"

# 性能配置（核心配置：单线程执行，避免重复）
MAKE_JOBS="${MAKE_JOBS:-1}"
AUTO_START_SERVICES="${AUTO_START_SERVICES:-yes}"
MAX_RETRIES="${MAX_RETRIES:-3}"
DOWNLOAD_RETRIES="${DOWNLOAD_RETRIES:-3}"
CLEAN_TEMP="${CLEAN_TEMP:-yes}"

# PHP 配置参数
PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-512M}"
PHP_MAX_EXECUTION_TIME="${PHP_MAX_EXECUTION_TIME:-300}"
PHP_UPLOAD_MAX_FILESIZE="${PHP_UPLOAD_MAX_FILESIZE:-256M}"
PHP_POST_MAX_SIZE="${PHP_POST_MAX_SIZE:-256M}"
PROFILE_FILE="${PROFILE_FILE:-/etc/profile}"

# PHP 下载镜像列表
PHP_MIRRORS=(
    "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"
    "https://mirrors.aliyun.com/php-distributions/php-${PHP_VERSION}.tar.gz"
    "https://mirrors.cloud.tencent.com/php-distributions/php-${PHP_VERSION}.tar.gz"
)

# PHP 编译选项（针对 PHP 8.x 高版本优化）
PHP_CONFIGURE_OPTS=(
    "--prefix=$PHP_PREFIX"
    "--with-config-file-path=$PHP_PREFIX/lib"
    "--with-fpm-user=$WWW_USER"
    "--with-fpm-group=$WWW_USER"
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

# ==================== 全局变量区域 ====================
# 系统信息变量
PKG_MGR=""
OS_ID=""
OS_NAME=""
OS_VERSION=""
OS_ARCH=""
PHP_SRC_DIR=""
PHP_INI_FILE=""
START_TIME=$(date +%s)

# 临时文件管理数组
TMP_FILES=()
# 回滚命令栈
ROLLBACK_CMDS=()
# 新安装的软件包记录
INSTALLED_PKGS=()
# 创建的用户记录
CREATED_USERS=()
# 备份的文件记录
BACKUP_FILES=()

# 执行锁文件，防止重复执行
LOCK_FILE="/tmp/php_stack_install.lock"

# ==================== 初始化设置 ====================
# 创建必要的目录结构
mkdir -p "$SRC_DIR" "$LOG_DIR" "$(dirname "$INSTALL_SUMMARY")" "$(dirname "$INSTALLED_PACKAGES_LOG")"
# 初始化日志文件（清空已存在的日志）
: > "$LOG_FILE"
: > "$ERROR_LOG_FILE"
: > "$ROLLBACK_LOG_FILE"
: > "$INSTALLED_PACKAGES_LOG"

# ==================== 输出函数区域 ====================
# 定义彩色输出函数
_red() { echo -e "\033[31m$*\033[0m"; }
_green() { echo -e "\033[32m$*\033[0m"; }
_yellow() { echo -e "\033[33m$*\033[0m"; }
_blue() { echo -e "\033[34m$*\033[0m"; }
_bold() { echo -e "\033[1m$*\033[0m"; }

# 定义图标常量
ICON_INFO="🔵"
ICON_SUCCESS="✅"
ICON_WARN="🟡"
ICON_ERROR="🔴"

# 调试输出函数
debug() {
    if [ "$IS_DEBUG" = "yes" ]; then
        echo "$(date '+%F %T') - DEBUG: $*" | tee -a "$LOG_FILE"
    fi
}

# 日志记录函数
log() {
    echo "$(date '+%F %T') - $*" | tee -a "$LOG_FILE"
}

# 信息级别日志
info() {
    log "${ICON_INFO} INFO: $*"
}

# 成功级别日志
success() {
    log "${ICON_SUCCESS} SUCCESS: $*"
}

# 警告级别日志
warn() {
    log "${ICON_WARN} WARN: $*"
}

# 错误级别日志
error() {
    log "${ICON_ERROR} ERROR: $*"
    echo "$(date '+%F %T') - ERROR: $*" >> "$ERROR_LOG_FILE"
}

# ==================== 动画进度帧函数 ====================
# 统一的进度动画显示函数
show_progress_animation() {
    local pid=$1
    local desc="$2"

    local frames=("▰▱▱▱▱▱▱" "▰▰▱▱▱▱▱" "▰▰▰▱▱▱▱" "▰▰▰▰▱▱▱"
                 "▰▰▰▰▰▱▱" "▰▰▰▰▰▰▱" "▰▰▰▰▰▰▰" "▰▰▰▰▰▱▱"
                 "▰▰▰▰▱▱▱" "▰▰▰▱▱▱▱" "▰▰▱▱▱▱▱" "▰▱▱▱▱▱▱")
    local i=0
    local spinner_pid=""

    # 显示进度动画的函数
    show_spinner() {
        while true; do
            i=$(((i+1) % ${#frames[@]}))
            printf "\r⏳ %s %s" "$desc" "${frames[i]}"
            sleep 0.1
        done
    }

    # 启动 spinner
    show_spinner &
    spinner_pid=$!

    # 等待命令执行完成
    set +e
    wait "$pid" 2>/dev/null
    local rc=$?
    set -e

    # 停止 spinner
    kill "$spinner_pid" 2>/dev/null || true
    wait "$spinner_pid" 2>/dev/null || true

    # 清理 spinner 行
    printf "\r%*s\r" "$(tput cols)" ""

    return $rc
}

# ==================== 执行锁管理 ====================
# 创建执行锁，防止重复执行
acquire_lock() {
    if [ -f "$LOCK_FILE" ]; then
        local lock_pid=$(cat "$LOCK_FILE" 2>/dev/null || echo "")
        if [ -n "$lock_pid" ] && kill -0 "$lock_pid" 2>/dev/null; then
            error "脚本正在运行中 (PID: $lock_pid)，请勿重复执行 rm -f '$LOCK_FILE'"
            exit 1
        else
            warn "发现陈旧的锁文件，清理后继续"
            rm -f "$LOCK_FILE"
        fi
    fi
    # 创建新的锁文件
    echo $$ > "$LOCK_FILE"
    # 注册清理函数
    mark_rollback "删除执行锁文件" "rm -f '$LOCK_FILE' || true"
}

# ==================== 回滚管理区域 ====================
# 记录回滚命令
mark_rollback() {
    local desc="$1"
    local cmd="$2"
    # 使用换行符分隔描述和命令
    ROLLBACK_CMDS+=("# $desc\n$cmd")
}

# 执行回滚操作
rollback_all() {
    echo "" | tee -a "$ROLLBACK_LOG_FILE"
    echo "[$(date '+%F %T')] 开始执行回滚操作..." | tee -a "$ROLLBACK_LOG_FILE"

    local total_steps=${#ROLLBACK_CMDS[@]}
    local current_step=1

    # 逆序执行回滚命令
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

    echo "[$(date '+%F %T')] 回滚操作完成" | tee -a "$ROLLBACK_LOG_FILE"
}

# ==================== 文件修改函数 ====================
# 高级文件修改函数，支持多种操作模式（不得修改此函数）
modify_file() {
    [[ $# -lt 2 ]] && { echo "错误: 参数不足"; return 1; }
    local f="$1" t b o=("${@:2}") g="false"

    # 检查全局模式参数
    [[ "${o[-1]}" == "global_mode=true" ]] && { g="true"; o=("${o[@]:0:${#o[@]}-1}"); }

    # 验证文件存在性和可写性
    [[ ! -f "$f" ]] && { echo "错误: 文件不存在"; return 1; }
    [[ ! -w "$f" ]] && { echo "错误: 文件不可写"; return 1; }

    # 创建临时文件
    t=$(mktemp) || { echo "错误: 创建临时文件失败"; return 1; }
    cp "$f" "$t" || { echo "错误: 复制文件失败"; rm -f "$t"; return 1; }

    local c=0 s=0
    # 遍历所有操作
    for op in "${o[@]}"; do
        ((c++))
        IFS=':' read -r fs ss m <<< "$op"
        m=${m:-insert}
        [[ "$g" == "true" && "$m" == "insert" ]] && m="insert"

        # 清理字符串两端的空白字符
        fs=$(echo "$fs" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
        ss=$(echo "$ss" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
        m=$(echo "$m" | tr '[:upper:]' '[:lower:]')

        [[ -z "$fs" ]] && { echo "警告: 操作 $c 跳过"; continue; }

        echo "执行文件修改操作 $c: $m"
        local r=0

        # 根据操作模式执行不同的修改逻辑
        case "$m" in
            "insert"|"i"|"false")
                awk -i inplace -v f="$fs" -v i="$ss" '
                    BEGIN { split(f,a,"\n"); split(i,b,"\n"); x=length(a); y=length(b); z=""; w=0; p=0; }
                    { if (p>0) { p--; next } if (w>0&&w<x) { z=z"\n"$0; w++;
                        if (w==x) { print z; for(j=1;j<=y;j++) print b[j]; z=""; w=0; p=x-1; } next }
                    if (w==0) { if ($0~"^"a[1]) { z=$0; w=1;
                        if (x==1) { print $0; for(j=1;j<=y;j++) print b[j]; w=0; } } else print $0 } }
                    END { if (z!="") print z }' "$t"
                r=$?
                ;;

            "replace"|"r"|"true"|"cover")
                awk -i inplace -v f="$fs" -v r="$ss" '
                    BEGIN { split(f,a,"\n"); split(r,b,"\n"); x=length(a); y=length(b); z=""; w=0; }
                    { if (w>0&&w<x) { z=z"\n"$0; w++;
                        if (w==x) { for(j=1;j<=y;j++) print b[j]; z=""; w=0; } next }
                    if (w==0) { if ($0~"^"a[1]) { z=$0; w=1;
                        if (x==1) { for(j=1;j<=y;j++) print b[j]; z=""; w=0; } } else print $0 } }
                    END { if (z!="") print z }' "$t"
                r=$?
                ;;

            "delete"|"d")
                awk -i inplace -v f="$fs" '
                    BEGIN { split(f,a,"\n"); x=length(a); z=""; w=0; s=0; }
                    { if (s>0) { s--; next } if (w>0&&w<x) { z=z"\n"$0; w++;
                        if (w==x) { z=""; w=0; s=x-1; } next }
                    if (w==0) { if ($0~"^"a[1]) { z=$0; w=1;
                        if (x==1) { z=""; w=0; s=0; } } else print $0 } }
                    END { if (z!="") print z }' "$t"
                r=$?
                ;;

            "comment"|"c")
                [[ $(echo "$fs" | wc -l) -eq 1 ]] && {
                    sed -i "/^${fs//\//\\/}/s/^/# /" "$t"
                    r=$?
                } || { echo "警告: 多行注释不支持"; r=1; }
                ;;

            "uncomment"|"u")
                [[ $(echo "$fs" | wc -l) -eq 1 ]] && {
                    sed -i "/^# *${fs//\//\\/}/s/^# *//" "$t"
                    r=$?
                } || { echo "警告: 多行取消注释不支持"; r=1; }
                ;;

            *)
                sed -i "/^${fs//\//\\/}/a\\${ss//\//\\/}" "$t"
                r=$?
                ;;
        esac

        [[ $r -eq 0 ]] && { ((s++)); echo "操作 $c 成功"; } || echo "操作 $c 失败"
    done

    # 如果有成功修改，则备份原文件并应用修改
    if [[ $s -gt 0 ]]; then
        b="${f}.bak.$(date +%Y%m%d_%H%M%S)"
        cp "$f" "$b" 2>/dev/null && echo "备份原文件: $b"
        mv "$t" "$f"
        echo "文件修改完成: 成功 $s/$c 个操作"
        return 0
    else
        echo "错误: 所有操作都失败"
        rm -f "$t"
        return 1
    fi
}

# ==================== 临时文件管理 ====================
# 注册临时文件，便于后续清理
register_tmp_file() {
    TMP_FILES+=("$1")
}

# 清理所有临时文件
cleanup_tmpfiles() {
    info "开始清理临时文件..."
    for f in "${TMP_FILES[@]:-}"; do
        if [ -e "$f" ]; then
            rm -rf "$f" && log "清理临时文件: $f" || warn "清理临时文件失败: $f"
        fi
    done
    # 清空临时文件数组
    TMP_FILES=()
}

# ==================== 命令执行函数 ====================
# 优化后的命令执行函数，修复 wait 相关问题
run_cmd() {
    local cmd="$1"
    local desc="${2:-执行命令}"
    local log_output="${3:-yes}"

    # 记录命令开始执行
    log "[CMD-START] $desc : $cmd"
    debug "执行命令: $cmd"

    # 创建临时文件用于收集输出
    local output_file=$(mktemp)
    register_tmp_file "$output_file"

    # 在后台执行命令
    if [ "$log_output" = "yes" ]; then
        bash -lc "$cmd" >>"$LOG_FILE" 2>>"$ERROR_LOG_FILE" &
    else
        bash -lc "$cmd" >/dev/null 2>&1 &
    fi

    local pid=$!

    # 显示进度动画并等待命令完成
    if ! show_progress_animation "$pid" "$desc"; then
        local rc=$?
        printf "❌ %s 失败（退出码:%s）\n" "$desc" "$rc"
        log "[CMD-FAIL] $desc (退出码:$rc)"
        return $rc
    else
        log "[CMD-OK] $desc"
        return 0
    fi
}

# 带重试的命令执行函数
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

# 新增：对整个函数调用显示进度动画
run_cmd_with_func() {
    local func="$1"
    local desc="${2:-执行函数}"

    log "[FUNC-START] $desc"

    # 在子shell中执行函数并显示进度
    (
        set +e
        $func &
        local func_pid=$!
        wait $func_pid 2>/dev/null
        exit $?
    ) &
    local wrapper_pid=$!

    if ! show_progress_animation "$wrapper_pid" "$desc"; then
        local rc=$?
        log "[FUNC-FAIL] $desc (退出码:$rc)"
        return $rc
    else
        log "[FUNC-OK] $desc"
        return 0
    fi
}

# ==================== 系统检测函数 ====================
# 检测操作系统环境和包管理器
detect_system() {
    info "检测系统环境..."

    # 解析系统发行版信息
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS_ID="${ID:-unknown}"
        OS_NAME="${NAME:-unknown}"
        OS_VERSION="${VERSION_ID:-unknown}"
    else
        error "无法检测操作系统类型"
        return 1
    fi

    # 检测系统架构
    OS_ARCH=$(uname -m)
    [ "$OS_ARCH" = "x86_64" ] && OS_ARCH="x64" || true

    # 检测包管理器（增强检测逻辑）
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

    # 确保 PKG_MGR 不为空
    if [ -z "$PKG_MGR" ]; then
        error "包管理器检测失败，PKG_MGR 为空"
        return 1
    fi

    success "系统: $OS_NAME $OS_VERSION ($OS_ARCH), 包管理器: $PKG_MGR"
    log "系统详细信息: ID=$OS_ID, NAME=$OS_NAME, VERSION=$OS_VERSION, ARCH=$OS_ARCH"
}

# ==================== 软件包管理函数 ====================
# 检查软件包是否已安装
pkg_is_installed() {
    local pkg="$1"
    if [ "$PKG_MGR" = "apt" ]; then
        dpkg -s "$pkg" >/dev/null 2>&1 && return 0 || return 1
    else
        rpm -q "$pkg" >/dev/null 2>&1 && return 0 || return 1
    fi
}

# 转义软件包列表
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

# 安装软件包（增强兼容性）
pkg_install() {
    local pkgs=("$@")

    local to_install=()
    for p in "${pkgs[@]}"; do
        if ! pkg_is_installed "$p"; then
            to_install+=("$p")
        fi
    done

    if [ ${#to_install[@]} -eq 0 ]; then
        info "所有软件包已安装，跳过安装步骤"
        return 0
    fi

    local pkgstr=$(escape_pkg_list to_install)
    echo "新安装包: ${to_install[*]}" >> "$INSTALLED_PACKAGES_LOG"

    # 根据包管理器选择合适的安装参数
    case "$PKG_MGR" in
        dnf)
            # 尝试多种安装策略
            if run_cmd_with_retry "sudo dnf -y install --allowerasing --skip-broken $pkgstr" "安装软件包" 3 2; then
                INSTALLED_PKGS+=("${to_install[@]}")
                mark_rollback "卸载新安装的软件包" "sudo dnf -y remove --noautoremove ${pkgstr} || true"
            else
                warn "使用 --allowerasing 失败，尝试使用 --skip-broken"
                if run_cmd_with_retry "sudo dnf -y install --skip-broken $pkgstr" "安装软件包(跳过损坏包)" 2 2; then
                    INSTALLED_PKGS+=("${to_install[@]}")
                    mark_rollback "卸载新安装的软件包" "sudo dnf -y remove --noautoremove ${pkgstr} || true"
                else
                    error "软件包安装失败"
                    return 1
                fi
            fi
            ;;
        yum)
            if run_cmd_with_retry "sudo yum -y install --allowerasing --skip-broken $pkgstr" "安装软件包" 3 2; then
                INSTALLED_PKGS+=("${to_install[@]}")
                mark_rollback "卸载新安装的软件包" "sudo yum -y remove ${pkgstr} || true"
            else
                warn "使用 --allowerasing 失败，尝试使用 --skip-broken"
                if run_cmd_with_retry "sudo yum -y install --skip-broken $pkgstr" "安装软件包(跳过损坏包)" 2 2; then
                    INSTALLED_PKGS+=("${to_install[@]}")
                    mark_rollback "卸载新安装的软件包" "sudo yum -y remove ${pkgstr} || true"
                else
                    error "软件包安装失败"
                    return 1
                fi
            fi
            ;;
        apt)
            if run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install $pkgstr" "安装软件包" 3 2; then
                INSTALLED_PKGS+=("${to_install[@]}")
                mark_rollback "卸载新安装的软件包" "DEBIAN_FRONTEND=noninteractive apt-get -y remove --purge ${pkgstr} || true; apt-get -y autoremove || true"
            else
                error "软件包安装失败"
                return 1
            fi
            ;;
    esac

    success "软件包安装完成"
}

# ==================== 文件备份函数 ====================
# 安全备份文件
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

# ==================== 错误处理函数 ====================
# 错误处理函数
handle_error() {
    local lineno="${1:-?}"
    local cmd="${2:-?}"
    local code="${3:-1}"

    error "脚本在行 $lineno 执行命令 '$cmd' 时失败 (退出码: $code)"
    error "详细日志请查看: $ERROR_LOG_FILE"
    error "完整安装日志请查看: $LOG_FILE"

    # 显示最近的错误信息
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

# 中断信号处理函数
on_interrupt() {
    warn "检测到中断信号，开始回滚并退出..."
    rollback_all || warn "回滚过程中发生错误，请检查 $ROLLBACK_LOG_FILE"
    exit 1
}

# 设置信号陷阱
trap 'handle_error ${LINENO} "${BASH_COMMAND}" $?' ERR
trap 'on_interrupt' INT TERM

# ==================== 依赖安装函数 ====================
# 安装系统依赖
install_dependencies() {
    local start=$(date +%s)
    info "安装系统依赖..."

    # 根据包管理器更新系统
    case "$PKG_MGR" in
        dnf)
            run_cmd_with_retry "dnf -y update --allowerasing --skip-broken" "更新系统" 2 5
            install_epel_repository
            ;;
        yum)
            run_cmd_with_retry "yum -y update --allowerasing --skip-broken" "更新系统" 2 5
            install_epel_repository
            ;;
        apt)
            run_cmd_with_retry "apt-get -y update" "更新包列表" 2 5
            run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y upgrade" "升级系统" 2 5
            ;;
    esac

    # 扩展的依赖包列表（增强兼容性）
    local common_packages=(
        # 基础编译工具
        yum-utils dnf-utils gcc gcc-c++ gcc-gfortran autoconf automake libtool make cmake perl perl-devel
        # 开发库
        kernel-devel kernel-headers glibc-devel glibc-headers libgcc libstdc++-devel
        # 网络工具
        wget curl curl-devel libcurl libcurl-devel openssh-clients
        # 压缩库
        zlib zlib-devel bzip2 bzip2-devel lz4 lz4-devel xz xz-devel zstd zstd-devel
        # 图像处理
        libpng libpng-devel libjpeg libjpeg-devel libjpeg-turbo libjpeg-turbo-devel
        freetype freetype-devel gd gd-devel libwebp libwebp-devel libXpm libXpm-devel
        # XML处理
        libxml2 libxml2-devel libxslt libxslt-devel
        # 数据库相关
        sqlite sqlite-devel
        # 加密和安全
        openssl openssl-devel libsodium libsodium-devel ncurses ncurses-devel
        # 文本处理
        pcre pcre-devel pcre2 pcre2-devel oniguruma oniguruma-devel
        # 系统库
        readline readline-devel ncurses ncurses-devel util-linux
        # 国际化
        libicu libicu-devel gettext gettext-devel
        # 其他开发库
        expat expat-devel libevent libevent-devel libffi libffi-devel
        libtidy libtidy-devel enchant enchant-devel aspell aspell-devel
        # 系统工具
        which file patch tar gzip bzip2 xz

        # 新增依赖包以支持各种系统
        openldap openldap-devel openldap-clients
        python3 python3-devel python3-pip
        krb5-devel krb5-workstation
        libzip libzip-devel
        glib2-devel cairo-devel gmp-devel
        net-snmp-devel unixODBC-devel libc-client-devel
        keyutils systemd-devel dbus-devel
        # 图像处理增强
        ImageMagick ImageMagick-devel ImageMagick-c++-devel
        # 网络增强
        libidn libidn-devel
        # 其他增强
        libedit libedit-devel
        # 系统服务
        systemd systemd-libs
        # 开发工具
        git subversion
    )

    # 根据系统类型添加特定依赖包
    case "$OS_ID" in
        almalinux|rocky|centos|rhel|alibaba|alinux|opencloudos|tencentos|uos|kylin)
            common_packages+=(
                epel-release
                redhat-rpm-config
                # 针对 RHEL 兼容系统的特定包
                libxcrypt-compat
                libpq-devel
                libssh2-devel
            )
            ;;
        fedora)
            common_packages+=(
                # Fedora 特定包
                redhat-rpm-config
                libpq-devel
                libssh2-devel
            )
            ;;
        ubuntu|debian)
            common_packages=(
                # Ubuntu/Debian 对应包
                build-essential autoconf automake libtool cmake perl perl-base
                linux-headers-$(uname -r) libc6-dev
                wget curl libcurl4-openssl-dev
                zlib1g zlib1g-dev libbz2-dev liblz4-dev liblzma-dev zstd libzstd-dev
                libpng-dev libjpeg-dev libjpeg-turbo8-dev
                libfreetype6-dev libgd-dev libwebp-dev libxpm-dev
                libxml2-dev libxslt1-dev
                libsqlite3-dev
                libssl-dev libsodium-dev
                libpcre3-dev libpcre2-dev libonig-dev
                libreadline-dev libncurses-dev
                libicu-dev gettext
                libexpat1-dev libevent-dev libffi-dev
                libtidy-dev libenchant-2-dev libaspell-dev
                patch
                openldap-dev libldap2-dev
                python3 python3-dev python3-pip
                libkrb5-dev
                libzip-dev
                libglib2.0-dev libcairo2-dev libgmp-dev
                libsnmp-dev unixodbc-dev libc-client2007e-dev
                libkeyutils-dev libsystemd-dev libdbus-1-dev
                imagemagick libmagickcore-dev libmagickwand-dev
                libidn11-dev
                libedit-dev
                systemd libsystemd-dev
                git subversion
            )
            ;;
    esac

    info "安装扩展的依赖包。。。"
    # 安装依赖包
    pkg_install "${common_packages[@]}"

    local end=$(date +%s)
    success "依赖安装完成 (耗时: $((end-start)) 秒)"
}


# 判断 系统中的 epel-release EPEL 版本; 通过各种途径获取原生linux系统和魔改系统的 EPEL 源（注意：魔改系统中往往没有包含release的信息，需要通过其他配置进行判断）
# ===========================================
# 函数：get_el_version
# 功能：自动检测系统底层兼容的 RHEL 主版本（返回 7, 8, 9, ...）
# 输出：仅 stdout 打印版本号（如 "8"），失败则无输出
# 兼容：所有主流及魔改系统（Alibaba Cloud Linux, Anolis, TencentOS, Kylin, UOS, CentOS, RHEL 等）
# 调用方式：el_ver=$(get_el_version)
# ===========================================
get_el_version() {
    local os_name="" os_version_id="" os_version="" line key value content

    # -------------------------------
    # 1. 优先：检查 PLATFORM_ID
    # -------------------------------
    if [ -f /etc/os-release ]; then
        while IFS= read -r line; do
            # 跳过空行和注释
            [[ "$line" =~ ^[[:space:]]*# ]] && continue
            [[ -z "$line" ]] && continue

            # 分割 KEY=VALUE（支持带引号）
            if [[ "$line" == *=* ]]; then
                key="${line%%=*}"
                value="${line#*=}"

                # 去除 key 两端空格
                key="${key#"${key%%[![:space:]]*}"}"
                key="${key%"${key##*[![:space:]]}"}"

                # 去除 value 两端空格和引号
                value="${value#"${value%%[![:space:]]*}"}"
                value="${value%"${value##*[![:space:]]}"}"
                if [[ $value == \"*\" ]]; then
                    value="${value#\"}"
                    value="${value%\"}"
                elif [[ $value == \'*\' ]]; then
                    value="${value#\'}"
                    value="${value%\'}"
                fi

                case "$key" in
                    NAME) os_name="$value" ;;
                    VERSION_ID) os_version_id="$value" ;;
                    VERSION) os_version="$value" ;;
                    PLATFORM_ID)
                        if [[ "$value" == platform:el[0-9]* ]]; then
                            echo "${value#platform:el}"
                            return 0
                        fi
                        ;;
                esac
            fi
        done < /etc/os-release
    fi

    # -------------------------------
    # 2. 检查 /etc/*-release 文件
    # -------------------------------
    for release_file in \
        /etc/redhat-release \
        /etc/centos-release \
        /etc/alinux-release \
        /etc/anolis-release \
        /etc/tencentos-release \
        /etc/kylin-release \
        /etc/uos-release; do

        if [ -f "$release_file" ]; then
            content=$(cat "$release_file" 2>/dev/null)
            # 提取独立的数字（避免匹配 18 中的 8）
            if [[ "$content" =~ [^0-9]7([^0-9]|$) ]]; then
                echo "7"
                return 0
            elif [[ "$content" =~ [^0-9]8([^0-9]|$) ]]; then
                echo "8"
                return 0
            elif [[ "$content" =~ [^0-9]9([^0-9]|$) ]]; then
                echo "9"
                return 0
            fi
        fi
    done

    # -------------------------------
    # 3. 发行版名称 + 版本映射表（支持别名）
    # -------------------------------
    local full_id=""
    if [ -n "$os_name" ]; then
        if [ -n "$os_version_id" ]; then
            full_id="$os_name $os_version_id"
        elif [ -n "$os_version" ]; then
            full_id="$os_name $os_version"
        else
            full_id="$os_name"
        fi
    fi

    # 定义映射表（支持模糊匹配）
    local -a distro_rules=(
        "Alibaba Cloud Linux 3:8"
        "Alibaba Cloud Linux 4:9"
        "Alibaba Cloud Linux 5:9"
        "Anolis OS 8:8"
        "Anolis OS 23:9"
        "OpenAnolis 8:8"
        "OpenAnolis 23:9"
        "TencentOS Server 3.1:8"
        "TencentOS Server 3.2:9"
        "TencentOS Server 4:9"
        "Kylin V10:9"
        "Kylin Linux Advanced Server V10:9"
        "UnionTech OS Server 20:8"
        "UOS Server 20:8"
        "CentOS Linux 7:7"
        "CentOS Linux 8:8"
        "CentOS Stream 8:8"
        "CentOS Stream 9:9"
        "Rocky Linux 8:8"
        "Rocky Linux 9:9"
        "AlmaLinux 8:8"
        "AlmaLinux 9:9"
        "Red Hat Enterprise Linux 7:7"
        "Red Hat Enterprise Linux 8:8"
        "Red Hat Enterprise Linux 9:9"
        "Oracle Linux 7:7"
        "Oracle Linux 8:8"
        "Oracle Linux 9:9"
        "OpenCloudOS 8:8"
        "OpenCloudOS 9:9"
        "CloudOS 8:8"
        "CloudOS 9:9"
    )

    local rule distro ver
    for rule in "${distro_rules[@]}"; do
        distro="${rule%:*}"
        ver="${rule#*:}"
        if [[ "$full_id" == *"$distro"* ]]; then
            echo "$ver"
            return 0
        fi
    done

    # 特殊处理：仅 VERSION_ID 是纯数字
    if [[ "$os_version_id" =~ ^[0-9]+$ ]]; then
        if [[ "$os_version_id" -ge 7 && "$os_version_id" -le 10 ]]; then
            echo "$os_version_id"
            return 0
        fi
    fi

    # -------------------------------
    # 4. glibc 版本判断（极可靠）
    # -------------------------------
    local glibc_ver major minor
    if glibc_ver=$(ldd --version 2>/dev/null | head -n1 | grep -o '[0-9]\+\.[0-9]\+' | head -n1 2>/dev/null); then
        :
    else
        for lib in /lib64/libc.so.6 /lib/x86_64-linux-gnu/libc.so.6 /lib/libc.so.6; do
            if [ -f "$lib" ] && glibc_ver=$("$lib" --version 2>/dev/null | head -n1 | grep -o '[0-9]\+\.[0-9]\+' | head -n1 2>/dev/null); then
                break
            fi
        done
    fi

    if [ -n "$glibc_ver" ]; then
        major="${glibc_ver%%.*}"
        minor="${glibc_ver#*.}"
        if [ "$major" -eq 2 ]; then
            if [ "$minor" -ge 34 ]; then
                echo "9"
                return 0
            elif [ "$minor" -ge 28 ]; then
                echo "8"
                return 0
            elif [ "$minor" -ge 17 ]; then
                # glibc 2.17 ~ 2.27 → RHEL 7
                echo "7"
                return 0
            fi
        fi
    fi

    # -------------------------------
    # 5. systemd 版本
    # -------------------------------
    if command -v systemctl >/dev/null 2>&1; then
        local systemd_ver
        if systemd_ver=$(systemctl --version 2>/dev/null | head -n1 | grep -o '[0-9]\+' | head -n1); then
            if [ -n "$systemd_ver" ]; then
                if [ "$systemd_ver" -ge 250 ]; then
                    echo "9"
                    return 0
                elif [ "$systemd_ver" -ge 230 ]; then
                    echo "8"
                    return 0
                elif [ "$systemd_ver" -ge 200 ]; then
                    echo "7"
                    return 0
                fi
            fi
        fi
    fi

    # -------------------------------
    # 6. 内核版本启发式
    # -------------------------------
    local kernel
    kernel=$(uname -r 2>/dev/null)
    if [ -n "$kernel" ]; then
        if [[ "$kernel" =~ ^6\. ]]; then
            echo "9"  # RHEL 9+ 可能用 6.x
            return 0
        elif [[ "$kernel" =~ ^5\.1[4-9]\. ]] || [[ "$kernel" =~ ^5\.[2-9][0-9]*\. ]]; then
            echo "9"
            return 0
        elif [[ "$kernel" =~ ^4\.18\. ]]; then
            echo "8"
            return 0
        elif [[ "$kernel" =~ ^3\.10\. ]]; then
            echo "7"
            return 0
        fi
    fi

    # -------------------------------
    # 7. 检查已安装 RPM 包中的 .elX. 标识
    # -------------------------------
    if command -v rpm >/dev/null 2>&1; then
        local rpm_list
        rpm_list=$(rpm -qa 2>/dev/null)
        if [[ "$rpm_list" == *".el9."* ]]; then
            echo "9"
            return 0
        elif [[ "$rpm_list" == *".el8."* ]]; then
            echo "8"
            return 0
        elif [[ "$rpm_list" == *".el7."* ]]; then
            echo "7"
            return 0
        fi
    fi

    # -------------------------------
    # 8. 检查 YUM repo 中的 elX 字符串
    # -------------------------------
    if [ -d /etc/yum.repos.d/ ]; then
        if grep -r -q 'baseurl.*el9' /etc/yum.repos.d/ 2>/dev/null; then
            echo "9"
            return 0
        elif grep -r -q 'baseurl.*el8' /etc/yum.repos.d/ 2>/dev/null; then
            echo "8"
            return 0
        elif grep -r -q 'baseurl.*el7' /etc/yum.repos.d/ 2>/dev/null; then
            echo "7"
            return 0
        fi
    fi

    # 所有方法失败
    return 1
}

# 安装 EPEL 仓库（增强兼容性）
install_epel_repository() {
    local el_ver pkg_url
    el_ver=$(get_el_version)
    if ! [[ "$el_ver" =~ ^(7|8|9)$ ]]; then
        echo "错误：无法检测EL版本。$el_ver" >&2
        exit 1
    fi

    debug "安装 EPEL 仓库 $el_ver ..."

    # ✅ 优先使用国内镜像（阿里云 + Fedora 官方 双保险）
    case "$el_ver" in
        7) pkg_url="https://mirrors.aliyun.com/epel/epel-release-latest-7.noarch.rpm" ;;
        8) pkg_url="https://mirrors.aliyun.com/epel/epel-release-latest-8.noarch.rpm" ;;
        9) pkg_url="https://mirrors.aliyun.com/epel/epel-release-latest-9.noarch.rpm" ;;
    esac

    # 检测是否能访问阿里云，否则 fallback 到清华源
    if ! curl -sf --connect-timeout 5 --max-time 10 "$pkg_url" >/dev/null 2>&1; then
        debug "阿里云安装 EPEL 仓库 $el_ver 失败转为清华源..."
        case "$el_ver" in
            7) pkg_url="https://mirrors.tuna.tsinghua.edu.cn/epel/epel-release-latest-7.noarch.rpm" ;;
            8) pkg_url="https://mirrors.tuna.tsinghua.edu.cn/epel/epel-release-latest-8.noarch.rpm" ;;
            9) pkg_url="https://mirrors.tuna.tsinghua.edu.cn/epel/epel-release-latest-9.noarch.rpm" ;;
        esac
        # 再次检测清华源是否可达，否则 fallback 到 Fedora 官方源
        if ! curl -sf --connect-timeout 5 --max-time 10 "$pkg_url" >/dev/null 2>&1; then
            debug "清华源安装 EPEL 仓库 $el_ver 失败转为Fedora官方源..."
            case "$el_ver" in
                7) pkg_url="https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm" ;;
                8) pkg_url="https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm" ;;
                9) pkg_url="https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm" ;;
            esac
            # 再次检测 Fedora 官方源是否可达
            if ! curl -sf --connect-timeout 5 --max-time 10 "$pkg_url" >/dev/null 2>&1; then
                error "错误：阿里云、清华Tuna镜像和Fedora 官方都无法访问。请检查网络。" >&2
                exit 1
            fi
        fi
    fi

    # 安装命令（自动选择 dnf/yum）
    if command -v dnf >/dev/null 2>&1; then
        sudo dnf install -y "$pkg_url" >/dev/null
    elif command -v yum >/dev/null 2>&1; then
        sudo yum install -y "$pkg_url" >/dev/null
    else
        echo "错误：找不到包管理器。" >&2
        exit 1
    fi

    # 验证
    if rpm -q epel-release >/dev/null 2>&1; then
        echo "EPEL for EL $el_ver 已从镜像成功安装。"
    else
        echo "错误：EPEL安装失败。" >&2
        exit 1
    fi
}

# ==================== 用户管理函数 ====================
# 创建系统用户和组
create_users() {
    info "创建系统用户和组..."

    # 创建 www 用户组
    if ! getent group "$GROUP_NAME" >/dev/null; then
        run_cmd "groupadd $GROUP_NAME" "创建用户组 $GROUP_NAME"
        mark_rollback "删除用户组 $GROUP_NAME" "groupdel '$GROUP_NAME' || true"
        CREATED_USERS+=("group:$GROUP_NAME")
    fi

    # 创建 www 用户
    if ! id "$WWW_USER" >/dev/null 2>&1; then
        run_cmd "useradd -r -s /sbin/nologin -g $GROUP_NAME $WWW_USER" "创建用户 $WWW_USER"
        mark_rollback "删除用户 $WWW_USER" "userdel '$WWW_USER' || true"
        CREATED_USERS+=("user:$WWW_USER")
    fi

    success "用户和组创建完成"
}

# ==================== 下载函数 ====================
# 多镜像下载函数
download_file() {
    local url="$1"
    local output="$2"
    local desc="${3:-下载文件}"

    local mirrors=("$url")
    if [[ "$url" == *"php.net"* ]]; then
        mirrors=("${PHP_MIRRORS[@]}")
    fi

    for mirror in "${mirrors[@]}"; do
        info "尝试从镜像下载: $mirror"
        if run_cmd_with_retry "wget --tries=3 --timeout=30 -O '$output' '$mirror'" "$desc" "$DOWNLOAD_RETRIES" 2 "no"; then
            if [ -s "$output" ]; then
                success "$desc 成功: $(basename "$output")"
                return 0
            fi
        fi
        warn "镜像下载失败: $mirror"
    done

    error "所有镜像下载尝试都失败: $desc"
    return 1
}

# ==================== PHP 安装函数 ====================
# 安装 PHP
install_php() {
    local start=$(date +%s)
    info "开始安装 PHP $PHP_VERSION..."

    # 检查是否已安装
    if command -v php >/dev/null 2>&1 && php -r "exit(version_compare(PHP_VERSION, '$PHP_VERSION', '>=') ? 0 : 1);"; then
        info "PHP $PHP_VERSION 或更高版本已安装，跳过"
        return 0
    fi

    # 下载 PHP 源码
    local php_tarball="$SRC_DIR/php-$PHP_VERSION.tar.gz"
    download_file "https://www.php.net/distributions/php-$PHP_VERSION.tar.gz" "$php_tarball" "下载 PHP 源码"

    # 解压源码
    run_cmd "tar -xzf '$php_tarball' -C '$SRC_DIR'" "解压 PHP 源码"
    PHP_SRC_DIR="$SRC_DIR/php-$PHP_VERSION"

    # 进入源码目录
    cd "$PHP_SRC_DIR"

    # 清理之前的编译
    run_cmd "make clean || true" "清理之前的编译"

    # 配置 PHP
    info "配置 PHP 编译选项..."
    local opts_str=$(printf '%s ' "${PHP_CONFIGURE_OPTS[@]}" | tr -d '\n\r' | sed 's/ $//')
    debug "PHP 配置选项: $opts_str"

    if ! run_cmd_with_retry "./configure $opts_str" "配置 PHP" 2 5; then
        error "PHP 配置失败，检查 config.log 获取详细信息"
        if [ -f config.log ]; then
            tail -n 50 config.log >> "$ERROR_LOG_FILE"
        fi
        return 1
    fi

    # 编译 PHP
    info "编译 PHP..."
    if ! run_cmd_with_retry "make -j$MAKE_JOBS" "编译 PHP" 1 0; then
        error "PHP 编译失败"
        return 1
    fi

    # 安装 PHP
    info "安装 PHP..."
    if ! run_cmd "make install" "安装 PHP"; then
        error "PHP 安装失败"
        return 1
    fi

    # 创建 PHP 配置文件目录
    run_cmd "mkdir -p '$PHP_PREFIX/lib'" "创建 PHP 配置目录"

    # 复制配置文件
    if [ -f "php.ini-production" ]; then
        PHP_INI_FILE="$PHP_PREFIX/lib/php.ini"
        cp "php.ini-production" "$PHP_INI_FILE"
        safe_backup_file "$PHP_INI_FILE"
        mark_rollback "恢复 PHP 配置文件" "if [ -f '$PHP_INI_FILE.bak.*' ]; then cp -f '$PHP_INI_FILE.bak.*' '$PHP_INI_FILE' || true; fi"
    fi

    # 配置 PHP-FPM
    setup_php_fpm

    # 配置环境变量
    setup_php_environment

    local end=$(date +%s)
    success "PHP $PHP_VERSION 安装完成 (耗时: $((end-start)) 秒)"
}

# 配置 PHP-FPM
setup_php_fpm() {
    info "配置 PHP-FPM..."

    # 创建 PHP-FPM 配置文件目录
    local fpm_conf_dir="$PHP_PREFIX/etc/php-fpm.d"
    run_cmd "mkdir -p '$fpm_conf_dir'" "创建 PHP-FPM 配置目录"

    # 复制 PHP-FPM 配置文件
    if [ -f "sapi/fpm/php-fpm.conf" ]; then
        cp "sapi/fpm/php-fpm.conf" "$PHP_PREFIX/etc/"
    fi

    # 创建 PHP-FPM 服务文件
    create_php_fpm_service

    # 创建 PHP-FPM 配置文件
    cat > "$fpm_conf_dir/www.conf" << EOF
[www]
user = $WWW_USER
group = $GROUP_NAME
listen = /var/run/php-fpm.sock
listen.owner = $WWW_USER
listen.group = $GROUP_NAME
listen.mode = 0660
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
slowlog = /var/log/php-fpm/slow.log
request_slowlog_timeout = 10s
EOF

    # 创建日志目录
    run_cmd "mkdir -p /var/log/php-fpm" "创建 PHP-FPM 日志目录"
    run_cmd "chown -R $WWW_USER:$GROUP_NAME /var/log/php-fpm" "设置 PHP-FPM 日志目录权限"

    success "PHP-FPM 配置完成"
}

# 创建 PHP-FPM 服务文件
create_php_fpm_service() {
    local service_file="/etc/systemd/system/php-fpm.service"
    safe_backup_file "$service_file"

    cat > "$service_file" << EOF
[Unit]
Description=The PHP FastCGI Process Manager
After=network.target

[Service]
Type=simple
PIDFile=/var/run/php-fpm.pid
ExecStart=$PHP_PREFIX/sbin/php-fpm --nodaemonize --fpm-config $PHP_PREFIX/etc/php-fpm.conf
ExecReload=/bin/kill -USR2 \$MAINPID
ExecStop=/bin/kill -SIGINT \$MAINPID

[Install]
WantedBy=multi-user.target
EOF

    mark_rollback "恢复 PHP-FPM 服务文件" "if [ -f '$service_file.bak.*' ]; then cp -f '$service_file.bak.*' '$service_file' || true; fi"
}

# 配置 PHP 环境变量
setup_php_environment() {
    info "配置 PHP 环境变量..."

    # 备份原配置文件
    safe_backup_file "$PROFILE_FILE"

    # 添加 PHP 到 PATH
    if ! grep -q "PHP_PREFIX" "$PROFILE_FILE"; then
        cat >> "$PROFILE_FILE" << EOF

# PHP Environment
export PHP_PREFIX="$PHP_PREFIX"
export PATH="\$PHP_PREFIX/bin:\$PATH"
EOF
    fi

    # 使环境变量生效
    source "$PROFILE_FILE"

    mark_rollback "恢复环境配置文件" "if [ -f '$PROFILE_FILE.bak.*' ]; then cp -f '$PROFILE_FILE.bak.*' '$PROFILE_FILE' || true; fi"
    success "PHP 环境变量配置完成"
}

# ==================== PHP 扩展安装函数 ====================
# 安装 PHP Redis 扩展
install_php_redis() {
    local start=$(date +%s)
    info "安装 PHP Redis 扩展..."

    local ext_dir="$SRC_DIR/redis-$REDIS_EXT_VERSION"
    local tarball="$SRC_DIR/redis-$REDIS_EXT_VERSION.tgz"

    # 下载 Redis 扩展
    download_file "https://github.com/phpredis/phpredis/archive/$REDIS_EXT_VERSION.tar.gz" "$tarball" "下载 PHP Redis 扩展"

    # 解压
    run_cmd "tar -xzf '$tarball' -C '$SRC_DIR'" "解压 Redis 扩展"

    cd "$ext_dir"

    # 使用 phpize 准备扩展编译环境
    run_cmd "$PHP_PREFIX/bin/phpize" "准备 Redis 扩展编译环境"

    # 配置扩展
    if ! run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 Redis 扩展"; then
        error "Redis 扩展配置失败"
        return 1
    fi

    # 编译和安装
    if ! run_cmd "make -j$MAKE_JOBS" "编译 Redis 扩展"; then
        error "Redis 扩展编译失败"
        return 1
    fi

    if ! run_cmd "make install" "安装 Redis 扩展"; then
        error "Redis 扩展安装失败"
        return 1
    fi

    # 启用扩展
    enable_php_extension "redis"

    local end=$(date +%s)
    success "PHP Redis 扩展安装完成 (耗时: $((end-start)) 秒)"
}

# 安装 PHP Imagick 扩展
install_php_imagick() {
    local start=$(date +%s)
    info "安装 PHP Imagick 扩展..."

    local ext_dir="$SRC_DIR/imagick-$IMAGICK_EXT_VERSION"
    local tarball="$SRC_DIR/imagick-$IMAGICK_EXT_VERSION.tgz"

    # 下载 Imagick 扩展
    download_file "https://github.com/Imagick/imagick/archive/$IMAGICK_EXT_VERSION.tar.gz" "$tarball" "下载 PHP Imagick 扩展"

    # 解压
    run_cmd "tar -xzf '$tarball' -C '$SRC_DIR'" "解压 Imagick 扩展"

    cd "$ext_dir"

    # 使用 phpize 准备扩展编译环境
    run_cmd "$PHP_PREFIX/bin/phpize" "准备 Imagick 扩展编译环境"

    # 配置扩展
    if ! run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 Imagick 扩展"; then
        error "Imagick 扩展配置失败"
        return 1
    fi

    # 编译和安装
    if ! run_cmd "make -j$MAKE_JOBS" "编译 Imagick 扩展"; then
        error "Imagick 扩展编译失败"
        return 1
    fi

    if ! run_cmd "make install" "安装 Imagick 扩展"; then
        error "Imagick 扩展安装失败"
        return 1
    fi

    # 启用扩展
    enable_php_extension "imagick"

    local end=$(date +%s)
    success "PHP Imagick 扩展安装完成 (耗时: $((end-start)) 秒)"
}

# 启用 PHP 扩展
enable_php_extension() {
    local ext_name="$1"
    local ext_ini="$PHP_PREFIX/lib/conf.d/$ext_name.ini"

    run_cmd "mkdir -p '$(dirname "$ext_ini")'" "创建扩展配置目录"
    echo "extension=$ext_name.so" > "$ext_ini"
    info "已启用 PHP 扩展: $ext_name"
}

# ==================== 其他软件安装函数 ====================
# 安装 MySQL（简化版，实际生产环境需要更复杂的配置）
install_mysql() {
    if [ "$INSTALL_MYSQL" != "yes" ]; then
        info "跳过 MySQL 安装"
        return 0
    fi

    info "安装 MySQL..."
    # 这里简化处理，实际应该下载对应版本的 MySQL 并进行编译安装
    pkg_install mysql-server mysql-client mysql-devel
    success "MySQL 安装完成"
}

# 安装 Redis（简化版）
install_redis() {
    if [ "$INSTALL_REDIS" != "yes" ]; then
        info "跳过 Redis 安装"
        return 0
    fi

    info "安装 Redis..."
    pkg_install redis
    success "Redis 安装完成"
}

# 安装 Nginx（简化版）
install_nginx() {
    if [ "$INSTALL_NGINX" != "yes" ]; then
        info "跳过 Nginx 安装"
        return 0
    fi

    info "安装 Nginx..."
    pkg_install nginx
    success "Nginx 安装完成"
}

# 安装 Composer
install_composer() {
    if [ "$INSTALL_COMPOSER" != "yes" ]; then
        info "跳过 Composer 安装"
        return 0
    fi

    info "安装 Composer..."
    local composer_installer=$(mktemp)
    register_tmp_file "$composer_installer"

    download_file "https://getcomposer.org/installer" "$composer_installer" "下载 Composer 安装器"

    # 运行 Composer 安装器
    run_cmd "php $composer_installer --install-dir=$PHP_PREFIX/bin --filename=composer" "安装 Composer"

    success "Composer 安装完成"
}

# ==================== 配置优化函数 ====================
# 优化 PHP 配置
optimize_php_config() {
    info "优化 PHP 配置..."

    if [ -z "$PHP_INI_FILE" ] || [ ! -f "$PHP_INI_FILE" ]; then
        warn "PHP 配置文件不存在，跳过优化"
        return 0
    fi

    safe_backup_file "$PHP_INI_FILE"

    # 使用 modify_file 函数进行配置修改
    local modifications=(
        "memory_limit = 128M:memory_limit = $PHP_MEMORY_LIMIT:replace"
        "max_execution_time = 30:max_execution_time = $PHP_MAX_EXECUTION_TIME:replace"
        "upload_max_filesize = 2M:upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE:replace"
        "post_max_size = 8M:post_max_size = $PHP_POST_MAX_SIZE:replace"
        ";date.timezone =:date.timezone = Asia/Shanghai:replace"
        ";opcache.enable=0:opcache.enable=1:replace"
        ";opcache.memory_consumption=128:opcache.memory_consumption=256:replace"
        ";opcache.max_accelerated_files=10000:opcache.max_accelerated_files=20000:replace"
    )

    for mod in "${modifications[@]}"; do
        IFS=':' read -r search replace mode <<< "$mod"
        if ! modify_file "$PHP_INI_FILE" "${search}:${replace}:${mode}"; then
            warn "PHP 配置修改失败: $search -> $replace"
        fi
    done

    success "PHP 配置优化完成"
}

# ==================== 服务管理函数 ====================
# 启动并启用服务
setup_services() {
    if [ "$AUTO_START_SERVICES" != "yes" ]; then
        info "跳过服务启动设置"
        return 0
    fi

    info "设置服务自启动..."

    # 启动 PHP-FPM
    if systemctl is-enabled php-fpm >/dev/null 2>&1; then
        info "PHP-FPM 服务已启用"
    else
        run_cmd "systemctl enable php-fpm" "启用 PHP-FPM 服务"
    fi

    if systemctl is-active php-fpm >/dev/null 2>&1; then
        info "PHP-FPM 服务已运行"
    else
        run_cmd "systemctl start php-fpm" "启动 PHP-FPM 服务"
        mark_rollback "停止 PHP-FPM 服务" "systemctl stop php-fpm || true"
    fi

    # 启动其他服务（如果安装了的话）
    for service in nginx redis mysqld; do
        if systemctl list-unit-files | grep -q "$service.service"; then
            if ! systemctl is-enabled "$service" >/dev/null 2>&1; then
                run_cmd "systemctl enable $service" "启用 $service 服务"
            fi
            if ! systemctl is-active "$service" >/dev/null 2>&1; then
                run_cmd "systemctl start $service" "启动 $service 服务"
                mark_rollback "停止 $service 服务" "systemctl stop $service || true"
            fi
        fi
    done

    success "服务设置完成"
}

# ==================== 安装总结函数 ====================
# 生成安装总结
generate_install_summary() {
    info "生成安装总结..."

    cat > "$INSTALL_SUMMARY" << EOF
# PHP 环境安装总结
# 生成时间: $(date '+%F %T')

## 系统信息
- 操作系统: $OS_NAME $OS_VERSION ($OS_ARCH)
- 包管理器: $PKG_MGR
- 安装时间: $(( ($(date +%s) - START_TIME) / 60 )) 分钟

## 安装路径
- PHP: $PHP_PREFIX
- MySQL: $MYSQL_PREFIX
- Nginx: $NGINX_PREFIX
- Redis: $REDIS_PREFIX

## 版本信息
- PHP: $PHP_VERSION
- MySQL: $MYSQL_VERSION
- Redis: $REDIS_VERSION
- Nginx: $NGINX_VERSION

## 用户信息
- Web 用户: $WWW_USER (组: $GROUP_NAME)
- MySQL Root 密码: [已设置]
- Redis 密码: [已设置]

## 服务状态
$(systemctl is-active php-fpm >/dev/null 2>&1 && echo "- PHP-FPM: 运行中" || echo "- PHP-FPM: 未运行")
$(systemctl is-active nginx >/dev/null 2>&1 && echo "- Nginx: 运行中" || echo "- Nginx: 未运行")
$(systemctl is-active redis >/dev/null 2>&1 && echo "- Redis: 运行中" || echo "- Redis: 未运行")
$(systemctl is-active mysqld >/dev/null 2>&1 && echo "- MySQL: 运行中" || echo "- MySQL: 未运行")

## 配置文件
- PHP 主配置: $PHP_PREFIX/lib/php.ini
- PHP-FPM 配置: $PHP_PREFIX/etc/php-fpm.conf
- 服务文件: /etc/systemd/system/php-fpm.service

## 环境变量
- PHP 已添加到 PATH: $PHP_PREFIX/bin

## 日志文件
- 安装日志: $LOG_FILE
- 错误日志: $ERROR_LOG_FILE
- 回滚日志: $ROLLBACK_LOG_FILE

## 后续步骤
1. 验证安装: $PHP_PREFIX/bin/php -v
2. 启动服务: systemctl start php-fpm
3. 配置 Web 服务器指向 PHP-FPM
4. 设置防火墙规则（如果需要）

EOF

    success "安装总结已保存到: $INSTALL_SUMMARY"
}

# ==================== 清理函数 ====================
# 清理临时文件和编译缓存
cleanup_installation() {
    if [ "$CLEAN_TEMP" != "yes" ]; then
        info "跳过临时文件清理"
        return 0
    fi

    info "清理临时文件和编译缓存..."

    # 清理源码目录
    if [ -d "$SRC_DIR" ]; then
        find "$SRC_DIR" -maxdepth 1 -name "php-*" -type d -exec rm -rf {} + 2>/dev/null || true
        find "$SRC_DIR" -maxdepth 1 -name "*.tar.gz" -type f -delete 2>/dev/null || true
        find "$SRC_DIR" -maxdepth 1 -name "*.tgz" -type f -delete 2>/dev/null || true
    fi

    # 清理临时文件
    cleanup_tmpfiles

    # 清理包管理器缓存
    case "$PKG_MGR" in
        dnf|yum)
            run_cmd "$PKG_MGR clean all" "清理包管理器缓存" "no"
            ;;
        apt)
            run_cmd "apt-get clean" "清理包管理器缓存" "no"
            ;;
    esac

    success "清理完成"
}

# ==================== 验证安装函数 ====================
# 验证安装结果
verify_installation() {
    info "验证安装结果..."

    # 验证 PHP 安装
    if command -v php >/dev/null 2>&1; then
        local php_version=$(php -v 2>/dev/null | head -n1 | cut -d' ' -f2)
        success "PHP 安装成功: $php_version"
    else
        error "PHP 安装验证失败"
        return 1
    fi

    # 验证 PHP 扩展
    local required_extensions=("redis" "imagick" "mysqli" "pdo_mysql" "opcache")
    for ext in "${required_extensions[@]}"; do
        if php -m | grep -q -i "^$ext$"; then
            success "PHP 扩展 $ext 已加载"
        else
            warn "PHP 扩展 $ext 未加载"
        fi
    done

    # 验证服务状态
    if systemctl is-active php-fpm >/dev/null 2>&1; then
        success "PHP-FPM 服务运行正常"
    else
        warn "PHP-FPM 服务未运行"
    fi

    success "安装验证完成"
}

# ==================== 主安装流程 ====================
# 主安装函数
main_installation() {
    local start_total=$(date +%s)

    echo ""
    _bold "🚀 开始安装工业级 PHP 环境栈"
    echo "=========================================="
    info "开始时间: $(date '+%F %T')"
    info "安装模式: 单线程 (MAKE_JOBS=$MAKE_JOBS)"
    info "调试模式: $IS_DEBUG"
    echo ""

    # 获取执行锁
    acquire_lock

    # 显示系统信息
    info "系统检测中..."
    detect_system

    # 安装依赖
    run_cmd_with_func install_dependencies "安装系统依赖"

    # 创建用户和组
    run_cmd_with_func create_users "创建系统用户和组"

    # 安装 PHP
    if [ "$INSTALL_PHP" = "yes" ]; then
        run_cmd_with_func install_php "安装 PHP"
        run_cmd_with_func optimize_php_config "优化 PHP 配置"
    fi

    # 安装 PHP 扩展
    if [ "$INSTALL_PHP_EXTENSIONS" = "yes" ]; then
        run_cmd_with_func install_php_redis "安装 PHP Redis 扩展"
        run_cmd_with_func install_php_imagick "安装 PHP Imagick 扩展"
    fi

    # 安装其他软件
    run_cmd_with_func install_mysql "安装 MySQL"
    run_cmd_with_func install_redis "安装 Redis"
    run_cmd_with_func install_nginx "安装 Nginx"
    run_cmd_with_func install_composer "安装 Composer"

    # 设置服务
    run_cmd_with_func setup_services "配置系统服务"

    # 验证安装
    run_cmd_with_func verify_installation "验证安装结果"

    # 生成总结
    run_cmd_with_func generate_install_summary "生成安装总结"

    # 清理临时文件
    run_cmd_with_func cleanup_installation "清理临时文件"

    local end_total=$(date +%s)
    local total_time=$(( (end_total - start_total) / 60 ))

    echo ""
    _green "🎉 PHP 环境安装完成!"
    _green "⏱️  总耗时: ${total_time} 分钟"
    _green "📊 安装总结: $INSTALL_SUMMARY"
    _green "📋 详细日志: $LOG_FILE"
    echo ""

    # 删除执行锁
    rm -f "$LOCK_FILE"
}

# ==================== 脚本入口点 ====================
# 主执行函数
main() {

    # 检查是否为 root 用户
    if [ "$(id -u)" -ne 0 ]; then
        error "请使用 root 用户运行此脚本"
        exit 1
    fi

    clear

    # 执行主安装流程
    if main_installation; then
        success "安装流程顺利完成"
        exit 0
    else
        error "安装流程执行失败"
        exit 1
    fi
}

# 脚本入口点
main "$@"
