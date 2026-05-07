#!/usr/bin/env bash
# Configuração inicial da app no VPS após provision.sh.
# Roda como usuário deploy.
#
# Pré-requisitos: provision.sh executado, MySQL com usuário criado, .env já populado em shared/.env

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/secadordecafe}"
REPO_URL="${REPO_URL:-https://github.com/jfbritto/secadordecafe.git}"
BRANCH="${BRANCH:-main}"

cd "$APP_DIR"

if [[ ! -f shared/.env ]]; then
    echo "ERRO: ${APP_DIR}/shared/.env não existe. Crie-o a partir de .env.production.example antes de continuar."
    exit 1
fi

TS=$(date +%Y-%m-%d-%H%M)
RELEASE_DIR="${APP_DIR}/releases/${TS}"

echo ">>> Clonando ${REPO_URL} em ${RELEASE_DIR}..."
git clone --branch "$BRANCH" --depth 1 "$REPO_URL" "$RELEASE_DIR"

cd "$RELEASE_DIR"

echo ">>> Symlinks .env e storage..."
ln -nfs "${APP_DIR}/shared/.env" "${RELEASE_DIR}/.env"
rm -rf "${RELEASE_DIR}/storage"
ln -nfs "${APP_DIR}/shared/storage" "${RELEASE_DIR}/storage"

echo ">>> composer install..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo ">>> npm ci + build..."
npm ci
npm run build

echo ">>> php artisan storage:link..."
php artisan storage:link || true

echo ">>> php artisan key:generate (se APP_KEY vazia)..."
if ! grep -q '^APP_KEY=base64:' "${APP_DIR}/shared/.env"; then
    php artisan key:generate --force
fi

echo ">>> migrate..."
php artisan migrate --force

echo ">>> caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo ">>> Atualizando symlink current..."
ln -nfs "$RELEASE_DIR" "${APP_DIR}/current"

echo ">>> Permissões..."
sudo chown -R "$(whoami)":www-data "$RELEASE_DIR"
sudo chown -R www-data:www-data "${APP_DIR}/shared/storage"
sudo chmod -R g+rwx "${APP_DIR}/shared/storage"

echo "Setup inicial concluído. Release: ${RELEASE_DIR}"
echo "Próximo: configurar Nginx + Supervisor + Cron e rodar 'php artisan db:seed' (apenas RoleSeeder)."
