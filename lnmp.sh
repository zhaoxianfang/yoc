#!/bin/bash

# =================================================================
# 企业级 PHP + MySQL + Nginx + Redis + Composer 自动化安装脚本
# 兼容原生Linux系统和国产/云厂商魔改系统
# 安装 LNMP 环境
# /bin/bash -c "$(curl -fsSL http://yoc.cn/install/linux/lnmp.sh)"
# 兼容：
#    1、Alibaba Cloud Linux 3
#    2、Alibaba Cloud Linux 4
# =================================================================

set -euo pipefail

# 脚本配置
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="${SCRIPT_DIR}/install.log"
COMMAND_FILE="${SCRIPT_DIR}/command.log" # 所有执行的命令日志
CONFIG_FILE="${SCRIPT_DIR}/install.conf"

# 控制安装开关
CREATE_MANAGER_USER="${CREATE_MANAGER_USER:-yes}" # 是否创建管理员用户
WWW_DIR="${WWW_DIR:-/www}" # 是否创建/www网站运行目录
INSTALL_PHP="${INSTALL_PHP:-yes}" # 是否安装 php
INSTALL_MYSQL="${INSTALL_MYSQL:-yes}" # 是否安装 mysql
INSTALL_NGINX="${INSTALL_NGINX:-yes}" # 是否安装 nginx
INSTALL_REDIS="${INSTALL_REDIS:-yes}" # 是否安装 redis
INSTALL_REDIS_EXT="${INSTALL_REDIS_EXT:-yes}" # 是否安装 redis 扩展
INSTALL_IMAGICK_EXT="${INSTALL_IMAGICK_EXT:-yes}" # 是否安装 imagick 扩展
INSTALL_SWOOLE_EXT="${INSTALL_SWOOLE_EXT:-yes}" # 是否安装 swoole 扩展
INSTALL_COMPOSER="${INSTALL_COMPOSER:-yes}" # 是否安装 composer
DEPLOY_NGINX_DOMAIN="${DEPLOY_NGINX_DOMAIN:-yoc.cn}" # 是否部署 网站 域名解析文件,不带 www

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

# 脚本内部调度变量
SERVER_IP="${SERVER_IP:-}" # 服务器IP地址
DOMAIN="${DOMAIN:-}" # 服务器部署域名
IS_FINISH="${DOMAIN:-no}" # 是否完成安装

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
# 检查参数
# 使用示例
# HOST="${HOST:-}"
# if host=$(check_param "$HOST"); then
#     echo "配置主机地址: $host"
# fi
check_param() {
    local value="$1"
    if [ -n "$value" ]; then
        echo "$value"
        return 0
    else
        return 1
    fi
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

# 改进的spinner函数 - 加强使用和展示
spinner() {
    local pid=$1
    local message=$2
    local i=0
    local j=0
    local start_time
    start_time=$(date +%s)

    # 显示初始状态
    printf "\r${SPINNER_FRAMES_BEFORE[i]} ${message} ${SPINNER_FRAMES_AFTER[i]} (0s)" >&2

    while kill -0 "$pid" 2>/dev/null; do
        i=$(( (i+1) % ${#SPINNER_FRAMES_BEFORE[@]} ))
        j=$(( (j+1) % ${#SPINNER_FRAMES_AFTER[@]} ))
        local current_time
        current_time=$(date +%s)
        local elapsed=$((current_time - start_time))
        printf "\r%s %s %s (%ds)" "${SPINNER_FRAMES_BEFORE[i]}" "$message" "${SPINNER_FRAMES_AFTER[j]}" "$elapsed" >&2
        sleep 0.1 # 100毫秒/帧
    done

    # 检查进程退出状态
    wait "$pid"
    local exit_code=$?
    local end_time
    end_time=$(date +%s)
    local total_time=$((end_time - start_time))

    # 清除当前行并重新输出结果
    if [ $exit_code -eq 0 ]; then
        printf "\r\033[K✓ ${message}完成！(%ds)\n" "$total_time" >&2
        echo "命令执行成功: $message (耗时: ${total_time}s)" >> "$LOG_FILE"
        return 0
    else
        printf "\r\033[K✗ ${message}失败！(%ds)\n" "$total_time" >&2
        echo "命令执行失败: $message (退出码: $exit_code, 耗时: ${total_time}s)" >> "$LOG_FILE"
        return $exit_code
    fi
}

# 修改文件
# modify_file <文件名> "查找|替换|模式" "查找2|替换2|模式2" ...
# 模式(不区分大小写):
#   - 空或replace: 替换整行
#   - insert: 在匹配行后插入
#   - prepend: 在匹配行开头插入
#   - comment: 进行注释
#   - uncomment: 取消注释
# eg: "target|inserted_line|INSERT" 单行插入
# 查找和替换中的 \n 表示换行
# listen.* 表示查找listen 开头的字符串
# 查找 为空表示文件结尾插入；eg "|string"
# 替换 为空表示删除；eg "string|"

modify_file() {
    local file="$1"
    shift

    # 检查文件权限，无写权限时静默退出
    [[ -f "$file" ]] || touch "$file"
    [[ -w "$file" ]] || return 1

    # 创建临时文件，失败时退出
    local temp_file=$(mktemp) && cp "$file" "$temp_file" || return 1

    # 处理每个操作参数
    for operation in "$@"; do
        IFS='|' read -r target handle mode <<< "$operation"

        # 清理参数：去除首尾空格并处理多行内容
        target=$(printf "%b" "${target#"${target%%[![:space:]]*}"}")  # 去除头部空格
        target=$(printf "%b" "${target%"${target##*[![:space:]]}"}")  # 去除尾部空格
        # handle=$(printf "%b" "${handle#"${handle%%[![:space:]]*}"}")  # 去除头部空格
        handle=$(printf "%b" "${handle%"${handle##*[![:space:]]}"}")  # 去除尾部空格
        mode=$(echo "${mode^^}")  # 转换为大写统一处理

        # 转义函数：处理正则表达式特殊字符，同时保留\$的字面意义
        escape_regex() {
            echo "$1" | sed -e 's/[][\/\.*^+?{}|()]/\\&/g' -e 's/\\\\\$/\$/g'
        }

        local escaped_target=$(escape_regex "$target")
        local is_target_multiline=$(echo "$target" | grep -q $'\n' && echo true || echo false)
        local is_handle_multiline=$(echo "$handle" | grep -q $'\n' && echo true || echo false)

        # 情况1: 文件结尾插入（target为空）
        if [[ -z "$target" && -n "$handle" ]]; then
            [[ -s "$temp_file" ]] && [[ -n "$(tail -c 1 "$temp_file")" ]] && echo "" >> "$temp_file"
            echo "$handle" >> "$temp_file"
            continue
        fi

        # 情况2: 删除操作（handle为空）
        if [[ -n "$target" && -z "$handle" ]]; then
            if [[ "$target" == *".*" ]]; then
                # 行首匹配删除：匹配以指定内容开头的行
                local line_pattern=$(escape_regex "${target%.*}")
                # 支持匹配带空格开头的内容
                sed -i "/^[[:space:]]*$line_pattern/d" "$temp_file"
            elif [[ "$is_target_multiline" == "true" ]]; then
                # 多行内容删除：使用Perl处理多行匹配
                perl -i -pe 'BEGIN{undef $/} s/\Q'"$target"'\E//g' "$temp_file"
            else
                # 单行内容删除，支持匹配带空格开头的内容
                sed -i "/[[:space:]]*$escaped_target/d" "$temp_file"
            fi
            continue
        fi

        # 情况3: 替换/插入操作（target和handle都不为空）
        if [[ -n "$target" && -n "$handle" ]]; then
            # 检查是否为行首匹配模式
            local is_line_match=false
            local line_pattern=""
            if [[ "$target" == *".*" ]]; then
                is_line_match=true
                line_pattern=$(escape_regex "${target%.*}")
            fi

            case "$mode" in
                "INSERT")
                    if [[ "$is_line_match" == true ]]; then
                        # 行首匹配后插入，支持匹配带空格开头的内容
                        if [[ "$is_handle_multiline" == "true" ]]; then
                            # 多行插入：逐行处理
                            echo "$handle" | sed '1!s/^/\\&/' | xargs -I {} sed -i "/^[[:space:]]*$line_pattern/a{}" "$temp_file"
                        else
                            sed -i "/^[[:space:]]*$line_pattern/a$handle" "$temp_file"
                        fi
                    else
                        # 普通内容后插入，支持匹配带空格开头的内容
                        if [[ "$is_target_multiline" == "true" ]]; then
                            # 多行匹配后插入
                            perl -i -pe 's/\Q'"$target"'\E/'"$target\\n$handle"'/g' "$temp_file"
                        else
                            # 单行匹配后插入
                            if [[ "$is_handle_multiline" == "true" ]]; then
                                echo "$handle" | sed '1!s/^/\\&/' | xargs -I {} sed -i "/[[:space:]]*$escaped_target/a{}" "$temp_file"
                            else
                                sed -i "/[[:space:]]*$escaped_target/a$handle" "$temp_file"
                            fi
                        fi
                    fi
                    ;;

                "PREPEND")
                    if [[ "$is_line_match" == true ]]; then
                        # 行首匹配前插入，支持匹配带空格开头的内容
                        if [[ "$is_handle_multiline" == "true" ]]; then
                            # 多行前置：逆序插入
                            tac <<< "$handle" | sed '1!s/^/\\&/' | xargs -I {} sed -i "/^[[:space:]]*$line_pattern/i{}" "$temp_file"
                        else
                            sed -i "/^[[:space:]]*$line_pattern/i$handle" "$temp_file"
                        fi
                    else
                        # 普通内容前插入，支持匹配带空格开头的内容
                        if [[ "$is_target_multiline" == "true" ]]; then
                            # 多行匹配前插入
                            perl -i -pe 's/\Q'"$target"'\E/'"$handle\\n$target"'/g' "$temp_file"
                        else
                            # 单行匹配前插入
                            if [[ "$is_handle_multiline" == "true" ]]; then
                                tac <<< "$handle" | sed '1!s/^/\\&/' | xargs -I {} sed -i "/[[:space:]]*$escaped_target/i{}" "$temp_file"
                            else
                                sed -i "/[[:space:]]*$escaped_target/i$handle" "$temp_file"
                            fi
                        fi
                    fi
                    ;;

                "COMMENT")
                    if [[ "$is_line_match" == true ]]; then
                        # 注释行首匹配的行，支持匹配带空格开头的内容
                        sed -i "/^[[:space:]]*$line_pattern/s/^/# /" "$temp_file"
                    else
                        # 注释包含目标内容的行，支持匹配带空格开头的内容
                        if [[ "$is_target_multiline" == "true" ]]; then
                            # 多行内容注释：每行前加注释符号
                            perl -i -pe 's/\Q'"$target"'\E/'$(echo "$target" | sed 's/^/# /; s/\n/\\n# /g')'/g' "$temp_file"
                        else
                            sed -i "/[[:space:]]*$escaped_target/s/^/# /" "$temp_file"
                        fi
                    fi
                    ;;

                "UNCOMMENT")
                    if [[ "$is_line_match" == true ]]; then
                        # 取消注释行首匹配的行，支持匹配带空格开头的内容
                        sed -i "/^[[:space:]]*#\s*$line_pattern/s/^[[:space:]]*#\s*//" "$temp_file"
                    else
                        # 取消注释包含目标内容的行，支持匹配带空格开头的内容
                        sed -i "/[[:space:]]*#\s*$escaped_target/s/^[[:space:]]*#\s*//" "$temp_file"
                    fi
                    ;;

                *)  # 默认模式：REPLACE 或未指定模式
                    if [[ "$is_line_match" == true ]]; then
                        # 替换整行内容，支持匹配带空格开头的内容
                        if [[ "$is_handle_multiline" == "true" ]]; then
                            sed -i "/^[[:space:]]*$line_pattern/c\\$handle" "$temp_file"
                        else
                            sed -i "s/^[[:space:]]*$line_pattern.*/$handle/g" "$temp_file"
                        fi
                    elif [[ "$is_target_multiline" == "true" || "$is_handle_multiline" == "true" ]]; then
                        # 多行内容替换：使用Perl处理
                        perl -i -pe 'BEGIN{undef $/} s/\Q'"$target"'\E/'"$handle"'/g' "$temp_file"
                    else
                        # 单行内容替换，支持匹配带空格开头的内容
                        sed -i "s/[[:space:]]*$escaped_target/$handle/g" "$temp_file"
                    fi
                    ;;
            esac
        fi
    done

    local backup="$file.bak.$(date '+%Y%m%d%H')"
    # 1. 无变化：清理并退出
    diff -q "$file" "$temp_file" >/dev/null && { echo "[INFO] 无变化: $file"; rm -f "$temp_file"; return 1; }
    # 2. 有变化：备份原文件（如果存在）
    [[ -f "$file" ]] && cp "$file" "$backup"
    # 3. 尝试更新
    if mv "$temp_file" "$file" 2>/dev/null; then
        echo "[SUCCESS] 更新成功: $file"
    else
        echo "[ERROR] 更新失败: $file"
        [[ -f "$backup" ]] && mv "$backup" "$file" && echo "[INFO] 已恢复: $file"
    fi
    # 4. 统一清理（关键：确保中间文件被删除）
    rm -f "$backup" "$temp_file"
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
    error "脚本被中断！"
    rollback_changes
    exit 1
}

handle_exit() {
    if [ $? -ne 0 ]; then
        echo "非正常退出..." | tee -a "$LOG_FILE"
        rollback_changes
    else
        if need_install "$IS_FINISH"; then
            success "所有组件安装结束（建议进行一次重启）！"  | tee -a "$LOG_FILE"
            echo "==========================================" | tee -a "$LOG_FILE"
            read -p "是否进行重启（reboot）？(y/n): " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                info "已取消重启，请稍后自行执行重启操作！"
                exit 0
            fi
            reboot
        else
            success "安装结束"  | tee -a "$LOG_FILE"
        fi
    fi
}

# 安全的回滚函数 - 不使用eval
rollback_changes() {

  if ! need_install "$IS_FINISH"; then
      read -p "是否回滚安装程序？(y/n): " -n 1 -r
      echo
      if [[ ! $REPLY =~ ^[Yy]$ ]]; then
          info "回滚已取消"
          # 标记安装结束
          IS_FINISH='yes'
          exit 0
      fi
      info "开始执行回滚操作..."

      # 逆序执行回滚操作
      for ((i=${#ROLLBACK_ACTIONS[@]}-1; i>=0; i--)); do
          local action="${ROLLBACK_ACTIONS[i]}"
          execute_command "$action >> \"$LOG_FILE\" 2>&1 &"
      done

      success "回滚操作完成"
  fi
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

    IP=$(get_ip_with_curl)
    if domain=$(check_param "$DEPLOY_NGINX_DOMAIN"); then
        if [ -n "$domain" ]; then
            DOMAIN=$domain
        fi
    fi

    # 创建安装目录
    execute_command "mkdir -p $INSTALL_DIR" "创建安装目录"

    step "检测系统信息" | tee -a "$LOG_FILE"
    echo "==========================================" | tee -a "$LOG_FILE"
    echo "操作系统: $(cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d '\"')"
    echo "内核版本: $(uname -r)" | tee -a "$LOG_FILE"
    echo "系统架构: $(uname -m)" | tee -a "$LOG_FILE"
    echo "主机名称: $(hostname)" | tee -a "$LOG_FILE"
    echo "  内网IP: $(hostname -I 2>/dev/null | awk '{print $1}' || ip addr show | grep inet | grep -v 127.0.0.1 | head -1 | awk '{print $2}')" | tee -a "$LOG_FILE"
    echo "  公网IP: $IP" | tee -a "$LOG_FILE"
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
        if [ -n "$DOMAIN" ]; then
            print_aligned "Nginx 部署域名" "$DOMAIN"
        fi
    fi

    if need_install "$INSTALL_REDIS"; then
        print_aligned "Redis 版本" "$NGINX_PREFIX"
        print_aligned "Redis 安装路径" "$REDIS_PREFIX"
        print_aligned "Redis 密码" "$REDIS_PASSWORD"
    fi

    if need_install "$CREATE_MANAGER_USER"; then
        print_aligned "远程登录用户" "$WWW_USER:$WWW_GROUP"
        print_aligned "远程登录密码" "$WWW_PASSWORD"
    fi
    if need_install "$WWW_DIR"; then
        print_aligned "网站运行目录" "$WWW_DIR"
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
        modify_file "/etc/fstab" "|/swapfile swap swap defaults 0 0"

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
        execute_command "apt -y upgrade" "升级系统"
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

    execute_command "systemctl daemon-reload" ">>> 重新加载系统服务 <<<"

    success "依赖包安装完成"
}

# 检查安装的失败依赖包
check_fail_package(){
    local command="${1,,}"
    local pkg="${2:-dnf}"

    # 将当前路径赋值给变量
    local current_dir=$(pwd)

    if [[ $command == "oniguruma-devel" ]]; then
        warn "尝试重新安装oniguruma-devel..."
        # 清理缓存并重新搜索
        execute_command "sudo $pkg clean all" "清理缓存"
        execute_command "sudo $pkg makecache" "重新搜索"
        execute_command "sudo $pkg config-manager --set-enabled crb" "启用 PowerTools/CRB 仓库"
        execute_command "sudo $pkg install -y oniguruma-devel" "再次尝试安装 oniguruma"
    fi

    if [[ $command == "libzip-devel" ]]; then
        local libzip_version="libzip-1.11.4"
        local build_dir="$libzip_version/build"

        warn "尝试重新安装 $libzip_version"

        # 下载、编译和安装 libzip
        execute_command "wget -q https://libzip.org/download/$libzip_version.tar.gz" "下载 $libzip_version 源码"
        execute_command "tar -xzf $libzip_version.tar.gz" "解压 libzip 源码"

        # 创建构建目录并编译安装
        mkdir -p "$build_dir"
        execute_command "cd '$build_dir' && cmake .. && make -j\$(nproc) && make install" "构建编译安装 libzip"

        # 清理临时文件
        execute_command "rm -rf $libzip_version.tar.* $libzip_version" "清理 libzip 下载文件"
    fi
}
install_epel_fallback() {
    local el_ver
    el_ver=$(get_el_version)
    if ! [[ "$el_ver" =~ ^(7|8|9)$ ]]; then
        echo "错误：无法检测EL版本。$el_ver" >&2
        # exit 1
        el_ver=8
    fi
    echo "进行EPEL $el_ver 安装..." >&2

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
        echo "EPEL for EPEL $el_ver 已从镜像成功安装。"
    else
        echo "错误：EPEL安装失败。" >&2
        exit 1
    fi
}

# 获取 服务器实际的 EPEL 版本
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
        execute_command "useradd -g $WWW_GROUP $WWW_USER" "创建用户$WWW_USER"
        execute_command "echo '$WWW_USER:$WWW_PASSWORD' | chpasswd" "设置用户密码"

        if grep -q "^$WWW_USER.*NOPASSWD.*ALL" /etc/sudoers; then
            info "www 用户 sudo 权限已存在"
            return 0
        fi

        NOT_PASS_TIPS="# 允许 $WWW_USER 以无需密码执行所有命令"

        # 使用 tee 追加到 sudoers 文件
        modify_file "/etc/sudoers" "|\n$NOT_PASS_TIPS\n$WWW_USER ALL=(ALL) NOPASSWD:ALL"
        execute_command "sudo chmod 0440 /etc/sudoers" # 要求文件权限必须是440

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

create_www_dir(){
    if [ ! -d "$WWW_DIR" ]; then
        step "创建Web服务目录..."
        execute_command "mkdir -p $WWW_DIR" "创建Web服务目录"
    fi
    execute_command "chown -R $WWW_USER:$WWW_GROUP $WWW_DIR" "设置Web服务目录权限"
    execute_command "chmod -R 755 $WWW_DIR" "设置Web服务目录权限"
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

    # 环境变量配置部分
    configure_php_environment

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

    # 回滚操作 - 更精确的回滚
    add_php_rollback_actions

    # 添加回滚操作
    add_rollback "rm -rf $PHP_PREFIX; rm -f /etc/systemd/system/php-fpm.service; systemctl daemon-reload"

    success "PHP安装完成"
}

# 专门处理PHP环境变量配置
configure_php_environment() {
    info "配置PHP环境变量..."

    local php_bin_path="${PHP_PREFIX}/bin"
    local path_entry="export PATH=\"${php_bin_path}:\$PATH\""

    # 更精确的检查方式
    if ! grep -q "export PATH=.*${PHP_PREFIX}/bin" "$PROFILE_FILE" 2>/dev/null; then
        if [ -f "$PROFILE_FILE" ] && [ -w "$PROFILE_FILE" ]; then
            # 添加分隔符和注释
            echo "" >> "$PROFILE_FILE"
            echo "# PHP Environment Variables" >> "$PROFILE_FILE"
            echo "$path_entry" >> "$PROFILE_FILE"

            info "已添加PHP到环境变量文件"

            # 立即在当前会话中生效
            export PATH="${php_bin_path}:$PATH"

            # 验证是否生效
            if command -v php >/dev/null 2>&1; then
                local installed_version
                installed_version=$(${PHP_PREFIX}/bin/php -v | head -n1)
                info "PHP环境变量配置成功: $installed_version"
            else
                warn "PHP环境变量配置后验证失败，可能需要重新登录"
            fi
        else
            error "无法写入环境变量文件: $PROFILE_FILE"
            warn "请手动添加: $path_entry"
        fi
    else
        info "PHP环境变量已配置"
    fi
}

# 新增：专门的PHP回滚操作
add_php_rollback_actions() {
    info "添加PHP回滚操作..."

    # 1. 停止并禁用服务
    add_rollback "systemctl stop php-fpm 2>/dev/null || true"
    add_rollback "systemctl disable php-fpm 2>/dev/null || true"
    add_rollback "rm -f /etc/systemd/system/php-fpm.service"

    # 2. 从环境变量中移除PHP路径
    local php_bin_path="${PHP_PREFIX}/bin"
    add_rollback "sed -i '/# PHP Environment Variables/,/export PATH=.*${php_bin_path}/d' '$PROFILE_FILE' 2>/dev/null || true"
    add_rollback "sed -i '/${php_bin_path}/d' '$PROFILE_FILE' 2>/dev/null || true"

    # 3. 删除安装目录（更安全的删除）
    add_rollback "if [ -d '$PHP_PREFIX' ]; then rm -rf '$PHP_PREFIX'; fi"

    # 4. 删除源码目录
    add_rollback "if [ -d '$INSTALL_DIR/php-$PHP_VERSION' ]; then rm -rf '$INSTALL_DIR/php-$PHP_VERSION'; fi"
    add_rollback "if [ -f '$INSTALL_DIR/php.tar.gz' ]; then rm -f '$INSTALL_DIR/php.tar.gz'; fi"

    # 5. 重新加载系统服务
    add_rollback "systemctl daemon-reload"

    # 6. 从当前环境移除（如果可能）
    add_rollback "export PATH=\$(echo \$PATH | sed 's|${php_bin_path}:||g')"
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
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport"content="width=device-width, initial-scale=1.0"><title>欢迎页面</title><style>*{margin:0;padding:0;box-sizing:border-box}body{margin:0;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:flex;justify-content:center;align-items:center;min-height:100vh;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:#fff;overflow:hidden;position:relative}.bg-animation{position:absolute;top:0;left:0;width:100%;height:100%;z-index:-1}.floating-circle{position:absolute;border-radius:50%;background:rgba(255,255,255,0.05);animation:float 15s infinite ease-in-out}.circle-1{width:200px;height:200px;top:10%;left:10%;animation-delay:0s}.circle-2{width:150px;height:150px;top:60%;left:80%;animation-delay:3s}.circle-3{width:100px;height:100px;top:80%;left:20%;animation-delay:6s}@keyframes float{0%,100%{transform:translateY(0)translateX(0)}25%{transform:translateY(-20px)translateX(10px)}50%{transform:translateY(10px)translateX(-15px)}75%{transform:translateY(-15px)translateX(-10px)}}.container{text-align:center;padding:40px;background:rgba(255,255,255,0.1);backdrop-filter:blur(10px);border-radius:20px;box-shadow:0 10px 30px rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.1);max-width:500px;width:90%;animation:fadeIn 1.5s ease-out;position:relative;overflow:hidden}@keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}.welcome-text{font-size:2.8rem;font-weight:700;margin-bottom:20px;background:linear-gradient(45deg,#ff6b6b,#ffa726,#ff6b6b);-webkit-background-clip:text;background-clip:text;color:transparent;background-size:200%200%;animation:gradientShift 3s ease infinite}@keyframes gradientShift{0%{background-position:0%50%}50%{background-position:100%50%}100%{background-position:0%50%}}.hostname{font-size:1.2rem;margin-bottom:30px;color:#a5b4fc;font-weight:500}.time-container{background:rgba(15,12,41,0.7);border-radius:15px;padding:20px;margin:20px 0;box-shadow:0 5px 15px rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.1);transition:transform 0.3s ease}.time-container:hover{transform:translateY(-5px)}.date{font-size:1.5rem;font-weight:600;margin-bottom:10px;color:#ffd166}.time{font-size:2.2rem;font-weight:700;color:#06d6a0;letter-spacing:2px;text-shadow:0 0 10px rgba(6,214,160,0.5)}.seconds{font-size:1.2rem;vertical-align:super;margin-left:5px}.additional-info{display:flex;justify-content:space-around;margin-top:25px;flex-wrap:wrap}.info-box{background:rgba(255,255,255,0.08);border-radius:12px;padding:15px;margin:10px;flex:1;min-width:120px;transition:all 0.3s ease}.info-box:hover{background:rgba(255,255,255,0.15);transform:scale(1.05)}.info-label{font-size:0.9rem;color:#a5b4fc;margin-bottom:5px}.info-value{font-size:1.2rem;font-weight:600;color:#ffd166}.footer{margin-top:30px;font-size:0.9rem;color:#a5b4fc}@media(max-width:600px){.container{padding:25px}.welcome-text{font-size:2.2rem}.time{font-size:1.8rem}.date{font-size:1.3rem}}</style></head><body><div class="bg-animation"><div class="floating-circle circle-1"></div><div class="floating-circle circle-2"></div><div class="floating-circle circle-3"></div></div><div class="container"><h1 class="welcome-text">欢迎来访!</h1><div class="hostname"id="hostname"></div><div class="time-container"><div class="date"id="dateDisplay"></div><div class="time"><span id="timeDisplay"></span><span class="seconds"id="secondsDisplay"></span></div></div><div class="additional-info"><div class="info-box"><div class="info-label">星期</div><div class="info-value"id="weekdayDisplay"></div></div><div class="info-box"><div class="info-label">本月第</div><div class="info-value"id="weekOfMonthDisplay"></div></div><div class="info-box"><div class="info-label">今年第</div><div class="info-value"id="dayOfYearDisplay"></div></div></div><div class="footer">by:yoc.cn</div></div><script>const pageLoadTime=Date.now();function updateTime(){const now=new Date();const year=now.getFullYear();const month=String(now.getMonth()+1).padStart(2,'0');const day=String(now.getDate()).padStart(2,'0');document.getElementById('dateDisplay').textContent=`${year}年${month}月${day}日`;const hours=String(now.getHours()).padStart(2,'0');const minutes=String(now.getMinutes()).padStart(2,'0');const seconds=String(now.getSeconds()).padStart(2,'0');document.getElementById('timeDisplay').textContent=`${hours}:${minutes}`;document.getElementById('secondsDisplay').textContent=seconds;const weekdays=['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];document.getElementById('weekdayDisplay').textContent=weekdays[now.getDay()];const firstDayOfMonth=new Date(now.getFullYear(),now.getMonth(),1);const weekOfMonth=Math.ceil((now.getDate()+firstDayOfMonth.getDay())/7);document.getElementById('weekOfMonthDisplay').textContent=`${weekOfMonth}周`;const startOfYear=new Date(now.getFullYear(),0,0);const diff=now-startOfYear;const oneDay=86400000;const dayOfYear=Math.floor(diff/oneDay);document.getElementById('dayOfYearDisplay').textContent=`${dayOfYear}天`}updateTime();setInterval(updateTime,1000);document.getElementById('hostname').textContent=location.hostname||"yoc.cn";</script></body></html>
EOF
fi

# 判断  /usr/share/nginx/html/50x.html 文件是否存在
if [ -f "/usr/share/nginx/html/50x.html" ]; then
    sudo tee /usr/share/nginx/html/50x.html > /dev/null <<'EOF'
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport"content="width=device-width, initial-scale=1.0"><title>出错啦！</title><style>body,html{margin:0;padding:0;height:100%;width:100%;display:flex;justify-content:center;align-items:center;font-family:Arial,sans-serif;background-color:#f0f0f0;overflow:hidden}.message-box{padding:20px;position:relative;text-align:center;font-size:24px;color:#196aa8}@keyframes flash{0%{opacity:1}50%{opacity:0}100%{opacity:1}}.rect{background:linear-gradient(to left,#196aa8,#196aa8)left top no-repeat,linear-gradient(to bottom,#196aa8,#196aa8)left top no-repeat,linear-gradient(to left,#196aa8,#196aa8)right top no-repeat,linear-gradient(to bottom,#196aa8,#196aa8)right top no-repeat,linear-gradient(to left,#196aa8,#196aa8)left bottom no-repeat,linear-gradient(to bottom,#196aa8,#196aa8)left bottom no-repeat,linear-gradient(to left,#196aa8,#196AA8)right bottom no-repeat,linear-gradient(to left,#196aa8,#196aa8)right bottom no-repeat;background-size:2px 15px,20px 2px,2px 15px,20px 2px}</style></head><body><div class="message-box rect"><!--<div style="text-align: center;"><img src="{{$img}}"alt="Img"style="width: 50px;height: 50px;"></div>-->抱歉，您访问的页面暂时无法访问</div></body></html>
EOF
fi


    # 启动服务
    execute_command "systemctl start nginx" "启动Nginx服务"
    execute_command "systemctl enable nginx" "设置Nginx开机启动"

    # 添加回滚操作
    add_rollback "systemctl stop nginx; yum remove -y nginx"

    success "Nginx安装完成"

    # 配置Nginx
    configure_ngixn
}

# 配置Nginx
configure_ngixn(){
    step "配置Nginx..."
    execute_command "cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak" "备份nginx.conf"
    execute_command "cp /etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf.bak" "备份default.conf"

    sudo tee /etc/nginx/nginx.conf > /dev/null <<'EOF'
user  nginx;
# user www;
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

    if [ -f "/etc/nginx/conf.d/default.conf" ]; then
        sudo tee /etc/nginx/conf.d/default.conf > /dev/null <<'EOF'
# 监听 443 端口（HTTPS），并强制跳转到 HTTP
#server {
#    listen 443 ssl; # 监听 443 端口
#
#    # ssl_certificate /etc/nginx/conf.d/ssl/domain.crt; #(证书公钥)
#    # ssl_certificate_key /etc/nginx/conf.d/ssl/domain.key; #(证书私钥)
#    charset utf-8;
#
#    server_name $IP;
#
#    # 强制跳转到 HTTP
#    return 301 http://$host$request_uri;
#}


server {
    listen       80;
    server_name  $IP localhost;

    #access_log  /var/log/nginx/host.access.log  main;

    location / {
        root   /usr/share/nginx/html;
        index  index.html index.htm;
    }

    #error_page  404              /404.html;

    # redirect server error pages to the static page /50x.html
    #
    error_page   500 502 503 504  /50x.html;
    location = /50x.html {
        root   /usr/share/nginx/html;
    }

    # proxy the PHP scripts to Apache listening on 127.0.0.1:80
    #
    #location ~ \.php$ {
    #    proxy_pass   http://127.0.0.1;
    #}

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    #location ~ \.php$ {
    #    root           html;
    #    fastcgi_pass   127.0.0.1:9000;
    #    fastcgi_index  index.php;
    #    fastcgi_param  SCRIPT_FILENAME  /scripts$fastcgi_script_name;
    #    include        fastcgi_params;
    #}

    # deny access to .htaccess files, if Apache's document root
    # concurs with nginx's one
    #
    #location ~ /\.ht {
    #    deny  all;
    #}
}

EOF

       modify_file '/etc/nginx/conf.d/default.conf' \
        "    server_name  \$IP local.*|    server_name  $IP localhost;" \
        "#    server_name \$IP;|#    server_name $IP;"

    fi

    step "配置域名解析: $DOMAIN"
    if [ -n "$DOMAIN" ]; then
      # 将 . 替换为 _
      domain_dir="${DOMAIN//./_}"

      execute_command "mkdir -p $WWW_DIR/$domain_dir/public" "创建网站目录"

      create_www_dir

      sudo tee "$WWW_DIR/$domain_dir/public/index.php" > /dev/null <<EOF
<?php
echo "HELLO $DOMAIN";

EOF

      sudo tee "/etc/nginx/conf.d/$DOMAIN.conf" > /dev/null <<'EOF'
server {
    listen 80;
    access_log  /var/log/nginx/host.access.log  main;
    charset utf-8;

    # 域名名称
    server_name YOUR_DOMAIN www.YOUR_DOMAIN; #localhost;

    root   /www/YOUR_DOMAIN_DIR/public/;
    index  index.php index.html index.htm;

    # 不带www 的全部跳转到www 域名
    # if ($host != 'www.YOUR_DOMAIN') {
    #     return 301 https://www.$host$request_uri;
    # }

    # 方法一：自动跳转到HTTPS(可选，如果需要强制https可以添加该配置)
    #if ($server_port = 80){
    #    return 301 https://$host$request_uri;
    #}

    # 方法二：自动跳转到HTTPS(可选，如果需要强制https可以添加该配置)
    # if ($scheme = http ) {
    #     return 301 https://$host$request_uri;
    # }

    # 默认 路径
    location / {
        # # alias   /www/YOUR_DOMAIN_DIR/public/;
        # root   /www/YOUR_DOMAIN_DIR/public/;
        # index  index.php index.html index.htm;


        ################ 跨域处理 ################
        # 允许跨域的请求，可以自定义变量$http_origin，*表示所有
        add_header 'Access-Control-Allow-Origin' *;
        # 允许携带cookie请求
        add_header 'Access-Control-Allow-Credentials' 'true';
        # 允许跨域请求的方法：GET,POST,OPTIONS,PUT
        add_header 'Access-Control-Allow-Methods' 'GET,POST,OPTIONS,PUT';
        # 允许请求时携带的头部信息，*表示所有
        add_header 'Access-Control-Allow-Headers' *;
        # 允许发送按段获取资源的请求
        add_header 'Access-Control-Expose-Headers' 'Content-Length,Content-Range';
        # 一定要有！！！否则Post请求无法进行跨域！
        # 在发送Post跨域请求前，会以Options方式发送预检请求，服务器接受时才会正式请求
        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Max-Age' 1728000;
            add_header 'Content-Type' 'text/plain; charset=utf-8';
            add_header 'Content-Length' 0;
            # 对于Options方式的请求返回204，表示接受跨域请求
            return 204;
        }

        # 当上游响应的响应码'大于等于'300[常见"404"、"500"等]时; 按error_page指令处理
        proxy_intercept_errors on;
        # 创建自己的404.html页面 需要放在 nginx 的html路径下
        fastcgi_intercept_errors on;


        try_files $uri $uri/ /index.php?$query_string;

        # 文件和目录不存在的时重定向
        if (!-e $request_filename) {
          # rewrite ^(.*)$ /index.php?s=$1 last;
          rewrite ^(.*)$ /index.php last;
          break;
        }
    }

    # 资源缓存
    # location ~ .*\.(html|htm|gif|jpg|jpeg|bmp|png|ico|txt|js|css)
    # ~代表匹配时区分大小写
    # .*代表任意字符都可以出现零次或多次，即资源名不限制
    # \.代表匹配后缀分隔符.
    # (html|...|css)代表匹配括号里所有静态资源类型
    # 该配置表示匹配以.css~.webm为后缀的所有资源请求。
    location ~* ^.+\.(css|js|ico|gif|jpg|jpeg|png|gz|svg|svgz|mp4|ogg|ogv|webm)$ {
        log_not_found off;
        # 关闭日志
        access_log off;
        # 缓存时间7天
        expires 7d;
        # 源服务器
        # proxy_pass http://localhost:8888;
        # 指定上面设置的缓存区域
        proxy_cache imgcache;
        # 缓存过期管理
        proxy_cache_valid 200 302 1d;
        proxy_cache_valid 404 10m;
        proxy_cache_valid any 1h;
        proxy_cache_use_stale error timeout invalid_header updating http_500 http_502 http_503 http_504;
    }

    # 定义错误页面
    # error_page 400 401 402 403 404 405 408 410 412 413 414 415 500 501 502 503 504 505 506 @jump_to_error;
    location @jump_to_error {
    #    # return text - ok
    #    # default_type text/plain; # 文本格式
    #    # return 404 'Not Found Page...';

         # return json - ok
         default_type application/json;   # json格式
         return 200 '{"code": "500","msg": "系统出错啦!"}';
    }

    # 定义错误页面
    # error_page 400 401 402 403 404 405 408 410 412 413 414 415 500 501 502 503 504 505 506 /error.html;
    # location = /error.html {
    #    # 注意：设置了error.html 就必须在 下面的root 路径中定义一个同名的 error.html 文件
    #    # root /etc/nginx/conf.d/error/;
    #     root   /usr/share/nginx/html;
    # }

    # proxy the PHP scripts to Apache listening on 127.0.0.1:80
    #
    #location ~ \.php$ {
    #    proxy_pass   http://127.0.0.1;
    #}

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    #location ~ \.php$ {
    #    root           html;
    #    fastcgi_pass   127.0.0.1:9000;
    #    fastcgi_index  index.php;
    #    fastcgi_param  SCRIPT_FILENAME  /scripts$fastcgi_script_name;
    #    include        fastcgi_params;
    #}

    # location ~ [^/]\.php(/|$) {
    # location ~ \.php($|/) {
    # location ~* ^.+\.php($|/) {
    #     # 响应json
    #     # default_type    application/json;
    #     # return 200 '{"status":502,"msg":"服务正在升级，请稍后再试…���"}';
    #
    #     root   /data/www/YOUR_DOMAIN_DIR/default/dist/;

    #     fastcgi_pass            127.0.0.1:9000;
    #     # fastcgi_pass   unix:/run/php-fpm/php-fpm.sock;                   #跟php-fpm的监听端口一样
    #     fastcgi_split_path_info ^(.+?\.php)(/.+)$;
    #     fastcgi_param           SCRIPT_FILENAME $document_root$fastcgi_script_name;
    #     fastcgi_param           PATH_INFO       $fastcgi_path_info;
    #     fastcgi_param           PATH_TRANSLATED $document_root$fastcgi_script_name;
    #     include                 fastcgi_params;
    # }
    location ~ \.php$ {
        fastcgi_pass            127.0.0.1:9000;
        fastcgi_split_path_info ^(.+?\.php)(/.+)$;
        fastcgi_param           SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param           PATH_INFO       $fastcgi_path_info;
        fastcgi_param           PATH_TRANSLATED $document_root$fastcgi_script_name;
        include                 fastcgi_params;
    }

    # 文件和目录不存在的时重定向
    if (!-e $request_filename) {
       # rewrite ^(.*)$ /index.php?s=$1 last;
       rewrite ^(.*)$ /index.php last;
       break;
    }

    # deny access to .htaccess files, if Apache's document root
    # concurs with nginx's one
    #
    #location ~ /\.ht {
    #    deny  all;
    #}
}

EOF

      modify_file "/etc/nginx/conf.d/$DOMAIN.conf" \
       "    server_name YOUR_DOMAIN.*|    server_name $DOMAIN www.$DOMAIN; #localhost;" \
       "    root   /www/YOUR_DOMAIN_DIR.*|    root   /www/$domain_dir/public/;"

    fi

    execute_command "sudo nginx -t && sudo nginx -s reload" "重载Nginx..."
}

# 尝试使用curl获取IP
get_ip_with_curl() {
    local services=(
        "ifconfig.co"
        "ipinfo.io/ip"
        "icanhazip.com"
        "api.ipify.org"
        "checkip.amazonaws.com"
    )

    for service in "${services[@]}"; do
        ip=$(curl -s --connect-timeout 10 "$service")
        if [ -n "$ip" ] && [ "$ip" != "Could not get your IP" ]; then
            echo "$ip"
            return 0
        fi
    done
    return 1
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
    modify_file "$PHP_PREFIX/etc/php.ini" ';zend_extension.*|extension=redis.so\n|prepend'

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
    modify_file "$PHP_PREFIX/etc/php.ini" ';zend_extension.*|extension=imagick.so\n|prepend'

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
    modify_file "$PHP_PREFIX/etc/php.ini" ';zend_extension.*|extension=swoole.so\n|prepend'
    # Swoole 低配服务器优化配置
    modify_file "$PHP_PREFIX/etc/php.ini" '|\n\nswoole.use_shortname = Off\nswoole.enable_coroutine = On\nswoole.display_errors = On'

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

    success "LNMP环境安装完成 (安装项 均已配置开机启动)！" | tee -a "$LOG_FILE"
}

# 主安装函数
main() {
    clear # 清屏
    # 标记安装未完成
    IS_FINISH='no'

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
        # 标记安装结束
        IS_FINISH='yes'
        exit 0
    fi

    # 检查并配置SWAP
    configure_swap

    # 执行安装步骤
    install_dependencies
    create_www_user
    create_www_dir
    install_php
    install_mysql
    install_redis
    install_nginx
    install_php_extensions
    install_composer

    configure_services
    show_installation_result

    echo "安装完成时间: $(date)" | tee -a "$LOG_FILE"

    # 标记安装结束
    IS_FINISH='yes'
}

# 执行主函数
main "$@"
