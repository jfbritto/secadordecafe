#!/usr/bin/env bash
# Provisiona um VPS Ubuntu 22.04+ para rodar secadordecafe.
# Uso: rode este script como root ou com sudo na VPS.
#
#   sudo bash provision.sh
#
# É idempotente: rodar novamente é seguro.

set -euo pipefail

if [[ $EUID -ne 0 ]]; then
    echo "Rode como root: sudo bash $0"
    exit 1
fi

APP_USER="${APP_USER:-deploy}"
APP_DIR="${APP_DIR:-/var/www/secadordecafe}"
APP_DOMAIN="${APP_DOMAIN:-app.exemplo.com.br}"

echo ">>> Atualizando sistema..."
apt-get update -qq
apt-get upgrade -y -qq

echo ">>> Instalando pacotes base..."
apt-get install -y -qq \
    nginx \
    mysql-server \
    redis-server \
    supervisor \
    git curl unzip jq ufw fail2ban \
    software-properties-common ca-certificates lsb-release apt-transport-https \
    certbot python3-certbot-nginx \
    unattended-upgrades

echo ">>> Adicionando repo PHP (Ondrej)..."
add-apt-repository -y ppa:ondrej/php
apt-get update -qq

echo ">>> Instalando PHP 8.3 + extensions..."
apt-get install -y -qq \
    php8.3-fpm php8.3-cli \
    php8.3-bcmath php8.3-intl php8.3-mbstring php8.3-mysql \
    php8.3-redis php8.3-gd php8.3-xml php8.3-zip php8.3-curl \
    php8.3-opcache

echo ">>> Instalando Composer..."
if ! command -v composer >/dev/null; then
    curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo ">>> Instalando Node 22..."
if ! command -v node >/dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
    apt-get install -y -qq nodejs
fi

echo ">>> Configurando firewall..."
ufw --force reset >/dev/null
ufw default deny incoming
ufw default allow outgoing
ufw allow OpenSSH
ufw allow http
ufw allow https
yes | ufw enable

echo ">>> Endurecendo SSH..."
sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config
sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
systemctl reload ssh || systemctl reload sshd

echo ">>> Habilitando auto security updates..."
dpkg-reconfigure -f noninteractive unattended-upgrades

echo ">>> Criando usuário ${APP_USER}..."
if ! id -u "$APP_USER" >/dev/null 2>&1; then
    adduser --disabled-password --gecos "" "$APP_USER"
    usermod -aG www-data "$APP_USER"
fi

echo ">>> Criando estrutura ${APP_DIR}..."
mkdir -p "${APP_DIR}"/{releases,shared/storage/{logs,framework/{sessions,views,cache,testing},app/public},backups}
mkdir -p /var/log/secadordecafe
chown -R "$APP_USER":www-data "$APP_DIR"
chown -R www-data:www-data "${APP_DIR}/shared/storage"
chown -R "$APP_USER":www-data /var/log/secadordecafe
chmod -R g+rwx "${APP_DIR}/shared/storage"
chmod g+s "${APP_DIR}/shared/storage"

echo ">>> Configurando Redis com password..."
REDIS_CONF=/etc/redis/redis.conf
if ! grep -q '^requirepass ' "$REDIS_CONF"; then
    REDIS_PW=$(openssl rand -base64 24)
    echo "requirepass ${REDIS_PW}" >> "$REDIS_CONF"
    echo "Redis password: ${REDIS_PW}" > /root/.redis-credentials
    chmod 600 /root/.redis-credentials
    systemctl restart redis-server
    echo "*** REDIS PASSWORD salva em /root/.redis-credentials ***"
fi

echo ">>> Configurando MySQL (rode mysql_secure_installation manualmente)..."
systemctl enable mysql --now

echo ">>> Configurando PHP-FPM (opcache + memory)..."
PHP_INI=/etc/php/8.3/fpm/php.ini
sed -i 's/^memory_limit = .*/memory_limit = 512M/' "$PHP_INI"
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 32M/' "$PHP_INI"
sed -i 's/^post_max_size = .*/post_max_size = 32M/' "$PHP_INI"
sed -i 's/^date.timezone = .*/date.timezone = America\/Sao_Paulo/' "$PHP_INI"

cat > /etc/php/8.3/fpm/conf.d/99-opcache.ini <<'EOF'
opcache.enable=1
opcache.memory_consumption=192
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
EOF

systemctl restart php8.3-fpm

echo ">>> Provisão concluída."
echo
echo "Próximos passos:"
echo "  1. mysql_secure_installation"
echo "  2. mysql: CREATE DATABASE secadordecafe; CREATE USER 'secadordecafe'@'localhost' IDENTIFIED BY '<senha>'; GRANT ALL ON secadordecafe.* TO 'secadordecafe'@'localhost';"
echo "  3. Como ${APP_USER}: clone o repo em ${APP_DIR}/releases/initial e rode setup-app.sh"
echo "  4. Configure ${APP_DIR}/shared/.env (use .env.production.example como base)"
echo "  5. Copie infra/nginx/secadordecafe.conf para /etc/nginx/sites-available/, ajuste server_name e ative"
echo "  6. certbot --nginx -d ${APP_DOMAIN}"
echo "  7. Copie infra/supervisor/secadordecafe-worker.conf para /etc/supervisor/conf.d/ e supervisorctl reread+update"
echo "  8. Adicione cron de infra/cron/secadordecafe.cron"
