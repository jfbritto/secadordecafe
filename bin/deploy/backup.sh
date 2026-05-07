#!/usr/bin/env bash
# Backup MySQL diário com retenção de 14 dias.
# Crontab: 30 2 * * * root /var/www/secadordecafe/current/bin/deploy/backup.sh
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/secadordecafe}"
BACKUP_DIR="${APP_DIR}/backups"
RETENTION_DAYS="${RETENTION_DAYS:-14}"

mkdir -p "$BACKUP_DIR"

if [[ ! -f "${APP_DIR}/shared/.env" ]]; then
    echo "ERRO: .env não encontrado em ${APP_DIR}/shared/"
    exit 1
fi

# Lê credenciais do .env (sem export do Laravel runtime)
DB_NAME=$(grep -E '^DB_DATABASE=' "${APP_DIR}/shared/.env" | cut -d= -f2- | tr -d '"')
DB_USER=$(grep -E '^DB_USERNAME=' "${APP_DIR}/shared/.env" | cut -d= -f2- | tr -d '"')
DB_PASS=$(grep -E '^DB_PASSWORD=' "${APP_DIR}/shared/.env" | cut -d= -f2- | tr -d '"')
DB_HOST=$(grep -E '^DB_HOST=' "${APP_DIR}/shared/.env" | cut -d= -f2- | tr -d '"')

DATE=$(date +%F-%H%M)
FILE="${BACKUP_DIR}/db-${DATE}.sql.gz"

echo ">>> Backup MySQL → ${FILE}"
mysqldump --single-transaction --quick --lock-tables=false \
    -h "${DB_HOST:-127.0.0.1}" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    | gzip > "$FILE"

# Verifica tamanho mínimo (1KB) — se falhou, mysqldump pode ter retornado vazio
if [[ ! -s "$FILE" ]] || [[ $(stat -c%s "$FILE") -lt 1024 ]]; then
    echo "ERRO: backup vazio/corrompido. Removendo."
    rm -f "$FILE"
    exit 1
fi

echo ">>> Limpando backups com mais de ${RETENTION_DAYS} dias..."
find "$BACKUP_DIR" -name 'db-*.sql.gz' -mtime +"$RETENTION_DAYS" -delete

# Upload S3 opcional
if [[ -n "${S3_BACKUP_BUCKET:-}" ]] && command -v aws >/dev/null; then
    echo ">>> Upload para s3://${S3_BACKUP_BUCKET}/"
    aws s3 cp "$FILE" "s3://${S3_BACKUP_BUCKET}/$(basename "$FILE")"
fi

echo "Backup concluído: ${FILE} ($(du -h "$FILE" | cut -f1))"
