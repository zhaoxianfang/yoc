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
#  - 优化依赖包管理，支持更多系统版本
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

# ==================== 动画进度显示函数 ====================
# 统一的动画进度显示函数
show_spinner() {
    local pid=$1
    local desc="$2"

    # 定义进度动画帧
    local frames=("▰▱▱▱▱▱▱" "▰▰▱▱▱▱▱" "▰▰▰▱▱▱▱" "▰▰▰▰▱▱▱"
                 "▰▰▰▰▰▱▱" "▰▰▰▰▰▰▱" "▰▰▰▰▰▰▰" "▰▰▰▰▰▱▱"
                 "▰▰▰▰▱▱▱" "▰▰▰▱▱▱▱" "▰▰▱▱▱▱▱" "▰▱▱▱▱▱▱")
    local i=0

    # 显示进度动画
    while kill -0 "$pid" 2>/dev/null; do
        i=$(((i+1) % ${#frames[@]}))
        printf "\r⏳ %s %s" "$desc" "${frames[i]}"
        sleep 0.1
    done

    # 清理 spinner 行
    printf "\r%*s\r" "$(tput cols)" ""
}

# ==================== 执行锁管理 ====================
# 创建执行锁，防止重复执行
acquire_lock() {
    if [ -f "$LOCK_FILE" ]; then
        local lock_pid=$(cat "$LOCK_FILE" 2>/dev/null || echo "")
        if [ -n "$lock_pid" ] && kill -0 "$lock_pid" 2>/dev/null; then
            error "脚本正在运行中 (PID: $lock_pid)，请勿重复执行"
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
# 高级文件修改函数，支持多种操作模式
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
    local spinner_pid=""

    # 启动 spinner
    show_spinner "$pid" "$desc" &
    spinner_pid=$!

    # 等待命令执行完成，增加错误处理
    set +e
    wait "$pid" 2>/dev/null
    local rc=$?
    set -e

    # 停止 spinner
    kill "$spinner_pid" 2>/dev/null || true
    wait "$spinner_pid" 2>/dev/null || true

    # 处理命令执行结果
    if [ $rc -ne 0 ]; then
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

# 支持函数调用的进度动画执行函数
run_cmd_with_func() {
    local func="$1"
    local desc="${2:-执行函数}"

    # 在后台执行函数
    {
        $func
    } &

    local pid=$!

    # 显示进度动画
    show_spinner "$pid" "$desc"

    # 等待函数执行完成
    wait "$pid"
    local rc=$?

    if [ $rc -ne 0 ]; then
        printf "❌ %s 失败（退出码:%s）\n" "$desc" "$rc"
        return $rc
    else
        printf "✅ %s 完成\n" "$desc"
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
    case "$OS_ARCH" in
        x86_64) OS_ARCH="x64" ;;
        aarch64) OS_ARCH="arm64" ;;
        armv7l) OS_ARCH="armv7" ;;
        *) OS_ARCH="unknown" ;;
    esac

    # 检测包管理器
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

# 安装软件包（智能选择安装参数）
pkg_install() {
    local pkgs=("$@")

    local to_install=()
    for p in "${pkgs[@]}"; do
        if ! pkg_is_installed "$p"; then
            to_install+=("$p")
        fi
    done

    if [ ${#to_install[@]} -eq 0 ]; then
        return 0
    fi

    local pkgstr=$(escape_pkg_list to_install)
    echo "新安装包: ${to_install[*]}" >> "$INSTALLED_PACKAGES_LOG"

    # 根据包管理器选择合适的安装参数
    case "$PKG_MGR" in
        dnf)
            # 尝试多种参数组合确保安装成功
            if run_cmd_with_retry "dnf -y install --allowerasing $pkgstr" "安装软件包" 2 3; then
                INSTALLED_PKGS+=("${to_install[@]}")
            elif run_cmd_with_retry "dnf -y install --skip-broken $pkgstr" "安装软件包(跳过损坏)" 2 3; then
                INSTALLED_PKGS+=("${to_install[@]}")
            elif run_cmd_with_retry "dnf -y install --nobest $pkgstr" "安装软件包(不限制版本)" 2 3; then
                INSTALLED_PKGS+=("${to_install[@]}")
            else
                error "软件包安装失败"
                return 1
            fi
            mark_rollback "卸载新安装的软件包" "dnf -y remove --noautoremove ${pkgstr} || true"
            ;;
        yum)
            if run_cmd_with_retry "yum -y install --allowerasing $pkgstr" "安装软件包" 2 3; then
                INSTALLED_PKGS+=("${to_install[@]}")
            elif run_cmd_with_retry "yum -y install --skip-broken $pkgstr" "安装软件包(跳过损坏)" 2 3; then
                INSTALLED_PKGS+=("${to_install[@]}")
            else
                error "软件包安装失败"
                return 1
            fi
            mark_rollback "卸载新安装的软件包" "yum -y remove ${pkgstr} || true"
            ;;
        apt)
            if run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install $pkgstr" "安装软件包" 2 3; then
                INSTALLED_PKGS+=("${to_install[@]}")
            else
                error "软件包安装失败"
                return 1
            fi
            mark_rollback "卸载新安装的软件包" "DEBIAN_FRONTEND=noninteractive apt-get -y remove --purge ${pkgstr} || true; apt-get -y autoremove || true"
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
            run_cmd_with_retry "dnf -y update --allowerasing" "更新系统" 2 5
            install_epel_repository
            ;;
        yum)
            run_cmd_with_retry "yum -y update --allowerasing" "更新系统" 2 5
            install_epel_repository
            ;;
        apt)
            run_cmd_with_retry "apt-get -y update" "更新包列表" 2 5
            run_cmd_with_retry "apt-get -y upgrade" "升级系统" 2 5
            ;;
    esac

    # 扩展的依赖包列表，支持更多系统和版本
    local common_packages=(
        # 基础编译工具
        yum-utils gcc gcc-c++ gcc-gfortran autoconf automake libtool make cmake perl perl-devel
        # 开发库
        kernel-devel kernel-headers glibc-devel glibc-headers
        # 网络工具
        wget curl curl-devel libcurl libcurl-devel
        # 压缩库
        zlib zlib-devel bzip2 bzip2-devel lz4 lz4-devel xz xz-devel
        # 图像处理
        libpng libpng-devel libjpeg libjpeg-devel libjpeg-turbo libjpeg-turbo-devel
        freetype freetype-devel gd gd-devel libwebp libwebp-devel
        # XML处理
        libxml2 libxml2-devel libxslt libxslt-devel
        # 数据库相关
        sqlite sqlite-devel
        # 加密和安全
        openssl openssl-devel libsodium libsodium-devel
        # 文本处理
        pcre pcre-devel pcre2 pcre2-devel oniguruma oniguruma-devel
        # 系统库
        readline readline-devel ncurses ncurses-devel
        # 国际化
        libicu libicu-devel gettext gettext-devel
        # 其他开发库
        expat expat-devel libevent libevent-devel libffi libffi-devel
        libtidy libtidy-devel enchant enchant-devel aspell aspell-devel
        # 系统工具
        which file patch
    )

    # 根据架构添加特定包
    if [ "$OS_ARCH" = "arm64" ] || [ "$OS_ARCH" = "aarch64" ]; then
        common_packages+=(gcc-aarch64-linux-gnu libatomic)
    fi

    local packages_str=$(printf '%s ' "${common_packages[@]}" | tr -d '\n\r' | sed 's/ $//')

    info "正在安装系统依赖... $packages_str"
    pkg_install "$packages_str"

    success "系统依赖安装完成 (耗时: $(($(date +%s) - start)) 秒)"
}

# 安装 EPEL 仓库
install_epel_repository() {
    # 首先尝试通过包管理器安装
    if run_cmd_with_retry "${PKG_MGR} -y install --allowerasing epel-release" "安装 EPEL 仓库" 2 3; then
        success "EPEL 仓库安装成功"
        return 0
    fi

    warn "通过包管理器安装 EPEL 失败，尝试备用方式..."
    install_epel_fallback
}

# EPEL 备用安装方式
install_epel_fallback() {
    local el_ver pkg_url
    el_ver=$(get_el_version)
    if ! [[ "$el_ver" =~ ^(7|8|9)$ ]]; then
        error "无法检测EL版本: $el_ver"
        return 1
    fi

    # 优先使用国内镜像（阿里云 + 清华 双保险）
    case "$el_ver" in
        7) pkg_url="https://mirrors.aliyun.com/epel/epel-release-latest-7.noarch.rpm" ;;
        8) pkg_url="https://mirrors.aliyun.com/epel/epel-release-latest-8.noarch.rpm" ;;
        9) pkg_url="https://mirrors.aliyun.com/epel/epel-release-latest-9.noarch.rpm" ;;
    esac

    # 检测是否能访问阿里云，否则 fallback 到清华源
    if ! curl -sf --connect-timeout 5 --max-time 10 "$pkg_url" >/dev/null 2>&1; then
        case "$el_ver" in
            7) pkg_url="https://mirrors.tuna.tsinghua.edu.cn/epel/epel-release-latest-7.noarch.rpm" ;;
            8) pkg_url="https://mirrors.tuna.tsinghua.edu.cn/epel/epel-release-latest-8.noarch.rpm" ;;
            9) pkg_url="https://mirrors.tuna.tsinghua.edu.cn/epel/epel-release-latest-9.noarch.rpm" ;;
        esac
    fi

    if run_cmd_with_retry "rpm -Uvh --nodeps $pkg_url" "安装 EPEL 仓库(备用方式)" 2 5; then
        success "EPEL 仓库安装成功(备用方式)"
        return 0
    else
        error "EPEL 仓库安装失败"
        return 1
    fi
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

# ==================== 用户和组管理 ====================
# 创建系统用户和组
create_users_and_groups() {
    info "创建用户和组..."

    # 创建 www 用户组
    if ! getent group "$GROUP_NAME" >/dev/null; then
        run_cmd "groupadd $GROUP_NAME" "创建组 $GROUP_NAME"
        mark_rollback "删除组 $GROUP_NAME" "groupdel $GROUP_NAME || true"
    fi

    # 创建 www 用户
    if ! id "$WWW_USER" >/dev/null 2>&1; then
        run_cmd "useradd -M -s /sbin/nologin -g $GROUP_NAME $WWW_USER" "创建用户 $WWW_USER"
        CREATED_USERS+=("$WWW_USER")
        mark_rollback "删除用户 $WWW_USER" "userdel $WWW_USER || true"
    fi

    success "用户和组创建完成"
}

# ==================== 下载函数 ====================
# 安全下载函数，支持重试和备用镜像
safe_download() {
    local url="$1"
    local output="$2"
    local desc="${3:-文件}"

    # 检查是否已存在
    if [ -f "$output" ]; then
        info "$desc 已存在: $output"
        return 0
    fi

    local retries=0
    local max_retries="$DOWNLOAD_RETRIES"

    while [ $retries -lt $max_retries ]; do
        if run_cmd "wget --timeout=30 --tries=3 --no-check-certificate -O '$output' '$url'" "下载 $desc"; then
            success "下载 $desc 成功"
            return 0
        fi

        retries=$((retries + 1))
        if [ $retries -lt $max_retries ]; then
            warn "下载 $desc 失败，${retries}/${max_retries} 次重试..."
            sleep 2
        fi
    done

    error "下载 $desc 失败: $url"
    return 1
}

# ==================== PHP 安装函数 ====================
# 安装 PHP
install_php() {
    local start=$(date +%s)
    info "开始安装 PHP $PHP_VERSION..."

    # 检查是否已安装
    if [ -x "$PHP_PREFIX/bin/php" ]; then
        local installed_ver=$("$PHP_PREFIX/bin/php" -v | head -1 | awk '{print $2}')
        if [ "$installed_ver" = "$PHP_VERSION" ]; then
            success "PHP $PHP_VERSION 已安装"
            return 0
        else
            warn "已安装的 PHP 版本 ($installed_ver) 与目标版本 ($PHP_VERSION) 不一致"
        fi
    fi

    # 下载 PHP 源码
    PHP_SRC_DIR="$SRC_DIR/php-$PHP_VERSION"
    local php_archive="$SRC_DIR/php-$PHP_VERSION.tar.gz"

    # 尝试多个镜像源下载
    local download_success=false
    for mirror in "${PHP_MIRRORS[@]}"; do
        if safe_download "$mirror" "$php_archive" "PHP $PHP_VERSION 源码"; then
            download_success=true
            break
        fi
    done

    if [ "$download_success" != "true" ]; then
        error "所有 PHP 镜像下载失败"
        return 1
    fi

    # 解压源码
    run_cmd "tar -xzf '$php_archive' -C '$SRC_DIR'" "解压 PHP 源码"

    # 进入源码目录
    cd "$PHP_SRC_DIR"

    # 清理之前的编译
    run_cmd "make clean" "清理之前的编译" || true

    # 配置 PHP
    info "配置 PHP 编译选项..."

    # 将数组转换为字符串
    local opts_str=$(printf '%s ' "${PHP_CONFIGURE_OPTS[@]}" | tr -d '\n\r' | sed 's/ $//')

    if ! run_cmd_with_retry "./configure $opts_str" "配置 PHP" 2 5; then
        error "PHP 配置失败"
        return 1
    fi

    # 编译 PHP
    info "编译 PHP..."
    if ! run_cmd_with_retry "make -j$MAKE_JOBS" "编译 PHP" 2 5; then
        error "PHP 编译失败"
        return 1
    fi

    # 安装 PHP
    info "安装 PHP..."
    if ! run_cmd "make install" "安装 PHP"; then
        error "PHP 安装失败"
        return 1
    fi

    # 创建配置文件目录
    mkdir -p "$PHP_PREFIX/lib"

    # 复制配置文件
    if [ -f "php.ini-production" ]; then
        cp php.ini-production "$PHP_PREFIX/lib/php.ini"
        PHP_INI_FILE="$PHP_PREFIX/lib/php.ini"
    fi

    # 创建 PHP-FPM 配置文件
    if [ -f "$PHP_PREFIX/etc/php-fpm.conf.default" ]; then
        cp "$PHP_PREFIX/etc/php-fpm.conf.default" "$PHP_PREFIX/etc/php-fpm.conf"
    fi
    if [ -f "$PHP_PREFIX/etc/php-fpm.d/www.conf.default" ]; then
        cp "$PHP_PREFIX/etc/php-fpm.d/www.conf.default" "$PHP_PREFIX/etc/php-fpm.d/www.conf"
    fi

    # 配置 PHP.ini
    configure_php_ini

    # 配置 PHP-FPM
    configure_php_fpm

    # 创建软链接
    create_symlinks

    success "PHP $PHP_VERSION 安装完成 (耗时: $(($(date +%s) - start)) 秒)"
}

# 配置 PHP.ini
configure_php_ini() {
    info "配置 PHP.ini..."

    if [ -f "$PHP_INI_FILE" ]; then
        safe_backup_file "$PHP_INI_FILE"

        # 使用 modify_file 函数进行配置修改
        local php_ini_mods=(
            "memory_limit = 128M:memory_limit = $PHP_MEMORY_LIMIT:replace"
            "max_execution_time = 30:max_execution_time = $PHP_MAX_EXECUTION_TIME:replace"
            "upload_max_filesize = 2M:upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE:replace"
            "post_max_size = 8M:post_max_size = $PHP_POST_MAX_SIZE:replace"
            ";date.timezone =:date.timezone = Asia/Shanghai:replace"
            ";opcache.enable=0:opcache.enable=1:replace"
            ";opcache.memory_consumption=128:opcache.memory_consumption=256:replace"
            ";opcache.interned_strings_buffer=8:opcache.interned_strings_buffer=16:replace"
            ";opcache.max_accelerated_files=10000:opcache.max_accelerated_files=20000:replace"
            ";opcache.jit_buffer_size=0:opcache.jit_buffer_size=100M:replace"
        )

        for mod in "${php_ini_mods[@]}"; do
            IFS=':' read -r search replace mode <<< "$mod"
            if ! modify_file "$PHP_INI_FILE" "${search}:${replace}:${mode}" "global_mode=true"; then
                warn "PHP.ini 配置修改失败: $search -> $replace"
            fi
        done

        success "PHP.ini 配置完成"
    else
        warn "PHP.ini 文件不存在，跳过配置"
    fi
}

# 配置 PHP-FPM
configure_php_fpm() {
    info "配置 PHP-FPM..."

    local php_fpm_conf="$PHP_PREFIX/etc/php-fpm.conf"
    local www_conf="$PHP_PREFIX/etc/php-fpm.d/www.conf"

    if [ -f "$php_fpm_conf" ]; then
        safe_backup_file "$php_fpm_conf"

        local php_fpm_mods=(
            ";pid = run/php-fpm.pid:pid = $PHP_PREFIX/var/run/php-fpm.pid:replace"
            ";error_log = log/php-fpm.log:error_log = /var/log/php-fpm.log:replace"
            ";daemonize = yes:daemonize = yes:replace"
        )

        for mod in "${php_fpm_mods[@]}"; do
            IFS=':' read -r search replace mode <<< "$mod"
            if ! modify_file "$php_fpm_conf" "${search}:${replace}:${mode}"; then
                warn "PHP-FPM 配置修改失败: $search -> $replace"
            fi
        done
    fi

    if [ -f "$www_conf" ]; then
        safe_backup_file "$www_conf"

        local www_conf_mods=(
            "user = nobody:user = $WWW_USER:replace"
            "group = nobody:group = $GROUP_NAME:replace"
            ";listen.owner = nobody:listen.owner = $WWW_USER:replace"
            ";listen.group = nobody:listen.group = $GROUP_NAME:replace"
            ";listen.mode = 0660:listen.mode = 0660:replace"
            ";listen = 127.0.0.1:9000:listen = /var/run/php-fpm.sock:replace"
        )

        for mod in "${www_conf_mods[@]}"; do
            IFS=':' read -r search replace mode <<< "$mod"
            if ! modify_file "$www_conf" "${search}:${replace}:${mode}"; then
                warn "PHP-FPM www.conf 配置修改失败: $search -> $replace"
            fi
        done
    fi

    # 创建必要的目录
    mkdir -p /var/log /var/run
    mkdir -p "$PHP_PREFIX/var/run"
    chown -R "$WWW_USER:$GROUP_NAME" /var/log /var/run "$PHP_PREFIX/var/run"

    success "PHP-FPM 配置完成"
}

# 创建软链接
create_symlinks() {
    info "创建 PHP 软链接..."

    # 创建 PHP 二进制文件的软链接
    for bin in php phpize php-config; do
        if [ -f "$PHP_PREFIX/bin/$bin" ]; then
            ln -sf "$PHP_PREFIX/bin/$bin" "/usr/local/bin/$bin" 2>/dev/null || true
        fi
    done

    # 创建 PHP-FPM 软链接
    if [ -f "$PHP_PREFIX/sbin/php-fpm" ]; then
        ln -sf "$PHP_PREFIX/sbin/php-fpm" "/usr/local/sbin/php-fpm" 2>/dev/null || true
    fi

    success "PHP 软链接创建完成"
}

# ==================== PHP 扩展安装函数 ====================
# 安装 PHP 扩展
install_php_extensions() {
    info "安装 PHP 扩展..."

    # 安装 Redis 扩展
    install_php_redis_extension

    # 安装 Imagick 扩展
    install_php_imagick_extension

    success "PHP 扩展安装完成"
}

# 安装 PHP Redis 扩展
install_php_redis_extension() {
    info "安装 PHP Redis 扩展..."

    local redis_ext_dir="$SRC_DIR/redis-$REDIS_EXT_VERSION"
    local redis_ext_archive="$SRC_DIR/redis-$REDIS_EXT_VERSION.tgz"

    # 下载 Redis 扩展
    if safe_download "https://github.com/phpredis/phpredis/archive/$REDIS_EXT_VERSION.tar.gz" \
        "$redis_ext_archive" "PHP Redis 扩展"; then

        # 解压扩展
        run_cmd "tar -xzf '$redis_ext_archive' -C '$SRC_DIR'" "解压 Redis 扩展"

        # 进入扩展目录
        cd "$redis_ext_dir"

        # 使用 phpize 准备扩展编译环境
        run_cmd "$PHP_PREFIX/bin/phpize" "准备 Redis 扩展编译环境"

        # 配置扩展
        run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 Redis 扩展"

        # 编译和安装
        run_cmd "make -j$MAKE_JOBS" "编译 Redis 扩展"
        run_cmd "make install" "安装 Redis 扩展"

        # 启用扩展
        if [ -f "$PHP_INI_FILE" ]; then
            echo "extension=redis.so" >> "$PHP_INI_FILE"
        fi

        success "PHP Redis 扩展安装完成"
    else
        error "PHP Redis 扩展下载失败"
        return 1
    fi
}

# 安装 PHP Imagick 扩展
install_php_imagick_extension() {
    info "安装 PHP Imagick 扩展..."

    # 先安装 ImageMagick 依赖
    pkg_install ImageMagick ImageMagick-devel

    local imagick_ext_dir="$SRC_DIR/imagick-$IMAGICK_EXT_VERSION"
    local imagick_ext_archive="$SRC_DIR/imagick-$IMAGICK_EXT_VERSION.tgz"

    # 下载 Imagick 扩展
    if safe_download "https://github.com/Imagick/imagick/archive/$IMAGICK_EXT_VERSION.tar.gz" \
        "$imagick_ext_archive" "PHP Imagick 扩展"; then

        # 解压扩展
        run_cmd "tar -xzf '$imagick_ext_archive' -C '$SRC_DIR'" "解压 Imagick 扩展"

        # 进入扩展目录
        cd "$imagick_ext_dir"

        # 使用 phpize 准备扩展编译环境
        run_cmd "$PHP_PREFIX/bin/phpize" "准备 Imagick 扩展编译环境"

        # 配置扩展
        run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 Imagick 扩展"

        # 编译和安装
        run_cmd "make -j$MAKE_JOBS" "编译 Imagick 扩展"
        run_cmd "make install" "安装 Imagick 扩展"

        # 启用扩展
        if [ -f "$PHP_INI_FILE" ]; then
            echo "extension=imagick.so" >> "$PHP_INI_FILE"
        fi

        success "PHP Imagick 扩展安装完成"
    else
        error "PHP Imagick 扩展下载失败"
        return 1
    fi
}

# ==================== MySQL 安装函数 ====================
# 安装 MySQL
install_mysql() {
    local start=$(date +%s)
    info "开始安装 MySQL..."

    # 检查是否已安装
    if [ -x "$MYSQL_PREFIX/bin/mysqld" ]; then
        success "MySQL 已安装"
        return 0
    fi

    # 下载 MySQL
    local mysql_archive="$SRC_DIR/mysql-$MYSQL_VERSION.tar.gz"
    if safe_download "https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-$MYSQL_VERSION.tar.gz" \
        "$mysql_archive" "MySQL $MYSQL_VERSION 源码"; then

        # 解压 MySQL
        run_cmd "tar -xzf '$mysql_archive' -C '$SRC_DIR'" "解压 MySQL 源码"

        local mysql_src_dir="$SRC_DIR/mysql-$MYSQL_VERSION"
        cd "$mysql_src_dir"

        # 安装 MySQL 依赖
        pkg_install ncurses-devel openssl-devel libtirpc-devel

        # 配置 MySQL
        info "配置 MySQL..."
        run_cmd "cmake . -DCMAKE_INSTALL_PREFIX=$MYSQL_PREFIX \
                  -DMYSQL_DATADIR=/data/mysql \
                  -DSYSCONFDIR=/etc \
                  -DWITH_INNOBASE_STORAGE_ENGINE=1 \
                  -DWITH_ARCHIVE_STORAGE_ENGINE=1 \
                  -DWITH_BLACKHOLE_STORAGE_ENGINE=1 \
                  -DWITH_READLINE=1 \
                  -DWITH_SSL=system \
                  -DWITH_ZLIB=system \
                  -DDEFAULT_CHARSET=utf8mb4 \
                  -DDEFAULT_COLLATION=utf8mb4_unicode_ci \
                  -DENABLED_LOCAL_INFILE=1" "配置 MySQL"

        # 编译和安装
        info "编译 MySQL..."
        run_cmd "make -j$MAKE_JOBS" "编译 MySQL"
        run_cmd "make install" "安装 MySQL"

        # 创建 MySQL 用户和组
        if ! getent group mysql >/dev/null; then
            run_cmd "groupadd mysql" "创建 MySQL 组"
        fi
        if ! id mysql >/dev/null 2>&1; then
            run_cmd "useradd -r -g mysql -s /bin/false mysql" "创建 MySQL 用户"
        fi

        # 创建数据目录
        mkdir -p /data/mysql
        chown -R mysql:mysql /data/mysql

        # 初始化 MySQL
        initialize_mysql

        # 配置 MySQL 服务
        configure_mysql_service

        success "MySQL $MYSQL_VERSION 安装完成 (耗时: $(($(date +%s) - start)) 秒)"
    else
        error "MySQL 下载失败"
        return 1
    fi
}

# 初始化 MySQL
initialize_mysql() {
    info "初始化 MySQL..."

    cd "$MYSQL_PREFIX"

    # 初始化数据库
    run_cmd "bin/mysqld --initialize-insecure --user=mysql --basedir=$MYSQL_PREFIX --datadir=/data/mysql" "初始化 MySQL 数据库"

    # 设置 SSL
    run_cmd "bin/mysql_ssl_rsa_setup --datadir=/data/mysql" "设置 MySQL SSL"

    success "MySQL 初始化完成"
}

# 配置 MySQL 服务
configure_mysql_service() {
    info "配置 MySQL 服务..."

    # 创建配置文件
    local my_cnf="/etc/my.cnf"
    safe_backup_file "$my_cnf"

    cat > "$my_cnf" << EOF
[mysqld]
basedir=$MYSQL_PREFIX
datadir=/data/mysql
socket=/var/lib/mysql/mysql.sock
port=3306
user=mysql
symbolic-links=0
log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

# 性能优化配置
innodb_buffer_pool_size=128M
innodb_log_file_size=64M
max_connections=100
query_cache_size=32M
query_cache_type=1

[client]
socket=/var/lib/mysql/mysql.sock
EOF

    # 创建必要的目录
    mkdir -p /var/lib/mysql /var/log /var/run/mysqld
    chown -R mysql:mysql /var/lib/mysql /var/run/mysqld

    # 创建 systemd 服务文件
    local service_file="/etc/systemd/system/mysqld.service"
    safe_backup_file "$service_file"

    cat > "$service_file" << EOF
[Unit]
Description=MySQL Server
After=network.target

[Service]
User=mysql
Group=mysql
ExecStart=$MYSQL_PREFIX/bin/mysqld --defaults-file=/etc/my.cnf
ExecReload=/bin/kill -HUP \$MAINPID
LimitNOFILE=65536
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

    # 重新加载 systemd
    run_cmd "systemctl daemon-reload" "重新加载 systemd"

    # 启动 MySQL 服务
    if [ "$AUTO_START_SERVICES" = "yes" ]; then
        run_cmd "systemctl enable mysqld" "设置 MySQL 开机自启"
        run_cmd "systemctl start mysqld" "启动 MySQL 服务"

        # 设置 root 密码
        secure_mysql_installation
    fi

    success "MySQL 服务配置完成"
}

# 安全 MySQL 安装
secure_mysql_installation() {
    info "安全配置 MySQL..."

    # 等待 MySQL 启动
    sleep 5

    # 设置 root 密码
    run_cmd "$MYSQL_PREFIX/bin/mysqladmin -u root password \"$MYSQL_ROOT_PASS\"" "设置 MySQL root 密码"

    # 创建远程管理用户
    local create_user_sql="CREATE USER IF NOT EXISTS '$MYSQL_REMOTE_ADMIN_USER'@'%' IDENTIFIED BY '$MYSQL_REMOTE_ADMIN_PASS';"
    local grant_privs_sql="GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_REMOTE_ADMIN_USER'@'%' WITH GRANT OPTION;"
    local flush_sql="FLUSH PRIVILEGES;"

    run_cmd "$MYSQL_PREFIX/bin/mysql -u root -p\"$MYSQL_ROOT_PASS\" -e \"$create_user_sql\"" "创建 MySQL 远程用户"
    run_cmd "$MYSQL_PREFIX/bin/mysql -u root -p\"$MYSQL_ROOT_PASS\" -e \"$grant_privs_sql\"" "授予 MySQL 权限"
    run_cmd "$MYSQL_PREFIX/bin/mysql -u root -p\"$MYSQL_ROOT_PASS\" -e \"$flush_sql\"" "刷新 MySQL 权限"

    success "MySQL 安全配置完成"
}

# ==================== Redis 安装函数 ====================
# 安装 Redis
install_redis() {
    local start=$(date +%s)
    info "开始安装 Redis..."

    # 检查是否已安装
    if [ -x "$REDIS_PREFIX/bin/redis-server" ]; then
        success "Redis 已安装"
        return 0
    fi

    # 下载 Redis
    local redis_archive="$SRC_DIR/redis-$REDIS_VERSION.tar.gz"
    if safe_download "http://download.redis.io/releases/redis-$REDIS_VERSION.tar.gz" \
        "$redis_archive" "Redis $REDIS_VERSION 源码"; then

        # 解压 Redis
        run_cmd "tar -xzf '$redis_archive' -C '$SRC_DIR'" "解压 Redis 源码"

        local redis_src_dir="$SRC_DIR/redis-$REDIS_VERSION"
        cd "$redis_src_dir"

        # 编译 Redis
        info "编译 Redis..."
        run_cmd "make -j$MAKE_JOBS" "编译 Redis"
        run_cmd "make PREFIX=$REDIS_PREFIX install" "安装 Redis"

        # 创建 Redis 用户和组
        if ! getent group redis >/dev/null; then
            run_cmd "groupadd redis" "创建 Redis 组"
        fi
        if ! id redis >/dev/null 2>&1; then
            run_cmd "useradd -r -g redis -s /bin/false redis" "创建 Redis 用户"
        fi

        # 配置 Redis
        configure_redis

        # 配置 Redis 服务
        configure_redis_service

        success "Redis $REDIS_VERSION 安装完成 (耗时: $(($(date +%s) - start)) 秒)"
    else
        error "Redis 下载失败"
        return 1
    fi
}

# 配置 Redis
configure_redis() {
    info "配置 Redis..."

    # 创建配置目录
    mkdir -p /etc/redis /var/lib/redis /var/log/redis
    chown -R redis:redis /var/lib/redis /var/log/redis

    # 创建 Redis 配置文件
    local redis_conf="/etc/redis/redis.conf"
    safe_backup_file "$redis_conf"

    # 生成基础配置
    cat > "$redis_conf" << EOF
bind 127.0.0.1
port 6379
daemonize yes
pidfile /var/run/redis/redis.pid
logfile /var/log/redis/redis.log
dir /var/lib/redis
requirepass $REDIS_PASS
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
EOF

    success "Redis 配置完成"
}

# 配置 Redis 服务
configure_redis_service() {
    info "配置 Redis 服务..."

    # 创建 systemd 服务文件
    local service_file="/etc/systemd/system/redis.service"
    safe_backup_file "$service_file"

    cat > "$service_file" << EOF
[Unit]
Description=Redis In-Memory Data Store
After=network.target

[Service]
User=redis
Group=redis
ExecStart=$REDIS_PREFIX/bin/redis-server /etc/redis/redis.conf
ExecStop=$REDIS_PREFIX/bin/redis-cli shutdown
Restart=always
Type=forking

[Install]
WantedBy=multi-user.target
EOF

    # 重新加载 systemd
    run_cmd "systemctl daemon-reload" "重新加载 systemd"

    # 启动 Redis 服务
    if [ "$AUTO_START_SERVICES" = "yes" ]; then
        run_cmd "systemctl enable redis" "设置 Redis 开机自启"
        run_cmd "systemctl start redis" "启动 Redis 服务"
    fi

    success "Redis 服务配置完成"
}

# ==================== Nginx 安装函数 ====================
# 安装 Nginx
install_nginx() {
    local start=$(date +%s)
    info "开始安装 Nginx..."

    # 检查是否已安装
    if [ -x "$NGINX_PREFIX/sbin/nginx" ]; then
        success "Nginx 已安装"
        return 0
    fi

    # 下载 Nginx
    local nginx_archive="$SRC_DIR/nginx-$NGINX_VERSION.tar.gz"
    if safe_download "http://nginx.org/download/nginx-$NGINX_VERSION.tar.gz" \
        "$nginx_archive" "Nginx $NGINX_VERSION 源码"; then

        # 解压 Nginx
        run_cmd "tar -xzf '$nginx_archive' -C '$SRC_DIR'" "解压 Nginx 源码"

        local nginx_src_dir="$SRC_DIR/nginx-$NGINX_VERSION"
        cd "$nginx_src_dir"

        # 安装 Nginx 依赖
        pkg_install pcre-devel zlib-devel openssl-devel

        # 配置 Nginx
        info "配置 Nginx..."
        run_cmd "./configure --prefix=$NGINX_PREFIX \
                  --user=$WWW_USER \
                  --group=$GROUP_NAME \
                  --with-http_ssl_module \
                  --with-http_v2_module \
                  --with-http_realip_module \
                  --with-http_stub_status_module \
                  --with-http_gzip_static_module \
                  --with-pcre \
                  --with-stream \
                  --with-stream_ssl_module" "配置 Nginx"

        # 编译和安装
        info "编译 Nginx..."
        run_cmd "make -j$MAKE_JOBS" "编译 Nginx"
        run_cmd "make install" "安装 Nginx"

        # 配置 Nginx
        configure_nginx

        # 配置 Nginx 服务
        configure_nginx_service

        success "Nginx $NGINX_VERSION 安装完成 (耗时: $(($(date +%s) - start)) 秒)"
    else
        error "Nginx 下载失败"
        return 1
    fi
}

# 配置 Nginx
configure_nginx() {
    info "配置 Nginx..."

    # 创建必要的目录
    mkdir -p /var/log/nginx /var/cache/nginx
    chown -R "$WWW_USER:$GROUP_NAME" /var/log/nginx /var/cache/nginx

    # 备份原始配置文件
    local nginx_conf="$NGINX_PREFIX/conf/nginx.conf"
    safe_backup_file "$nginx_conf"

    # 生成优化的 Nginx 配置
    cat > "$nginx_conf" << EOF
user $WWW_USER $GROUP_NAME;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    include $NGINX_PREFIX/conf/mime.types;
    default_type application/octet-stream;

    log_format main '\$remote_addr - \$remote_user [\$time_local] "\$request" '
                    '\$status \$body_bytes_sent "\$http_referer" '
                    '"\$http_user_agent" "\$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;

    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    include $NGINX_PREFIX/conf/conf.d/*.conf;
}
EOF

    # 创建 conf.d 目录
    mkdir -p "$NGINX_PREFIX/conf/conf.d"

    # 创建默认虚拟主机配置
    local default_site="$NGINX_PREFIX/conf/conf.d/default.conf"
    cat > "$default_site" << EOF
server {
    listen 80;
    server_name localhost;
    root /data/www;
    index index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

    success "Nginx 配置完成"
}

# 配置 Nginx 服务
configure_nginx_service() {
    info "配置 Nginx 服务..."

    # 创建 systemd 服务文件
    local service_file="/etc/systemd/system/nginx.service"
    safe_backup_file "$service_file"

    cat > "$service_file" << EOF
[Unit]
Description=nginx - high performance web server
Documentation=http://nginx.org/en/docs/
After=network.target

[Service]
Type=forking
PIDFile=/var/run/nginx.pid
ExecStart=$NGINX_PREFIX/sbin/nginx -c $NGINX_PREFIX/conf/nginx.conf
ExecReload=/bin/kill -s HUP \$MAINPID
ExecStop=/bin/kill -s QUIT \$MAINPID
PrivateTmp=true
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

    # 重新加载 systemd
    run_cmd "systemctl daemon-reload" "重新加载 systemd"

    # 启动 Nginx 服务
    if [ "$AUTO_START_SERVICES" = "yes" ]; then
        run_cmd "systemctl enable nginx" "设置 Nginx 开机自启"
        run_cmd "systemctl start nginx" "启动 Nginx 服务"
    fi

    success "Nginx 服务配置完成"
}

# ==================== Composer 安装函数 ====================
# 安装 Composer
install_composer() {
    info "安装 Composer..."

    local composer_installer="$SRC_DIR/composer-installer.php"
    local composer_phar="/usr/local/bin/composer"

    # 下载 Composer 安装器
    if safe_download "https://getcomposer.org/installer" "$composer_installer" "Composer 安装器"; then
        # 使用 PHP 运行安装器
        run_cmd "$PHP_PREFIX/bin/php $composer_installer --install-dir=/usr/local/bin --filename=composer" "安装 Composer"

        # 设置权限
        run_cmd "chmod +x $composer_phar" "设置 Composer 可执行权限"

        # 配置 Composer 使用国内镜像
        run_cmd "$composer_phar config -g repo.packagist composer https://mirrors.aliyun.com/composer/" "配置 Composer 中国镜像"

        success "Composer 安装完成"
    else
        error "Composer 安装失败"
        return 1
    fi
}

# ==================== 环境配置函数 ====================
# 配置环境变量
configure_environment() {
    info "配置环境变量..."

    # 备份 profile 文件
    safe_backup_file "$PROFILE_FILE"

    # 添加环境变量到 profile
    local env_config="# PHP Stack Environment Variables
export PATH=$PHP_PREFIX/bin:$MYSQL_PREFIX/bin:$REDIS_PREFIX/bin:$NGINX_PREFIX/sbin:\$PATH
export PHP_HOME=$PHP_PREFIX
export MYSQL_HOME=$MYSQL_PREFIX
export REDIS_HOME=$REDIS_PREFIX
export NGINX_HOME=$NGINX_PREFIX"

    # 使用 modify_file 函数添加环境变量
    if ! modify_file "$PROFILE_FILE" "$env_config" "insert"; then
        warn "环境变量配置失败，尝试手动添加"
        echo "$env_config" >> "$PROFILE_FILE"
    fi

    # 立即生效
    source "$PROFILE_FILE"

    success "环境变量配置完成"
}

# ==================== 清理函数 ====================
# 清理临时文件和编译缓存
cleanup_installation() {
    info "清理安装临时文件..."

    # 清理源码目录
    if [ -d "$SRC_DIR" ] && [ "$CLEAN_TEMP" = "yes" ]; then
        find "$SRC_DIR" -maxdepth 1 -type d -name "php-*" -o -name "mysql-*" -o -name "redis-*" -o -name "nginx-*" | xargs rm -rf 2>/dev/null || true
        find "$SRC_DIR" -maxdepth 1 -type f -name "*.tar.gz" -o -name "*.tgz" | xargs rm -f 2>/dev/null || true
    fi

    # 清理临时文件数组中的文件
    cleanup_tmpfiles

    success "清理完成"
}

# ==================== 安装总结函数 ====================
# 生成安装总结
generate_installation_summary() {
    info "生成安装总结..."

    local end_time=$(date +%s)
    local duration=$((end_time - START_TIME))

    cat > "$INSTALL_SUMMARY" << EOF
# PHP Stack 安装总结
安装时间: $(date '+%F %T')
安装耗时: $((duration / 60)) 分 $((duration % 60)) 秒

## 安装组件
- PHP: ${PHP_VERSION} (${PHP_PREFIX})
- MySQL: ${MYSQL_VERSION} (${MYSQL_PREFIX})
- Redis: ${REDIS_VERSION} (${REDIS_PREFIX})
- Nginx: ${NGINX_VERSION} (${NGINX_PREFIX})
- Composer: $(composer --version 2>/dev/null | head -1 || echo "未安装")

## 服务状态
$(systemctl is-active php-fpm >/dev/null 2>&1 && echo "- PHP-FPM: 运行中" || echo "- PHP-FPM: 未运行")
$(systemctl is-active mysqld >/dev/null 2>&1 && echo "- MySQL: 运行中" || echo "- MySQL: 未运行")
$(systemctl is-active redis >/dev/null 2>&1 && echo "- Redis: 运行中" || echo "- Redis: 未运行")
$(systemctl is-active nginx >/dev/null 2>&1 && echo "- Nginx: 运行中" || echo "- Nginx: 未运行")

## 重要信息
- MySQL root 密码: $MYSQL_ROOT_PASS
- MySQL 远程用户: $MYSQL_REMOTE_ADMIN_USER / $MYSQL_REMOTE_ADMIN_PASS
- Redis 密码: $REDIS_PASS
- Web 用户: $WWW_USER:$GROUP_NAME

## 配置文件
- PHP: $PHP_INI_FILE
- MySQL: /etc/my.cnf
- Redis: /etc/redis/redis.conf
- Nginx: $NGINX_PREFIX/conf/nginx.conf

## 日志文件
- 安装日志: $LOG_FILE
- 错误日志: $ERROR_LOG_FILE
- 回滚日志: $ROLLBACK_LOG_FILE

## 服务管理命令
- 启动服务: systemctl start php-fpm mysqld redis nginx
- 停止服务: systemctl stop php-fpm mysqld redis nginx
- 重启服务: systemctl restart php-fpm mysqld redis nginx
- 查看状态: systemctl status php-fpm mysqld redis nginx

EOF

    success "安装总结已保存到: $INSTALL_SUMMARY"

    # 显示关键信息
    echo ""
    _bold "🎉 PHP Stack 安装完成!"
    echo "📊 安装耗时: $((duration / 60)) 分 $((duration % 60)) 秒"
    echo "📝 详细总结: $INSTALL_SUMMARY"
    echo "📋 服务状态: systemctl status php-fpm mysqld redis nginx"
    echo ""
    _yellow "⚠️  重要安全提示:"
    echo "   - 请立即修改默认密码"
    echo "   - 检查防火墙配置"
    echo "   - 定期备份重要数据"
}

# ==================== 主安装流程 ====================
# 主安装函数
main_installation() {
    local start_time=$(date +%s)

    echo ""
    _bold "🚀 开始安装 PHP Stack 环境..."
    echo "📋 安装组件: PHP ${PHP_VERSION}, MySQL ${MYSQL_VERSION}, Redis ${REDIS_VERSION}, Nginx ${NGINX_VERSION}"
    echo "⏰ 开始时间: $(date '+%F %T')"
    echo "📁 安装目录:"
    echo "   - PHP: $PHP_PREFIX"
    echo "   - MySQL: $MYSQL_PREFIX"
    echo "   - Redis: $REDIS_PREFIX"
    echo "   - Nginx: $NGINX_PREFIX"
    echo ""

    # 获取执行锁
    acquire_lock

    # 记录开始信息
    info "=== PHP Stack 安装开始 ==="
    info "版本: PHP $PHP_VERSION, MySQL $MYSQL_VERSION, Redis $REDIS_VERSION, Nginx $NGINX_VERSION"
    info "系统: $OS_NAME $OS_VERSION ($OS_ARCH)"
    info "用户: $(whoami)"
    info "工作目录: $(pwd)"

    # 执行安装步骤
    local steps=(
        "detect_system:检测系统环境"
        "install_dependencies:安装系统依赖"
        "create_users_and_groups:创建用户和组"
        "install_php:安装 PHP"
        "install_mysql:安装 MySQL"
        "install_redis:安装 Redis"
        "install_nginx:安装 Nginx"
        "install_composer:安装 Composer"
        "install_php_extensions:安装 PHP 扩展"
        "configure_environment:配置环境变量"
    )

    for step in "${steps[@]}"; do
        IFS=':' read -r func desc <<< "$step"

        # 根据安装选项跳过某些步骤
        case "$func" in
            install_php) [[ "$INSTALL_PHP" != "yes" ]] && continue ;;
            install_mysql) [[ "$INSTALL_MYSQL" != "yes" ]] && continue ;;
            install_redis) [[ "$INSTALL_REDIS" != "yes" ]] && continue ;;
            install_nginx) [[ "$INSTALL_NGINX" != "yes" ]] && continue ;;
            install_composer) [[ "$INSTALL_COMPOSER" != "yes" ]] && continue ;;
            install_php_extensions) [[ "$INSTALL_PHP_EXTENSIONS" != "yes" ]] && continue ;;
        esac

        info "开始步骤: $desc"

        if run_cmd_with_func "$func" "$desc"; then
            success "$desc 完成"
        else
            error "$desc 失败"
            handle_error "${BASH_LINENO[0]}" "$func" "$?"
        fi
    done

    # 清理工作
    cleanup_installation

    # 生成总结
    generate_installation_summary

    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    success "所有安装步骤完成! 总耗时: $((duration / 60)) 分 $((duration % 60)) 秒"

    # 清理锁文件
    rm -f "$LOCK_FILE"
}

# ==================== 脚本入口点 ====================
# 显示欢迎信息
show_welcome() {
    echo ""
    _bold "🌈 PHP Stack 一键安装脚本"
    echo "📚 版本: 最终优化版"
    echo "💡 支持系统: Alibaba Cloud Linux, OpenCloudOS, TencentOS, UOS, Kylin 等"
    echo "⚡ 优化特性: 高兼容性、错误恢复、详细日志、性能优化"
    echo ""
}

# 参数解析
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --php-version)
                PHP_VERSION="$2"
                shift 2
                ;;
            --mysql-version)
                MYSQL_VERSION="$2"
                shift 2
                ;;
            --redis-version)
                REDIS_VERSION="$2"
                shift 2
                ;;
            --nginx-version)
                NGINX_VERSION="$2"
                shift 2
                ;;
            --prefix)
                PHP_PREFIX="$2"
                MYSQL_PREFIX="$2/mysql"
                REDIS_PREFIX="$2/redis"
                NGINX_PREFIX="$2/nginx"
                shift 2
                ;;
            --no-php)
                INSTALL_PHP="no"
                shift
                ;;
            --no-mysql)
                INSTALL_MYSQL="no"
                shift
                ;;
            --no-redis)
                INSTALL_REDIS="no"
                shift
                ;;
            --no-nginx)
                INSTALL_NGINX="no"
                shift
                ;;
            --no-composer)
                INSTALL_COMPOSER="no"
                shift
                ;;
            --no-extensions)
                INSTALL_PHP_EXTENSIONS="no"
                shift
                ;;
            --help|-h)
                show_help
                exit 0
                ;;
            *)
                error "未知参数: $1"
                show_help
                exit 1
                ;;
        esac
    done
}

# 显示帮助信息
show_help() {
    cat << EOF
用法: $0 [选项]

选项:
    --php-version VERSION     设置 PHP 版本 (默认: $PHP_VERSION)
    --mysql-version VERSION   设置 MySQL 版本 (默认: $MYSQL_VERSION)
    --redis-version VERSION   设置 Redis 版本 (默认: $REDIS_VERSION)
    --nginx-version VERSION   设置 Nginx 版本 (默认: $NGINX_VERSION)
    --prefix DIR              设置安装前缀 (默认: $PHP_PREFIX)
    --no-php                  跳过 PHP 安装
    --no-mysql                跳过 MySQL 安装
    --no-redis                跳过 Redis 安装
    --no-nginx                跳过 Nginx 安装
    --no-composer             跳过 Composer 安装
    --no-extensions           跳过 PHP 扩展安装
    --help, -h                显示此帮助信息

环境变量:
    可以通过环境变量覆盖默认配置，例如:
    export PHP_VERSION="8.3.0"
    export MYSQL_ROOT_PASS="新密码"
    $0

示例:
    $0 --php-version 8.3.0 --no-mysql
    INSTALL_REDIS=no $0 --prefix /opt/phpstack

EOF
}

# 预检查函数
preflight_check() {
    info "执行预检查..."

    # 检查 root 权限
    if [[ $EUID -ne 0 ]]; then
        error "需要 root 权限运行此脚本"
        exit 1
    fi

    # 检查磁盘空间 (至少需要 2GB)
    local available_space=$(df / | awk 'NR==2 {print $4}')
    if [ "$available_space" -lt 2097152 ]; then  # 2GB in KB
        warn "磁盘空间可能不足 (可用: $((available_space/1024))MB, 需要: 2048MB)"
    fi

    # 检查内存 (至少需要 1GB)
    local total_mem=$(free -m | awk 'NR==2{print $2}')
    if [ "$total_mem" -lt 1024 ]; then
        warn "内存可能不足 (可用: ${total_mem}MB, 推荐: 1024MB)"
    fi

    success "预检查完成"
}

# 主函数
main() {
    show_welcome
    parse_arguments "$@"
    preflight_check
    main_installation
}

# 脚本入口点
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
