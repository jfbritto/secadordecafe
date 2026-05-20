#!/bin/bash
# Script de deploy do Roça Nossa no VPS.
# Cole este arquivo em /home/deploy/deploy-rocanossa.sh no servidor
# (padrão deploy-<projeto>.sh, igual deploy-taketicket.sh, deploy-masterveiculos.sh).
# O workflow do GitHub Actions chama ele via SSH a cada push em main.

set -e

cd /var/www/rocanossa

echo ">> Modo de manutenção..."
php artisan down --retry=15 --refresh=5 2>/dev/null || true

echo ">> Resetando alterações locais..."
git checkout -- .

echo ">> Puxando última versão..."
git pull origin main

echo ">> Composer install (sem dev, otimizado)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo ">> Rodando migrations..."
php artisan migrate --force

echo ">> Limpando e recompilando caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo ">> Reiniciando workers da fila..."
sudo supervisorctl restart rocanossa-worker:* || true

echo ">> Saindo do modo de manutenção..."
php artisan up

echo ">> Deploy concluído!"
