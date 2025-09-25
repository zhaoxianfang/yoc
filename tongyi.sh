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
# 初始化日志文件
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
    # echo -e "${ICON_WARN} $*" >&2
}

# 错误级别日志
error() {
    log "${ICON_ERROR} ERROR: $*"
    # echo -e "${ICON_ERROR} $*" >&2
    echo "$(date '+%F %T') - ERROR: $*" >> "$ERROR_LOG_FILE"
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

    # 定义进度动画帧
    local frames=("▰▱▱▱▱▱▱" "▰▰▱▱▱▱▱" "▰▰▰▱▱▱▱" "▰▰▰▰▱▱▱"
                 "▰▰▰▰▰▱▱" "▰▰▰▰▰▰▱" "▰▰▰▰▰▰▰" "▰▰▰▰▰▱▱"
                 "▰▰▰▰▱▱▱" "▰▰▰▱▱▱▱" "▰▰▱▱▱▱▱" "▰▱▱▱▱▱▱")

    # 在后台执行命令
    if [ "$log_output" = "yes" ]; then
        bash -lc "$cmd" >>"$LOG_FILE" 2>>"$ERROR_LOG_FILE" &
    else
        bash -lc "$cmd" >/dev/null 2>&1 &
    fi

    local pid=$!
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

    # 等待命令执行完成，增加错误处理
    set +e
    wait "$pid" 2>/dev/null
    local rc=$?
    set -e

    # 停止 spinner
    kill "$spinner_pid" 2>/dev/null || true
    wait "$spinner_pid" 2>/dev/null || true

    # 清理 spinner 行
    printf "\r%*s\r" "$(tput cols)" ""

    # 处理命令执行结果
    if [ $rc -ne 0 ]; then
        printf "❌ %s 失败（退出码:%s）\n" "$desc" "$rc"
        log "[CMD-FAIL] $desc (退出码:$rc)"
        return $rc
    else
        # printf "✅ %s 完成\n" "$desc"
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

# 安装软件包（使用 --allowerasing 避免冲突）
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

    # 使用 --allowerasing 允许替换冲突包
    case "$PKG_MGR" in
        dnf)
            run_cmd_with_retry "dnf -y install --allowerasing $pkgstr" "安装软件包" || return 1
            INSTALLED_PKGS+=("${to_install[@]}")
            mark_rollback "卸载新安装的软件包" "dnf -y remove --noautoremove ${pkgstr} || true"
            ;;
        yum)
            run_cmd_with_retry "yum -y install --allowerasing $pkgstr" "安装软件包" || return 1
            INSTALLED_PKGS+=("${to_install[@]}")
            mark_rollback "卸载新安装的软件包" "yum -y remove ${pkgstr} || true"
            ;;
        apt)
            run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install ${pkgstr}" "安装软件包" || return 1
            INSTALLED_PKGS+=("${to_install[@]}")
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
            # run_cmd_with_retry "dnf -y update --allowerasing" "更新系统" 2 5
            run_cmd_with_retry "dnf -y update --nobest" "更新系统" 2 5
            install_epel_repository
            ;;
        yum)
            # run_cmd_with_retry "yum -y update --allowerasing" "更新系统" 2 5
            run_cmd_with_retry "yum -y update --nobest" "更新系统" 2 5
            install_epel_repository
            ;;
        apt)
            # run_cmd_with_retry "apt-get -y update --allowerasing" "更新包列表" 2 5
            run_cmd_with_retry "apt-get -y update --nobest" "更新包列表" 2 5
            # run_cmd_with_retry "apt-get -y upgrade --allowerasing" "升级系统" 2 5
            run_cmd_with_retry "apt-get -y upgrade --nobest" "升级系统" 2 5
            ;;
    esac

    # 定义通用依赖包
    local common_packages=(
        yum-utils gcc gcc-c++ autoconf libtool make wget curl
        libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel
        openldap openldap-devel freetype freetype-devel libwebp-devel
        libxml2 libxml2-devel sqlite-devel zlib zlib-devel pcre pcre-devel
        gd gd-devel expat-devel libicu-devel bzip2 bzip2-devel
        oniguruma oniguruma-devel zstd glibc-headers krb5-devel
        libzip libzip-devel libxslt libxslt-devel openssl openssl-devel
        libsodium-devel glib2-devel cairo-devel gmp-devel libevent-devel
        readline-devel net-snmp-devel aspell-devel unixODBC-devel
        libc-client-devel libXpm-devel enchant-devel automake
        libtidy libtidy-devel ImageMagick ImageMagick-devel
    )

    pkg_install "${common_packages[@]}"

    success "系统依赖安装完成 (耗时: $(($(date +%s) - start)) 秒)"
}

# 安装 EPEL 仓库
install_epel_repository() {
    if ! run_cmd_with_retry "${PKG_MGR} -y install --allowerasing epel-release" "安装 EPEL 仓库" 3 2; then
        warn "通过包管理器安装 EPEL 失败，尝试备用方式..."
        install_epel_fallback
    fi
}

# EPEL 备用安装方式
install_epel_fallback() {
    local el_ver pkg_url
    el_ver=$(get_el_version)
    if ! [[ "$el_ver" =~ ^(7|8|9)$ ]]; then
        echo "错误：无法检测EL版本。" >&2
        exit 1
    fi

    # ✅ 优先使用国内镜像（阿里云 + 清华 双保险）
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
        # 再次检测清华源是否可达
        if ! curl -sf --connect-timeout 5 --max-time 10 "$pkg_url" >/dev/null 2>&1; then
            echo "错误：阿里云和Tuna镜像都无法访问。请检查网络。" >&2
            exit 1
        fi
    fi

    # 安装命令（自动选择 dnf/yum）
    if command -v dnf >/dev/null 2>&1; then
        dnf install -y "$pkg_url" >/dev/null
    elif command -v yum >/dev/null 2>&1; then
        yum install -y "$pkg_url" >/dev/null
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

# 判断 系统中的 epel-release EPEL 版本; 通过各种途径获取原生linux系统和魔改系统的 EPEL 源（注意：魔改系统中往往没有包含release的信息，需要通过其他配置进行判断）
# 函数：get_el_version
# 功能：自动检测系统底层兼容的 RHEL 主版本或当前系统兼容的 RHEL 主版本（Enterprise Linux 版本）
# 支持：EL7 / EL8 / EL9（未来可轻松扩展 EL10）
# 返回：
#   - 成功：标准输出打印 "7"、"8" 或 "9"
#   - 失败：无输出，返回非零状态码
# 设计原则：兼容性优先、静默输出、多方法交叉验证、支持国产操作系统
get_el_version() {
    # 初始化变量
    el_version=""

    # ------------------------------------------------------------
    # 辅助函数：安全加载 os-release 文件（兼容 /etc 和 /usr/lib 路径）
    # ------------------------------------------------------------
    load_os_release() {
        if [ -f /etc/os-release ]; then
            . /etc/os-release 2>/dev/null
            return 0
        elif [ -f /usr/lib/os-release ]; then
            . /usr/lib/os-release 2>/dev/null
            return 0
        fi
        return 1
    }

    # ------------------------------------------------------------
    # 方法 1：通过 PLATFORM_ID 判断（最权威，由 RHEL 系系统定义）
    # 示例：PLATFORM_ID="platform:el8"
    # ------------------------------------------------------------
    if load_os_release && [ -n "${PLATFORM_ID:-}" ]; then
        case "$PLATFORM_ID" in
            *el7*) el_version="7" ;;
            *el8*) el_version="8" ;;
            *el9*) el_version="9" ;;
            *el10*) el_version="10" ;;  # 预留 EL10 支持
        esac
        if [ -n "$el_version" ]; then
            echo "$el_version"
            return 0
        fi
    fi

    # ------------------------------------------------------------
    # 方法 2：解析 /etc/*-release 文件内容（支持多种发行版）
    # 使用 case 模式匹配，避免正则兼容性问题
    # ------------------------------------------------------------
    for release_file in \
        /etc/redhat-release \
        /etc/centos-release \
        /etc/alinux-release \
        /etc/anolis-release \
        /etc/tencentos-release \
        /etc/opencloudos-release \
        /etc/euleros-release \
        /etc/kylin-release \
        /etc/system-release \
        /etc/os-release; do

        if [ -f "$release_file" ]; then
            # 读取文件内容并压缩多余空格，便于匹配
            content=$(cat "$release_file" 2>/dev/null | tr -s ' \t' ' ')
            if [ -z "$content" ]; then
                continue
            fi

            # 根据内容关键词判断 EL 版本（覆盖主流及国产系统）
            case "$content" in
                # EL7 系列
                *[Rr]ed\ [Hh]at*\ 7*|*CentOS*\ 7*|*Alibaba*\ Linux\ 2*|*Anolis*\ 7*|*OpenAnolis*\ 7*|*TencentOS*\ 2.4*|*OpenCloudOS*\ 7*|*EulerOS*\ 2.0*|*Kylin*\ V10\ $Tercel$*|*Rocky*\ 7*|*AlmaLinux*\ 7*)
                    el_version="7" ;;

                # EL8 系列
                *[Rr]ed\ [Hh]at*\ 8*|*CentOS*\ 8*|*Alibaba*\ Linux\ 3*|*Anolis*\ 8*|*OpenAnolis*\ 8*|*TencentOS*\ 3.1*|*OpenCloudOS*\ 8*|*UnionTech*\ 20*|*UOS*\ 20*|*Kylin*\ V10\ $Sword$*|*Rocky*\ 8*|*AlmaLinux*\ 8*|*EulerOS*\ 2.9*)
                    el_version="8" ;;

                # EL9 系列
                *[Rr]ed\ [Hh]at*\ 9*|*CentOS*\ Stream\ 9*|*Alibaba*\ Linux\ [45]*|*Anolis*\ 23*|*OpenAnolis*\ 23*|*TencentOS*\ [34].*|*OpenCloudOS*\ 9*|*Kylin*\ V10*|*UnionTech*\ 23*|*UOS*\ 23*|*Rocky*\ 9*|*AlmaLinux*\ 9*|*EulerOS*\ 2.10*)
                    el_version="9" ;;
            esac

            if [ -n "$el_version" ]; then
                echo "$el_version"
                return 0
            fi
        fi
    done

    # ------------------------------------------------------------
    # 方法 3：通过 glibc 版本判断（极其可靠）
    #   - EL7: glibc 2.17
    #   - EL8: glibc 2.28
    #   - EL9: glibc 2.34+
    # ------------------------------------------------------------
    if [ -z "$el_version" ]; then
        glibc_ver=""
        # 尝试通过 ldd 获取版本
        if command -v ldd >/dev/null 2>&1; then
            glibc_ver=$(ldd --version 2>/dev/null | head -n1 | awk '{print $NF}' | head -n1 2>/dev/null)
        fi
        # 若失败，尝试直接调用 libc.so
        if [ -z "$glibc_ver" ]; then
            for lib in /lib64/libc.so.6 /lib/x86_64-linux-gnu/libc.so.6 /lib/libc.so.6; do
                if [ -f "$lib" ]; then
                    glibc_ver=$("$lib" 2>/dev/null | head -n1 | awk '{print $NF}' | head -n1 2>/dev/null)
                    if [ -n "$glibc_ver" ]; then
                        break
                    fi
                fi
            done
        fi

        # 解析主次版本号
        if [ -n "$glibc_ver" ]; then
            major=$(echo "$glibc_ver" | cut -d. -f1)
            minor=$(echo "$glibc_ver" | cut -d. -f2)
            if [ "$major" = "2" ] && [ -n "$minor" ] && [ "$minor" -eq "$minor" ] 2>/dev/null; then
                if [ "$minor" -ge 34 ]; then
                    el_version="9"
                elif [ "$minor" -ge 28 ]; then
                    el_version="8"
                elif [ "$minor" -ge 17 ]; then
                    el_version="7"
                fi
            fi
        fi

        if [ -n "$el_version" ]; then
            echo "$el_version"
            return 0
        fi
    fi

    # ------------------------------------------------------------
    # 方法 4：通过 systemd 版本判断
    #   - EL7: ~219
    #   - EL8: ~239
    #   - EL9: ≥250
    # ------------------------------------------------------------
    if command -v systemctl >/dev/null 2>&1; then
        systemd_line=$(systemctl --version 2>/dev/null | head -n1)
        systemd_ver=$(echo "$systemd_line" | awk '{print $2}' 2>/dev/null)
        if [ -n "$systemd_ver" ] && [ "$systemd_ver" -eq "$systemd_ver" ] 2>/dev/null; then
            if [ "$systemd_ver" -ge 250 ]; then
                echo "9"; return 0
            elif [ "$systemd_ver" -ge 230 ]; then
                echo "8"; return 0
            elif [ "$systemd_ver" -ge 210 ]; then
                echo "7"; return 0
            fi
        fi
    fi

    # ------------------------------------------------------------
    # 方法 5：通过内核版本判断（启发式，作为辅助）
    #   - EL7: 3.10.x
    #   - EL8: 4.18.x
    #   - EL9: 5.14+
    # ------------------------------------------------------------
    kernel=$(uname -r 2>/dev/null)
    if [ -n "$kernel" ]; then
        case "$kernel" in
            5.1[4-9].*|5.[2-9][0-9]*|6.*|7.*|8.*|9.*|1[0-9].*)
                echo "9"; return 0 ;;
            4.18.*)
                echo "8"; return 0 ;;
            3.10.*)
                echo "7"; return 0 ;;
        esac
    fi

    # ------------------------------------------------------------
    # 方法 6：检查已安装 RPM 包中是否包含 .el7/.el8/.el9 标识
    # 这是 RPM 系统最直接的证据
    # ------------------------------------------------------------
    if command -v rpm >/dev/null 2>&1; then
        # 只检查前 100 个包以提高效率（避免全量扫描）
        if rpm -qa --qf '%{NAME}-%{VERSION}-%{RELEASE}\n' 2>/dev/null | head -n 100 | grep -q '\.el9\.'; then
            echo "9"; return 0
        elif rpm -qa --qf '%{NAME}-%{VERSION}-%{RELEASE}\n' 2>/dev/null | head -n 100 | grep -q '\.el8\.'; then
            echo "8"; return 0
        elif rpm -qa --qf '%{NAME}-%{VERSION}-%{RELEASE}\n' 2>/dev/null | head -n 100 | grep -q '\.el7\.'; then
            echo "7"; return 0
        fi
    fi

    # ------------------------------------------------------------
    # 方法 7：检查 YUM/DNF 仓库配置中是否包含 .elX.
    # ------------------------------------------------------------
    if [ -d /etc/yum.repos.d/ ]; then
        if grep -r '\.el9\.' /etc/yum.repos.d/ >/dev/null 2>&1; then
            echo "9"; return 0
        elif grep -r '\.el8\.' /etc/yum.repos.d/ >/dev/null 2>&1; then
            echo "8"; return 0
        elif grep -r '\.el7\.' /etc/yum.repos.d/ >/dev/null 2>&1; then
            echo "7"; return 0
        fi
    fi

    # ------------------------------------------------------------
    # 方法 8：通过 OpenSSL 版本辅助判断（EL9 起默认使用 OpenSSL 3.0+）
    # ------------------------------------------------------------
    if command -v openssl >/dev/null 2>&1; then
        ssl_output=$(openssl version 2>/dev/null)
        ssl_ver=$(echo "$ssl_output" | awk '{print $2}' 2>/dev/null)
        if [ -n "$ssl_ver" ]; then
            case "$ssl_ver" in
                3.*|4.*)
                    echo "9"; return 0 ;;
            esac
        fi
    fi

    # ------------------------------------------------------------
    # 方法 9：通过 GCC 版本辅助判断
    #   - EL7: GCC 4.8
    #   - EL8: GCC 8
    #   - EL9: GCC 11+
    # ------------------------------------------------------------
    if command -v gcc >/dev/null 2>&1; then
        gcc_ver=$(gcc -dumpversion 2>/dev/null)
        case "$gcc_ver" in
            1[1-9]*|2[0-9]*)
                echo "9"; return 0 ;;
            8.*|9.*|10.*)
                echo "8"; return 0 ;;
            4.8*)
                echo "7"; return 0 ;;
        esac
    fi

    # ------------------------------------------------------------
    # 方法 10：通过 Python 版本辅助判断（谨慎使用，仅作参考）
    # 注意：Python 可能被用户自定义安装，故仅在其他方法失败时参考
    # ------------------------------------------------------------
    for py_cmd in python3 python; do
        if command -v "$py_cmd" >/dev/null 2>&1; then
            py_ver=$("$py_cmd" -c 'import sys; print("{}.{}".format(sys.version_info[0], sys.version_info[1]))' 2>/dev/null)
            if [ -n "$py_ver" ]; then
                case "$py_ver" in
                    3.9|3.1[0-9]|3.[2-9][0-9])
                        # 仅当其他方法都失败时才用，此处暂不直接返回
                        # 但若前面都失败，可在此处启用
                        :
                        ;;
                esac
            fi
        fi
    done

    # ------------------------------------------------------------
    # 方法 11：检查 /proc/version（某些容器或精简系统可能只有此信息）
    # ------------------------------------------------------------
    if [ -f /proc/version ]; then
        proc_ver=$(cat /proc/version 2>/dev/null)
        case "$proc_ver" in
            *el7*|*Red\ Hat\ Enterprise\ Linux\ Server\ release\ 7*)
                echo "7"; return 0 ;;
            *el8*|*Red\ Hat\ Enterprise\ Linux\ release\ 8*)
                echo "8"; return 0 ;;
            *el9*|*Red\ Hat\ Enterprise\ Linux\ release\ 9*)
                echo "9"; return 0 ;;
        esac
    fi

    # ------------------------------------------------------------
    # 方法 12：检测是否运行在 Docker 容器中，并尝试从镜像名推断
    # （仅当其他方法失败时作为最后手段）
    # ------------------------------------------------------------
    if [ -f /proc/1/cgroup ] && grep -q docker /proc/1/cgroup 2>/dev/null; then
        # 检查环境变量或 /etc/os-release 中是否有线索
        if load_os_release; then
            case "$PRETTY_NAME" in
                *7*) echo "7"; return 0 ;;
                *8*) echo "8"; return 0 ;;
                *9*) echo "9"; return 0 ;;
            esac
        fi
    fi

    # ------------------------------------------------------------
    # 所有方法均失败，无法确定 EL 版本
    # ------------------------------------------------------------
    return 1
}

# ==================== SWAP 配置函数 ====================
# 配置 SWAP 空间防止编译内存不足
setup_swap() {
    local current_swap=$(free -m | awk '/Swap:/{print $2}')
    if [ "$current_swap" -lt 2048 ]; then
        info "配置 SWAP 空间(2GB)以防止编译内存不足..."
        local swap_file="/var/cache/swap/swap0"

        mkdir -p /var/cache/swap/
        if [ ! -f "$swap_file" ]; then
            run_cmd "dd if=/dev/zero of=$swap_file bs=64M count=32" "创建 SWAP 文件"
            run_cmd "chmod 600 $swap_file" "设置 SWAP 文件权限"
            run_cmd "mkswap $swap_file" "初始化 SWAP"
        fi

        if ! swapon -s | grep -q "$swap_file"; then
            run_cmd "swapon $swap_file" "启用 SWAP"
            if ! grep -q "$swap_file" /etc/fstab; then
                echo "$swap_file swap swap defaults 0 0" >> /etc/fstab
            fi
            mark_rollback "移除 SWAP 配置" "swapoff $swap_file 2>/dev/null || true; sed -i '\|$swap_file|d' /etc/fstab || true"
        fi

        run_cmd "free -h" "检查内存状态"
        success "SWAP 配置完成"
    else
        info "现有 SWAP 空间充足(${current_swap}MB)，跳过配置"
    fi
}

# ==================== PHP 安装函数 ====================
# 下载 PHP 源码
download_php() {
    local start=$(date +%s)
    info "下载 PHP 源码..."
    mkdir -p "$SRC_DIR" && cd "$SRC_DIR"
    local php_archive="php-${PHP_VERSION}.tar.gz"

    for mirror in "${PHP_MIRRORS[@]}"; do
        info "尝试从镜像下载: $mirror"
        if command -v wget >/dev/null 2>&1; then
            if run_cmd_with_retry "wget -c --tries=$DOWNLOAD_RETRIES --timeout=30 '$mirror' -O '$php_archive'" "下载 PHP" 3 3; then
                if [ -s "$php_archive" ]; then
                    success "PHP 下载成功"
                    register_tmp_file "$SRC_DIR/$php_archive"
                    return 0
                fi
            fi
        else
            if run_cmd_with_retry "curl -L --retry $DOWNLOAD_RETRIES --connect-timeout 30 '$mirror' -o '$php_archive'" "下载 PHP" 3 3; then
                if [ -s "$php_archive" ]; then
                    success "PHP 下载成功"
                    register_tmp_file "$SRC_DIR/$php_archive"
                    return 0
                fi
            fi
        fi
        warn "镜像下载失败: $mirror"
    done

    error "所有镜像下载失败，请检查网络连接"
    return 1
}

# 编译安装 PHP
compile_php() {
    local start=$(date +%s)
    info "编译安装 PHP..."
    cd "$SRC_DIR"
    local php_src_dir="php-${PHP_VERSION}"

    if [ ! -d "$php_src_dir" ]; then
        run_cmd "tar -xzf php-${PHP_VERSION}.tar.gz" "解压 PHP 源码"
    fi
    cd "$php_src_dir"

    # 清理之前的编译
    run_cmd "make clean 2>/dev/null || true" "清理之前的编译"

    # 配置 PHP 编译选项
    info "配置 PHP 编译选项..."
    local opts_str=$(printf '%s ' "${PHP_CONFIGURE_OPTS[@]}" | tr -d '\n\r' | sed 's/ $//')
    run_cmd "./configure $opts_str" "配置 PHP" || {
        error "PHP 配置失败"
        return 1
    }

    # 编译 PHP
    info "编译 PHP (使用 $MAKE_JOBS 线程)..."
    run_cmd_with_retry "make -j$MAKE_JOBS" "编译 PHP" 2 5 || {
        error "PHP 编译失败"
        return 1
    }

    # 安装 PHP
    info "安装 PHP..."
    run_cmd "make install" "安装 PHP" || {
        error "PHP 安装失败"
        return 1
    }

    # 记录安装信息
    PHP_SRC_DIR="$SRC_DIR/$php_src_dir"
    PHP_INI_FILE="$PHP_PREFIX/lib/php.ini"

    success "PHP 编译安装完成 (耗时: $(($(date +%s) - start)) 秒)"
}

# 配置 PHP
configure_php() {
    info "配置 PHP..."

    # 创建 PHP 配置目录
    mkdir -p "$PHP_PREFIX/lib"

    # 复制配置文件
    if [ -f "$PHP_SRC_DIR/php.ini-production" ]; then
        cp "$PHP_SRC_DIR/php.ini-production" "$PHP_INI_FILE"
        success "PHP 配置文件已创建"
    fi

    # 配置 PHP-FPM
    if [ -f "$PHP_PREFIX/etc/php-fpm.conf.default" ]; then
        cp "$PHP_PREFIX/etc/php-fpm.conf.default" "$PHP_PREFIX/etc/php-fpm.conf"
    fi
    if [ -f "$PHP_PREFIX/etc/php-fpm.d/www.conf.default" ]; then
        cp "$PHP_PREFIX/etc/php-fpm.d/www.conf.default" "$PHP_PREFIX/etc/php-fpm.d/www.conf"
    fi

    # 优化 PHP 配置
    modify_php_config

    success "PHP 配置完成"
}

# 修改 PHP 配置
modify_php_config() {
    info "优化 PHP 配置..."
    safe_backup_file "$PHP_INI_FILE"

    # 定义 PHP 配置修改数组
    local php_config_modifications=(
        "memory_limit = 128M:memory_limit = $PHP_MEMORY_LIMIT:replace"
        "max_execution_time = 30:max_execution_time = $PHP_MAX_EXECUTION_TIME:replace"
        "max_input_time.*:max_input_time = 300:replace"
        "upload_max_filesize = 2M:upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE:replace"
        "post_max_size = 8M:post_max_size = $PHP_POST_MAX_SIZE:replace"
        "display_errors = Off:display_errors = On:replace"
        "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT:error_reporting = E_ALL:replace"
        "opcache.enable=0:opcache.enable=1:replace"
        ";opcache.memory_consumption=128:opcache.memory_consumption=256:replace"
        ";opcache.interned_strings_buffer=8:opcache.interned_strings_buffer=16:replace"
        ";opcache.max_accelerated_files=10000:opcache.max_accelerated_files=20000:replace"
        ";opcache.revalidate_freq=2:opcache.revalidate_freq=60:replace"
        ";opcache.enable_cli=0:opcache.enable_cli=1:replace"
        ";opcache.jit_buffer_size=0:opcache.jit_buffer_size=100M:replace"
        ";opcache.jit=1235:opcache.jit=1255:replace"
        ";date.timezone.*:date.timezone = Asia/Shanghai:insert"
    )

    # 应用 PHP 配置修改
    for config in "${php_config_modifications[@]}"; do
        IFS=':' read -r search replace mode <<< "$config"
        if [ -n "$search" ] && [ -n "$replace" ]; then
            modify_file "$PHP_INI_FILE" "$search:$replace:${mode:-replace}" ||
                warn "修改 PHP 配置失败: $search -> $replace"
        fi
    done

    success "PHP 配置优化完成"
}

# ==================== PHP 扩展安装函数 ====================
# 安装 PHP Redis 扩展
install_php_redis() {
    local start=$(date +%s)
    info "安装 PHP Redis 扩展..."
    cd "$SRC_DIR"

    # 通过GitHub 获取最新版本
    local redis_ext_url="https://github.com/phpredis/phpredis/archive/refs/tags/${REDIS_EXT_VERSION}.tar.gz"
    local redis_ext_dir="phpredis-${REDIS_EXT_VERSION}"
    local redis_ext_archive="phpredis-${REDIS_EXT_VERSION}.tar.gz"

    # 下载 Redis 扩展
    if command -v wget >/dev/null 2>&1; then
        run_cmd_with_retry "wget -c '$redis_ext_url' -O '$redis_ext_archive'" "下载 Redis 扩展" 3 3
    else
        run_cmd_with_retry "curl -L '$redis_ext_url' -o '$redis_ext_archive'" "下载 Redis 扩展" 3 3
    fi

    # 解压和编译
    run_cmd "tar -xzf '$redis_ext_archive'" "解压 Redis 扩展"
    cd "$redis_ext_dir"

    # 使用 phpize 准备扩展编译环境
    run_cmd "$PHP_PREFIX/bin/phpize" "准备 Redis 扩展编译环境"
    run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 Redis 扩展"
    run_cmd "make -j$MAKE_JOBS" "编译 Redis 扩展"
    run_cmd "make install" "安装 Redis 扩展"

    # 启用扩展
    modify_file "$PHP_INI_FILE" \
    ";extension=zip.*:extension=redis.so:insert"

    success "PHP Redis 扩展安装完成 (耗时: $(($(date +%s) - start)) 秒)"
}

# 安装 PHP Imagick 扩展
install_php_imagick() {
    local start=$(date +%s)
    info "安装 PHP Imagick 扩展..."
    cd "$SRC_DIR"

    # 通过GitHub 获取最新版本
    local imagick_ext_url="https://github.com/Imagick/imagick/archive/refs/tags/${IMAGICK_EXT_VERSION}.tar.gz"
    local imagick_ext_dir="imagick-${IMAGICK_EXT_VERSION}"
    local imagick_ext_archive="imagick-${IMAGICK_EXT_VERSION}.tar.gz"

    # 下载 Imagick 扩展
    if command -v wget >/dev/null 2>&1; then
        run_cmd_with_retry "wget -c '$imagick_ext_url' -O '$imagick_ext_archive'" "下载 Imagick 扩展" 3 3
    else
        run_cmd_with_retry "curl -L '$imagick_ext_url' -o '$imagick_ext_archive'" "下载 Imagick 扩展" 3 3
    fi

    # 解压和编译
    run_cmd "tar -xzf '$imagick_ext_archive'" "解压 Imagick 扩展"
    cd "$imagick_ext_dir"

    # 使用 phpize 准备扩展编译环境
    run_cmd "$PHP_PREFIX/bin/phpize" "准备 Imagick 扩展编译环境"
    run_cmd "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置 Imagick 扩展"
    run_cmd "make -j$MAKE_JOBS" "编译 Imagick 扩展"
    run_cmd "make install" "安装 Imagick 扩展"

    # 启用扩展
    # echo "extension=imagick.so" > "$PHP_PREFIX/lib/conf.d/imagick.ini"

    # 启用扩展
    modify_file "$PHP_INI_FILE" \
    ";extension=zip.*:extension=imagick.so:insert"

    success "PHP Imagick 扩展安装完成 (耗时: $(($(date +%s) - start)) 秒)"
}

# ==================== 系统服务配置函数 ====================
# 创建系统用户和组
create_system_user() {
    info "创建系统用户和组..."

    # 创建 www 用户组
    if ! getent group "$GROUP_NAME" >/dev/null; then
        run_cmd "groupadd $GROUP_NAME" "创建用户组 $GROUP_NAME"
        CREATED_USERS+=("group:$GROUP_NAME")
        mark_rollback "删除用户组 $GROUP_NAME" "groupdel $GROUP_NAME 2>/dev/null || true"
    fi

    # 创建 www 用户
    if ! id -u "$WWW_USER" >/dev/null 2>&1; then
        run_cmd "useradd -M -s /sbin/nologin -g $GROUP_NAME $WWW_USER" "创建用户 $WWW_USER"
        CREATED_USERS+=("user:$WWW_USER")
        mark_rollback "删除用户 $WWW_USER" "userdel $WWW_USER 2>/dev/null || true"
    fi

    success "系统用户创建完成"
}

# 配置 PHP-FPM 服务
configure_php_fpm_service() {
    info "配置 PHP-FPM 服务..."

    # 创建 systemd 服务文件
    local service_file="/etc/systemd/system/php-fpm.service"
    safe_backup_file "$service_file"

    cat > "$service_file" << EOF
[Unit]
Description=The PHP FastCGI Process Manager
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

    # 重新加载 systemd
    run_cmd "systemctl daemon-reload" "重新加载 systemd"

    # 启用并启动服务
    if [ "$AUTO_START_SERVICES" = "yes" ]; then
        run_cmd "systemctl enable php-fpm" "启用 PHP-FPM 服务"
        run_cmd "systemctl start php-fpm" "启动 PHP-FPM 服务"
        mark_rollback "停止并禁用 PHP-FPM 服务" "systemctl stop php-fpm 2>/dev/null || true; systemctl disable php-fpm 2>/dev/null || true"
    fi

    success "PHP-FPM 服务配置完成"
}

# ==================== 环境变量配置函数 ====================
# 配置环境变量
setup_environment() {
    info "配置环境变量..."
    safe_backup_file "$PROFILE_FILE"

    # 添加 PHP 到 PATH
    #modify_file "$PROFILE_FILE" "export PATH.*:export PATH=$PHP_PREFIX/bin:$PATH:insert"

    # 添加 PHP 到 PATH
    if ! grep -q "$PHP_PREFIX/bin" "$PROFILE_FILE"; then
        cat >> "$PROFILE_FILE" << EOF

# PHP Environment
export PATH=$PHP_PREFIX/bin:\$PATH
export PHP_HOME=$PHP_PREFIX
EOF
        success "环境变量已添加到 $PROFILE_FILE"
    fi
    # 立即生效
    source "$PROFILE_FILE"

    success "环境变量配置完成"
}

# ==================== 清理函数 ====================
# 清理临时文件和编译缓存
cleanup_system() {
    info "清理系统临时文件..."

    # 清理包管理器缓存
    case "$PKG_MGR" in
        dnf|yum)
            run_cmd "$PKG_MGR clean all" "清理包管理器缓存"
            ;;
        apt)
            run_cmd "apt-get clean" "清理 APT 缓存"
            ;;
    esac

    # 清理源码目录
    if [ "$CLEAN_TEMP" = "yes" ]; then
        run_cmd "rm -rf $SRC_DIR/php-${PHP_VERSION}" "清理 PHP 源码目录"
        run_cmd "rm -rf $SRC_DIR/phpredis-${REDIS_EXT_VERSION}" "清理 Redis 扩展源码"
        run_cmd "rm -rf $SRC_DIR/imagick-${IMAGICK_EXT_VERSION}" "清理 Imagick 扩展源码"
    fi

    # 清理临时文件
    cleanup_tmpfiles

    success "系统清理完成"
}

# ==================== 验证函数 ====================
# 验证安装结果
verify_installation() {
    info "验证安装结果..."

    # 验证 PHP 安装
    if command -v php >/dev/null 2>&1; then
        local php_version=$(php -v | head -n1)
        success "PHP 安装成功: $php_version"

        # 验证 PHP 扩展
        if php -m | grep -q redis; then
            success "PHP Redis 扩展已启用"
        else
            warn "PHP Redis 扩展未启用"
        fi

        if php -m | grep -q imagick; then
            success "PHP Imagick 扩展已启用"
        else
            warn "PHP Imagick 扩展未启用"
        fi
    else
        error "PHP 安装失败"
        return 1
    fi

    # 验证 PHP-FPM 服务
    if systemctl is-active php-fpm >/dev/null 2>&1; then
        success "PHP-FPM 服务运行正常"
    else
        warn "PHP-FPM 服务未运行"
    fi

    success "安装验证完成"
}

# ==================== 安装摘要函数 ====================
# 生成安装摘要
generate_install_summary() {
    info "生成安装摘要..."

    cat > "$INSTALL_SUMMARY" << EOF
============================================
PHP 环境安装摘要
生成时间: $(date '+%F %T')
安装耗时: $(($(date +%s) - START_TIME)) 秒
============================================

系统信息:
- 操作系统: $OS_NAME $OS_VERSION ($OS_ARCH)
- 包管理器: $PKG_MGR

安装版本:
- PHP: $PHP_VERSION
- Redis 扩展: $REDIS_EXT_VERSION
- Imagick 扩展: $IMAGICK_EXT_VERSION

安装路径:
- PHP: $PHP_PREFIX
- 配置文件: $PHP_INI_FILE
- 源码目录: $SRC_DIR

服务状态:
- PHP-FPM: $(systemctl is-enabled php-fpm 2>/dev/null || echo '未启用')
- PHP-FPM 运行状态: $(systemctl is-active php-fpm 2>/dev/null || echo '未运行')

环境配置:
- PHP Memory Limit: $PHP_MEMORY_LIMIT
- PHP Max Execution Time: $PHP_MAX_EXECUTION_TIME
- PHP Upload Max Filesize: $PHP_UPLOAD_MAX_FILESIZE

重要文件:
- 安装日志: $LOG_FILE
- 错误日志: $ERROR_LOG_FILE
- 回滚日志: $ROLLBACK_LOG_FILE

验证命令:
- 检查 PHP 版本: $PHP_PREFIX/bin/php -v
- 检查 PHP 模块: $PHP_PREFIX/bin/php -m
- 重启 PHP-FPM: systemctl restart php-fpm

注意事项:
1. 请确保防火墙已放行相关端口
2. 建议定期备份配置文件
3. 监控系统资源使用情况

EOF

    success "安装摘要已保存到: $INSTALL_SUMMARY"
}

# ==================== 主安装流程 ====================
# 主安装函数
main_install() {
    clear # 先清屏
    local start_time=$(date +%s)

    # 显示安装开始信息
    echo ""
    _bold "============================================"
    _bold "  PHP 环境一键安装脚本"
    _bold "  版本: 优化加强版 v1.0"
    _bold "  开始时间: $(date '+%F %T')"
    _bold "============================================"
    _bold "  作者: yoc.cn , weisifang.com"
    _bold "============================================"
    _bold "     PHP: V $PHP_VERSION"
    _bold "   MySQL: V $MYSQL_VERSION"
    _bold "   Redis: V $REDIS_VERSION"
    _bold "   Nginx: V $NGINX_VERSION"
    _bold " 安装日志: $LOG_FILE"
    _bold " 错误日志: $ERROR_LOG_FILE"
    _bold "---------------------------------------------"
    echo ""

    # 获取执行锁
    acquire_lock

    # 记录开始时间
    log "=== 安装开始 ==="
    log "开始时间: $(date '+%F %T')"
    log "安装参数: PHP=$PHP_VERSION, Redis扩展=$REDIS_EXT_VERSION, Imagick扩展=$IMAGICK_EXT_VERSION"

    # 执行安装步骤
    info "开始执行安装流程..."

    # 步骤 1: 系统检测
    detect_system || handle_error ${LINENO} "系统检测失败"

    # 步骤 2: 必须先安装依赖
    install_dependencies || handle_error ${LINENO} "依赖安装失败"

    # 步骤 3: 配置 SWAP
    setup_swap || warn "SWAP 配置失败，继续安装"

    # 步骤 4: 下载 PHP
    download_php || handle_error ${LINENO} "PHP 下载失败"

    # 步骤 5: 编译安装 PHP
    compile_php || handle_error ${LINENO} "PHP 编译安装失败"

    # 步骤 6: 配置 PHP
    configure_php || handle_error ${LINENO} "PHP 配置失败"

    # 步骤 7: 安装 PHP 扩展
    install_php_redis || handle_error ${LINENO} "Redis 扩展安装失败"
    install_php_imagick || handle_error ${LINENO} "Imagick 扩展安装失败"

    # 步骤 8: 创建系统用户
    create_system_user || handle_error ${LINENO} "用户创建失败"

    # 步骤 9: 配置 PHP-FPM 服务
    configure_php_fpm_service || handle_error ${LINENO} "PHP-FPM 服务配置失败"

    # 步骤 10: 配置环境变量
    setup_environment || handle_error ${LINENO} "环境变量配置失败"

    # 步骤 11: 验证安装
    verify_installation || handle_error ${LINENO} "安装验证失败"

    # 步骤 12: 清理系统
    cleanup_system || warn "系统清理失败，但不影响安装"

    # 步骤 13: 生成安装摘要
    generate_install_summary || warn "安装摘要生成失败"

    # 计算总耗时
    local total_time=$(($(date +%s) - start_time))
    log "=== 安装完成 ==="
    log "结束时间: $(date '+%F %T')"
    log "总耗时: ${total_time} 秒"

    # 显示完成信息
    echo ""
    _bold "============================================"
    _green "✅ PHP 环境安装完成!"
    _bold "总耗时: ${total_time} 秒"
    _bold "============================================"
    echo ""
    _green "安装摘要: $INSTALL_SUMMARY"
    _green "详细日志: $LOG_FILE"
    echo ""
    _yellow "下一步操作:"
    _yellow "1. 检查服务状态: systemctl status php-fpm"
    _yellow "2. 验证 PHP 安装: $PHP_PREFIX/bin/php -v"
    _yellow "3. 检查已安装模块: $PHP_PREFIX/bin/php -m"
    echo ""

    # 清理执行锁
    rm -f "$LOCK_FILE"

    return 0
}

# ==================== 脚本入口点 ====================
# 主执行逻辑
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    # 检查是否以 root 权限运行
    if [[ $EUID -ne 0 ]]; then
        error "此脚本必须以 root 权限运行"
        exit 1
    fi

    # 执行主安装函数
    main_install "$@"

    # 退出码
    exit $?
fi
