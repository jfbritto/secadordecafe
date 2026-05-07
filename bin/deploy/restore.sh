#!/usr/bin/env bash
# Restore de um backup MySQL.
# Uso: restore.sh /var/www/secadordecafe/backups/db-2026-05-06-0230.sql.gz
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/secadordecafe}"
DUMP="${1:-}"

if [[ -z "$DUMP" || ! -f "$DUMP" ]]; then
    echo "Uso: $0 <caminho-do-dump.sql.gz>"
    exit 1
fi

DB_NAME=$(grep -E '^DB_DATABASE=' "${APP_DIR}/shared/.env" | cut -d= -f2-)
DB_USER=$(grep -E '^DB_USERNAME=' "${APP_DIR}/shared/.env" | cut -d= -f2-)
DB_PASS=$(grep -E '^DB_PASSWORD=' "${APP_DIR}/shared/.env" | cut -d= -f2-)

read -p "Restaurar ${DUMP} sobre ${DB_NAME}? Isso APAGA dados atuais. [yes/N] " confirm
[[ "$confirm" == "yes" ]] || { echo "Cancelado."; exit 1; }

echo ">>> Restaurando..."
gunzip -c "$DUMP" | mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"
echo "Restore concluído."
