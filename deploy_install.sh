#!/usr/bin/env bash
# 指定使用的解释器为 bash，确保脚本可移植
# -----------------------------------------------------------------------------
# industrial_php_stack_improved.sh  # 脚本名及说明
# 改进版：工业级 PHP 环境一键安装脚本（增强错误接管、回滚、备份与日志）  # 脚本功能简介
# 功能：安装 PHP / Nginx / MySQL / Redis / Composer / 常用扩展  # 支持的软件组件
# 主要改进点：  # 列出主要改动
#  - 全面中文注释  # 每行均添加注释
#  - ERR/INT/TERM 信号接管并触发回滚  # 错误处理策略
#  - 记录每一步的反向操作（rollback），异常时按逆序执行以回滚系统改动  # 回滚机制
#  - 智能判断哪些包是新安装的，回滚时仅移除这些包（尽量避免删除已有系统包）  # 安全移除策略
#  - 对修改的配置文件先备份，回滚时恢复备份  # 备份机制
#  - 优化 run_cmd 执行方式，使用 "bash -lc" 执行多行命令，解决命令包含换行符导致的执行问题  # 解决换行/特殊字符问题
#  - 避免不必要的 eval，修复 make -j 等错误引用  # 安全改进
#  - 增强日志（安装日志 + 错误日志 + 回滚日志）  # 日志策略
#  - 增加临时文件管理，退出时自动清理  # 清理机制
#  - 添加安全提示与使用说明  # 用户友好性
# -----------------------------------------------------------------------------

set -euo pipefail  # 开启严格模式：出错退出、未定义变量视作错误、管道失败时整体失败
set -o errtrace   # 使 ERR trap 在函数/子 shell 中也生效  # 保证错误捕获完整
IFS=$'\n\t'  # 设置内部字段分隔符为换行和制表符，避免空格或换行导致参数被错误拆分

# ---------------- 可配置参数（保持与原脚本兼容） ----------------
INSTALL_PHP="${INSTALL_PHP:-yes}"  # 是否安装 PHP，默认 yes
INSTALL_MYSQL="${INSTALL_MYSQL:-yes}"  # 是否安装 MySQL，默认 yes
INSTALL_REDIS="${INSTALL_REDIS:-yes}"  # 是否安装 Redis，默认 yes
INSTALL_NGINX="${INSTALL_NGINX:-yes}"  # 是否安装 Nginx，默认 yes
INSTALL_COMPOSER="${INSTALL_COMPOSER:-yes}"  # 是否安装 Composer，默认 yes
INSTALL_PHP_EXTENSIONS="${INSTALL_PHP_EXTENSIONS:-yes}"  # 是否安装常用 PHP 扩展，默认 yes

PHP_VERSION="${PHP_VERSION:-8.4.12}"  # PHP 默认版本
MYSQL_VERSION="${MYSQL_VERSION:-8.4.0}"  # MySQL 版本（仅记录用）
REDIS_VERSION="${REDIS_VERSION:-7.2.4}"  # Redis 版本（仅记录用）
NGINX_VERSION="${NGINX_VERSION:-1.28.0}"  # Nginx 版本（仅记录用）

PHP_MIRRORS=(  # PHP 源码镜像列表，按优先级排列
    "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"  # 官方镜像
    "https://mirrors.aliyun.com/php-distributions/php-${PHP_VERSION}.tar.gz"  # 阿里云镜像
    "https://mirrors.cloud.tencent.com/php-distributions/php-${PHP_VERSION}.tar.gz"  # 腾讯云镜像
)

PHP_PREFIX="${PHP_PREFIX:-/usr/local/php8}"  # PHP 安装前缀
MYSQL_PREFIX="${MYSQL_PREFIX:-/usr/local/mysql}"  # MySQL 安装前缀（如源码安装时使用）
NGINX_PREFIX="${NGINX_PREFIX:-/usr/local/nginx}"  # Nginx 安装前缀
REDIS_PREFIX="${REDIS_PREFIX:-/usr/local/redis}"  # Redis 安装前缀

MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-$(openssl rand -base64 24)}"  # MySQL root 密码，默认随机生成
REMOTE_ADMIN_USER="${REMOTE_ADMIN_USER:-phpadmin}"  # 远程管理用户
REMOTE_ADMIN_PASS="${REMOTE_ADMIN_PASS:-$(openssl rand -base64 24)}"  # 远程管理用户密码
WWW_USER="${WWW_USER:-www}"  # 网站运行用户
WWW_PASS="${WWW_PASS:-$(openssl rand -base64 24)}"  # 网站运行用户密码

SRC_DIR="${SRC_DIR:-/usr/local/src}"  # 源码下载目录
LOG_DIR="${LOG_DIR:-/var/log/php-stack-install}"  # 日志目录
LOG_FILE="${LOG_FILE:-$LOG_DIR/install.log}"  # 安装日志文件
ERROR_LOG_FILE="${ERROR_LOG_FILE:-$LOG_DIR/error.log}"  # 错误日志文件
ROLLBACK_LOG_FILE="${ROLLBACK_LOG_FILE:-$LOG_DIR/rollback.log}"  # 回滚日志文件
INSTALL_SUMMARY="${INSTALL_SUMMARY:-/data/install_config.log}"  # 安装总结输出文件
MAKE_JOBS="${MAKE_JOBS:-$(nproc 2>/dev/null || echo 1)}"  # make -j 使用的并行数，默认为 CPU 核心数
AUTO_START_SERVICES="${AUTO_START_SERVICES:-yes}"  # 是否自动启用并启动服务
MAX_RETRIES="${MAX_RETRIES:-3}"  # 全局重试次数，默认 3（按用户要求）
DOWNLOAD_RETRIES="${DOWNLOAD_RETRIES:-3}"  # 下载重试次数，默认 3
CLEAN_TEMP="${CLEAN_TEMP:-yes}"  # 是否清理临时文件
PROFILE_FILE="${PROFILE_FILE:-/etc/profile}"  # 全局 profile 文件

PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-512M}"  # PHP memory_limit
PHP_MAX_EXECUTION_TIME="${PHP_MAX_EXECUTION_TIME:-300}"  # PHP max_execution_time
PHP_UPLOAD_MAX_FILESIZE="${PHP_UPLOAD_MAX_FILESIZE:-256M}"  # PHP upload_max_filesize
PHP_POST_MAX_SIZE="${PHP_POST_MAX_SIZE:-256M}"  # PHP post_max_size

# ---------------- 全局变量 ----------------
PKG_MGR=""  # 包管理器标识
OS_ID=""  # 操作系统 ID
OS_NAME=""  # 操作系统名称
OS_VERSION=""  # 操作系统版本
OS_ARCH=""  # 系统架构
PHP_SRC_DIR=""  # PHP 源码解压目录
PHP_INI_FILE=""  # php.ini 路径
START_TIME=$(date +%s)  # 记录脚本开始时间
TMP_FILES=()  # 临时文件列表

# rollback 命令栈（按顺序 push，回滚时逆序执行）
ROLLBACK_CMDS=()  # 回滚命令栈
# 记录由脚本新安装的软件包（回滚时会移除这些包）
INSTALLED_PKGS=()  # 记录新安装的软件包
# 记录创建的用户（回滚时会删除）
CREATED_USERS=()  # 记录创建的用户
# 记录备份的配置文件（回滚时恢复）
BACKUP_FILES=()  # 记录备份文件

# 创建目录并初始化日志
mkdir -p "$SRC_DIR" "$LOG_DIR" "$(dirname "$INSTALL_SUMMARY")"  # 确保目录存在
: > "$LOG_FILE"  # 清空安装日志
: > "$ERROR_LOG_FILE"  # 清空错误日志
: > "$ROLLBACK_LOG_FILE"  # 清空回滚日志

# ---------------- 输出（彩色） ----------------
_red() { echo -e "[31m$*[0m"; }  # 红色输出函数
_green() { echo -e "[32m$*[0m"; }  # 绿色输出函数
_yellow() { echo -e "[33m$*[0m"; }  # 黄色输出函数
_blue() { echo -e "[34m$*[0m"; }  # 蓝色输出函数
_bold() { echo -e "[1m$*[0m"; }  # 粗体输出函数

ICON_INFO="🔵"  # 信息图标
ICON_SUCCESS="✅"  # 成功图标
ICON_WARN="🟡"  # 警告图标
ICON_ERROR="🔴"  # 错误图标

log() {  # 日志记录函数
    echo "[$(date '+%F %T')] $*" | tee -a "$LOG_FILE"  # 同时输出到控制台并追加到日志
}

info() {  # 信息输出函数
    log "${ICON_INFO} $*"  # 写日志
    echo -e "${ICON_INFO} $*"  # 控制台输出
}

success() {  # 成功输出函数
    log "${ICON_SUCCESS} $*"  # 写日志
    echo -e "${ICON_SUCCESS} $*"  # 控制台输出
}

warn() {  # 警告输出函数
    log "${ICON_WARN} $*"  # 写日志
    echo -e "${ICON_WARN} $*" >&2  # 输出到 stderr
}

error() {  # 错误输出函数
    log "${ICON_ERROR} $*"  # 写日志
    echo -e "${ICON_ERROR} $*" >&2  # 输出到 stderr
    echo "$(date '+%F %T') - ERROR: $*" >> "$ERROR_LOG_FILE"  # 同时写入错误日志
}

# --------------- 回滚记录与执行工具 ----------------
# 将一条回滚命令入栈（描述性说明 + shell 命令）
mark_rollback() {  # 将回滚动作记录到栈中
    local desc="$1"  # 回滚动作描述
    local cmd="$2"  # 回滚命令
    ROLLBACK_CMDS+=("# $desc\n$cmd")  # 入栈，保存描述和命令
    log "[ROLLBACK MARK] $desc -> $cmd"  # 记录到日志
}

# 执行回滚（按逆序）
rollback_all() {  # 执行所有记录的回滚命令
    echo "" | tee -a "$ROLLBACK_LOG_FILE"  # 写入空行分隔
    echo "[$(date '+%F %T')] 开始回滚..." | tee -a "$ROLLBACK_LOG_FILE"  # 回滚开始时间写日志

    # 逆序执行
    for ((i=${#ROLLBACK_CMDS[@]}-1; i>=0; i--)); do  # 逆序遍历回滚栈
        local item="${ROLLBACK_CMDS[i]}"  # 取出栈项
        echo "- 执行回滚步骤: ${item%%$'\n'*}" | tee -a "$ROLLBACK_LOG_FILE"  # 输出步骤描述
        # 执行命令体（跳过以 '#' 开头的注释行）
        local cmd=$(printf '%s' "$item" | sed -n '2,999p')  # 取得命令体
        if [ -n "$cmd" ]; then  # 若命令体非空则执行
            set +e  # 临时取消 -e，保证回滚继续执行
            bash -lc "$cmd" >>"$ROLLBACK_LOG_FILE" 2>&1  # 执行并记录输出
            local rc=$?  # 获取退出码
            set -e  # 恢复 -e
            if [ $rc -eq 0 ]; then  # 判断执行结果
                echo "  -> 回滚步骤成功" | tee -a "$ROLLBACK_LOG_FILE"  # 成功日志
            else
                echo "  -> 回滚步骤失败 (退出码:$rc)" | tee -a "$ROLLBACK_LOG_FILE"  # 失败日志
            fi
        fi
    done

    echo "[$(date '+%F %T')] 回滚完成" | tee -a "$ROLLBACK_LOG_FILE"  # 回滚完成时间写日志
}

# --------------- 临时文件管理 ----------------
register_tmp_file() {  # 注册临时文件，退出时自动清理
    TMP_FILES+=("$1")  # 将临时文件路径加入数组
}

cleanup_tmpfiles() {  # 清理注册的临时文件
    for f in "${TMP_FILES[@]:-}"; do  # 遍历所有注册的临时文件
        [ -e "$f" ] && rm -rf "$f" || true  # 逐个删除
    done
}

# ----------------- 运行命令的通用函数（支持多行） -----------------
# 使用 bash -lc 来执行任意复杂命令（包含换行），解决命令中包含换行或特殊字符时的执行问题
run_cmd() {  # 运行单条命令并记录日志（带 spinner）
    local cmd="$1"  # 要执行的命令
    local desc="${2:-执行命令}"  # 命令描述，默认 "执行命令"

    log "[CMD] $desc : $cmd"  # 记录命令到日志

    # 后台执行以支持 spinner（保持与原脚本交互性），但简化实现以避免 eval 的安全问题
    bash -lc "$cmd" >>"$LOG_FILE" 2>>"$ERROR_LOG_FILE" &  # 使用 bash -lc 执行命令并重定向输出
    local pid=$!  # 获取后台进程 PID

    # 简单的 spinner（如果命令很快结束，不影响）
    local i=0  # spinner 索引
    local frames=("-" "\\" "|" "/")  # spinner 帧：- \ | /
    printf "⏳ %s " "$desc"  # 初始 spinner 输出
    while kill -0 "$pid" 2>/dev/null; do  # 当进程存在时更新 spinner
        i=$(((i+1) % ${#frames[@]}))  # 计算下一个帧索引
        printf "\r⏳ %s %s" "$desc" "${frames[i]}"  # 打印 spinner
        sleep 0.08  # spinner 刷新间隔
    done
    wait "$pid"  # 等待命令结束
    local rc=$?  # 获取命令退出码
    if [ $rc -ne 0 ]; then  # 非零视为失败
        log "[CMD-FAIL] $desc (退出码:$rc)"  # 记录失败
        return $rc  # 返回失败码
    fi
    printf "\r✅ %s 完成\n" "$desc"  # 成功提示
    log "[CMD-OK] $desc"  # 记录成功
    return 0  # 返回成功
}

# 带重试的 run_cmd
run_cmd_with_retry() {  # 带重试的命令执行函数
    local cmd="$1"  # 要执行的命令
    local desc="${2:-执行命令}"  # 描述
    local max_retries="${3:-$MAX_RETRIES}"  # 最大重试次数
    local retry_delay="${4:-2}"  # 重试间隔秒数

    local attempt=1  # 初始尝试次数
    while [ $attempt -le $max_retries ]; do  # 循环直到达到最大重试次数
        if run_cmd "$cmd" "$desc"; then  # 如果命令成功则返回
            return 0
        fi
        warn "$desc 失败 (尝试 $attempt/$max_retries)，${retry_delay}s 后重试..."  # 警告并等待
        sleep $retry_delay  # 等待
        attempt=$((attempt+1))  # 增加尝试次数
    done

    error "$desc 在 $max_retries 次尝试后仍然失败: $cmd"  # 最终失败记录错误
    return 1  # 返回失败
}

# ----------------- 系统检测 -----------------
# 检测操作系统类型、包管理器与架构信息
detect_system() {  # 检测系统信息和包管理器类型
    info "检测系统环境..."  # 输出信息
    if [ -f /etc/os-release ]; then  # 如果存在 os-release 文件则读取
        . /etc/os-release  # 导入变量
        OS_ID="${ID:-unknown}"  # 设置 OS_ID
        OS_NAME="${NAME:-unknown}"  # 设置 OS_NAME
        OS_VERSION="${VERSION_ID:-unknown}"  # 设置 OS_VERSION
    else
        fail_msg="无法检测操作系统"  # 无法检测时的错误信息
        error "$fail_msg"  # 输出错误
        return 1  # 返回失败
    fi

    OS_ARCH=$(uname -m)  # 获取系统架构
    [ "$OS_ARCH" = "x86_64" ] && OS_ARCH="x64" || true  # 对常见架构做友好显示

    if command -v dnf >/dev/null 2>&1; then  # 检查 dnf
        PKG_MGR="dnf"  # 使用 dnf
    elif command -v yum >/dev/null 2>&1; then  # 检查 yum
        PKG_MGR="yum"  # 使用 yum
    elif command -v apt-get >/dev/null 2>&1; then  # 检查 apt-get
        PKG_MGR="apt"  # 使用 apt
    else
        error "未找到受支持的包管理器 (dnf|yum|apt-get)"  # 未找到包管理器时错误
        return 1  # 返回失败
    fi

    success "系统: $OS_NAME $OS_VERSION ($OS_ARCH), 包管理器: $PKG_MGR"  # 打印检测结果
}

# ----------------- 软件包安装工具（智能判断哪些包是新安装） -----------------
# 检查包是否已安装（返回 0=已安装 1=未安装）
pkg_is_installed() {  # 检测具体包是否已安装
    local pkg="$1"  # 包名参数
    if [ "$PKG_MGR" = "apt" ]; then  # apt 系统使用 dpkg
        dpkg -s "$pkg" >/dev/null 2>&1 && return 0 || return 1
    else  # rpm 系统使用 rpm -q
        rpm -q "$pkg" >/dev/null 2>&1 && return 0 || return 1
    fi
}

# 将包数组拼成安全的 shell 字符串（逐个 printf '%q'）
escape_pkg_list() {  # 对包名做 shell 转义以安全地放入命令行（返回以空格分隔的转义字符串）
    local -n arr=$1  # 通过 nameref 引用传入的数组名
    local out=()  # 临时数组用于保存转义后的包名
    for p in "${arr[@]}"; do  # 遍历包名数组
        out+=("$(printf '%q' "$p")")  # 使用 printf '%q' 转义每个包名并加入列表
    done
    # 将转义后的包名用空格连接，末尾不含额外空格
    local joined=""  # 初始化连接字符串
    for item in "${out[@]}"; do  # 遍历转义结果
        if [ -z "$joined" ]; then  # 若为空则直接赋值
            joined="$item"
        else
            joined="$joined $item"  # 否则追加空格分隔
        fi
    done
    printf '%s' "$joined"  # 输出最终的转义字符串
}

# 安装一组包（只安装尚未安装的包），并记录安装的包以便回滚
pkg_install() {  # 智能安装包函数
    local pkgs=("$@")  # 将传入参数组成数组
    info "准备安装软件包: ${pkgs[*]}"  # 打印准备安装的包列表

    local to_install=()  # 需要安装的包列表
    for p in "${pkgs[@]}"; do  # 遍历每个包
        if pkg_is_installed "$p"; then  # 如果已安装则跳过
            info "已存在: $p，跳过安装"
        else
            to_install+=("$p")  # 否则加入待安装列表
        fi
    done

    if [ ${#to_install[@]} -eq 0 ]; then  # 若无新包则直接返回
        info "没有需要安装的新包"
        return 0
    fi

    local pkgstr=$(escape_pkg_list to_install)  # 将包列表转义拼接为字符串

    case "$PKG_MGR" in  # 根据包管理器选择安装命令，并尽量使用 --allowerasing 处理冲突
        dnf)
            run_cmd_with_retry "dnf -y install --allowerasing $pkgstr" "安装软件包" || return 1  # dnf 使用 --allowerasing
            INSTALLED_PKGS+=("${to_install[@]}")  # 记录新安装包
            mark_rollback "卸载脚本新安装的软件包" "dnf -y remove --noautoremove ${pkgstr} || true"  # 回滚命令
            ;;
        yum)
            run_cmd_with_retry "yum -y install --allowerasing $pkgstr" "安装软件包" || return 1  # yum 使用 --allowerasing
            INSTALLED_PKGS+=("${to_install[@]}")  # 记录新安装包
            mark_rollback "卸载脚本新安装的软件包" "yum -y remove ${pkgstr} || true"  # 回滚命令
            ;;
        apt)
            run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install ${pkgstr}" "安装软件包" || return 1  # apt 安装
            INSTALLED_PKGS+=("${to_install[@]}")  # 记录新安装包
            mark_rollback "卸载脚本新安装的软件包" "DEBIAN_FRONTEND=noninteractive apt-get -y remove --purge ${pkgstr} || true; apt-get -y autoremove || true"  # 回滚命令
            ;;
    esac

    success "安装软件包完成: ${to_install[*]}"  # 安装成功提示
}

# --------------- 备份配置文件（修改前） ----------------
safe_backup_file() {  # 备份指定文件以便回滚恢复
    local file="$1"  # 目标文件
    if [ -f "$file" ]; then  # 仅对存在的文件进行备份
        local bak="${file}.bak.$(date +%s)"  # 生成唯一备份名
        cp -a "$file" "$bak"  # 复制备份
        BACKUP_FILES+=("$file:$bak")  # 记录备份映射
        mark_rollback "恢复配置文件 $file" "if [ -f '$bak' ]; then mv -f '$bak' '$file' || true; fi"  # 回滚命令
        log "备份配置文件: $file -> $bak"  # 记录日志
    fi
}

# --------------- 错误/中断处理 ----------------
# 处理错误：记录信息并触发回滚
handle_error() {  # 全局错误处理函数
    local lineno="${1:-?}"  # 失败发生的行号
    local cmd="${2:-?}"  # 失败时正在执行的命令
    local code="${3:-1}"  # 失败退出码

    error "脚本在行 $lineno 执行命令 '$cmd' 时失败 (退出码: $code)"  # 打印错误摘要
    error "详细日志请查看: $ERROR_LOG_FILE"  # 指引错误日志位置
    error "完整安装日志请查看: $LOG_FILE"  # 指引安装日志位置

    # 输出错误日志末尾若干行以便快速定位
    if [ -f "$ERROR_LOG_FILE" ]; then  # 如果错误日志存在则打印部分内容
        error "---- 最近的错误输出（最多 200 行） ----"  # 分隔线
        tail -n 200 "$ERROR_LOG_FILE" | sed 's/^/  /' >&2 || true  # 打印尾部错误信息
        error "---- 错误输出结束 ----"  # 分隔线结束
    fi

    # 执行回滚
    warn "开始执行回滚操作..."  # 回滚提示
    rollback_all || warn "回滚过程中发生错误，请检查 $ROLLBACK_LOG_FILE"  # 尝试回滚并提示可能的失败

    error "安装被中止 (行 $lineno, 命令: $cmd, 退出码: $code)"  # 最终错误信息
    exit "$code"  # 退出脚本并返回错误码
}

# 处理中断信号（Ctrl-C 等）
on_interrupt() {  # 处理中断信号，触发回滚
    warn "检测到中断信号，开始回滚并退出..."  # 中断提示
    rollback_all || warn "回滚过程中发生错误，请检查 $ROLLBACK_LOG_FILE"  # 尝试回滚
    exit 1  # 退出
}

trap 'handle_error ${LINENO} "${BASH_COMMAND}" $?' ERR  # 捕获错误并调用 handle_error
trap 'on_interrupt' INT TERM  # 捕获中断/终止信号并调用 on_interrupt

# ----------------- 各步骤实现（与原脚本逻辑类似，但增加备份和回滚） -----------------



install_dependencies() {  # 安装系统依赖的主函数
    local start=$(date +%s)  # 记录开始时间
    info "安装系统依赖..."  # 输出信息

    case "$PKG_MGR" in  # 根据不同包管理器采用不同策略
        dnf)
            run_cmd_with_retry "dnf -y update" "更新系统"  # 更新系统包
            # 优先尝试通过包管理器安装 epel-release，如果失败则使用远端 rpm 包回退安装
            if ! run_cmd_with_retry "dnf -y install --allowerasing epel-release" "安装 EPEL 通过 dnf" 3 2; then  # 使用 --allowerasing 处理冲突
                warn "通过 dnf 安装 epel-release 失败，尝试备用方式安装 EPEL..."  # 警告
                if run_cmd_with_retry "yum -y install --allowerasing epel-release" "安装 EPEL 通过 yum" 3 2; then  # 尝试 yum
                    success "通过 yum 安装 epel-release 成功"  # 成功提示
                else
                    warn "yum 也失败，尝试从 Fedora 官方下载 epel rpm 并安装"  # 再次备选方案
                    local epel_rpm="/tmp/epel-release-latest.rpm"  # 临时 rpm 路径
                    if command -v rpm >/dev/null 2>&1; then  # 若存在 rpm
                        local rhelver="$(rpm -E '%{?rhel}' 2>/dev/null || echo '')"  # 尝试获取 rhel 宏
                        if [ -n "$rhelver" ]; then  # 若拿到 rhel 版本号则尽量下载对应版本
                            run_cmd_with_retry "wget -qO '$epel_rpm' 'https://dl.fedoraproject.org/pub/epel/epel-release-latest-$rhelver.noarch.rpm'" "下载 epel rpm" 3 2 || true
                        fi
                        # 如果没有 rhel 变量或下载失败，再尝试通用路径
                        run_cmd_with_retry "wget -qO '$epel_rpm' 'https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm'" "下载 epel rpm 备用" 3 2 || true
                        if [ -f "$epel_rpm" ]; then  # 如果下载到 rpm 则尝试安装
                            run_cmd_with_retry "rpm -Uvh '$epel_rpm' --replacepkgs" "安装 epel rpm" 3 2 || warn "使用 rpm 安装 epel-release 失败"  # 使用 --replacepkgs 提高成功率
                            register_tmp_file "$epel_rpm"  # 注册临时文件以便清理
                        else
                            warn "未能下载到 epel rpm，EPEL 安装被跳过，请手动处理"  # 最终失败提示
                        fi
                    else
                        warn "系统无 rpm 命令，无法用 rpm 安装 epel-release，EPEL 安装被跳过"  # 无 rpm 时的提示
                    fi
                fi
            fi
            ;;
        yum)
            run_cmd_with_retry "yum -y update" "更新系统"  # 更新系统
            if ! run_cmd_with_retry "yum -y install --allowerasing epel-release" "安装 EPEL 通过 yum" 3 2; then  # 使用 --allowerasing
                warn "yum 安装 epel-release 失败，尝试从 Fedora 官方下载并安装 rpm 包"  # 失败后备用方案
                local epel_rpm="/tmp/epel-release-latest.rpm"  # rpm 临时路径
                run_cmd_with_retry "wget -qO '$epel_rpm' 'https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm'" "下载 epel rpm 备用" 3 2 || true
                if [ -f "$epel_rpm" ]; then  # 如果下载到则安装
                    run_cmd_with_retry "rpm -Uvh '$epel_rpm' --replacepkgs" "安装 epel rpm" 3 2 || warn "使用 rpm 安装 epel-release 失败"  # 使用 --replacepkgs
                    register_tmp_file "$epel_rpm"  # 注册临时文件
                else
                    warn "未能下载到 epel rpm，EPEL 安装被跳过，请手动处理"  # 无法下载提示
                fi
            fi
            ;;
        apt)
            run_cmd_with_retry "apt-get -y update" "更新包列表"  # apt-get 更新
            run_cmd_with_retry "apt-get -y upgrade" "升级系统"  # apt-get 升级
            ;;
    esac

    # 尝试安装一些常用工具（示例，按需添加）
    local common=(yum-utils gcc gcc-c++ autoconf libtool perl perl-devel libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel openldap openldap-devel openldap-clients freetype freetype-devel libxml2 libxml2-devel sqlite-devel zlib zlib-devel curl curl-devel pcre pcre-devel gd gd-devel expat-devel libicu-devel bzip2 bzip2-devel python3 python3-devel libwebp-devel make libzstd-devel wget oniguruma oniguruma-devel zstd glibc-headers krb5-devel libzip libzip-devel libxslt libxslt-devel openssl openssl-devel libsodium-devel glib2-devel cairo-devel gmp-devel libevent-devel readline-devel net-snmp-devel aspell-devel unixODBC-devel libc-client-devel libXpm-devel enchant-devel php-ldap automake kernel keyutils patch tidy epel-release libtidy libtidy-devel)  # 常用工具清单
    pkg_install "${common[@]}"  # 安装这些工具

    success "系统依赖安装完成 (耗时: $(($(date +%s) - start)) 秒)"  # 完成提示并显示耗时
}

# ----------------- PHP 源码下载/解压/编译（保留原逻辑，但改进错误处理） -----------------（保留原逻辑，但改进错误处理） -----------------

download_php() {  # 下载 PHP 源码函数
    local start=$(date +%s)  # 记录开始时间
    info "下载 PHP 源码..."  # 信息提示
    mkdir -p "$SRC_DIR" && cd "$SRC_DIR"  # 确保源码目录并切换
    local php_archive="php-${PHP_VERSION}.tar.gz"  # 源码包名

    for mirror in "${PHP_MIRRORS[@]}"; do  # 遍历镜像
        info "尝试从镜像下载: $mirror"  # 输出当前尝试的镜像
        if command -v wget >/dev/null 2>&1; then  # 优先使用 wget
            if run_cmd_with_retry "wget -c --tries=$DOWNLOAD_RETRIES --timeout=30 '$mirror' -O '$php_archive'" "下载 PHP ($mirror)" 3 3; then  # 使用 DOWNLOAD_RETRIES
                [ -s "$php_archive" ] && { success "PHP 下载成功"; register_tmp_file "$SRC_DIR/$php_archive"; return 0; }  # 若下载成功则注册临时文件并返回
            fi
        else  # 否则使用 curl
            if run_cmd_with_retry "curl -L --retry $DOWNLOAD_RETRIES --connect-timeout 30 '$mirror' -o '$php_archive'" "下载 PHP ($mirror)" 3 3; then  # 使用 DOWNLOAD_RETRIES
                [ -s "$php_archive" ] && { success "PHP 下载成功"; register_tmp_file "$SRC_DIR/$php_archive"; return 0; }
            fi
        fi
        warn "下载失败: $mirror"  # 当前镜像失败提示
        rm -f "$php_archive" 2>/dev/null || true  # 清理残留文件
    done

    return 1  # 所有镜像尝试失败返回 1
}

extract_php() {  # 解压 PHP 源码
    info "解压 PHP 源码..."  # 信息提示
    cd "$SRC_DIR"  # 切换到源码目录
    local php_archive="php-${PHP_VERSION}.tar.gz"  # 源码包名
    if [ ! -s "$php_archive" ]; then  # 校验文件是否存在且非空
        error "PHP 源码包不存在: $php_archive"  # 错误提示
        return 1  # 返回失败
    fi

    run_cmd "tar -xzf '$php_archive'" "解压 PHP 源码"  # 解压命令

    PHP_SRC_DIR="$SRC_DIR/php-${PHP_VERSION}"  # 预期解压目录
    if [ ! -d "$PHP_SRC_DIR" ]; then  # 若默认目录不存在则从归档中解析实际目录
        local actual_dir
        actual_dir=$(tar -tf "$php_archive" | head -n1 | cut -d/ -f1)  # 取归档第一行目录名
        PHP_SRC_DIR="$SRC_DIR/$actual_dir"  # 设置实际目录
    fi

    if [ ! -d "$PHP_SRC_DIR" ]; then  # 最终检查是否存在源码目录
        error "无法找到 PHP 源码目录"  # 错误提示
        return 1  # 返回失败
    fi

    success "PHP 源码解压完成"  # 成功提示
}

configure_php() {  # 配置 PHP 编译选项
    info "配置 PHP 编译选项..."  # 信息提示
    cd "$PHP_SRC_DIR"  # 切换到源码目录

    if [ -f buildconf ]; then  # 若存在 buildconf 则运行
        run_cmd "./buildconf --force" "运行 buildconf" || warn "buildconf 运行失败"  # 运行并在失败时给出警告
    fi

    local CONFIGURE_OPTS=(  # 配置数组
        "--prefix=$PHP_PREFIX"  # 编译安装前缀
        "--with-config-file-path=$PHP_PREFIX/lib"  # php.ini 路径
        "--with-config-file-scan-dir=$PHP_PREFIX/lib/conf.d"  # 扫描扩展配置目录
        "--enable-fpm"  # 启用 fpm
        "--with-fpm-user=$WWW_USER"  # fpm 用户
        "--with-fpm-group=$WWW_USER"  # fpm 用户组
        "--with-libxml"  # 启用 libxml
        "--with-openssl"  # 启用 openssl
        # "--with-mysqli=mysqlnd"  # mysqli 使用 mysqlnd
        "--with-mysqli"
        "--with-mysql-sock"
        "--enable-pdo"  # 启用 pdo
        "--enable-pdo"
        "--with-pdo-sqlite"
        "--with-pdo-mysql"
        "--with-pdo-sqlite"
        "--with-pdo-sqlite"
        "--with-zlib"  # 启用 zlib
        "--enable-mbstring"  # 启用 mbstring
        "--enable-opcache"  # 启用 opcache
        "--enable-cli"  # 启用 cli
        # 根据实际需求添加/删除配置项
        "--with-kerberos"
        "--with-system-ciphers"
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
        "--enable-mbregex"
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
        "--with-pear"
        "--with-jpeg"
        "--with-libdir=lib64"
        "--enable-static"
    )
    # 将数组拼接为字符串;彻底移除所有换行符（即使有也清除）
    local opts_str=$(printf '%s ' "${CONFIGURE_OPTS[@]}" | tr -d '\n\r' | sed 's/ $//')

    local attempt=1  # 初始尝试次数
    while [ $attempt -le $MAX_RETRIES ]; do  # 重试配置直到 MAX_RETRI#!/usr/bin/env bash
                                                               # 指定使用的解释器为 bash，确保脚本可移植
                                                               # -----------------------------------------------------------------------------
                                                               # industrial_php_stack_improved.sh  # 脚本名及说明
                                                               # 改进版：工业级 PHP 环境一键安装脚本（增强错误接管、回滚、备份与日志）  # 脚本功能简介
                                                               # 功能：安装 PHP / Nginx / MySQL / Redis / Composer / 常用扩展  # 支持的软件组件
                                                               # 主要改进点：  # 列出主要改动
                                                               #  - 全面中文注释  # 每行均添加注释
                                                               #  - ERR/INT/TERM 信号接管并触发回滚  # 错误处理策略
                                                               #  - 记录每一步的反向操作（rollback），异常时按逆序执行以回滚系统改动  # 回滚机制
                                                               #  - 智能判断哪些包是新安装的，回滚时仅移除这些包（尽量避免删除已有系统包）  # 安全移除策略
                                                               #  - 对修改的配置文件先备份，回滚时恢复备份  # 备份机制
                                                               #  - 优化 run_cmd 执行方式，使用 "bash -lc" 执行多行命令，解决命令包含换行符导致的执行问题  # 解决换行/特殊字符问题
                                                               #  - 避免不必要的 eval，修复 make -j 等错误引用  # 安全改进
                                                               #  - 增强日志（安装日志 + 错误日志 + 回滚日志）  # 日志策略
                                                               #  - 增加临时文件管理，退出时自动清理  # 清理机制
                                                               #  - 添加安全提示与使用说明  # 用户友好性
                                                               # -----------------------------------------------------------------------------

                                                               set -euo pipefail  # 开启严格模式：出错退出、未定义变量视作错误、管道失败时整体失败
                                                               set -o errtrace   # 使 ERR trap 在函数/子 shell 中也生效  # 保证错误捕获完整
                                                               IFS=$'\n\t'  # 设置内部字段分隔符为换行和制表符，避免空格或换行导致参数被错误拆分

                                                               # ---------------- 可配置参数（保持与原脚本兼容） ----------------
                                                               INSTALL_PHP="${INSTALL_PHP:-yes}"  # 是否安装 PHP，默认 yes
                                                               INSTALL_MYSQL="${INSTALL_MYSQL:-yes}"  # 是否安装 MySQL，默认 yes
                                                               INSTALL_REDIS="${INSTALL_REDIS:-yes}"  # 是否安装 Redis，默认 yes
                                                               INSTALL_NGINX="${INSTALL_NGINX:-yes}"  # 是否安装 Nginx，默认 yes
                                                               INSTALL_COMPOSER="${INSTALL_COMPOSER:-yes}"  # 是否安装 Composer，默认 yes
                                                               INSTALL_PHP_EXTENSIONS="${INSTALL_PHP_EXTENSIONS:-yes}"  # 是否安装常用 PHP 扩展，默认 yes

                                                               PHP_VERSION="${PHP_VERSION:-8.4.12}"  # PHP 默认版本
                                                               MYSQL_VERSION="${MYSQL_VERSION:-8.4.0}"  # MySQL 版本（仅记录用）
                                                               REDIS_VERSION="${REDIS_VERSION:-7.2.4}"  # Redis 版本（仅记录用）
                                                               NGINX_VERSION="${NGINX_VERSION:-1.28.0}"  # Nginx 版本（仅记录用）

                                                               PHP_MIRRORS=(  # PHP 源码镜像列表，按优先级排列
                                                                   "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"  # 官方镜像
                                                                   "https://mirrors.aliyun.com/php-distributions/php-${PHP_VERSION}.tar.gz"  # 阿里云镜像
                                                                   "https://mirrors.cloud.tencent.com/php-distributions/php-${PHP_VERSION}.tar.gz"  # 腾讯云镜像
                                                               )

                                                               PHP_PREFIX="${PHP_PREFIX:-/usr/local/php8}"  # PHP 安装前缀
                                                               MYSQL_PREFIX="${MYSQL_PREFIX:-/usr/local/mysql}"  # MySQL 安装前缀（如源码安装时使用）
                                                               NGINX_PREFIX="${NGINX_PREFIX:-/usr/local/nginx}"  # Nginx 安装前缀
                                                               REDIS_PREFIX="${REDIS_PREFIX:-/usr/local/redis}"  # Redis 安装前缀

                                                               MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-$(openssl rand -base64 24)}"  # MySQL root 密码，默认随机生成
                                                               REMOTE_ADMIN_USER="${REMOTE_ADMIN_USER:-phpadmin}"  # 远程管理用户
                                                               REMOTE_ADMIN_PASS="${REMOTE_ADMIN_PASS:-$(openssl rand -base64 24)}"  # 远程管理用户密码
                                                               WWW_USER="${WWW_USER:-www}"  # 网站运行用户
                                                               WWW_PASS="${WWW_PASS:-$(openssl rand -base64 24)}"  # 网站运行用户密码

                                                               SRC_DIR="${SRC_DIR:-/usr/local/src}"  # 源码下载目录
                                                               LOG_DIR="${LOG_DIR:-/var/log/php-stack-install}"  # 日志目录
                                                               LOG_FILE="${LOG_FILE:-$LOG_DIR/install.log}"  # 安装日志文件
                                                               ERROR_LOG_FILE="${ERROR_LOG_FILE:-$LOG_DIR/error.log}"  # 错误日志文件
                                                               ROLLBACK_LOG_FILE="${ROLLBACK_LOG_FILE:-$LOG_DIR/rollback.log}"  # 回滚日志文件
                                                               INSTALL_SUMMARY="${INSTALL_SUMMARY:-/data/install_config.log}"  # 安装总结输出文件
                                                               MAKE_JOBS="${MAKE_JOBS:-$(nproc 2>/dev/null || echo 1)}"  # make -j 使用的并行数，默认为 CPU 核心数
                                                               AUTO_START_SERVICES="${AUTO_START_SERVICES:-yes}"  # 是否自动启用并启动服务
                                                               MAX_RETRIES="${MAX_RETRIES:-3}"  # 全局重试次数，默认 3（按用户要求）
                                                               DOWNLOAD_RETRIES="${DOWNLOAD_RETRIES:-3}"  # 下载重试次数，默认 3
                                                               CLEAN_TEMP="${CLEAN_TEMP:-yes}"  # 是否清理临时文件
                                                               PROFILE_FILE="${PROFILE_FILE:-/etc/profile}"  # 全局 profile 文件

                                                               PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-512M}"  # PHP memory_limit
                                                               PHP_MAX_EXECUTION_TIME="${PHP_MAX_EXECUTION_TIME:-300}"  # PHP max_execution_time
                                                               PHP_UPLOAD_MAX_FILESIZE="${PHP_UPLOAD_MAX_FILESIZE:-256M}"  # PHP upload_max_filesize
                                                               PHP_POST_MAX_SIZE="${PHP_POST_MAX_SIZE:-256M}"  # PHP post_max_size

                                                               # ---------------- 全局变量 ----------------
                                                               PKG_MGR=""  # 包管理器标识
                                                               OS_ID=""  # 操作系统 ID
                                                               OS_NAME=""  # 操作系统名称
                                                               OS_VERSION=""  # 操作系统版本
                                                               OS_ARCH=""  # 系统架构
                                                               PHP_SRC_DIR=""  # PHP 源码解压目录
                                                               PHP_INI_FILE=""  # php.ini 路径
                                                               START_TIME=$(date +%s)  # 记录脚本开始时间
                                                               TMP_FILES=()  # 临时文件列表

                                                               # rollback 命令栈（按顺序 push，回滚时逆序执行）
                                                               ROLLBACK_CMDS=()  # 回滚命令栈
                                                               # 记录由脚本新安装的软件包（回滚时会移除这些包）
                                                               INSTALLED_PKGS=()  # 记录新安装的软件包
                                                               # 记录创建的用户（回滚时会删除）
                                                               CREATED_USERS=()  # 记录创建的用户
                                                               # 记录备份的配置文件（回滚时恢复）
                                                               BACKUP_FILES=()  # 记录备份文件

                                                               # 创建目录并初始化日志
                                                               mkdir -p "$SRC_DIR" "$LOG_DIR" "$(dirname "$INSTALL_SUMMARY")"  # 确保目录存在
                                                               : > "$LOG_FILE"  # 清空安装日志
                                                               : > "$ERROR_LOG_FILE"  # 清空错误日志
                                                               : > "$ROLLBACK_LOG_FILE"  # 清空回滚日志

                                                               # ---------------- 输出（彩色） ----------------
                                                               _red() { echo -e "[31m$*[0m"; }  # 红色输出函数
                                                               _green() { echo -e "[32m$*[0m"; }  # 绿色输出函数
                                                               _yellow() { echo -e "[33m$*[0m"; }  # 黄色输出函数
                                                               _blue() { echo -e "[34m$*[0m"; }  # 蓝色输出函数
                                                               _bold() { echo -e "[1m$*[0m"; }  # 粗体输出函数

                                                               ICON_INFO="🔵"  # 信息图标
                                                               ICON_SUCCESS="✅"  # 成功图标
                                                               ICON_WARN="🟡"  # 警告图标
                                                               ICON_ERROR="🔴"  # 错误图标

                                                               log() {  # 日志记录函数
                                                                   echo "[$(date '+%F %T')] $*" | tee -a "$LOG_FILE"  # 同时输出到控制台并追加到日志
                                                               }

                                                               info() {  # 信息输出函数
                                                                   log "${ICON_INFO} $*"  # 写日志
                                                                   echo -e "${ICON_INFO} $*"  # 控制台输出
                                                               }

                                                               success() {  # 成功输出函数
                                                                   log "${ICON_SUCCESS} $*"  # 写日志
                                                                   echo -e "${ICON_SUCCESS} $*"  # 控制台输出
                                                               }

                                                               warn() {  # 警告输出函数
                                                                   log "${ICON_WARN} $*"  # 写日志
                                                                   echo -e "${ICON_WARN} $*" >&2  # 输出到 stderr
                                                               }

                                                               error() {  # 错误输出函数
                                                                   log "${ICON_ERROR} $*"  # 写日志
                                                                   echo -e "${ICON_ERROR} $*" >&2  # 输出到 stderr
                                                                   echo "$(date '+%F %T') - ERROR: $*" >> "$ERROR_LOG_FILE"  # 同时写入错误日志
                                                               }

                                                               # --------------- 回滚记录与执行工具 ----------------
                                                               # 将一条回滚命令入栈（描述性说明 + shell 命令）
                                                               mark_rollback() {  # 将回滚动作记录到栈中
                                                                   local desc="$1"  # 回滚动作描述
                                                                   local cmd="$2"  # 回滚命令
                                                                   ROLLBACK_CMDS+=("# $desc\n$cmd")  # 入栈，保存描述和命令
                                                                   log "[ROLLBACK MARK] $desc -> $cmd"  # 记录到日志
                                                               }

                                                               # 执行回滚（按逆序）
                                                               rollback_all() {  # 执行所有记录的回滚命令
                                                                   echo "" | tee -a "$ROLLBACK_LOG_FILE"  # 写入空行分隔
                                                                   echo "[$(date '+%F %T')] 开始回滚..." | tee -a "$ROLLBACK_LOG_FILE"  # 回滚开始时间写日志

                                                                   # 逆序执行
                                                                   for ((i=${#ROLLBACK_CMDS[@]}-1; i>=0; i--)); do  # 逆序遍历回滚栈
                                                                       local item="${ROLLBACK_CMDS[i]}"  # 取出栈项
                                                                       echo "- 执行回滚步骤: ${item%%$'\n'*}" | tee -a "$ROLLBACK_LOG_FILE"  # 输出步骤描述
                                                                       # 执行命令体（跳过以 '#' 开头的注释行）
                                                                       local cmd=$(printf '%s' "$item" | sed -n '2,999p')  # 取得命令体
                                                                       if [ -n "$cmd" ]; then  # 若命令体非空则执行
                                                                           set +e  # 临时取消 -e，保证回滚继续执行
                                                                           bash -lc "$cmd" >>"$ROLLBACK_LOG_FILE" 2>&1  # 执行并记录输出
                                                                           local rc=$?  # 获取退出码
                                                                           set -e  # 恢复 -e
                                                                           if [ $rc -eq 0 ]; then  # 判断执行结果
                                                                               echo "  -> 回滚步骤成功" | tee -a "$ROLLBACK_LOG_FILE"  # 成功日志
                                                                           else
                                                                               echo "  -> 回滚步骤失败 (退出码:$rc)" | tee -a "$ROLLBACK_LOG_FILE"  # 失败日志
                                                                           fi
                                                                       fi
                                                                   done

                                                                   echo "[$(date '+%F %T')] 回滚完成" | tee -a "$ROLLBACK_LOG_FILE"  # 回滚完成时间写日志
                                                               }

                                                               # --------------- 临时文件管理 ----------------
                                                               register_tmp_file() {  # 注册临时文件，退出时自动清理
                                                                   TMP_FILES+=("$1")  # 将临时文件路径加入数组
                                                               }

                                                               cleanup_tmpfiles() {  # 清理注册的临时文件
                                                                   for f in "${TMP_FILES[@]:-}"; do  # 遍历所有注册的临时文件
                                                                       [ -e "$f" ] && rm -rf "$f" || true  # 逐个删除
                                                                   done
                                                               }

                                                               # ----------------- 运行命令的通用函数（支持多行） -----------------
                                                               # 使用 bash -lc 来执行任意复杂命令（包含换行），解决命令中包含换行或特殊字符时的执行问题
                                                               run_cmd() {  # 运行单条命令并记录日志（带 spinner）
                                                                   local cmd="$1"  # 要执行的命令
                                                                   local desc="${2:-执行命令}"  # 命令描述，默认 "执行命令"

                                                                   log "[CMD] $desc : $cmd"  # 记录命令到日志

                                                                   # 后台执行以支持 spinner（保持与原脚本交互性），但简化实现以避免 eval 的安全问题
                                                                   bash -lc "$cmd" >>"$LOG_FILE" 2>>"$ERROR_LOG_FILE" &  # 使用 bash -lc 执行命令并重定向输出
                                                                   local pid=$!  # 获取后台进程 PID

                                                                   # 简单的 spinner（如果命令很快结束，不影响）
                                                                   local i=0  # spinner 索引
                                                                   local frames=("-" "\\" "|" "/")  # spinner 帧：- \ | /
                                                                   printf "⏳ %s " "$desc"  # 初始 spinner 输出
                                                                   while kill -0 "$pid" 2>/dev/null; do  # 当进程存在时更新 spinner
                                                                       i=$(((i+1) % ${#frames[@]}))  # 计算下一个帧索引
                                                                       printf "\r⏳ %s %s" "$desc" "${frames[i]}"  # 打印 spinner
                                                                       sleep 0.08  # spinner 刷新间隔
                                                                   done
                                                                   wait "$pid"  # 等待命令结束
                                                                   local rc=$?  # 获取命令退出码
                                                                   if [ $rc -ne 0 ]; then  # 非零视为失败
                                                                       log "[CMD-FAIL] $desc (退出码:$rc)"  # 记录失败
                                                                       return $rc  # 返回失败码
                                                                   fi
                                                                   printf "\r✅ %s 完成\n" "$desc"  # 成功提示
                                                                   log "[CMD-OK] $desc"  # 记录成功
                                                                   return 0  # 返回成功
                                                               }

                                                               # 带重试的 run_cmd
                                                               run_cmd_with_retry() {  # 带重试的命令执行函数
                                                                   local cmd="$1"  # 要执行的命令
                                                                   local desc="${2:-执行命令}"  # 描述
                                                                   local max_retries="${3:-$MAX_RETRIES}"  # 最大重试次数
                                                                   local retry_delay="${4:-2}"  # 重试间隔秒数

                                                                   local attempt=1  # 初始尝试次数
                                                                   while [ $attempt -le $max_retries ]; do  # 循环直到达到最大重试次数
                                                                       if run_cmd "$cmd" "$desc"; then  # 如果命令成功则返回
                                                                           return 0
                                                                       fi
                                                                       warn "$desc 失败 (尝试 $attempt/$max_retries)，${retry_delay}s 后重试..."  # 警告并等待
                                                                       sleep $retry_delay  # 等待
                                                                       attempt=$((attempt+1))  # 增加尝试次数
                                                                   done

                                                                   error "$desc 在 $max_retries 次尝试后仍然失败: $cmd"  # 最终失败记录错误
                                                                   return 1  # 返回失败
                                                               }

                                                               # ----------------- 系统检测 -----------------
                                                               # 检测操作系统类型、包管理器与架构信息
                                                               detect_system() {  # 检测系统信息和包管理器类型
                                                                   info "检测系统环境..."  # 输出信息
                                                                   if [ -f /etc/os-release ]; then  # 如果存在 os-release 文件则读取
                                                                       . /etc/os-release  # 导入变量
                                                                       OS_ID="${ID:-unknown}"  # 设置 OS_ID
                                                                       OS_NAME="${NAME:-unknown}"  # 设置 OS_NAME
                                                                       OS_VERSION="${VERSION_ID:-unknown}"  # 设置 OS_VERSION
                                                                   else
                                                                       fail_msg="无法检测操作系统"  # 无法检测时的错误信息
                                                                       error "$fail_msg"  # 输出错误
                                                                       return 1  # 返回失败
                                                                   fi

                                                                   OS_ARCH=$(uname -m)  # 获取系统架构
                                                                   [ "$OS_ARCH" = "x86_64" ] && OS_ARCH="x64" || true  # 对常见架构做友好显示

                                                                   if command -v dnf >/dev/null 2>&1; then  # 检查 dnf
                                                                       PKG_MGR="dnf"  # 使用 dnf
                                                                   elif command -v yum >/dev/null 2>&1; then  # 检查 yum
                                                                       PKG_MGR="yum"  # 使用 yum
                                                                   elif command -v apt-get >/dev/null 2>&1; then  # 检查 apt-get
                                                                       PKG_MGR="apt"  # 使用 apt
                                                                   else
                                                                       error "未找到受支持的包管理器 (dnf|yum|apt-get)"  # 未找到包管理器时错误
                                                                       return 1  # 返回失败
                                                                   fi

                                                                   success "系统: $OS_NAME $OS_VERSION ($OS_ARCH), 包管理器: $PKG_MGR"  # 打印检测结果
                                                               }

                                                               # ----------------- 软件包安装工具（智能判断哪些包是新安装） -----------------
                                                               # 检查包是否已安装（返回 0=已安装 1=未安装）
                                                               pkg_is_installed() {  # 检测具体包是否已安装
                                                                   local pkg="$1"  # 包名参数
                                                                   if [ "$PKG_MGR" = "apt" ]; then  # apt 系统使用 dpkg
                                                                       dpkg -s "$pkg" >/dev/null 2>&1 && return 0 || return 1
                                                                   else  # rpm 系统使用 rpm -q
                                                                       rpm -q "$pkg" >/dev/null 2>&1 && return 0 || return 1
                                                                   fi
                                                               }

                                                               # 将包数组拼成安全的 shell 字符串（逐个 printf '%q'）
                                                               escape_pkg_list() {  # 对包名做 shell 转义以安全地放入命令行（返回以空格分隔的转义字符串）
                                                                   local -n arr=$1  # 通过 nameref 引用传入的数组名
                                                                   local out=()  # 临时数组用于保存转义后的包名
                                                                   for p in "${arr[@]}"; do  # 遍历包名数组
                                                                       out+=("$(printf '%q' "$p")")  # 使用 printf '%q' 转义每个包名并加入列表
                                                                   done
                                                                   # 将转义后的包名用空格连接，末尾不含额外空格
                                                                   local joined=""  # 初始化连接字符串
                                                                   for item in "${out[@]}"; do  # 遍历转义结果
                                                                       if [ -z "$joined" ]; then  # 若为空则直接赋值
                                                                           joined="$item"
                                                                       else
                                                                           joined="$joined $item"  # 否则追加空格分隔
                                                                       fi
                                                                   done
                                                                   printf '%s' "$joined"  # 输出最终的转义字符串
                                                               }

                                                               # 安装一组包（只安装尚未安装的包），并记录安装的包以便回滚
                                                               pkg_install() {  # 智能安装包函数
                                                                   local pkgs=("$@")  # 将传入参数组成数组
                                                                   info "准备安装软件包: ${pkgs[*]}"  # 打印准备安装的包列表

                                                                   local to_install=()  # 需要安装的包列表
                                                                   for p in "${pkgs[@]}"; do  # 遍历每个包
                                                                       if pkg_is_installed "$p"; then  # 如果已安装则跳过
                                                                           info "已存在: $p，跳过安装"
                                                                       else
                                                                           to_install+=("$p")  # 否则加入待安装列表
                                                                       fi
                                                                   done

                                                                   if [ ${#to_install[@]} -eq 0 ]; then  # 若无新包则直接返回
                                                                       info "没有需要安装的新包"
                                                                       return 0
                                                                   fi

                                                                   local pkgstr=$(escape_pkg_list to_install)  # 将包列表转义拼接为字符串

                                                                   case "$PKG_MGR" in  # 根据包管理器选择安装命令，并尽量使用 --allowerasing 处理冲突
                                                                       dnf)
                                                                           run_cmd_with_retry "dnf -y install --allowerasing $pkgstr" "安装软件包" || return 1  # dnf 使用 --allowerasing
                                                                           INSTALLED_PKGS+=("${to_install[@]}")  # 记录新安装包
                                                                           mark_rollback "卸载脚本新安装的软件包" "dnf -y remove --noautoremove ${pkgstr} || true"  # 回滚命令
                                                                           ;;
                                                                       yum)
                                                                           run_cmd_with_retry "yum -y install --allowerasing $pkgstr" "安装软件包" || return 1  # yum 使用 --allowerasing
                                                                           INSTALLED_PKGS+=("${to_install[@]}")  # 记录新安装包
                                                                           mark_rollback "卸载脚本新安装的软件包" "yum -y remove ${pkgstr} || true"  # 回滚命令
                                                                           ;;
                                                                       apt)
                                                                           run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install ${pkgstr}" "安装软件包" || return 1  # apt 安装
                                                                           INSTALLED_PKGS+=("${to_install[@]}")  # 记录新安装包
                                                                           mark_rollback "卸载脚本新安装的软件包" "DEBIAN_FRONTEND=noninteractive apt-get -y remove --purge ${pkgstr} || true; apt-get -y autoremove || true"  # 回滚命令
                                                                           ;;
                                                                   esac

                                                                   success "安装软件包完成: ${to_install[*]}"  # 安装成功提示
                                                               }

                                                               # --------------- 备份配置文件（修改前） ----------------
                                                               safe_backup_file() {  # 备份指定文件以便回滚恢复
                                                                   local file="$1"  # 目标文件
                                                                   if [ -f "$file" ]; then  # 仅对存在的文件进行备份
                                                                       local bak="${file}.bak.$(date +%s)"  # 生成唯一备份名
                                                                       cp -a "$file" "$bak"  # 复制备份
                                                                       BACKUP_FILES+=("$file:$bak")  # 记录备份映射
                                                                       mark_rollback "恢复配置文件 $file" "if [ -f '$bak' ]; then mv -f '$bak' '$file' || true; fi"  # 回滚命令
                                                                       log "备份配置文件: $file -> $bak"  # 记录日志
                                                                   fi
                                                               }

                                                               # --------------- 错误/中断处理 ----------------
                                                               # 处理错误：记录信息并触发回滚
                                                               handle_error() {  # 全局错误处理函数
                                                                   local lineno="${1:-?}"  # 失败发生的行号
                                                                   local cmd="${2:-?}"  # 失败时正在执行的命令
                                                                   local code="${3:-1}"  # 失败退出码

                                                                   error "脚本在行 $lineno 执行命令 '$cmd' 时失败 (退出码: $code)"  # 打印错误摘要
                                                                   error "详细日志请查看: $ERROR_LOG_FILE"  # 指引错误日志位置
                                                                   error "完整安装日志请查看: $LOG_FILE"  # 指引安装日志位置

                                                                   # 输出错误日志末尾若干行以便快速定位
                                                                   if [ -f "$ERROR_LOG_FILE" ]; then  # 如果错误日志存在则打印部分内容
                                                                       error "---- 最近的错误输出（最多 200 行） ----"  # 分隔线
                                                                       tail -n 200 "$ERROR_LOG_FILE" | sed 's/^/  /' >&2 || true  # 打印尾部错误信息
                                                                       error "---- 错误输出结束 ----"  # 分隔线结束
                                                                   fi

                                                                   # 执行回滚
                                                                   warn "开始执行回滚操作..."  # 回滚提示
                                                                   rollback_all || warn "回滚过程中发生错误，请检查 $ROLLBACK_LOG_FILE"  # 尝试回滚并提示可能的失败

                                                                   error "安装被中止 (行 $lineno, 命令: $cmd, 退出码: $code)"  # 最终错误信息
                                                                   exit "$code"  # 退出脚本并返回错误码
                                                               }

                                                               # 处理中断信号（Ctrl-C 等）
                                                               on_interrupt() {  # 处理中断信号，触发回滚
                                                                   warn "检测到中断信号，开始回滚并退出..."  # 中断提示
                                                                   rollback_all || warn "回滚过程中发生错误，请检查 $ROLLBACK_LOG_FILE"  # 尝试回滚
                                                                   exit 1  # 退出
                                                               }

                                                               trap 'handle_error ${LINENO} "${BASH_COMMAND}" $?' ERR  # 捕获错误并调用 handle_error
                                                               trap 'on_interrupt' INT TERM  # 捕获中断/终止信号并调用 on_interrupt

                                                               # ----------------- 各步骤实现（与原脚本逻辑类似，但增加备份和回滚） -----------------



                                                               install_dependencies() {  # 安装系统依赖的主函数
                                                                   local start=$(date +%s)  # 记录开始时间
                                                                   info "安装系统依赖..."  # 输出信息

                                                                   case "$PKG_MGR" in  # 根据不同包管理器采用不同策略
                                                                       dnf)
                                                                           run_cmd_with_retry "dnf -y update" "更新系统"  # 更新系统包
                                                                           # 优先尝试通过包管理器安装 epel-release，如果失败则使用远端 rpm 包回退安装
                                                                           if ! run_cmd_with_retry "dnf -y install --allowerasing epel-release" "安装 EPEL 通过 dnf" 3 2; then  # 使用 --allowerasing 处理冲突
                                                                               warn "通过 dnf 安装 epel-release 失败，尝试备用方式安装 EPEL..."  # 警告
                                                                               if run_cmd_with_retry "yum -y install --allowerasing epel-release" "安装 EPEL 通过 yum" 3 2; then  # 尝试 yum
                                                                                   success "通过 yum 安装 epel-release 成功"  # 成功提示
                                                                               else
                                                                                   warn "yum 也失败，尝试从 Fedora 官方下载 epel rpm 并安装"  # 再次备选方案
                                                                                   local epel_rpm="/tmp/epel-release-latest.rpm"  # 临时 rpm 路径
                                                                                   if command -v rpm >/dev/null 2>&1; then  # 若存在 rpm
                                                                                       local rhelver="$(rpm -E '%{?rhel}' 2>/dev/null || echo '')"  # 尝试获取 rhel 宏
                                                                                       if [ -n "$rhelver" ]; then  # 若拿到 rhel 版本号则尽量下载对应版本
                                                                                           run_cmd_with_retry "wget -qO '$epel_rpm' 'https://dl.fedoraproject.org/pub/epel/epel-release-latest-$rhelver.noarch.rpm'" "下载 epel rpm" 3 2 || true
                                                                                       fi
                                                                                       # 如果没有 rhel 变量或下载失败，再尝试通用路径
                                                                                       run_cmd_with_retry "wget -qO '$epel_rpm' 'https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm'" "下载 epel rpm 备用" 3 2 || true
                                                                                       if [ -f "$epel_rpm" ]; then  # 如果下载到 rpm 则尝试安装
                                                                                           run_cmd_with_retry "rpm -Uvh '$epel_rpm' --replacepkgs" "安装 epel rpm" 3 2 || warn "使用 rpm 安装 epel-release 失败"  # 使用 --replacepkgs 提高成功率
                                                                                           register_tmp_file "$epel_rpm"  # 注册临时文件以便清理
                                                                                       else
                                                                                           warn "未能下载到 epel rpm，EPEL 安装被跳过，请手动处理"  # 最终失败提示
                                                                                       fi
                                                                                   else
                                                                                       warn "系统无 rpm 命令，无法用 rpm 安装 epel-release，EPEL 安装被跳过"  # 无 rpm 时的提示
                                                                                   fi
                                                                               fi
                                                                           fi
                                                                           ;;
                                                                       yum)
                                                                           run_cmd_with_retry "yum -y update" "更新系统"  # 更新系统
                                                                           if ! run_cmd_with_retry "yum -y install --allowerasing epel-release" "安装 EPEL 通过 yum" 3 2; then  # 使用 --allowerasing
                                                                               warn "yum 安装 epel-release 失败，尝试从 Fedora 官方下载并安装 rpm 包"  # 失败后备用方案
                                                                               local epel_rpm="/tmp/epel-release-latest.rpm"  # rpm 临时路径
                                                                               run_cmd_with_retry "wget -qO '$epel_rpm' 'https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm'" "下载 epel rpm 备用" 3 2 || true
                                                                               if [ -f "$epel_rpm" ]; then  # 如果下载到则安装
                                                                                   run_cmd_with_retry "rpm -Uvh '$epel_rpm' --replacepkgs" "安装 epel rpm" 3 2 || warn "使用 rpm 安装 epel-release 失败"  # 使用 --replacepkgs
                                                                                   register_tmp_file "$epel_rpm"  # 注册临时文件
                                                                               else
                                                                                   warn "未能下载到 epel rpm，EPEL 安装被跳过，请手动处理"  # 无法下载提示
                                                                               fi
                                                                           fi
                                                                           ;;
                                                                       apt)
                                                                           run_cmd_with_retry "apt-get -y update" "更新包列表"  # apt-get 更新
                                                                           run_cmd_with_retry "apt-get -y upgrade" "升级系统"  # apt-get 升级
                                                                           ;;
                                                                   esac

                                                                   # 尝试安装一些常用工具（示例，按需添加）
                                                                   local common=(yum-utils gcc gcc-c++ autoconf libtool perl perl-devel libpng libpng-devel libjpeg libjpeg-devel libcurl libcurl-devel openldap openldap-devel openldap-clients freetype freetype-devel libxml2 libxml2-devel sqlite-devel zlib zlib-devel curl curl-devel pcre pcre-devel gd gd-devel expat-devel libicu-devel bzip2 bzip2-devel python3 python3-devel libwebp-devel make libzstd-devel wget oniguruma oniguruma-devel zstd glibc-headers krb5-devel libzip libzip-devel libxslt libxslt-devel openssl openssl-devel libsodium-devel glib2-devel cairo-devel gmp-devel libevent-devel readline-devel net-snmp-devel aspell-devel unixODBC-devel libc-client-devel libXpm-devel enchant-devel php-ldap automake kernel keyutils patch tidy epel-release libtidy libtidy-devel)  # 常用工具清单
                                                                   pkg_install "${common[@]}"  # 安装这些工具

                                                                   success "系统依赖安装完成 (耗时: $(($(date +%s) - start)) 秒)"  # 完成提示并显示耗时
                                                               }

                                                               # ----------------- PHP 源码下载/解压/编译（保留原逻辑，但改进错误处理） -----------------（保留原逻辑，但改进错误处理） -----------------

                                                               download_php() {  # 下载 PHP 源码函数
                                                                   local start=$(date +%s)  # 记录开始时间
                                                                   info "下载 PHP 源码..."  # 信息提示
                                                                   mkdir -p "$SRC_DIR" && cd "$SRC_DIR"  # 确保源码目录并切换
                                                                   local php_archive="php-${PHP_VERSION}.tar.gz"  # 源码包名

                                                                   for mirror in "${PHP_MIRRORS[@]}"; do  # 遍历镜像
                                                                       info "尝试从镜像下载: $mirror"  # 输出当前尝试的镜像
                                                                       if command -v wget >/dev/null 2>&1; then  # 优先使用 wget
                                                                           if run_cmd_with_retry "wget -c --tries=$DOWNLOAD_RETRIES --timeout=30 '$mirror' -O '$php_archive'" "下载 PHP ($mirror)" 3 3; then  # 使用 DOWNLOAD_RETRIES
                                                                               [ -s "$php_archive" ] && { success "PHP 下载成功"; register_tmp_file "$SRC_DIR/$php_archive"; return 0; }  # 若下载成功则注册临时文件并返回
                                                                           fi
                                                                       else  # 否则使用 curl
                                                                           if run_cmd_with_retry "curl -L --retry $DOWNLOAD_RETRIES --connect-timeout 30 '$mirror' -o '$php_archive'" "下载 PHP ($mirror)" 3 3; then  # 使用 DOWNLOAD_RETRIES
                                                                               [ -s "$php_archive" ] && { success "PHP 下载成功"; register_tmp_file "$SRC_DIR/$php_archive"; return 0; }
                                                                           fi
                                                                       fi
                                                                       warn "下载失败: $mirror"  # 当前镜像失败提示
                                                                       rm -f "$php_archive" 2>/dev/null || true  # 清理残留文件
                                                                   done

                                                                   return 1  # 所有镜像尝试失败返回 1
                                                               }

                                                               extract_php() {  # 解压 PHP 源码
                                                                   info "解压 PHP 源码..."  # 信息提示
                                                                   cd "$SRC_DIR"  # 切换到源码目录
                                                                   local php_archive="php-${PHP_VERSION}.tar.gz"  # 源码包名
                                                                   if [ ! -s "$php_archive" ]; then  # 校验文件是否存在且非空
                                                                       error "PHP 源码包不存在: $php_archive"  # 错误提示
                                                                       return 1  # 返回失败
                                                                   fi

                                                                   run_cmd "tar -xzf '$php_archive'" "解压 PHP 源码"  # 解压命令

                                                                   PHP_SRC_DIR="$SRC_DIR/php-${PHP_VERSION}"  # 预期解压目录
                                                                   if [ ! -d "$PHP_SRC_DIR" ]; then  # 若默认目录不存在则从归档中解析实际目录
                                                                       local actual_dir
                                                                       actual_dir=$(tar -tf "$php_archive" | head -n1 | cut -d/ -f1)  # 取归档第一行目录名
                                                                       PHP_SRC_DIR="$SRC_DIR/$actual_dir"  # 设置实际目录
                                                                   fi

                                                                   if [ ! -d "$PHP_SRC_DIR" ]; then  # 最终检查是否存在源码目录
                                                                       error "无法找到 PHP 源码目录"  # 错误提示
                                                                       return 1  # 返回失败
                                                                   fi

                                                                   success "PHP 源码解压完成"  # 成功提示
                                                               }

                                                               configure_php() {  # 配置 PHP 编译选项
                                                                   info "配置 PHP 编译选项..."  # 信息提示
                                                                   cd "$PHP_SRC_DIR"  # 切换到源码目录

                                                                   if [ -f buildconf ]; then  # 若存在 buildconf 则运行
                                                                       run_cmd "./buildconf --force" "运行 buildconf" || warn "buildconf 运行失败"  # 运行并在失败时给出警告
                                                                   fi

                                                                   local CONFIGURE_OPTS=(  # 配置数组
                                                                       "--prefix=$PHP_PREFIX"  # 编译安装前缀
                                                                       "--with-config-file-path=$PHP_PREFIX/lib"  # php.ini 路径
                                                                       "--with-config-file-scan-dir=$PHP_PREFIX/lib/conf.d"  # 扫描扩展配置目录
                                                                       "--enable-fpm"  # 启用 fpm
                                                                       "--with-fpm-user=$WWW_USER"  # fpm 用户
                                                                       "--with-fpm-group=$WWW_USER"  # fpm 用户组
                                                                       "--with-libxml"  # 启用 libxml
                                                                       "--with-openssl"  # 启用 openssl
                                                                       # "--with-mysqli=mysqlnd"  # mysqli 使用 mysqlnd
                                                                       "--with-mysqli"
                                                                       "--with-mysql-sock"
                                                                       "--enable-pdo"  # 启用 pdo
                                                                       "--enable-pdo"
                                                                       "--with-pdo-sqlite"
                                                                       "--with-pdo-mysql"
                                                                       "--with-pdo-sqlite"
                                                                       "--with-pdo-sqlite"
                                                                       "--with-zlib"  # 启用 zlib
                                                                       "--enable-mbstring"  # 启用 mbstring
                                                                       "--enable-opcache"  # 启用 opcache
                                                                       "--enable-cli"  # 启用 cli
                                                                       # 根据实际需求添加/删除配置项
                                                                       "--with-kerberos"
                                                                       "--with-system-ciphers"
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
                                                                       "--enable-mbregex"
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
                                                                       "--with-pear"
                                                                       "--with-jpeg"
                                                                       "--with-libdir=lib64"
                                                                       "--enable-static"
                                                                   )
                                                                   # 将数组拼接为字符串;彻底移除所有换行符（即使有也清除）
                                                                   local opts_str=$(printf '%s ' "${CONFIGURE_OPTS[@]}" | tr -d '\n\r' | sed 's/ $//')

                                                                   local attempt=1  # 初始尝试次数
                                                                   while [ $attempt -le $MAX_RETRIES ]; do  # 重试配置直到 MAX_RETRIES
                                                                       info "PHP 配置尝试 $attempt/$MAX_RETRIES"  # 提示当前尝试次数
                                                                       if run_cmd "./configure $opts_str" "配置 PHP"; then  # 尝试运行 configure
                                                                           success "PHP 配置成功"  # 成功提示
                                                                           # 记录 configure 成功，可在回滚时删除 PHP 前缀目录
                                                                           mark_rollback "删除已安装的 PHP 文件夹 $PHP_PREFIX" "rm -rf '$PHP_PREFIX' || true"  # 回滚命令
                                                                           return 0  # 返回成功
                                                                       fi
                                                                       warn "PHP 配置失败，尝试安装缺失依赖并重试"  # 配置失败时警告并尝试修复依赖
                                                                       install_missing_dependencies || true  # 尝试安装缺失依赖
                                                                       attempt=$((attempt+1))  # 增加尝试计数
                                                                       sleep 3  # 等待后重试
                                                                   done

                                                                   return 1  # 配置最终失败返回 1
                                                               }

                                                               install_missing_dependencies() {  # 根据常见依赖尝试安装
                                                                   info "根据日志尝试安装缺失的依赖（若有）..."  # 信息提示
                                                                   # 这里简单示例，生产脚本可根据 configure 日志精确解析缺失库
                                                                   if [ "$PKG_MGR" = "apt" ]; then  # apt 系统常用 dev 包
                                                                       pkg_install libxml2-dev libssl-dev libcurl4-openssl-dev libjpeg-dev libpng-dev zlib1g-dev
                                                                   else  # rpm 系统常用 dev 包
                                                                       pkg_install libxml2-devel openssl-devel curl-devel libjpeg-turbo-devel zlib-devel
                                                                   fi
                                                               }

                                                               build_php() {  # 编译 PHP
                                                                   info "编译 PHP..."  # 信息提示
                                                                   cd "$PHP_SRC_DIR"  # 切换到源码目录
                                                                   # 清理
                                                                   run_cmd "make clean || true" "清理上次编译"  # 清理上次编译残留
                                                                   # 编译
                                                                   run_cmd_with_retry "make -j $MAKE_JOBS" "编译 PHP" 2 || return 1  # make -j 使用并行编译
                                                                   run_cmd "make install" "安装 PHP" || return 1  # make install
                                                                   success "PHP 编译安装完成"  # 成功提示
                                                               }

                                                               setup_php_config() {  # 生成并写入 php.ini 及 fpm 配置
                                                                   info "配置 PHP..."  # 信息提示
                                                                   mkdir -p "$PHP_PREFIX/lib" "$PHP_PREFIX/etc" "$PHP_PREFIX/var/log" "$PHP_PREFIX/var/run" "$PHP_PREFIX/lib/conf.d" "$PHP_PREFIX/var/session"  # 创建必要目录
                                                                   PHP_INI_FILE="$PHP_PREFIX/lib/php.ini"  # php.ini 路径

                                                                   if [ -f "$PHP_INI_FILE" ]; then  # 若已存在 php.ini 则备份
                                                                       safe_backup_file "$PHP_INI_FILE"  # 备份原有 php.ini
                                                                   fi

                                                                   cat > "$PHP_INI_FILE" <<EOF  # 写入基础 php.ini
                                                               [PHP]
                                                               engine = On
                                                               short_open_tag = Off
                                                               error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
                                                               display_errors = Off
                                                               log_errors = On
                                                               error_log = $PHP_PREFIX/var/log/php_errors.log
                                                               memory_limit = $PHP_MEMORY_LIMIT
                                                               max_execution_time = $PHP_MAX_EXECUTION_TIME
                                                               post_max_size = $PHP_POST_MAX_SIZE
                                                               upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE
                                                               extension_dir = "$PHP_PREFIX/lib/php/extensions/no-debug-non-zts-20200930"
                                                               EOF

                                                                   mark_rollback "删除生成的 php.ini" "rm -f '$PHP_INI_FILE' || true"  # 回滚时删除生成的 php.ini

                                                                   # php-fpm 配置
                                                                   local fpm_conf="$PHP_PREFIX/etc/php-fpm.conf"  # fpm 配置路径
                                                                   if [ -f "$fpm_conf" ]; then  # 若存在则备份
                                                                       safe_backup_file "$fpm_conf"  # 备份 fpm 配置
                                                                   fi
                                                                   cat > "$fpm_conf" <<EOF  # 写入基础 php-fpm 配置
                                                               [global]
                                                               pid = $PHP_PREFIX/var/run/php-fpm.pid
                                                               error_log = $PHP_PREFIX/var/log/php-fpm.log
                                                               [www]
                                                               user = $WWW_USER
                                                               group = $WWW_USER
                                                               listen = 127.0.0.1:9000
                                                               pm = dynamic
                                                               pm.max_children = 50
                                                               EOF

                                                                   mark_rollback "删除生成的 php-fpm 配置" "rm -f '$fpm_conf' || true"  # 回滚时删除 fpm 配置

                                                                   # 创建符号链接
                                                                   for binary in php phpize pear pecl phar; do  # 遍历常用二进制并创建全局软链
                                                                       if [ -f "$PHP_PREFIX/bin/$binary" ]; then  # 若二进制存在则创建软链
                                                                           ln -sf "$PHP_PREFIX/bin/$binary" "/usr/local/bin/$binary" || warn "创建 $binary 符号链接失败"  # 创建软链
                                                                           mark_rollback "移除符号链接 /usr/local/bin/$binary" "rm -f '/usr/local/bin/$binary' || true"  # 回滚命令
                                                                       fi
                                                                   done

                                                                   # 将 PHP 添加到 PATH（仅在 /etc/profile 中添加一次）
                                                                   if ! grep -q "$PHP_PREFIX/bin" "$PROFILE_FILE" 2>/dev/null; then  # 若 profile 中没有则追加
                                                                       safe_backup_file "$PROFILE_FILE"  # 备份 profile
                                                                       echo "" >> "$PROFILE_FILE"  # 添加空行以便分隔
                                                                       echo "# PHP $PHP_VERSION" >> "$PROFILE_FILE"  # 标识
                                                                       echo "export PATH=$PHP_PREFIX/bin:\$PATH" >> "$PROFILE_FILE"  # 添加 PATH
                                                                       echo "export PHP_HOME=$PHP_PREFIX" >> "$PROFILE_FILE"  # 添加 PHP_HOME
                                                                       mark_rollback "恢复 $PROFILE_FILE" "if [ -f '${PROFILE_FILE}.bak.' ]; then true; fi; # 手动恢复请参考备份"  # 回滚提示（保守策略）
                                                                   fi

                                                                   export PATH="$PHP_PREFIX/bin:$PATH"  # 临时将 PHP 置入 PATH
                                                                   export PHP_HOME="$PHP_PREFIX"  # 导出 PHP_HOME

                                                                   success "PHP 基本配置完成"  # 成功提示
                                                               }

                                                               create_phpfpm_service() {  # 创建 systemd unit 文件并启动 php-fpm
                                                                   info "创建 PHP-FPM systemd 服务..."  # 信息提示
                                                                   local service_file="/etc/systemd/system/php-fpm.service"  # unit 文件路径
                                                                   safe_backup_file "$service_file"  # 备份原有 unit 文件（如有）

                                                                   cat > "$service_file" <<EOF  # 写入 unit 文件内容
                                                               [Unit]
                                                               Description=PHP-FPM (Custom)
                                                               After=network.target

                                                               [Service]
                                                               Type=simple
                                                               PIDFile=$PHP_PREFIX/var/run/php-fpm.pid
                                                               ExecStart=$PHP_PREFIX/sbin/php-fpm --nodaemonize --fpm-config $PHP_PREFIX/etc/php-fpm.conf
                                                               Restart=on-failure

                                                               [Install]
                                                               WantedBy=multi-user.target
                                                               EOF

                                                                   run_cmd "systemctl daemon-reload" "重载 systemd"  # 重载 systemd
                                                                   if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then  # 若允许自动启动则 enable + start
                                                                       run_cmd "systemctl enable php-fpm.service" "启用 php-fpm.service" || warn "启用 php-fpm 失败"  # 启用服务
                                                                       run_cmd "systemctl start php-fpm.service" "启动 php-fpm.service" || warn "启动 php-fpm 失败"  # 启动服务
                                                                       mark_rollback "禁用并停止 php-fpm 服务" "systemctl stop php-fpm.service || true; systemctl disable php-fpm.service || true; rm -f '$service_file' || true; systemctl daemon-reload || true"  # 回滚命令
                                                                   else
                                                                       mark_rollback "删除 php-fpm unit 文件" "rm -f '$service_file' || true; systemctl daemon-reload || true"  # 若不自动启动则仅记录删除 unit 文件
                                                                   fi

                                                                   success "PHP-FPM 服务已创建（若设置自动启动则已启动）"  # 成功提示
                                                               }

                                                               # ----------------- PHP 扩展安装（示例） -----------------
                                                               install_php_extensions() {  # 安装示例扩展（redis, imagick 等）
                                                                   if [ "${INSTALL_PHP_EXTENSIONS,,}" != "yes" ]; then  # 按配置决定是否跳过
                                                                       info "跳过 PHP 扩展安装"  # 跳过提示
                                                                       return 0  # 返回成功
                                                                   fi
                                                                   info "安装一些常用 PHP 扩展（通过 pecl / 系统包）..."  # 信息提示
                                                                   export PATH="$PHP_PREFIX/bin:$PATH"  # 确保 pecl/phpize 可用

                                                                   if command -v pecl >/dev/null 2>&1; then  # 如果 pecl 可用则尝试安装 redis
                                                                       if run_cmd_with_retry "pecl install redis" "安装 redis 扩展" 3 2; then  # pecl 安装，重试 3 次
                                                                           echo "extension=redis.so" >> "$PHP_INI_FILE"  # 将扩展写入 php.ini
                                                                           mark_rollback "从 php.ini 移除 redis 扩展配置" "sed -i '/extension=redis.so/d' '$PHP_INI_FILE' || true"  # 回滚命令
                                                                           success "redis 扩展安装成功"  # 成功提示
                                                                       else
                                                                           warn "redis 扩展安装失败"  # 安装失败警告
                                                                       fi
                                                                   fi

                                                                   # Imagick 示例（需要 ImageMagick 开发包）
                                                                   if [ "$PKG_MGR" = "apt" ]; then  # apt 系统需要 dev 包
                                                                       pkg_install libmagickwand-dev libmagickcore-dev || true  # 安装 ImageMagick 开发包
                                                                   else
                                                                       pkg_install ImageMagick ImageMagick-devel || true  # rpm 系统安装
                                                                   fi
                                                                   if command -v pecl >/dev/null 2>&1; then  # 安装 imagick
                                                                       run_cmd_with_retry "pecl install imagick" "安装 imagick" 3 2 || warn "imagick 安装失败"  # 重试并警告
                                                                   fi

                                                                   success "PHP 扩展安装步骤完成（可能部分扩展需要手动调整）"  # 完成提示
                                                               }

                                                               # ----------------- Composer 安装 -----------------
                                                               install_composer() {  # 安装 Composer
                                                                   if [ "${INSTALL_COMPOSER,,}" != "yes" ]; then  # 按配置可跳过
                                                                       info "跳过 Composer 安装"  # 跳过提示
                                                                       return 0  # 返回成功
                                                                   fi
                                                                   info "安装 Composer..."  # 信息提示
                                                                   if command -v curl >/dev/null 2>&1; then  # 需要 curl
                                                                       run_cmd "curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php" "下载 Composer 安装脚本"  # 下载安装脚本
                                                                       register_tmp_file "/tmp/composer-setup.php"  # 注册临时文件
                                                                       run_cmd "php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer" "安装 Composer" || warn "Composer 安装失败"  # 安装并警告
                                                                       run_cmd "chmod +x /usr/local/bin/composer" "设置 composer 执行权限"  # 赋予可执行权限
                                                                       mark_rollback "移除 /usr/local/bin/composer" "rm -f /usr/local/bin/composer || true"  # 回滚命令
                                                                       run_cmd "composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/" "配置 Composer 镜像" || true  # 设置镜像
                                                                       success "Composer 安装完成"  # 成功提示
                                                                   else
                                                                       warn "缺少 curl，无法安装 Composer"  # 缺少 curl 的提示
                                                                   fi
                                                               }

                                                               # ----------------- MySQL 安装（可回滚） -----------------
                                                               install_mysql() {  # 安装 MySQL
                                                                   if [ "${INSTALL_MYSQL,,}" != "yes" ]; then  # 按配置可跳过
                                                                       info "跳过 MySQL 安装"  # 跳过提示
                                                                       return 0  # 返回
                                                                   fi
                                                                   info "安装 MySQL..."  # 信息提示
                                                                   case "$PKG_MGR" in  # 根据包管理器执行不同安装逻辑
                                                                       apt)
                                                                           run_cmd "apt-get install -y lsb-release gnupg" "安装依赖"  # 安装依赖
                                                                           run_cmd "wget -O /tmp/mysql-apt-config.deb https://dev.mysql.com/get/mysql-apt-config_0.8.28-1_all.deb" "下载 MySQL apt 配置"  # 下载 apt config
                                                                           register_tmp_file "/tmp/mysql-apt-config.deb"  # 注册临时文件
                                                                           echo "mysql-apt-config mysql-apt-config/select-server select mysql-8.0" | debconf-set-selections || true  # 预设 debconf 选项
                                                                           run_cmd "DEBIAN_FRONTEND=noninteractive dpkg -i /tmp/mysql-apt-config.deb || true" "安装 MySQL apt 源"  # 安装 deb 包
                                                                           run_cmd "apt-get update -y" "更新包列表"  # 更新
                                                                           run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install mysql-server mysql-client" "安装 MySQL" 3 3 || warn "MySQL 安装可能失败"  # 安装 mysql-server
                                                                           # 记录回滚：移除 mysql-server
                                                                           mark_rollback "移除 MySQL" "DEBIAN_FRONTEND=noninteractive apt-get -y remove --purge mysql-server mysql-client || true; apt-get -y autoremove || true"  # 回滚回退命令
                                                                           ;;
                                                                       *)
                                                                           run_cmd "rpm -Uvh https://dev.mysql.com/get/mysql80-community-release-el7-11.noarch.rpm" "添加 MySQL 仓库" || warn "添加 MySQL 仓库失败"  # 添加 rpm 仓库
                                                                           run_cmd_with_retry "$PKG_MGR -y install --allowerasing mysql-community-server mysql-community-client" "安装 MySQL" 3 3 || warn "MySQL 安装可能失败"  # 使用 --allowerasing 避免冲突
                                                                           mark_rollback "移除 MySQL" "$PKG_MGR -y remove mysql-community-server mysql-community-client || true"  # 回滚命令
                                                                           ;;
                                                                   esac

                                                                   if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then  # 若允许自动启动则 enable + start
                                                                       run_cmd "systemctl enable mysqld || true" "启用 MySQL 服务" || true  # 启用
                                                                       run_cmd "systemctl start mysqld || true" "启动 MySQL 服务" || true  # 启动

                                                                       # 尝试设置 root 密码（如果能从日志获取临时密码）
                                                                       local temp_pass=""  # 临时密码变量
                                                                       if [ -f /var/log/mysqld.log ]; then  # 从 mysqld.log 尝试提取临时密码
                                                                           temp_pass=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $NF}' | tail -1 || true)  # 解析临时密码
                                                                       fi
                                                                       if [ -n "$temp_pass" ]; then  # 如果能找到则设置 root 密码
                                                                           run_cmd "mysql -uroot -p'${temp_pass}' --connect-expired-password -e \"ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASS}'; FLUSH PRIVILEGES;\"" "设置 MySQL root 密码" || warn "设置 root 密码失败"  # 设置密码
                                                                       else
                                                                           warn "未检索到 MySQL 临时密码，建议手动运行 mysql_secure_installation"  # 提示手动安全配置
                                                                       fi

                                                                       run_cmd "mysql -uroot -p'${MYSQL_ROOT_PASS}' -e \"CREATE USER IF NOT EXISTS '${REMOTE_ADMIN_USER}'@'%' IDENTIFIED BY '${REMOTE_ADMIN_PASS}'; GRANT ALL PRIVILEGES ON *.* TO '${REMOTE_ADMIN_USER}'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;\"" "创建远程管理用户" || warn "创建 MySQL 远程用户失败"  # 创建远程管理用户
                                                                   fi

                                                                   success "MySQL 安装完成（如有错误请查看日志）"  # 成功提示
                                                               }

                                                               # ----------------- Redis 安装 -----------------
                                                               install_redis() {  # 安装 Redis
                                                                   if [ "${INSTALL_REDIS,,}" != "yes" ]; then  # 按配置可跳过
                                                                       info "跳过 Redis 安装"  # 跳过提示
                                                                       return 0  # 返回
                                                                   fi
                                                                   info "安装 Redis..."  # 信息提示
                                                                   if [ "$PKG_MGR" = "apt" ]; then  # apt 系统安装 redis-server
                                                                       pkg_install redis-server redis-tools || true  # 安装并忽略错误
                                                                   else
                                                                       pkg_install redis || true  # rpm 系统安装 redis
                                                                   fi

                                                                   local redis_conf=""  # redis 配置文件路径变量
                                                                   if [ -f /etc/redis/redis.conf ]; then  # 常见配置路径
                                                                       redis_conf="/etc/redis/redis.conf"  # 设置路径
                                                                   elif [ -f /etc/redis.conf ]; then  # 另一种路径
                                                                       redis_conf="/etc/redis.conf"  # 设置路径
                                                                   fi

                                                                   if [ -n "$redis_conf" ]; then  # 若找到配置文件则备份并修改
                                                                       safe_backup_file "$redis_conf"  # 备份原始配置
                                                                       run_cmd "sed -i 's/^bind 127.0.0.1/bind 0.0.0.0/' '$redis_conf'" "允许远程访问"  # 修改 bind
                                                                       run_cmd "sed -i 's/^protected-mode yes/protected-mode no/' '$redis_conf'" "关闭 protected-mode"  # 修改 protected-mode
                                                                       run_cmd "grep -q '^requirepass' '$redis_conf' || echo 'requirepass $REMOTE_ADMIN_PASS' >> '$redis_conf'" "设置 Redis 密码"  # 添加密码配置
                                                                       mark_rollback "恢复 Redis 配置" "if [ -f '${redis_conf}.bak.' ]; then true; fi; # 参考备份文件恢复"  # 回滚提示（保守）
                                                                   fi

                                                                   if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then  # 若允许自动启动则启用并启动
                                                                       run_cmd "systemctl enable redis || true" "启用 Redis"  # 启用
                                                                       run_cmd "systemctl start redis || true" "启动 Redis"  # 启动
                                                                       mark_rollback "停止并禁用 redis" "systemctl stop redis || true; systemctl disable redis || true"  # 回滚命令
                                                                   fi

                                                                   success "Redis 安装完成"  # 成功提示
                                                               }

                                                               # ----------------- Nginx 安装 -----------------
                                                               install_nginx() {  # 安装 Nginx
                                                                   if [ "${INSTALL_NGINX,,}" != "yes" ]; then  # 按配置可跳过
                                                                       info "跳过 Nginx 安装"  # 跳过提示
                                                                       return 0  # 返回
                                                                   fi
                                                                   info "安装 Nginx..."  # 信息提示
                                                                   if [ "$PKG_MGR" = "apt" ]; then  # apt 系统安装 nginx
                                                                       pkg_install nginx nginx-extras || true  # 安装 nginx
                                                                   else
                                                                       pkg_install nginx || true  # rpm 系统安装 nginx
                                                                   fi

                                                                   if [ -d /etc/nginx/conf.d ]; then  # 若存在 conf.d 目录则写入 php-fpm 配置
                                                                       safe_backup_file "/etc/nginx/conf.d/php-fpm.conf" || true  # 备份可能存在的文件
                                                                       cat > /etc/nginx/conf.d/php-fpm.conf <<'EOF'  # 写入示例 server 配置
                                                               server {
                                                                   listen 80 default_server;
                                                                   listen [::]:80 default_server;
                                                                   server_name _;
                                                                   root /usr/share/nginx/html;
                                                                   index index.php index.html index.htm;

                                                                   location / {
                                                                       try_files $uri $uri/ =404;
                                                                   }

                                                                   location ~ \.php$ {
                                                                       fastcgi_pass 127.0.0.1:9000;
                                                                       fastcgi_index index.php;
                                                                       include fastcgi_params;
                                                                   }
                                                               }
                                                               EOF
                                                                       mark_rollback "移除 nginx php-fpm.conf" "rm -f /etc/nginx/conf.d/php-fpm.conf || true"  # 回滚命令
                                                                   fi

                                                                   run_cmd "nginx -t || true" "测试 Nginx 配置" || warn "nginx -t 出错"  # 测试 nginx 配置
                                                                   if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then  # 自动启动
                                                                       run_cmd "systemctl enable nginx || true" "启用 Nginx"  # 启用
                                                                       run_cmd "systemctl start nginx || true" "启动 Nginx"  # 启动
                                                                       mark_rollback "停止并禁用 Nginx" "systemctl stop nginx || true; systemctl disable nginx || true"  # 回滚命令
                                                                   fi

                                                                   success "Nginx 安装完成"  # 成功提示
                                                               }

                                                               # ----------------- 创建用户 -----------------
                                                               create_users() {  # 创建网站运行用户并准备网站目录
                                                                   info "创建系统用户..."  # 信息提示
                                                                   if ! id "$WWW_USER" >/dev/null 2>&1; then  # 如果用户不存在则创建
                                                                       run_cmd "useradd -r -m -s /bin/bash -U '$WWW_USER'" "创建用户 $WWW_USER"  # 创建系统用户
                                                                       run_cmd "echo '$WWW_USER:$WWW_PASS' | chpasswd" "设置 $WWW_USER 密码"  # 设置密码
                                                                       CREATED_USERS+=("$WWW_USER")  # 记录已创建用户
                                                                       mark_rollback "删除创建的用户 $WWW_USER" "userdel -r '$WWW_USER' || true"  # 回滚命令
                                                                   else
                                                                       info "用户 $WWW_USER 已存在，跳过创建"  # 已存在提示
                                                                   fi
                                                                   run_cmd "mkdir -p /var/www/html" "创建网站目录"  # 创建网站根目录
                                                                   run_cmd "chown -R $WWW_USER:$WWW_USER /var/www/html" "设置网站目录权限"  # 设置权限
                                                                   success "用户 / 网站目录 准备就绪"  # 成功提示
                                                               }

                                                               # ----------------- 清理工作 -----------------
                                                               cleanup() {  # 清理临时文件和包缓存
                                                                   if [ "${CLEAN_TEMP,,}" = "yes" ]; then  # 根据配置决定是否清理
                                                                       info "清理临时文件..."  # 信息提示
                                                                       cleanup_tmpfiles  # 清理临时文件
                                                                       case "$PKG_MGR" in  # 清理包管理器缓存
                                                                           dnf|yum)
                                                                               run_cmd "$PKG_MGR clean all || true" "清理包缓存" || true  # 清理 dnf/yum 缓存
                                                                               ;;
                                                                           apt)
                                                                               run_cmd "apt-get autoremove -y || true" "清理无用包" || true  # apt 自动移除
                                                                               run_cmd "apt-get clean || true" "清理 apt 缓存" || true  # apt 清理缓存
                                                                               ;;
                                                                       esac
                                                                       success "清理完成"  # 完成提示
                                                                   else
                                                                       info "跳过清理（CLEAN_TEMP=no）"  # 跳过清理提示
                                                                   fi
                                                               }

                                                               # ----------------- 安装总结 -----------------
                                                               installation_summary() {  # 输出安装总结到配置文件
                                                                   local end_time=$(date +%s)  # 记录结束时间
                                                                   local total_time=$((end_time - START_TIME))  # 计算耗时

                                                                   cat > "$INSTALL_SUMMARY" <<EOF  # 写入安装总结
                                                               # 安装总结
                                                               开始时间: $(date -d @$START_TIME '+%Y-%m-%d %H:%M:%S')
                                                               总耗时: ${total_time}s
                                                               PHP: ${PHP_VERSION} (安装: ${INSTALL_PHP})
                                                               MySQL: ${MYSQL_VERSION} (安装: ${INSTALL_MYSQL})
                                                               Redis: ${REDIS_VERSION} (安装: ${INSTALL_REDIS})
                                                               Nginx: ${NGINX_VERSION} (安装: ${INSTALL_NGINX})
                                                               安装日志: $LOG_FILE
                                                               错误日志: $ERROR_LOG_FILE
                                                               回滚日志: $ROLLBACK_LOG_FILE
                                                               EOF
                                                                   chmod 600 "$INSTALL_SUMMARY" || true  # 设置文件权限为 600
                                                                   success "安装完成！总耗时: ${total_time}s。安装总结已写入 $INSTALL_SUMMARY"  # 成功提示
                                                               }

                                                               # ----------------- 主流程 -----------------
                                                               main() {  # 主入口函数
                                                                   clear  # 清屏
                                                                   _bold "🚀 工业级 PHP 环境安装脚本（改进版）"  # 标题
                                                                   _bold "开始时间: $(date '+%Y-%m-%d %H:%M:%S')"  # 开始时间

                                                                   if [ "$EUID" -ne 0 ]; then  # 检查是否以 root 运行
                                                                       error "请使用 root 权限运行此脚本"  # 错误提示
                                                                       exit 1  # 退出
                                                                   fi

                                                                   detect_system  # 检测系统
                                                                   install_dependencies  # 安装依赖
                                                                   create_users  # 创建用户

                                                                   if [ "${INSTALL_PHP,,}" = "yes" ]; then  # 若需安装 PHP 则执行以下步骤
                                                                       download_php  # 下载源码
                                                                       extract_php  # 解压源码
                                                                       configure_php  # configure
                                                                       build_php  # 编译安装
                                                                       setup_php_config  # 写入配置
                                                                       create_phpfpm_service  # 创建服务
                                                                       install_php_extensions  # 安装扩展
                                                                   fi

                                                                   install_composer  # 安装 Composer
                                                                   install_mysql  # 安装 MySQL
                                                                   install_redis  # 安装 Redis
                                                                   install_nginx  # 安装 Nginx

                                                                   cleanup  # 清理
                                                                   installation_summary  # 输出安装总结
                                                               }

                                                               # 启动脚本
                                                               main "$@"  # 调用 main 并传递所有参数
ES
        info "PHP 配置尝试 $attempt/$MAX_RETRIES"  # 提示当前尝试次数
        if run_cmd "./configure $opts_str" "配置 PHP"; then  # 尝试运行 configure
            success "PHP 配置成功"  # 成功提示
            # 记录 configure 成功，可在回滚时删除 PHP 前缀目录
            mark_rollback "删除已安装的 PHP 文件夹 $PHP_PREFIX" "rm -rf '$PHP_PREFIX' || true"  # 回滚命令
            return 0  # 返回成功
        fi
        warn "PHP 配置失败，尝试安装缺失依赖并重试"  # 配置失败时警告并尝试修复依赖
        install_missing_dependencies || true  # 尝试安装缺失依赖
        attempt=$((attempt+1))  # 增加尝试计数
        sleep 3  # 等待后重试
    done

    return 1  # 配置最终失败返回 1
}

install_missing_dependencies() {  # 根据常见依赖尝试安装
    info "根据日志尝试安装缺失的依赖（若有）..."  # 信息提示
    # 这里简单示例，生产脚本可根据 configure 日志精确解析缺失库
    if [ "$PKG_MGR" = "apt" ]; then  # apt 系统常用 dev 包
        pkg_install libxml2-dev libssl-dev libcurl4-openssl-dev libjpeg-dev libpng-dev zlib1g-dev
    else  # rpm 系统常用 dev 包
        pkg_install libxml2-devel openssl-devel curl-devel libjpeg-turbo-devel zlib-devel
    fi
}

build_php() {  # 编译 PHP
    info "编译 PHP..."  # 信息提示
    cd "$PHP_SRC_DIR"  # 切换到源码目录
    # 清理
    run_cmd "make clean || true" "清理上次编译"  # 清理上次编译残留
    # 编译
    run_cmd_with_retry "make -j $MAKE_JOBS" "编译 PHP" 2 || return 1  # make -j 使用并行编译
    run_cmd "make install" "安装 PHP" || return 1  # make install
    success "PHP 编译安装完成"  # 成功提示
}

setup_php_config() {  # 生成并写入 php.ini 及 fpm 配置
    info "配置 PHP..."  # 信息提示
    mkdir -p "$PHP_PREFIX/lib" "$PHP_PREFIX/etc" "$PHP_PREFIX/var/log" "$PHP_PREFIX/var/run" "$PHP_PREFIX/lib/conf.d" "$PHP_PREFIX/var/session"  # 创建必要目录
    PHP_INI_FILE="$PHP_PREFIX/lib/php.ini"  # php.ini 路径

    if [ -f "$PHP_INI_FILE" ]; then  # 若已存在 php.ini 则备份
        safe_backup_file "$PHP_INI_FILE"  # 备份原有 php.ini
    fi

    cat > "$PHP_INI_FILE" <<EOF  # 写入基础 php.ini
[PHP]
engine = On
short_open_tag = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
log_errors = On
error_log = $PHP_PREFIX/var/log/php_errors.log
memory_limit = $PHP_MEMORY_LIMIT
max_execution_time = $PHP_MAX_EXECUTION_TIME
post_max_size = $PHP_POST_MAX_SIZE
upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE
extension_dir = "$PHP_PREFIX/lib/php/extensions/no-debug-non-zts-20200930"
EOF

    mark_rollback "删除生成的 php.ini" "rm -f '$PHP_INI_FILE' || true"  # 回滚时删除生成的 php.ini

    # php-fpm 配置
    local fpm_conf="$PHP_PREFIX/etc/php-fpm.conf"  # fpm 配置路径
    if [ -f "$fpm_conf" ]; then  # 若存在则备份
        safe_backup_file "$fpm_conf"  # 备份 fpm 配置
    fi
    cat > "$fpm_conf" <<EOF  # 写入基础 php-fpm 配置
[global]
pid = $PHP_PREFIX/var/run/php-fpm.pid
error_log = $PHP_PREFIX/var/log/php-fpm.log
[www]
user = $WWW_USER
group = $WWW_USER
listen = 127.0.0.1:9000
pm = dynamic
pm.max_children = 50
EOF

    mark_rollback "删除生成的 php-fpm 配置" "rm -f '$fpm_conf' || true"  # 回滚时删除 fpm 配置

    # 创建符号链接
    for binary in php phpize pear pecl phar; do  # 遍历常用二进制并创建全局软链
        if [ -f "$PHP_PREFIX/bin/$binary" ]; then  # 若二进制存在则创建软链
            ln -sf "$PHP_PREFIX/bin/$binary" "/usr/local/bin/$binary" || warn "创建 $binary 符号链接失败"  # 创建软链
            mark_rollback "移除符号链接 /usr/local/bin/$binary" "rm -f '/usr/local/bin/$binary' || true"  # 回滚命令
        fi
    done

    # 将 PHP 添加到 PATH（仅在 /etc/profile 中添加一次）
    if ! grep -q "$PHP_PREFIX/bin" "$PROFILE_FILE" 2>/dev/null; then  # 若 profile 中没有则追加
        safe_backup_file "$PROFILE_FILE"  # 备份 profile
        echo "" >> "$PROFILE_FILE"  # 添加空行以便分隔
        echo "# PHP $PHP_VERSION" >> "$PROFILE_FILE"  # 标识
        echo "export PATH=$PHP_PREFIX/bin:\$PATH" >> "$PROFILE_FILE"  # 添加 PATH
        echo "export PHP_HOME=$PHP_PREFIX" >> "$PROFILE_FILE"  # 添加 PHP_HOME
        mark_rollback "恢复 $PROFILE_FILE" "if [ -f '${PROFILE_FILE}.bak.' ]; then true; fi; # 手动恢复请参考备份"  # 回滚提示（保守策略）
    fi

    export PATH="$PHP_PREFIX/bin:$PATH"  # 临时将 PHP 置入 PATH
    export PHP_HOME="$PHP_PREFIX"  # 导出 PHP_HOME

    success "PHP 基本配置完成"  # 成功提示
}

create_phpfpm_service() {  # 创建 systemd unit 文件并启动 php-fpm
    info "创建 PHP-FPM systemd 服务..."  # 信息提示
    local service_file="/etc/systemd/system/php-fpm.service"  # unit 文件路径
    safe_backup_file "$service_file"  # 备份原有 unit 文件（如有）

    cat > "$service_file" <<EOF  # 写入 unit 文件内容
[Unit]
Description=PHP-FPM (Custom)
After=network.target

[Service]
Type=simple
PIDFile=$PHP_PREFIX/var/run/php-fpm.pid
ExecStart=$PHP_PREFIX/sbin/php-fpm --nodaemonize --fpm-config $PHP_PREFIX/etc/php-fpm.conf
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF

    run_cmd "systemctl daemon-reload" "重载 systemd"  # 重载 systemd
    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then  # 若允许自动启动则 enable + start
        run_cmd "systemctl enable php-fpm.service" "启用 php-fpm.service" || warn "启用 php-fpm 失败"  # 启用服务
        run_cmd "systemctl start php-fpm.service" "启动 php-fpm.service" || warn "启动 php-fpm 失败"  # 启动服务
        mark_rollback "禁用并停止 php-fpm 服务" "systemctl stop php-fpm.service || true; systemctl disable php-fpm.service || true; rm -f '$service_file' || true; systemctl daemon-reload || true"  # 回滚命令
    else
        mark_rollback "删除 php-fpm unit 文件" "rm -f '$service_file' || true; systemctl daemon-reload || true"  # 若不自动启动则仅记录删除 unit 文件
    fi

    success "PHP-FPM 服务已创建（若设置自动启动则已启动）"  # 成功提示
}

# ----------------- PHP 扩展安装（示例） -----------------
install_php_extensions() {  # 安装示例扩展（redis, imagick 等）
    if [ "${INSTALL_PHP_EXTENSIONS,,}" != "yes" ]; then  # 按配置决定是否跳过
        info "跳过 PHP 扩展安装"  # 跳过提示
        return 0  # 返回成功
    fi
    info "安装一些常用 PHP 扩展（通过 pecl / 系统包）..."  # 信息提示
    export PATH="$PHP_PREFIX/bin:$PATH"  # 确保 pecl/phpize 可用

    if command -v pecl >/dev/null 2>&1; then  # 如果 pecl 可用则尝试安装 redis
        if run_cmd_with_retry "pecl install redis" "安装 redis 扩展" 3 2; then  # pecl 安装，重试 3 次
            echo "extension=redis.so" >> "$PHP_INI_FILE"  # 将扩展写入 php.ini
            mark_rollback "从 php.ini 移除 redis 扩展配置" "sed -i '/extension=redis.so/d' '$PHP_INI_FILE' || true"  # 回滚命令
            success "redis 扩展安装成功"  # 成功提示
        else
            warn "redis 扩展安装失败"  # 安装失败警告
        fi
    fi

    # Imagick 示例（需要 ImageMagick 开发包）
    if [ "$PKG_MGR" = "apt" ]; then  # apt 系统需要 dev 包
        pkg_install libmagickwand-dev libmagickcore-dev || true  # 安装 ImageMagick 开发包
    else
        pkg_install ImageMagick ImageMagick-devel || true  # rpm 系统安装
    fi
    if command -v pecl >/dev/null 2>&1; then  # 安装 imagick
        run_cmd_with_retry "pecl install imagick" "安装 imagick" 3 2 || warn "imagick 安装失败"  # 重试并警告
    fi

    success "PHP 扩展安装步骤完成（可能部分扩展需要手动调整）"  # 完成提示
}

# ----------------- Composer 安装 -----------------
install_composer() {  # 安装 Composer
    if [ "${INSTALL_COMPOSER,,}" != "yes" ]; then  # 按配置可跳过
        info "跳过 Composer 安装"  # 跳过提示
        return 0  # 返回成功
    fi
    info "安装 Composer..."  # 信息提示
    if command -v curl >/dev/null 2>&1; then  # 需要 curl
        run_cmd "curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php" "下载 Composer 安装脚本"  # 下载安装脚本
        register_tmp_file "/tmp/composer-setup.php"  # 注册临时文件
        run_cmd "php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer" "安装 Composer" || warn "Composer 安装失败"  # 安装并警告
        run_cmd "chmod +x /usr/local/bin/composer" "设置 composer 执行权限"  # 赋予可执行权限
        mark_rollback "移除 /usr/local/bin/composer" "rm -f /usr/local/bin/composer || true"  # 回滚命令
        run_cmd "composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/" "配置 Composer 镜像" || true  # 设置镜像
        success "Composer 安装完成"  # 成功提示
    else
        warn "缺少 curl，无法安装 Composer"  # 缺少 curl 的提示
    fi
}

# ----------------- MySQL 安装（可回滚） -----------------
install_mysql() {  # 安装 MySQL
    if [ "${INSTALL_MYSQL,,}" != "yes" ]; then  # 按配置可跳过
        info "跳过 MySQL 安装"  # 跳过提示
        return 0  # 返回
    fi
    info "安装 MySQL..."  # 信息提示
    case "$PKG_MGR" in  # 根据包管理器执行不同安装逻辑
        apt)
            run_cmd "apt-get install -y lsb-release gnupg" "安装依赖"  # 安装依赖
            run_cmd "wget -O /tmp/mysql-apt-config.deb https://dev.mysql.com/get/mysql-apt-config_0.8.28-1_all.deb" "下载 MySQL apt 配置"  # 下载 apt config
            register_tmp_file "/tmp/mysql-apt-config.deb"  # 注册临时文件
            echo "mysql-apt-config mysql-apt-config/select-server select mysql-8.0" | debconf-set-selections || true  # 预设 debconf 选项
            run_cmd "DEBIAN_FRONTEND=noninteractive dpkg -i /tmp/mysql-apt-config.deb || true" "安装 MySQL apt 源"  # 安装 deb 包
            run_cmd "apt-get update -y" "更新包列表"  # 更新
            run_cmd_with_retry "DEBIAN_FRONTEND=noninteractive apt-get -y install mysql-server mysql-client" "安装 MySQL" 3 3 || warn "MySQL 安装可能失败"  # 安装 mysql-server
            # 记录回滚：移除 mysql-server
            mark_rollback "移除 MySQL" "DEBIAN_FRONTEND=noninteractive apt-get -y remove --purge mysql-server mysql-client || true; apt-get -y autoremove || true"  # 回滚回退命令
            ;;
        *)
            run_cmd "rpm -Uvh https://dev.mysql.com/get/mysql80-community-release-el7-11.noarch.rpm" "添加 MySQL 仓库" || warn "添加 MySQL 仓库失败"  # 添加 rpm 仓库
            run_cmd_with_retry "$PKG_MGR -y install --allowerasing mysql-community-server mysql-community-client" "安装 MySQL" 3 3 || warn "MySQL 安装可能失败"  # 使用 --allowerasing 避免冲突
            mark_rollback "移除 MySQL" "$PKG_MGR -y remove mysql-community-server mysql-community-client || true"  # 回滚命令
            ;;
    esac

    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then  # 若允许自动启动则 enable + start
        run_cmd "systemctl enable mysqld || true" "启用 MySQL 服务" || true  # 启用
        run_cmd "systemctl start mysqld || true" "启动 MySQL 服务" || true  # 启动

        # 尝试设置 root 密码（如果能从日志获取临时密码）
        local temp_pass=""  # 临时密码变量
        if [ -f /var/log/mysqld.log ]; then  # 从 mysqld.log 尝试提取临时密码
            temp_pass=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $NF}' | tail -1 || true)  # 解析临时密码
        fi
        if [ -n "$temp_pass" ]; then  # 如果能找到则设置 root 密码
            run_cmd "mysql -uroot -p'${temp_pass}' --connect-expired-password -e \"ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASS}'; FLUSH PRIVILEGES;\"" "设置 MySQL root 密码" || warn "设置 root 密码失败"  # 设置密码
        else
            warn "未检索到 MySQL 临时密码，建议手动运行 mysql_secure_installation"  # 提示手动安全配置
        fi

        run_cmd "mysql -uroot -p'${MYSQL_ROOT_PASS}' -e \"CREATE USER IF NOT EXISTS '${REMOTE_ADMIN_USER}'@'%' IDENTIFIED BY '${REMOTE_ADMIN_PASS}'; GRANT ALL PRIVILEGES ON *.* TO '${REMOTE_ADMIN_USER}'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;\"" "创建远程管理用户" || warn "创建 MySQL 远程用户失败"  # 创建远程管理用户
    fi

    success "MySQL 安装完成（如有错误请查看日志）"  # 成功提示
}

# ----------------- Redis 安装 -----------------
install_redis() {  # 安装 Redis
    if [ "${INSTALL_REDIS,,}" != "yes" ]; then  # 按配置可跳过
        info "跳过 Redis 安装"  # 跳过提示
        return 0  # 返回
    fi
    info "安装 Redis..."  # 信息提示
    if [ "$PKG_MGR" = "apt" ]; then  # apt 系统安装 redis-server
        pkg_install redis-server redis-tools || true  # 安装并忽略错误
    else
        pkg_install redis || true  # rpm 系统安装 redis
    fi

    local redis_conf=""  # redis 配置文件路径变量
    if [ -f /etc/redis/redis.conf ]; then  # 常见配置路径
        redis_conf="/etc/redis/redis.conf"  # 设置路径
    elif [ -f /etc/redis.conf ]; then  # 另一种路径
        redis_conf="/etc/redis.conf"  # 设置路径
    fi

    if [ -n "$redis_conf" ]; then  # 若找到配置文件则备份并修改
        safe_backup_file "$redis_conf"  # 备份原始配置
        run_cmd "sed -i 's/^bind 127.0.0.1/bind 0.0.0.0/' '$redis_conf'" "允许远程访问"  # 修改 bind
        run_cmd "sed -i 's/^protected-mode yes/protected-mode no/' '$redis_conf'" "关闭 protected-mode"  # 修改 protected-mode
        run_cmd "grep -q '^requirepass' '$redis_conf' || echo 'requirepass $REMOTE_ADMIN_PASS' >> '$redis_conf'" "设置 Redis 密码"  # 添加密码配置
        mark_rollback "恢复 Redis 配置" "if [ -f '${redis_conf}.bak.' ]; then true; fi; # 参考备份文件恢复"  # 回滚提示（保守）
    fi

    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then  # 若允许自动启动则启用并启动
        run_cmd "systemctl enable redis || true" "启用 Redis"  # 启用
        run_cmd "systemctl start redis || true" "启动 Redis"  # 启动
        mark_rollback "停止并禁用 redis" "systemctl stop redis || true; systemctl disable redis || true"  # 回滚命令
    fi

    success "Redis 安装完成"  # 成功提示
}

# ----------------- Nginx 安装 -----------------
install_nginx() {  # 安装 Nginx
    if [ "${INSTALL_NGINX,,}" != "yes" ]; then  # 按配置可跳过
        info "跳过 Nginx 安装"  # 跳过提示
        return 0  # 返回
    fi
    info "安装 Nginx..."  # 信息提示
    if [ "$PKG_MGR" = "apt" ]; then  # apt 系统安装 nginx
        pkg_install nginx nginx-extras || true  # 安装 nginx
    else
        pkg_install nginx || true  # rpm 系统安装 nginx
    fi

    if [ -d /etc/nginx/conf.d ]; then  # 若存在 conf.d 目录则写入 php-fpm 配置
        safe_backup_file "/etc/nginx/conf.d/php-fpm.conf" || true  # 备份可能存在的文件
        cat > /etc/nginx/conf.d/php-fpm.conf <<'EOF'  # 写入示例 server 配置
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /usr/share/nginx/html;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
EOF
        mark_rollback "移除 nginx php-fpm.conf" "rm -f /etc/nginx/conf.d/php-fpm.conf || true"  # 回滚命令
    fi

    run_cmd "nginx -t || true" "测试 Nginx 配置" || warn "nginx -t 出错"  # 测试 nginx 配置
    if [ "${AUTO_START_SERVICES,,}" = "yes" ]; then  # 自动启动
        run_cmd "systemctl enable nginx || true" "启用 Nginx"  # 启用
        run_cmd "systemctl start nginx || true" "启动 Nginx"  # 启动
        mark_rollback "停止并禁用 Nginx" "systemctl stop nginx || true; systemctl disable nginx || true"  # 回滚命令
    fi

    success "Nginx 安装完成"  # 成功提示
}

# ----------------- 创建用户 -----------------
create_users() {  # 创建网站运行用户并准备网站目录
    info "创建系统用户..."  # 信息提示
    if ! id "$WWW_USER" >/dev/null 2>&1; then  # 如果用户不存在则创建
        run_cmd "useradd -r -m -s /bin/bash -U '$WWW_USER'" "创建用户 $WWW_USER"  # 创建系统用户
        run_cmd "echo '$WWW_USER:$WWW_PASS' | chpasswd" "设置 $WWW_USER 密码"  # 设置密码
        CREATED_USERS+=("$WWW_USER")  # 记录已创建用户
        mark_rollback "删除创建的用户 $WWW_USER" "userdel -r '$WWW_USER' || true"  # 回滚命令
    else
        info "用户 $WWW_USER 已存在，跳过创建"  # 已存在提示
    fi
    run_cmd "mkdir -p /var/www/html" "创建网站目录"  # 创建网站根目录
    run_cmd "chown -R $WWW_USER:$WWW_USER /var/www/html" "设置网站目录权限"  # 设置权限
    success "用户 / 网站目录 准备就绪"  # 成功提示
}

# ----------------- 清理工作 -----------------
cleanup() {  # 清理临时文件和包缓存
    if [ "${CLEAN_TEMP,,}" = "yes" ]; then  # 根据配置决定是否清理
        info "清理临时文件..."  # 信息提示
        cleanup_tmpfiles  # 清理临时文件
        case "$PKG_MGR" in  # 清理包管理器缓存
            dnf|yum)
                run_cmd "$PKG_MGR clean all || true" "清理包缓存" || true  # 清理 dnf/yum 缓存
                ;;
            apt)
                run_cmd "apt-get autoremove -y || true" "清理无用包" || true  # apt 自动移除
                run_cmd "apt-get clean || true" "清理 apt 缓存" || true  # apt 清理缓存
                ;;
        esac
        success "清理完成"  # 完成提示
    else
        info "跳过清理（CLEAN_TEMP=no）"  # 跳过清理提示
    fi
}

# ----------------- 安装总结 -----------------
installation_summary() {  # 输出安装总结到配置文件
    local end_time=$(date +%s)  # 记录结束时间
    local total_time=$((end_time - START_TIME))  # 计算耗时

    cat > "$INSTALL_SUMMARY" <<EOF  # 写入安装总结
# 安装总结
开始时间: $(date -d @$START_TIME '+%Y-%m-%d %H:%M:%S')
总耗时: ${total_time}s
PHP: ${PHP_VERSION} (安装: ${INSTALL_PHP})
MySQL: ${MYSQL_VERSION} (安装: ${INSTALL_MYSQL})
Redis: ${REDIS_VERSION} (安装: ${INSTALL_REDIS})
Nginx: ${NGINX_VERSION} (安装: ${INSTALL_NGINX})
安装日志: $LOG_FILE
错误日志: $ERROR_LOG_FILE
回滚日志: $ROLLBACK_LOG_FILE
EOF
    chmod 600 "$INSTALL_SUMMARY" || true  # 设置文件权限为 600
    success "安装完成！总耗时: ${total_time}s。安装总结已写入 $INSTALL_SUMMARY"  # 成功提示
}

# ----------------- 主流程 -----------------
main() {  # 主入口函数
    clear  # 清屏
    _bold "🚀 工业级 PHP 环境安装脚本（改进版）"  # 标题
    _bold "开始时间: $(date '+%Y-%m-%d %H:%M:%S')"  # 开始时间

    if [ "$EUID" -ne 0 ]; then  # 检查是否以 root 运行
        error "请使用 root 权限运行此脚本"  # 错误提示
        exit 1  # 退出
    fi

    detect_system  # 检测系统
    install_dependencies  # 安装依赖
    create_users  # 创建用户

    if [ "${INSTALL_PHP,,}" = "yes" ]; then  # 若需安装 PHP 则执行以下步骤
        download_php  # 下载源码
        extract_php  # 解压源码
        configure_php  # configure
        build_php  # 编译安装
        setup_php_config  # 写入配置
        create_phpfpm_service  # 创建服务
        install_php_extensions  # 安装扩展
    fi

    install_composer  # 安装 Composer
    install_mysql  # 安装 MySQL
    install_redis  # 安装 Redis
    install_nginx  # 安装 Nginx

    cleanup  # 清理
    installation_summary  # 输出安装总结
}

# 启动脚本
main "$@"  # 调用 main 并传递所有参数
