#!/bin/bash

# =================================================================
# 企业级 PHP + MySQL + Nginx + Redis + Composer 自动化安装脚本
# 兼容原生Linux系统和国产/云厂商魔改系统
# 安装 LNMP 环境
# /bin/bash -c "$(curl -fsSL http://yoc.cn/install/linux/lnmp.sh)"
# 兼容：
#    1、Alibaba Cloud Linux 3
# =================================================================

set -euo pipefail

# 脚本配置
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="${SCRIPT_DIR}/install.log"
COMMAND_FILE="${SCRIPT_DIR}/command.log" # 所有执行的命令日志
CONFIG_FILE="${SCRIPT_DIR}/install.conf"

# 控制安装开关
CREATE_MANAGER_USER="${CREATE_MANAGER_USER:-yes}" # 是否创建管理员用户
INSTALL_PHP="${INSTALL_PHP:-yes}" # 是否安装 php
INSTALL_MYSQL="${INSTALL_MYSQL:-yes}" # 是否安装 mysql
INSTALL_NGINX="${INSTALL_NGINX:-yes}" # 是否安装 nginx
INSTALL_REDIS="${INSTALL_REDIS:-yes}" # 是否安装 redis
INSTALL_REDIS_EXT="${INSTALL_REDIS_EXT:-yes}" # 是否安装 redis 扩展
INSTALL_IMAGICK_EXT="${INSTALL_IMAGICK_EXT:-yes}" # 是否安装 imagick 扩展
INSTALL_SWOOLE_EXT="${INSTALL_SWOOLE_EXT:-yes}" # 是否安装 swoole 扩展
INSTALL_COMPOSER="${INSTALL_COMPOSER:-yes}" # 是否安装 composer

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
PROFILE_FILE="${PROFILE_FILE:-/etc/profile}" # 环境变量配置

# 初始化日志
> "$LOG_FILE"
> "$COMMAND_FILE"

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# 进度动画帧(文字之前)
SPINNER_FRAMES_BEFORE=("⠋" "⠙" "⠹" "⠸" "⠼" "⠴" "⠦" "⠧" "⠇" "⠏")
# 进度动画帧(文字之后)
SPINNER_FRAMES_AFTER=("▰▱▱▱▱▱" "▰▰▱▱▱▱" "▰▰▰▱▱▱" "▰▰▰▰▱▱" "▰▰▰▰▰▱" "▰▰▰▰▰▰" "▰▰▰▰▰▱" "▰▰▰▰▱▱" "▰▰▰▱▱▱" "▰▰▱▱▱▱")

# 回滚操作栈
ROLLBACK_ACTIONS=()

# 判断是否需要安装
# 参数: $1 - 要检查的变量值
# 返回: 0-需要安装, 1-不需要安装
need_install() {
    # 检查参数是否匹配需要安装的条件（不区分大小写）
    case "${1,,}" in
        yes|y|true|1|on) return 0 ;;  # 需要安装
        *) return 1 ;;                # 不需要安装
    esac
}

# 改进的命令执行函数 - 同时记录到命令日志和详细日志
execute_command() {
    local command="$1"
    local description="${2:-执行命令}"

    # 记录到命令日志（简洁格式）
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $command" >> "$COMMAND_FILE"

    echo "----------------------------------------" >> "$LOG_FILE"

    # 执行命令并同时输出到日志文件
    eval "$command >> \"$LOG_FILE\" 2>&1 &"

    # 使用spinner显示进度
    spinner "$!" "$description"
}

# 输出函数
error() {
    echo -e "${RED}错误：$*${NC}" | tee -a "$LOG_FILE"
    echo "[错误] $*" >> "$COMMAND_FILE"
}
warn() {
    echo -e "${YELLOW}警告：$*${NC}" | tee -a "$LOG_FILE"
    echo "[警告] $*" >> "$COMMAND_FILE"
}
info() {
    echo -e "${BLUE}信息：$*${NC}" | tee -a "$LOG_FILE"
    echo "[信息] $*" >> "$COMMAND_FILE"
}
success() {
    echo -e "${GREEN}成功：$*${NC}" | tee -a "$LOG_FILE"
    echo "[成功] $*" >> "$COMMAND_FILE"
}
step() {
    echo -e "${PURPLE} ➤ 步骤：$*${NC}" | tee -a "$LOG_FILE"
    echo "[步骤] $*" >> "$COMMAND_FILE"
}

# 安装配置
load_config() {
    step "加载安装配置..."

    # 如果存在配置文件，则加载
    if [[ -f "$CONFIG_FILE" ]]; then
        info "检查到配置文件: $CONFIG_FILE"
        # 使用source但限制权限
        if [[ $(stat -c %a "$CONFIG_FILE") -le 644 ]] && [[ $(stat -c %U "$CONFIG_FILE") == "root" ]]; then
            source "$CONFIG_FILE"
            echo "配置文件加载完成" >> "$COMMAND_FILE"
        else
            error "配置文件权限不安全，跳过加载"
        fi
    else
        info "未找到配置文件，使用默认配置"
    fi
}

# 改进的spinner函数
spinner() {
    local pid=$1
    local message=$2
    local i=0
    local j=0

    # 显示初始状态
    printf "\r${SPINNER_FRAMES_BEFORE[i]} ${message} ${SPINNER_FRAMES_AFTER[i]}"

    while kill -0 "$pid" 2>/dev/null; do
        i=$(( (i+1) % ${#SPINNER_FRAMES_BEFORE[@]} ))
        j=$(( (j+1) % ${#SPINNER_FRAMES_AFTER[@]} ))
        printf "\r%s %s %s" ${SPINNER_FRAMES_BEFORE[i]} "$message" "${SPINNER_FRAMES_AFTER[j]}"
        sleep 0.1 # 100毫秒/帧
    done

    # 检查进程退出状态
    wait "$pid"
    local exit_code=$?

    if [ $exit_code -eq 0 ]; then
        printf "\r✓ ${message}完成！\n"
    else
        printf "\r✗ ${message}失败！\n"
        return $exit_code
    fi
}

# 安全的后台任务执行函数
run_background_task() {
    local command="$1"
    local message="$2"

    # 记录命令
    echo "[后台任务] $command" >> "$COMMAND_FILE"
    echo "=== 后台任务: $message ===" >> "$LOG_FILE"
    echo "命令: $command" >> "$LOG_FILE"

    # 执行后台任务
    eval "$command >> \"$LOG_FILE\" 2>&1 &"
    local pid=$!

    # 使用spinner显示进度
    spinner "$pid" "$message"
    return $?
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

handle_exit() {
    if [ $? -ne 0 ]; then
        echo "非正常退出，执行回滚..." | tee -a "$LOG_FILE"
        rollback_changes
    else
        success "所有组件安装结束（需要进行一次重启）！"  | tee -a "$LOG_FILE"
        echo "==========================================" | tee -a "$LOG_FILE"
    fi
}

# 安全的回滚函数 - 不使用eval
rollback_changes() {
    info "开始执行回滚操作..."

    # 逆序执行回滚操作
    for ((i=${#ROLLBACK_ACTIONS[@]}-1; i>=0; i--)); do
        local action="${ROLLBACK_ACTIONS[i]}"
        execute_command "$action >> \"$LOG_FILE\" 2>&1 &"
    done

    success "回滚操作完成"
}

# 添加回滚操作
add_rollback() {
    ROLLBACK_ACTIONS+=("$1")
    echo "添加回滚操作: $1" >> "$COMMAND_FILE"
}

# 对齐打印信息并输入到日志中
print_aligned() {
    # %26s 表示右对齐，总宽度24字符（不含冒号）
    printf "%26s: %s\n" "$1" "$2" | tee -a "$LOG_FILE"
}

# 显示系统信息
show_system_info() {

    # 显示脚本信息
    cat << "EOF" | tee -a "$LOG_FILE"
 __  __      ____    ____        ____    ____
/\ \/\ \    / __ \  / ___\      / ___\  / __ \
\ \ \_\ \  /\ \_\ \/\ \___  __ /\ \__/ /\ \/\ \
 \ \____ \ \ \____/\ \____\/\_\\ \____\\ \_\ \_\
  \/___/\ \ \/___/  \/____/\/_/ \/____/ \/_/\/_/
     /\___/
     \/__/

   企业级LNMP环境自动化安装脚本:yoc.cn
EOF

    echo "==========================================" | tee -a "$LOG_FILE"
    echo "开始时间: $(date '+%Y-%m-%d %H:%M:%S')" | tee -a "$LOG_FILE"
    # 加载配置
    load_config

    # 创建安装目录
    execute_command "mkdir -p $INSTALL_DIR" "创建安装目录"

    step "检测系统信息" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"
    echo "操作系统: $(cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d '\"')"
    echo "内核版本: $(uname -r)" | tee -a "$LOG_FILE"
    echo "系统架构: $(uname -m)" | tee -a "$LOG_FILE"
    echo "主机名称: $(hostname)" | tee -a "$LOG_FILE"
    echo "  IP地址: $(hostname -I 2>/dev/null | awk '{print $1}' || ip addr show | grep inet | grep -v 127.0.0.1 | head -1 | awk '{print $2}')" | tee -a "$LOG_FILE"
    echo "内存大小: $(free -h | grep Mem | awk '{print $2}')" | tee -a "$LOG_FILE"
    echo "磁盘空间: $(df -h / | tail -1 | awk '{print $4}') 可用" | tee -a "$LOG_FILE"
    echo "当前用户: $(whoami)" | tee -a "$LOG_FILE"
    echo "安装目录: $INSTALL_DIR" | tee -a "$LOG_FILE"
    echo "日志文件: $LOG_FILE" | tee -a "$LOG_FILE"
    echo "命令日志: $COMMAND_FILE" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"

}

# 显示安装配置
show_install_config() {
    step "安装配置信息" | tee -a "$LOG_FILE"

    echo "==========================================" | tee -a "$LOG_FILE"
    if need_install "$INSTALL_PHP"; then
        print_aligned "PHP 版本" "$PHP_VERSION"
        print_aligned "PHP 安装路径" "$PHP_PREFIX"
    fi

    if need_install "$INSTALL_REDIS_EXT"; then
        print_aligned "PHP Redis扩展版本" "$REDIS_PHP_EXT_VERSION"
    fi

    if need_install "$INSTALL_IMAGICK_EXT"; then
        print_aligned "PHP Imagick扩展版本" "$IMAGICK_PHP_EXT_VERSION"
    fi

    if need_install "$INSTALL_SWOOLE_EXT"; then
        print_aligned "PHP Swoole扩展版本" "$SWOOLE_PHP_EXT_VERSION"
    fi

    if need_install "$INSTALL_MYSQL"; then
        print_aligned "MySQL 版本" "$MYSQL_VERSION"
        print_aligned "MySQL 安装路径" "$MYSQL_PREFIX"
        print_aligned "MySQL Root密码" "$MYSQL_ROOT_PASSWORD"
        print_aligned "MySQL 远程用户" "$MYSQL_REMOTE_ADMIN_USER"
        print_aligned "MySQL 远程用户密码" "$MYSQL_REMOTE_ADMIN_PASSWORD"
    fi

    if need_install "$INSTALL_NGINX"; then
        print_aligned "Nginx 版本" "$NGINX_VERSION"
        print_aligned "Nginx 安装路径" "$NGINX_PREFIX"
    fi

    if need_install "$INSTALL_REDIS"; then
        print_aligned "Redis 版本" "$NGINX_PREFIX"
        print_aligned "Redis 安装路径" "$REDIS_PREFIX"
        print_aligned "Redis 密码" "$REDIS_PASSWORD"
    fi

    if need_install "$CREATE_MANAGER_USER"; then
        print_aligned "Web用户" "$WWW_USER:$WWW_GROUP"
        print_aligned "Web用户密码" "$WWW_PASSWORD"
    fi

    echo "==========================================" | tee -a "$LOG_FILE"

}

# 检查并配置SWAP
configure_swap() {
    step "检查SWAP空间..."

    if command -v yum &> /dev/null; then
        execute_command "yum install -y bc" "安装bc依赖"
    elif command -v dnf &> /dev/null; then
        execute_command "dnf install -y bc" "安装bc依赖"
    fi

    local current_swap
    current_swap=$(free -g | grep Swap | awk '{print $2}')

    if [[ $current_swap -lt 2 ]]; then
        info "当前SWAP空间不足2GB，正在配置${SWAP_SIZE}的SWAP..."

        # 检查是否已存在swap文件
        if [[ -f /swapfile ]]; then
            warn "已存在swap文件，先删除旧文件"
            execute_command "swapoff /swapfile 2>/dev/null || true" "禁用旧SWAP"
            execute_command "rm -f /swapfile" "删除旧SWAP文件"
        fi

        # 计算SWAP大小
        local swap_size_bytes
        swap_size_bytes=$(echo "${SWAP_SIZE//[!0-9]/} * 1024 * 1024" | bc)

        # 创建swap文件
        run_background_task "dd if=/dev/zero of=/swapfile bs=1024 count=$swap_size_bytes" "创建SWAP文件"

        execute_command "chmod 600 /swapfile" "设置SWAP文件权限"
        run_background_task "mkswap /swapfile" "格式化SWAP文件"
        run_background_task "swapon /swapfile" "启用SWAP"

        # 添加到fstab
        execute_command "echo '/swapfile swap swap defaults 0 0' >> /etc/fstab" "配置SWAP开机启动"

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
        postgresql postgresql-devel
        c-ares-devel
        libonig-dev
    )

    # 检测包管理器
    local pkg_manager
    if command -v yum &> /dev/null; then
        pkg_manager="yum"
    elif command -v dnf &> /dev/null; then
        pkg_manager="dnf"
    elif command -v apt &> /dev/null; then
        pkg_manager="apt"
        # 转换包名为apt格式
        common_packages=($(echo "${common_packages[@]}" | sed 's/-devel/-dev/g'))
    else
        error "不支持的包管理器"
        return 1
    fi

    # 更新包管理器
    if [[ $pkg_manager == "yum" || $pkg_manager == "dnf" ]]; then
        run_background_task "$pkg_manager update -y --allowerasing" "更新包管理器"
    elif [[ $pkg_manager == "apt" ]]; then
        execute_command "apt update -y" "更新包管理器"
    fi

    if [[ $pkg_manager == "yum" || $pkg_manager == "dnf" ]]; then
        if execute_command "$pkg_manager install -y --allowerasing epel-release" "安装 EPEL"; then
            echo "成功安装: epel-release" >> "$LOG_FILE"
        else
            warn "安装 EPEL 失败epel-release，尝试备用方式安装 EPEL..." >> "$LOG_FILE"
            install_epel_fallback
        fi
    elif [[ $pkg_manager == "apt" ]]; then
        execute_command "apt install -y upgrade" "升级系统"
    fi


    # 安装开发工具组
    if [[ $pkg_manager == "yum" || $pkg_manager == "dnf" ]]; then
        run_background_task "$pkg_manager groupinstall -y 'Development Tools'" "安装开发工具组"
    elif [[ $pkg_manager == "apt" ]]; then
        execute_command "apt install -y build-essential" "安装开发工具"
    fi

    # 安装依赖包
    for package in "${common_packages[@]}"; do
        if execute_command "$pkg_manager install -y --allowerasing $package" "安装$package"; then
            echo "成功安装: $package" >> "$COMMAND_FILE"
        else
            warn "安装$package失败，继续..."
            echo "安装失败: $package" >> "$COMMAND_FILE"

            check_fail_package "$package" "$pkg_manager"
        fi
    done

    success "依赖包安装完成"
}

# 检查安装的失败依赖包
check_fail_package(){
    local command="${1,,}"
    local pkg="${2:-dnf}"

    if [[ $command == "oniguruma-devel" ]]; then
        warn "尝试重新安装oniguruma-devel..."
        # 清理缓存并重新搜索
        execute_command "sudo $pkg clean all" "清理缓存"
        execute_command "sudo $pkg makecache" "重新搜索"
        execute_command "sudo $pkg config-manager --set-enabled crb" "启用 PowerTools/CRB 仓库"
        execute_command "sudo $pkg install -y oniguruma-devel" "再次尝试安装 oniguruma"
    fi
}
install_epel_fallback() {
    local el_ver
    el_ver=$(get_el_version)
    if ! [[ "$el_ver" =~ ^(7|8|9)$ ]]; then
        echo "错误：无法检测EL版本。$el_ver" >&2
        exit 1
    fi

    # 镜像源按优先级排列
    local mirrors=(
        "https://mirrors.aliyun.com/epel/epel-release-latest-$el_ver.noarch.rpm"
        "https://mirrors.tuna.tsinghua.edu.cn/epel/epel-release-latest-$el_ver.noarch.rpm"
        "https://dl.fedoraproject.org/pub/epel/epel-release-latest-$el_ver.noarch.rpm"
    )

    local pkg_url
    for url in "${mirrors[@]}"; do
        if curl -sf --connect-timeout 5 --max-time 10 "$url" >/dev/null 2>&1; then
            pkg_url="$url"
            break
        fi
    done

    if [ -z "$pkg_url" ]; then
        echo "错误：阿里云、清华Tuna镜像和Fedora 官方都无法访问。请检查网络。" >&2
        exit 1
    fi

    # 安装
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

get_el_version() {
    local os_name os_version_id os_version content line key value

    # 1. 优先解析 /etc/os-release
    if [ -f /etc/os-release ]; then
        while IFS= read -r line; do
            [[ "$line" =~ ^[[:space:]]*# ]] || [[ -z "$line" ]] && continue
            [[ "$line" != *=* ]] && continue

            key="${line%%=*}"; key="${key#"${key%%[![:space:]]*}"}"; key="${key%"${key##*[![:space:]]}"}"
            value="${line#*=}"; value="${value#"${value%%[![:space:]]*}"}"; value="${value%"${value##*[![:space:]]}"}"
            [[ $value == \"*\" ]] && value="${value#\"}${value%\"}" || [[ $value == \'*\' ]] && value="${value#\'}${value%\'}"

            case "$key" in
                NAME) os_name="$value" ;;
                VERSION_ID) os_version_id="$value" ;;
                VERSION) os_version="$value" ;;
                PLATFORM_ID)
                    [[ "$value" == platform:el[0-9]* ]] && { echo "${value#platform:el}"; return 0; }
                    ;;
            esac
        done < /etc/os-release
    fi

    # 2. 检查常见 *-release 文件
    for f in redhat centos alinux anolis tencentos kylin uos; do
        [ -f "/etc/${f}-release" ] || continue
        content=$(cat "/etc/${f}-release")
        [[ "$content" =~ [^0-9](7|8|9)([^0-9]|$) ]] && { echo "${BASH_REMATCH[1]}"; return 0; }
    done

    # 3. 构建 full_id 并匹配发行版规则
    local full_id=""
    [ -n "$os_name" ] && full_id="$os_name ${os_version_id:-${os_version:-}}"

    local -A map=(
        ["Alibaba Cloud Linux 3"]="8" ["Alibaba Cloud Linux 4"]="9" ["Alibaba Cloud Linux 5"]="9"
        ["Anolis OS 8"]="8" ["Anolis OS 23"]="9" ["OpenAnolis 8"]="8" ["OpenAnolis 23"]="9"
        ["TencentOS Server 3.1"]="8" ["TencentOS Server 3.2"]="9" ["TencentOS Server 4"]="9"
        ["Kylin V10"]="9" ["Kylin Linux Advanced Server V10"]="9"
        ["UnionTech OS Server 20"]="8" ["UOS Server 20"]="8"
        ["CentOS Linux 7"]="7" ["CentOS Linux 8"]="8" ["CentOS Stream 8"]="8" ["CentOS Stream 9"]="9"
        ["Rocky Linux 8"]="8" ["Rocky Linux 9"]="9"
        ["AlmaLinux 8"]="8" ["AlmaLinux 9"]="9"
        ["Red Hat Enterprise Linux 7"]="7" ["Red Hat Enterprise Linux 8"]="8" ["Red Hat Enterprise Linux 9"]="9"
        ["Oracle Linux 7"]="7" ["Oracle Linux 8"]="8" ["Oracle Linux 9"]="9"
        ["OpenCloudOS 8"]="8" ["OpenCloudOS 9"]="9"
        ["CloudOS 8"]="8" ["CloudOS 9"]="9"
    )

    for distro in "${!map[@]}"; do
        [[ "$full_id" == *"$distro"* ]] && { echo "${map[$distro]}"; return 0; }
    done

    # 4. 直接使用纯数字 VERSION_ID（7-10）
    [[ "$os_version_id" =~ ^[7-9]$|^10$ ]] && { echo "$os_version_id"; return 0; }

    # 5. glibc 版本判断
    local glibc_ver major minor lib
    if ! glibc_ver=$(ldd --version 2>/dev/null | head -n1 | grep -o '[0-9]\+\.[0-9]\+' | head -n1); then
        for lib in /lib64/libc.so.6 /lib/x86_64-linux-gnu/libc.so.6 /lib/libc.so.6; do
            [ -f "$lib" ] && glibc_ver=$("$lib" --version 2>/dev/null | head -n1 | grep -o '[0-9]\+\.[0-9]\+' | head -n1) && break
        done
    fi

    if [ -n "$glibc_ver" ]; then
        major="${glibc_ver%%.*}"; minor="${glibc_ver#*.}"
        [ "$major" = 2 ] && {
            (( minor >= 34 )) && { echo "9"; return 0; }
            (( minor >= 28 )) && { echo "8"; return 0; }
            (( minor >= 17 )) && { echo "7"; return 0; }
        }
    fi

    # 6. systemd 版本
    if command -v systemctl >/dev/null; then
        local ver=$(systemctl --version 2>/dev/null | head -n1 | grep -o '[0-9]\+' | head -n1)
        [ -n "$ver" ] && {
            (( ver >= 250 )) && { echo "9"; return 0; }
            (( ver >= 230 )) && { echo "8"; return 0; }
            (( ver >= 200 )) && { echo "7"; return 0; }
        }
    fi

    # 7. 内核版本启发式
    local k=$(uname -r 2>/dev/null)
    [ -n "$k" ] && {
        [[ "$k" =~ ^6\. ]] && { echo "9"; return 0; }
        [[ "$k" =~ ^5\.([1-9][4-9]|[2-9][0-9])\. ]] && { echo "9"; return 0; }
        [[ "$k" =~ ^4\.18\. ]] && { echo "8"; return 0; }
        [[ "$k" =~ ^3\.10\. ]] && { echo "7"; return 0; }
    }

    # 8. RPM 包中的 .elX 标识
    if command -v rpm >/dev/null; then
        local pkgs=$(rpm -qa 2>/dev/null)
        [[ "$pkgs" == *".el9."* ]] && { echo "9"; return 0; }
        [[ "$pkgs" == *".el8."* ]] && { echo "8"; return 0; }
        [[ "$pkgs" == *".el7."* ]] && { echo "7"; return 0; }
    fi

    # 9. YUM repo 中的 elX 字符串
    [ -d /etc/yum.repos.d/ ] && {
        grep -rq 'baseurl.*el9' /etc/yum.repos.d/ 2>/dev/null && { echo "9"; return 0; }
        grep -rq 'baseurl.*el8' /etc/yum.repos.d/ 2>/dev/null && { echo "8"; return 0; }
        grep -rq 'baseurl.*el7' /etc/yum.repos.d/ 2>/dev/null && { echo "7"; return 0; }
    }

    return 1
}

# 创建用户和组
create_www_user() {
    if ! need_install "$CREATE_MANAGER_USER"; then
        echo "跳过 创建用户"
    fi

    step "创建Web服务用户..."

    if ! id "$WWW_USER" &>/dev/null; then
        execute_command "groupadd $WWW_GROUP" "创建用户组$WWW_GROUP"
        execute_command "useradd -g $WWW_GROUP -s /sbin/nologin -M $WWW_USER" "创建用户$WWW_USER"
        execute_command "echo '$WWW_USER:$WWW_PASSWORD' | chpasswd" "设置用户密码"

        if grep -q "^$WWW_USER.*NOPASSWD.*ALL" /etc/sudoers; then
            info "www 用户 sudo 权限已存在"
            return 0
        fi

        NOT_PASS_TIPS="# 允许 $WWW_USER 以无需密码执行所有命令"

        # 使用 tee 追加到 sudoers 文件
        {
            echo ""
            echo "$NOT_PASS_TIPS"
            echo "$WWW_USER ALL=(ALL) NOPASSWD:ALL"
        } | tee -a /etc/sudoers >/dev/null


        # 验证配置
        if visudo -c >/dev/null 2>&1; then
            success "www 用户 sudo 权限配置成功"

            # 添加回滚操作
            add_rollback "sed -i '/$WWW_USER ALL=(ALL) NOPASSWD:ALL/d' /etc/sudoers"
            add_rollback "sed -i '/$NOT_PASS_TIPS/d' /etc/sudoers"
        else
            error "sudoers 文件语法错误，回滚更改"
            sed -i '/$WWW_USER ALL=(ALL) NOPASSWD:ALL/d' /etc/sudoers
            sed -i '/$NOT_PASS_TIPS/d' /etc/sudoers
            return 1
        fi

        # 添加回滚操作
        add_rollback "userdel -r $WWW_USER 2>/dev/null; groupdel $WWW_GROUP 2>/dev/null"

        success "创建用户 $WWW_USER 完成"
    else
        info "用户 $WWW_USER 已存在"
    fi
}

# 安装PHP
install_php() {
    if ! need_install "$INSTALL_PHP"; then
        # 跳过安装
        return 0
    fi

    step "安装PHP $PHP_VERSION..."

    local php_dir="php-$PHP_VERSION"
    local php_url="https://www.php.net/distributions/php-$PHP_VERSION.tar.gz"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    # 下载PHP源码
    execute_command "wget -c $php_url -O php.tar.gz" "下载PHP源码"
    execute_command "tar -xzf php.tar.gz" "解压PHP源码"

    cd "$php_dir" || { error "无法进入PHP源码目录"; return 1; }

    # PHP编译配置
    local configure_opts=(
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
    execute_command "./configure ${configure_opts[*]}" "配置PHP编译选项"

    # 编译安装
    run_background_task "make -j\$(nproc)" "编译PHP"
    execute_command "make install" "安装PHP"

    # 创建配置目录
    execute_command "mkdir -p $PHP_PREFIX/etc/php-fpm.d" "创建PHP配置目录"

    # 复制配置文件后，添加针对低配服务器的 PHP-FPM 优化
    execute_command "cp php.ini-production $PHP_PREFIX/etc/php.ini" "复制PHP配置文件"
    execute_command "cp sapi/fpm/php-fpm.conf $PHP_PREFIX/etc/" "复制PHP-FPM配置"
    execute_command "cp sapi/fpm/www.conf $PHP_PREFIX/etc/php-fpm.d/" "复制PHP-FPM进程池配置"

    # 优化 PHP-FPM 配置 for 低配服务器
    execute_command "sed -i 's/^pm.max_children = [0-9]*/pm.max_children = 10/' $PHP_PREFIX/etc/php-fpm.d/www.conf" "设置最大子进程数"
    execute_command "sed -i 's/^pm.start_servers = [0-9]*/pm.start_servers = 2/' $PHP_PREFIX/etc/php-fpm.d/www.conf" "设置启动进程数"
    execute_command "sed -i 's/^pm.min_spare_servers = [0-9]*/pm.min_spare_servers = 1/' $PHP_PREFIX/etc/php-fpm.d/www.conf" "设置最小空闲进程"
    execute_command "sed -i 's/^pm.max_spare_servers = [0-9]*/pm.max_spare_servers = 5/' $PHP_PREFIX/etc/php-fpm.d/www.conf" "设置最大空闲进程"
    execute_command "sed -i 's/^pm.max_requests = [0-9]*/pm.max_requests = 500/' $PHP_PREFIX/etc/php-fpm.d/www.conf" "设置进程最大请求数"

    # PHP 内存限制优化
    execute_command "sed -i 's/^memory_limit = .*/memory_limit = 256M/' $PHP_PREFIX/etc/php.ini" "设置PHP内存限制"
    execute_command "sed -i 's/^max_execution_time = .*/max_execution_time = 300/' $PHP_PREFIX/etc/php.ini" "设置最大执行时间"
    execute_command "sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 120M/' $PHP_PREFIX/etc/php.ini" "设置上传文件大小限制"
    execute_command "sed -i 's/^post_max_size = .*/post_max_size = 120M/' $PHP_PREFIX/etc/php.ini" "设置POST数据大小限制"

    # 优化 OPcache 配置
    execute_command "echo 'opcache.memory_consumption=64' >> $PHP_PREFIX/etc/php.ini" "设置OPcache内存大小"
    execute_command "echo 'opcache.interned_strings_buffer=8' >> $PHP_PREFIX/etc/php.ini" "设置字符串缓冲区"
    execute_command "echo 'opcache.max_accelerated_files=4000' >> $PHP_PREFIX/etc/php.ini" "设置加速文件数量"
    execute_command "echo 'opcache.revalidate_freq=60' >> $PHP_PREFIX/etc/php.ini" "设置验证频率"
    execute_command "echo 'opcache.fast_shutdown=1' >> $PHP_PREFIX/etc/php.ini" "启用快速关闭"

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

    execute_command "chmod 644 /etc/systemd/system/php-fpm.service" "设置服务文件权限"

    if ! grep -q "${PHP_PREFIX}/bin" $PROFILE_FILE; then
        info "添加PHP到环境变量"
        cat >> $PROFILE_FILE << EOF
# PHP环境变量
export PATH="${PHP_PREFIX}/bin:\$PATH"
EOF

        # 更新环境变量，先检查文件是否存在
        if [ -f "$PROFILE_FILE" ]; then
            set +u  # 临时关闭nounset
            source $PROFILE_FILE
            set -u  # 恢复nounset（如果需要）
        fi
    fi

    # 添加回滚操作
    add_rollback "rm -rf $PHP_PREFIX; rm -f /etc/systemd/system/php-fpm.service; systemctl daemon-reload"

    success "PHP安装完成"
}

# 安装MySQL
install_mysql() {
    if ! need_install "$INSTALL_MYSQL"; then
        # 跳过安装
        return 0
    fi

    step "安装MySQL $MYSQL_VERSION..."

    # 下载MySQL Yum源
    execute_command "wget -c https://dev.mysql.com/get/mysql84-community-release-el8-1.noarch.rpm -O mysql.rpm" "下载MySQL源"

    # 安装MySQL源
    execute_command "yum localinstall -y mysql.rpm" "安装MySQL源"

    # 安装MySQL服务器
    run_background_task "yum install -y mysql-community-server" "安装MySQL服务器"

    # 启动MySQL服务
    execute_command "systemctl start mysqld" "启动MySQL服务"
    execute_command "systemctl enable mysqld" "设置MySQL开机启动"

    # 获取临时root密码
    local temp_password
    temp_password=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $NF}')

    if [[ -z "$temp_password" ]]; then
        error "无法获取MySQL临时密码"
        return 1
    fi

    # 修改root密码
    mysqladmin -u root -p"$temp_password" password "$MYSQL_ROOT_PASSWORD" >> "$LOG_FILE" 2>&1 &
    spinner $! "修改MySQL root密码"

    # 创建远程管理用户
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e \
        "CREATE USER '$MYSQL_REMOTE_ADMIN_USER'@'%' IDENTIFIED BY '$MYSQL_REMOTE_ADMIN_PASSWORD'; \
         GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_REMOTE_ADMIN_USER'@'%' WITH GRANT OPTION; \
         FLUSH PRIVILEGES;" >> "$LOG_FILE" 2>&1 &
    spinner $! "创建MySQL远程用户"

    # 调用MySQL优化
    optimize_mysql

    # 添加回滚操作
    add_rollback "systemctl stop mysqld; yum remove -y mysql-community-server; rm -f /etc/yum.repos.d/mysql-community*"

    success "MySQL安装完成"
}

optimize_mysql() {
    if ! need_install "$INSTALL_MYSQL"; then
        return 0
    fi

    step "优化MySQL配置..."

    # 创建MySQL优化配置文件
    cat > /etc/my.cnf.d/low_memory.cnf << 'EOF'
[mysqld]
# 低内存服务器优化配置
innodb_buffer_pool_size = 64M
innodb_log_buffer_size = 8M
key_buffer_size = 16M
max_connections = 50
thread_cache_size = 8
table_open_cache = 256
query_cache_size = 16M
query_cache_type = 1
tmp_table_size = 16M
max_heap_table_size = 16M
# 性能优化
innodb_flush_log_at_trx_commit = 2
sync_binlog = 0
# 连接优化
wait_timeout = 60
interactive_timeout = 60
# 禁用性能模式以减少内存使用
performance_schema = OFF
EOF

    # 重启MySQL使配置生效
    execute_command "systemctl restart mysqld" "重启MySQL服务"

    success "MySQL优化完成"
}

# 安装Redis
install_redis() {
    if ! need_install "$INSTALL_REDIS"; then
        return 0
    fi

    step "安装Redis $REDIS_VERSION..."

    local redis_url="https://github.com/redis/redis/archive/$REDIS_VERSION.tar.gz"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    # 下载Redis源码
    wget -c "$redis_url" -O redis.tar.gz >> "$LOG_FILE" 2>&1 &
    spinner $! "下载Redis源码"

    tar -xzf redis.tar.gz >> "$LOG_FILE" 2>&1
    cd "redis-$REDIS_VERSION" || { error "无法进入Redis源码目录"; return 1; }

    # 编译优化：针对低配服务器减少编译线程，避免内存不足
    local cpu_cores=$(nproc)
    local compile_jobs=$(( cpu_cores > 2 ? 2 : 1 ))  # 低配服务器最多使用2个核心编译

    make -j$compile_jobs >> "$LOG_FILE" 2>&1 &
    spinner $! "编译Redis"

    make PREFIX="$REDIS_PREFIX" install >> "$LOG_FILE" 2>&1 &
    spinner $! "安装Redis"

    # 创建配置目录
    execute_command "mkdir -p $REDIS_PREFIX/etc" "创建Redis配置目录"

    # 修改配置文件 - 针对低配服务器优化
    execute_command "cp redis.conf $REDIS_PREFIX/etc/" "复制Redis配置文件"

    # Redis 低配服务器优化配置
    execute_command "sed -i 's/^daemonize no/daemonize yes/' $REDIS_PREFIX/etc/redis.conf" "配置Redis守护进程"
    execute_command "sed -i 's/^# requirepass foobared/requirepass $REDIS_PASSWORD/' $REDIS_PREFIX/etc/redis.conf" "设置Redis密码"
    execute_command "sed -i 's/^bind 127.0.0.1/bind 0.0.0.0/' $REDIS_PREFIX/etc/redis.conf" "配置Redis监听地址"

    # 低内存优化配置
    execute_command "sed -i 's/^# maxmemory .*/maxmemory 256mb/' $REDIS_PREFIX/etc/redis.conf" "设置Redis最大内存"
    execute_command "sed -i 's/^# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' $REDIS_PREFIX/etc/redis.conf" "设置内存淘汰策略"
    execute_command "sed -i 's/^# save 900 1/save 900 1/' $REDIS_PREFIX/etc/redis.conf" "启用RDB持久化"
    execute_command "sed -i 's/^# save 300 10/save 300 10/' $REDIS_PREFIX/etc/redis.conf" "启用RDB持久化"
    execute_command "sed -i 's/^# save 60 10000/save 60 10000/' $REDIS_PREFIX/etc/redis.conf" "启用RDB持久化"

    # 性能优化配置
    execute_command "sed -i 's/^timeout 0/timeout 300/' $REDIS_PREFIX/etc/redis.conf" "设置客户端超时时间"
    execute_command "sed -i 's/^tcp-keepalive 300/tcp-keepalive 60/' $REDIS_PREFIX/etc/redis.conf" "减少TCP保活时间"
    execute_command "sed -i 's/^# maxclients 10000/maxclients 1000/' $REDIS_PREFIX/etc/redis.conf" "限制最大客户端连接数"

    # 低配服务器特定优化
    execute_command "echo 'hash-max-ziplist-entries 512' >> $REDIS_PREFIX/etc/redis.conf" "优化小哈希表内存使用"
    execute_command "echo 'hash-max-ziplist-value 64' >> $REDIS_PREFIX/etc/redis.conf" "优化小哈希表内存使用"
    execute_command "echo 'list-max-ziplist-size -2' >> $REDIS_PREFIX/etc/redis.conf" "优化列表内存使用"
    execute_command "echo 'activerehashing no' >> $REDIS_PREFIX/etc/redis.conf" "禁用主动rehashing减少CPU使用"

    # 创建优化的服务文件
    cat > /etc/systemd/system/redis.service << EOF
[Unit]
Description=Redis persistent key-value database
After=network.target
# 增加依赖关系，确保系统就绪后再启动
After=syslog.target

[Service]
Type=forking
ExecStart=$REDIS_PREFIX/bin/redis-server $REDIS_PREFIX/etc/redis.conf
ExecStop=$REDIS_PREFIX/bin/redis-cli -a $REDIS_PASSWORD shutdown
# 增加重启策略 - 针对低配服务器优化
Restart=on-failure
RestartSec=10s
# 资源限制 - 防止Redis占用过多资源
LimitNOFILE=65536
# OOM设置 - 避免因内存不足被系统杀死
OOMScoreAdjust=-100
# 超时设置
TimeoutStartSec=30
TimeoutStopSec=30
User=$WWW_USER
Group=$WWW_GROUP
# 工作目录
WorkingDirectory=$REDIS_PREFIX

[Install]
WantedBy=multi-user.target
EOF

    execute_command "chmod 644 /etc/systemd/system/redis.service" "设置服务文件权限"

    # 调用系统优化
    fix_redis

    # 设置权限
    execute_command "chown -R $WWW_USER:$WWW_GROUP $REDIS_PREFIX" "设置Redis目录权限"

    # 启动服务
    execute_command "systemctl daemon-reload" "重载系统服务"

    # 增加启动等待和重试机制
    local retry_count=0
    local max_retries=3

    while [ $retry_count -lt $max_retries ]; do
        if execute_command "systemctl start redis" "启动Redis服务(尝试 $((retry_count+1))/$max_retries)"; then
            break
        fi
        retry_count=$((retry_count+1))
        if [ $retry_count -eq $max_retries ]; then
            error "Redis服务启动失败，已达到最大重试次数"
            return 1
        fi
        warn "Redis服务启动失败，10秒后重试..."
        sleep 10
    done

    execute_command "systemctl enable redis" "设置Redis开机启动"

    # 添加回滚操作
    add_rollback "systemctl stop redis; rm -f /etc/systemd/system/redis.service; rm -rf $REDIS_PREFIX"

    success "Redis安装完成"
}

fix_redis() {
    # 检查是否为 Linux 系统（Redis 不支持 Windows/Linux 以外的 overcommit 设置）
    if [[ "$(uname)" != "Linux" ]]; then
        return 0
    fi

    # 启用内存overcommit - 避免Redis因内存不足而崩溃
    execute_command "sysctl -w vm.overcommit_memory=1" "启用内存overcommit"

    # 持久化配置：避免重启后失效
    CONF_FILE="/etc/sysctl.conf"

    # 配置系统参数 - 针对低配服务器优化
    cat >> "$CONF_FILE" << EOF
# Redis内存优化配置
vm.overcommit_memory = 1
net.core.somaxconn = 1024
vm.swappiness = 10
# 针对低内存环境的额外优化
vm.dirty_ratio = 5
vm.dirty_background_ratio = 3
vm.vfs_cache_pressure = 1000
EOF

    # 重新加载 sysctl 配置
    sysctl -p "$CONF_FILE" >/dev/null 2>&1 || sysctl -p

    # 添加回滚操作
    add_rollback "sysctl -w vm.overcommit_memory=0; sed -i '/# Redis内存优化配置/,/vm.vfs_cache_pressure = 1000/d' /etc/sysctl.conf"
}

# 安装Nginx
install_nginx() {
    if ! need_install "$INSTALL_NGINX"; then
        # 跳过安装
        return 0
    fi

    step "安装Nginx $NGINX_VERSION..."

    local nginx_url="http://nginx.org/packages/centos/8/x86_64/RPMS/nginx-$NGINX_VERSION-1.el8.ngx.x86_64.rpm"

    # 下载Nginx RPM包
    execute_command "wget -c $nginx_url -O nginx.rpm" "下载Nginx安装包"

    # 安装Nginx
    execute_command "yum localinstall -y nginx.rpm" "安装Nginx"

    # 修改Nginx配置
    execute_command "sed -i 's/user  nginx/user $WWW_USER/' /etc/nginx/nginx.conf" "配置Nginx运行用户"

# 判断  /usr/share/nginx/html/index.html 文件是否存在
if [ -f "/usr/share/nginx/html/index.html" ]; then
    sudo tee /usr/share/nginx/html/index.html > /dev/null <<'EOF'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>欢迎</title>
    <style>
        body{margin:0;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:grid;place-items:center;min-height:100vh;color:#ff4757;font-size:25px;font-weight:600;text-align:center;}.small{font-size:16px;}
    </style>
</head>
<body>
    <div>
        <div>欢迎来访!</div>
        <div class="small"><script>document.write(location.hostname || "yoc.cn")</script></div>
    </div>
</body>
</html>
EOF
fi


    # 启动服务
    execute_command "systemctl start nginx" "启动Nginx服务"
    execute_command "systemctl enable nginx" "设置Nginx开机启动"

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
    if ! need_install "$INSTALL_REDIS_EXT"; then
        # 跳过安装
        return 0
    fi

    info "安装PHP Redis扩展..."

    local ext_url="https://github.com/phpredis/phpredis/archive/$REDIS_PHP_EXT_VERSION.tar.gz"
    local ext_dir="phpredis-$REDIS_PHP_EXT_VERSION"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    execute_command "wget -c $ext_url -O phpredis.tar.gz" "下载Redis扩展"
    execute_command "tar -xzf phpredis.tar.gz" "解压Redis扩展"
    cd "$ext_dir" || { error "无法进入Redis扩展目录"; return 1; }

    execute_command "$PHP_PREFIX/bin/phpize" "准备Redis扩展编译环境"
    execute_command "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置Redis扩展"

    run_background_task "make -j\$(nproc)" "编译Redis扩展"
    execute_command "make install" "安装Redis扩展"

    # 添加到php.ini
    execute_command "echo 'extension=redis.so' >> $PHP_PREFIX/etc/php.ini" "启用Redis扩展"

    success "Redis扩展安装完成"
}

# 安装Imagick扩展
install_imagick_extension() {
    if ! need_install "$INSTALL_IMAGICK_EXT"; then
        # 跳过安装
        return 0
    fi

    info "安装PHP Imagick扩展..."

    local ext_url="https://github.com/Imagick/imagick/archive/refs/tags/$IMAGICK_PHP_EXT_VERSION.tar.gz"
    local ext_dir="imagick-$IMAGICK_PHP_EXT_VERSION"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    execute_command "wget -c $ext_url -O imagick.tar.gz" "下载Imagick扩展"
    execute_command "tar -xzf imagick.tar.gz" "解压Imagick扩展"
    cd "$ext_dir" || { error "无法进入Imagick扩展目录"; return 1; }

    execute_command "$PHP_PREFIX/bin/phpize" "准备Imagick扩展编译环境"
    execute_command "./configure --with-php-config=$PHP_PREFIX/bin/php-config" "配置Imagick扩展"

    run_background_task "make -j\$(nproc)" "编译Imagick扩展"
    execute_command "make install" "安装Imagick扩展"

    # 添加到php.ini
    execute_command "echo 'extension=imagick.so' >> $PHP_PREFIX/etc/php.ini" "启用Imagick扩展"

    success "Imagick扩展安装完成"
}

# 安装Swoole扩展
install_swoole_extension() {
    if ! need_install "$INSTALL_SWOOLE_EXT"; then
        return 0
    fi

    info "安装PHP Swoole扩展..."

    local ext_url="https://github.com/swoole/swoole-src/archive/refs/tags/v$SWOOLE_PHP_EXT_VERSION.tar.gz"
    local ext_dir="swoole-src-$SWOOLE_PHP_EXT_VERSION"

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    execute_command "wget -c $ext_url -O swoole.tar.gz" "下载Swoole扩展"
    execute_command "tar -xzf swoole.tar.gz" "解压Swoole扩展"
    cd "$ext_dir" || { error "无法进入Swoole扩展目录"; return 1; }

    execute_command "$PHP_PREFIX/bin/phpize" "准备Swoole扩展编译环境"

    # 针对低配服务器的编译优化配置
    local configure_opts=(
        "--with-php-config=$PHP_PREFIX/bin/php-config"
        "--enable-openssl"
        "--enable-sockets"
        "--enable-mysqlnd"
        "--enable-swoole-curl"
        "--enable-cares"
        "--enable-swoole-pgsql"
    )

    execute_command "./configure ${configure_opts[*]}" "配置Swoole扩展"

    # 低配服务器编译优化：减少并行编译任务，避免内存不足
    local cpu_cores=$(nproc)
    local compile_jobs=$(( cpu_cores > 3 ? 3 : 1 ))

    run_background_task "make -j$compile_jobs" "编译Swoole扩展"
    execute_command "make install" "安装Swoole扩展"

    # 添加到php.ini并配置优化参数
    execute_command "echo 'extension=swoole.so' >> $PHP_PREFIX/etc/php.ini" "启用Swoole扩展"

    # Swoole 低配服务器优化配置
    execute_command "echo 'swoole.use_shortname = Off' >> $PHP_PREFIX/etc/php.ini" "禁用Swoole短名称"
    execute_command "echo 'swoole.enable_coroutine = On' >> $PHP_PREFIX/etc/php.ini" "启用协程支持"
    execute_command "echo 'swoole.display_errors = On' >> $PHP_PREFIX/etc/php.ini" "显示错误信息"

    success "Swoole扩展安装完成"
}

# 安装Composer
install_composer() {
    if ! need_install "$INSTALL_COMPOSER"; then
        # 跳过安装
        return 0
    fi

    step "安装Composer..."

    cd "$INSTALL_DIR" || { error "无法进入安装目录"; return 1; }

    # 下载Composer
    execute_command "php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\"" "下载Composer安装器"

    execute_command "$PHP_PREFIX/bin/php composer-setup.php --install-dir=/usr/local/bin --filename=composer" "安装Composer"
    execute_command "php -r \"unlink('composer-setup.php');\"" "清理Composer安装文件"

    success "Composer安装完成"
}

# 添加系统资源检查函数
check_system_resources() {
    step "检查系统资源..."

    local total_memory
    total_memory=$(free -m | awk '/^Mem:/{print $2}')
    local total_cores
    total_cores=$(nproc)

    info "系统内存: ${total_memory}MB"
    info "CPU核心: ${total_cores}"

    # 低内存警告
    if [ "$total_memory" -lt 1024 ]; then
        warn "系统内存较低(小于1GB)，将启用极限优化模式"
        # 设置低内存标志
        export LOW_MEMORY_MODE=1
    fi

    # 单核CPU警告
    if [ "$total_cores" -eq 1 ]; then
        warn "单核CPU系统，编译过程可能较慢"
        export SINGLE_CORE_MODE=1
    fi
}

# 配置服务
configure_services() {
    step "配置系统服务..."

    if need_install "$INSTALL_MYSQL"; then
        # 重启Mysql
        execute_command "systemctl restart mysqld" "重启Mysql服务"
    fi

    if need_install "$INSTALL_PHP"; then
        # 启动PHP-FPM
        execute_command "systemctl daemon-reload" "重载系统服务"
        execute_command "systemctl start php-fpm" "启动PHP-FPM服务"
        execute_command "systemctl enable php-fpm" "设置PHP-FPM开机启动"
    fi

    if need_install "$INSTALL_NGINX"; then
        # 重启Nginx
        execute_command "systemctl restart nginx" "重启Nginx服务"
    fi


    success "服务配置完成"
}

# 显示安装结果
show_installation_result() {
    step "安装完成！"

    # 安装配置信息
    show_install_config

    # 显示服务状态
    # echo "服务状态:" | tee -a "$LOG_FILE"
    # execute_command "systemctl status php-fpm --no-pager" "PHP-FPM状态"
    # execute_command "systemctl status nginx --no-pager" "Nginx状态"
    # execute_command "systemctl status mysqld --no-pager" "MySQL状态"
    # execute_command "systemctl status redis --no-pager" "Redis状态" # 此行容易导致运行中止

    # 显示关键文件位置
    echo "关键文件位置:" | tee -a "$LOG_FILE"
    if need_install "$INSTALL_PHP"; then
        echo "PHP配置文件: $PHP_PREFIX/etc/php.ini" | tee -a "$LOG_FILE"
        echo "PHP-FPM配置: $PHP_PREFIX/etc/php-fpm.conf" | tee -a "$LOG_FILE"
    fi
    if need_install "$INSTALL_MYSQL"; then
        echo "MySQL配置: /etc/my.cnf" | tee -a "$LOG_FILE"
    fi
    if need_install "$INSTALL_NGINX"; then
        echo "Nginx配置: /etc/nginx/nginx.conf" | tee -a "$LOG_FILE"
    fi
    if need_install "$INSTALL_REDIS"; then
        echo "Redis配置: $REDIS_PREFIX/etc/redis.conf" | tee -a "$LOG_FILE"
    fi

    echo "安装日志: $LOG_FILE" | tee -a "$LOG_FILE"
    echo "命令日志: $COMMAND_FILE" | tee -a "$LOG_FILE"

    echo "==========================================" | tee -a "$COMMAND_FILE"
    echo "查看状态：systemctl status php-fpm|redis|nginx|mysqld " | tee -a "$COMMAND_FILE"
    echo "启动服务：systemctl start php-fpm|redis|nginx|mysqld " | tee -a "$COMMAND_FILE"
    echo "关闭服务：systemctl stop php-fpm|redis|nginx|mysqld " | tee -a "$COMMAND_FILE"
    echo "开机启动：systemctl enable php-fpm|redis|nginx|mysqld " | tee -a "$COMMAND_FILE"

    success "LNMP环境安装完成(安装项 均已配置开机启动)！" | tee -a "$LOG_FILE"
}

# 主安装函数
main() {
    clear # 清屏

    # 设置错误处理
    trap 'handle_error $LINENO "$BASH_COMMAND"' ERR
    trap 'handle_signal' INT TERM
    trap 'handle_exit' EXIT

    # 检查root权限
    if [[ $EUID -ne 0 ]]; then
        error "请使用root权限运行此脚本"
        exit 1
    fi

    echo "==========================================" | tee -a "$LOG_FILE"

    # 安装提示
    show_system_info
    # 系统资源检查
    check_system_resources
    show_install_config

    read -p "是否继续安装？(y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        info "安装已取消"
        exit 0
    fi

    # 检查并配置SWAP
    configure_swap

    # 执行安装步骤
    install_dependencies
    create_www_user
    install_php
    install_mysql
    install_redis
    install_nginx
    install_php_extensions
    install_composer

    configure_services
    show_installation_result

    echo "安装完成时间: $(date)" | tee -a "$LOG_FILE"
}

# 执行主函数
main "$@"
