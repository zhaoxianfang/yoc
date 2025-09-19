#!/bin/bash
# ==================================================
# PHP + MySQL + Nginx + Redis7 工业级安装脚本
# 特性：
# 1. 自动安装 PHP 指定版本及源码 ext 目录所有可用扩展
# 2. 安装 Redis7 服务端并开机自启
# 3. 安装 MySQL 并配置 root 密码、远程用户
# 4. 安装 Nginx、Composer、Git
# 5. www 用户创建并加入 sudo 权限
# 6. PHP 环境变量配置、开机自启
# 7. 中文彩色提示、容错、工业级部署标准
# ==================================================

set -euo pipefail

###########################
# 配置参数（可自定义）
###########################
PHP_VERSION="8.4.12"
PHP_PREFIX="/usr/local/php8"
PHP_CONFIG_PATH="$PHP_PREFIX/lib"
FPM_USER="www"
FPM_GROUP="www"
WWW_PASSWORD="www123"
PHP_DOWNLOAD_URL="https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"

MYSQL_ROOT_PASSWORD="zhaoXfMysql001."
MYSQL_REMOTE_USER="zhaoxianfang"
MYSQL_REMOTE_PASSWORD="zxfMysql001."

REDIS_VERSION="7.2.0"
INSTALL_CONFIG_FILE="/data/install_config.md"

###########################
# 彩色输出函数
###########################
success() { echo -e "\033[42;30m 成功 \033[0m $1"; }
info() { echo -e "\033[44;37m 信息 \033[0m $1"; }
warn() { echo -e "\033[43;30m 警告 \033[0m $1"; }
error() { echo -e "\033[41;37m 错误 \033[0m $1"; }

###########################
# 检测操作系统
###########################
detect_os() {
if [ -f /etc/redhat-release ]; then
OS="rhel"
elif [ -f /etc/debian_version ]; then
OS="debian"
else
error "不支持的操作系统"
exit 1
fi
info "检测到操作系统: $OS"
}

###########################
# 创建 www 用户
###########################
create_www_user() {
if ! id "$FPM_USER" &>/dev/null; then
groupadd "$FPM_GROUP"
useradd -g "$FPM_GROUP" -m "$FPM_USER"
echo "$FPM_USER:$WWW_PASSWORD" | chpasswd
grep -q "$FPM_USER" /etc/sudoers || echo "$FPM_USER ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers
success "用户 $FPM_USER 创建完成"
else
warn "用户 $FPM_USER 已存在"
fi
}

###########################
# 安装依赖
###########################
install_dependencies() {
info "安装系统依赖..."
if [ "$OS" == "rhel" ]; then
yum makecache
yum -y upgrade --allowerasing || warn "升级系统时出现冲突"
yum install -y epel-release yum-utils wget curl gcc gcc-c++ make autoconf libtool perl perl-devel \
libpng libpng-devel libjpeg-turbo-devel libcurl libcurl-devel openldap openldap-devel openldap-clients \
freetype freetype-devel libxml2 libxml2-devel sqlite-devel zlib zlib-devel pcre pcre-devel gd gd-devel \
expat-devel libicu-devel bzip2 bzip2-devel python3 python3-devel libwebp-devel libzstd-devel \
oniguruma oniguruma-devel zstd glibc-headers krb5-devel libzip libzip-devel libxslt libxslt-devel \
openssl openssl-devel libsodium-devel glib2-devel cairo-devel gmp-devel libevent-devel readline-devel \
net-snmp-devel aspell-devel unixODBC-devel libc-client-devel libXpm-devel enchant-devel php-ldap \
automake kernel keyutils patch tidy libtidy libtidy-devel crypt* libgcrypt* --allowerasing
else
apt update
DEBIAN_FRONTEND=noninteractive apt -y upgrade || warn "升级系统失败"
apt install -y build-essential autoconf libtool perl libpng-dev libjpeg-dev libcurl4-openssl-dev \
libldap2-dev freetype2-demos libxml2-dev sqlite3 zlib1g-dev libpcre3-dev libgd-dev \
libexpat1-dev libicu-dev bzip2 libbz2-dev python3 python3-dev libwebp-dev libzstd-dev \
libonig-dev zlib1g-dev libcairo2-dev libgmp-dev libevent-dev libreadline-dev net-snmp-dev \
aspell libodbc1 odbcinst unixodbc-dev libc-client2007e-dev libxpm-dev libenchant-dev \
libtidy-dev libgcrypt20-dev --fix-missing
fi
success "依赖安装完成"
}

###########################
# 下载并解压 PHP
###########################
download_php() {
info "下载 PHP $PHP_VERSION..."
mkdir -p /usr/src/php
cd /usr/src/php
[ ! -f "php-${PHP_VERSION}.tar.gz" ] && wget "$PHP_DOWNLOAD_URL" -O "php-${PHP_VERSION}.tar.gz"
tar zxvf "php-${PHP_VERSION}.tar.gz"
cd "php-${PHP_VERSION}"
success "PHP 源码准备完成"
}

###########################
# 编译安装 PHP
###########################
install_php() {
info "编译安装 PHP..."
./configure \
--prefix="$PHP_PREFIX" \
--with-config-file-path="$PHP_CONFIG_PATH" \
--enable-fpm \
--with-libxml \
--with-openssl \
--with-kerberos \
--with-system-ciphers \
--with-mysqli \
--with-mysql-sock \
--enable-pdo \
--with-pdo-sqlite \
--with-pdo-mysql \
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
--with-tidy \
--enable-tokenizer \
--enable-xml \
--with-xsl \
--with-zip \
--enable-mysqlnd \
--with-pear \
--with-jpeg \
--with-libdir=lib64 \
--enable-cli \
--enable-static \
--with-fpm-user="$FPM_USER" \
--with-fpm-group="$FPM_GROUP" \
|| error "PHP configure 配置失败"

    make -j"$(nproc)" || error "PHP make 编译失败"
    make install || error "PHP make install 安装失败"
    success "PHP 安装完成"

    # 环境变量
    if ! grep -q "$PHP_PREFIX/bin" /etc/profile; then
        echo "export PATH=\$PATH:$PHP_PREFIX/bin" >> /etc/profile
    fi
    source /etc/profile
    info "PHP 已加入环境变量"
}

###########################
# 自动安装 PHP ext 下所有扩展
###########################
install_php_all_extensions() {
info "安装 PHP 源码 ext 所有扩展..."
cd /usr/src/php/php-${PHP_VERSION}/ext
for ext in *; do
if [ -d "$ext" ] && [ -f "$ext/config.m4" ]; then
info "安装扩展: $ext"
cd "$ext"
$PHP_PREFIX/bin/phpize
./configure --with-php-config=$PHP_PREFIX/bin/php-config || warn "$ext configure 失败"
make -j"$(nproc)" && make install || warn "$ext make install 失败"
cd ..
fi
done
success "所有 PHP 扩展安装完成"
}

###########################
# 安装 Redis7 服务端
###########################
install_redis() {
info "安装 Redis $REDIS_VERSION..."
cd /usr/src
[ ! -f "redis-$REDIS_VERSION.tar.gz" ] && wget "http://download.redis.io/releases/redis-$REDIS_VERSION.tar.gz"
tar zxvf "redis-$REDIS_VERSION.tar.gz"
cd "redis-$REDIS_VERSION"
make -j"$(nproc)"
make install
# 开机自启
cp utils/redis_init_script /etc/init.d/redis
chmod +x /etc/init.d/redis
if [ "$OS" == "rhel" ]; then
chkconfig --add redis
systemctl enable redis
systemctl start redis
else
update-rc.d redis defaults
systemctl enable redis
systemctl start redis
fi
success "Redis 安装完成并开机自启"
}

###########################
# 安装 MySQL 并配置
###########################
install_mysql() {
info "安装 MySQL..."
if [ "$OS" == "rhel" ]; then
yum install -y https://dev.mysql.com/get/mysql80-community-release-el7-3.noarch.rpm
yum install -y mysql-community-server --allowerasing
systemctl enable mysqld
systemctl start mysqld
else
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql
fi
success "MySQL 安装完成"

    info "配置 MySQL root 密码..."
    MYSQL_TEMP_PASS=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $NF}' || echo "")
    mysql --connect-expired-password -uroot -p"$MYSQL_TEMP_PASS" -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';" 2>/dev/null || \
    mysql -uroot -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';" || warn "MySQL root 密码可能已存在"

    info "创建远程用户..."
    mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE USER IF NOT EXISTS '$MYSQL_REMOTE_USER'@'%' IDENTIFIED BY '$MYSQL_REMOTE_PASSWORD';"
    mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_REMOTE_USER'@'%' WITH GRANT OPTION;"
    mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "FLUSH PRIVILEGES;"
    success "远程 MySQL 用户 $MYSQL_REMOTE_USER 创建完成"
}

###########################
# 安装 Composer / Git / Nginx
###########################
install_other_tools() {
info "安装 Composer..."
wget -O composer-setup.php https://getcomposer.org/installer
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
success "Composer 安装完成"

    info "安装 Git..."
    if [ "$OS" == "rhel" ]; then yum install -y git; else apt install -y git; fi
    success "Git 安装完成"

    info "安装 Nginx..."
    if [ "$OS" == "rhel" ]; then
        yum install -y nginx
        systemctl enable nginx
        systemctl start nginx
    else
        apt install -y nginx
        systemctl enable nginx
        systemctl start nginx
    fi
    success "Nginx 安装完成"
}

###########################
# 保存安装配置
###########################
save_install_config() {
info "保存安装配置..."
mkdir -p "$(dirname "$INSTALL_CONFIG_FILE")"
cat >"$INSTALL_CONFIG_FILE" <<EOF
# 安装配置
PHP_VERSION: $PHP_VERSION
PHP_PREFIX: $PHP_PREFIX
PHP_CONFIG_PATH: $PHP_CONFIG_PATH
FPM_USER: $FPM_USER
FPM_GROUP: $FPM_GROUP
WWW_PASSWORD: $WWW_PASSWORD
MYSQL_ROOT_PASSWORD: $MYSQL_ROOT_PASSWORD
MYSQL_REMOTE_USER: $MYSQL_REMOTE_USER
MYSQL_REMOTE_PASSWORD: $MYSQL_REMOTE_PASSWORD
REDIS_VERSION: $REDIS_VERSION
安装时间: $(date)
EOF
success "安装配置保存到 $INSTALL_CONFIG_FILE"
}

###########################
# 主流程
###########################
main() {
detect_os
create_www_user
install_dependencies
download_php
install_php
install_php_all_extensions
install_mysql
install_redis
install_other_tools
save_install_config

    # PHP-FPM 开机自启
    systemctl enable php-fpm || systemctl enable php8.0-fpm || warn "PHP-FPM 开机自启失败"

    success "所有组件安装完成，生产环境可用！"
}

main "$@"
