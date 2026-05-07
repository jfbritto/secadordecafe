#!/usr/bin/env bash
# Rollback para a release anterior.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/secadordecafe}"

cd "${APP_DIR}/releases"

# Lista as releases mais recentes
RELEASES=( $(ls -1t) )

if [[ ${#RELEASES[@]} -lt 2 ]]; then
    echo "ERRO: precisa de pelo menos 2 releases para rollback."
    exit 1
fi

CURRENT_REL=$(basename "$(readlink "${APP_DIR}/current")")
PREV=""

for r in "${RELEASES[@]}"; do
    if [[ "$r" != "$CURRENT_REL" ]]; then
        PREV="$r"
        break
    fi
done

if [[ -z "$PREV" ]]; then
    echo "ERRO: nenhuma release anterior encontrada."
    exit 1
fi

echo ">>> Rollback de ${CURRENT_REL} para ${PREV}"
ln -nfs "${APP_DIR}/releases/${PREV}" "${APP_DIR}/current"

php "${APP_DIR}/current/artisan" config:cache
php "${APP_DIR}/current/artisan" queue:restart
sudo systemctl reload php8.3-fpm

echo "Rollback concluído para ${PREV}."
echo "ATENÇÃO: migrations não são revertidas automaticamente. Avalie 'php artisan migrate:rollback' caso necessário."
