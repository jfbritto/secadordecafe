#!/usr/bin/env bash
# Deploy de uma nova release no VPS.
# Roda como usuário deploy (ou via SSH a partir do desenvolvedor).
#
# Uso:
#   deploy.sh             # full deploy
#   deploy.sh --no-migrate
#   deploy.sh --branch=feature/x

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/secadordecafe}"
REPO_URL="${REPO_URL:-https://github.com/jfbritto/secadordecafe.git}"
BRANCH="${BRANCH:-main}"
KEEP_RELEASES="${KEEP_RELEASES:-3}"

DO_MIGRATE=1
for arg in "$@"; do
    case "$arg" in
        --no-migrate) DO_MIGRATE=0 ;;
        --branch=*)   BRANCH="${arg#--branch=}" ;;
    esac
done

cd "$APP_DIR"

TS=$(date +%Y-%m-%d-%H%M)
RELEASE_DIR="${APP_DIR}/releases/${TS}"

echo ">>> Clonando ${BRANCH} em ${RELEASE_DIR}..."
git clone --branch "$BRANCH" --depth 1 "$REPO_URL" "$RELEASE_DIR"

cd "$RELEASE_DIR"
ln -nfs "${APP_DIR}/shared/.env" "${RELEASE_DIR}/.env"
rm -rf storage
ln -nfs "${APP_DIR}/shared/storage" "${RELEASE_DIR}/storage"

echo ">>> composer install..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo ">>> npm ci + build..."
npm ci --silent
npm run build

if [[ $DO_MIGRATE -eq 1 ]]; then
    echo ">>> migrate..."
    php artisan migrate --force
else
    echo ">>> migrate skipped"
fi

echo ">>> caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo ">>> Atualizando symlink current..."
PREV=$(readlink "${APP_DIR}/current" 2>/dev/null || echo "")
ln -nfs "$RELEASE_DIR" "${APP_DIR}/current"

echo ">>> Reiniciando workers..."
php artisan queue:restart || true
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx || true

echo ">>> Limpando releases antigas (mantendo últimas ${KEEP_RELEASES})..."
cd "${APP_DIR}/releases"
ls -1tr | head -n -"${KEEP_RELEASES}" | xargs -r rm -rf

echo "Deploy concluído. Release: ${TS}"
[[ -n "$PREV" ]] && echo "Anterior: ${PREV}"
