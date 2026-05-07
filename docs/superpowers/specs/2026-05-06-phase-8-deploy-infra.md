# Fase 8 — Deploy e Infraestrutura — Spec/Runbook

**Data:** 2026-05-06
**Alvo:** VPS Ubuntu 22.04+ (HostGator), com SSL Let's Encrypt
**Status:** documentação + scripts; provisão manual com `bin/deploy/*.sh`

## Decisões

- **Sem Docker em produção (por enquanto)** — VPS HostGator pode não suportar Docker bem; deploy nativo Ubuntu (Nginx + PHP-FPM + MySQL + Supervisor + Redis) é o caminho mais robusto. A stack Docker continua sendo o ambiente de dev.
- **Deploy via SSH/rsync** com script idempotente. CI/CD posterior pode automatizar via GitHub Actions, mas v1 é manual.
- **Ambientes:** staging (subdomínio + DB separado) e production. `.env.production` versionado como `.env.production.example`.
- **Backups:** mysqldump diário + retenção de 14 dias, comprimido. Cron + script.
- **SSL:** certbot com renovação automática.
- **Filas:** Redis + Supervisor com pelo menos 2 workers (default + emails).
- **Rollback:** mantemos as 3 últimas releases em `releases/`. `current` é symlink. `bin/deploy/rollback.sh` troca o symlink.

## Topologia

```
/var/www/secadordecafe/
├── current -> releases/2026-05-06-1430
├── releases/
│   ├── 2026-05-06-1430/   # release ativa
│   ├── 2026-05-06-1100/
│   └── 2026-05-05-1900/
├── shared/
│   ├── .env               # secrets, NÃO versionado
│   ├── storage/           # logs, sessões, etc (symlink em cada release)
│   └── public/uploads/    # se aplicável
└── backups/
    └── db-2026-05-06.sql.gz
```

A pasta `releases/<timestamp>` é gerada por `deploy.sh`. Storage e .env são compartilhados via symlink.

## Provisionamento Inicial (one-shot)

`bin/deploy/provision.sh` — roda no servidor como root, instala stack completa.

### Pacotes
- nginx 1.24+
- php8.3-fpm + extensions: bcmath, intl, mbstring, mysql, redis, gd, opcache, pcntl, xml, zip, curl
- mysql-server 8.0+
- redis-server 7+
- supervisor
- certbot + python3-certbot-nginx
- git, unzip, curl, jq

### Hardening básico
- ufw deny incoming, allow ssh/http/https
- fail2ban defaults
- ssh: passwordauth=no, root login no
- Auto security updates: `unattended-upgrades`

## Deploy

### Primeiro deploy

1. SSH no VPS, rode `bin/deploy/provision.sh` como root.
2. Crie usuário `deploy`, SSH key.
3. No host local, configure `bin/deploy/config.sh` com IP, user, paths.
4. `bin/deploy/setup-app.sh` — clona repo, cria pastas shared, copia .env, gera APP_KEY, cria DB.
5. `bin/deploy/deploy.sh` — release inicial.

### Releases subsequentes

```bash
bin/deploy/deploy.sh           # full deploy
bin/deploy/deploy.sh --no-migrate  # skip migrate
bin/deploy/rollback.sh         # volta uma release
```

### Pipeline manual (deploy.sh resumido)

1. `ssh deploy@vps`
2. `git fetch && git checkout <ref>` no diretório `releases/<timestamp>`
3. `composer install --no-dev --optimize-autoloader --no-interaction`
4. `php artisan storage:link` (na release)
5. `php artisan migrate --force` (se não --no-migrate)
6. `php artisan config:cache && route:cache && view:cache`
7. `npm ci && npm run build`
8. `chown -R www-data:www-data storage bootstrap/cache`
9. Atualiza symlink `current -> releases/<timestamp>`
10. `php artisan queue:restart`
11. `nginx -s reload` (raramente necessário)

## Arquivos versionados

- `bin/deploy/provision.sh` — provisiona Ubuntu fresh
- `bin/deploy/setup-app.sh` — primeiro deploy / criação de DB / .env
- `bin/deploy/deploy.sh` — release
- `bin/deploy/rollback.sh` — volta release
- `bin/deploy/backup.sh` — dump MySQL + S3/local
- `bin/deploy/restore.sh` — restore de dump
- `infra/nginx/secadordecafe.conf` — site config
- `infra/supervisor/secadordecafe-worker.conf` — supervisor config para 2 workers
- `infra/cron/secadordecafe.cron` — schedule:run + backup
- `.env.production.example` — template

## Workers Supervisor

```ini
[program:secadordecafe-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/secadordecafe/current/artisan queue:work redis --queue=default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/secadordecafe/worker-default.log
stopwaitsecs=3600

[program:secadordecafe-emails]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/secadordecafe/current/artisan queue:work redis --queue=emails --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/secadordecafe/worker-emails.log
stopwaitsecs=3600
```

## Cron

```cron
# Laravel scheduler (chama subscriptions:block-overdue daily 03:00)
* * * * * www-data cd /var/www/secadordecafe/current && php artisan schedule:run >> /dev/null 2>&1

# Backup diário 02:30
30 2 * * * root /var/www/secadordecafe/current/bin/deploy/backup.sh >> /var/log/secadordecafe/backup.log 2>&1
```

## Backups

`bin/deploy/backup.sh` faz:
1. `mysqldump --single-transaction secadordecafe | gzip > backups/db-$(date +%F).sql.gz`
2. Mantém os últimos 14 dias.
3. (opcional) `aws s3 cp` para bucket externo se `S3_BACKUP_BUCKET` configurado.

## Monitoramento

Mínimo viável:
- Logs via `tail -f /var/log/secadordecafe/*.log`
- `php artisan pail` em dev
- Health check: `GET /up` retorna 200 (Laravel default)
- Notificação por e-mail se backup falhar

Próximo nível (futuro):
- Sentry para errors
- Uptime Kuma / UptimeRobot
- Grafana + Prometheus

## Checklist de Produção

- [ ] DNS configurado para domínio
- [ ] APP_DEBUG=false, APP_ENV=production
- [ ] APP_KEY gerada e backupeada offline
- [ ] DB com senha forte (gerada com `openssl rand -base64 24`)
- [ ] REDIS_PASSWORD configurada
- [ ] MAIL_* apontando para provedor real (não Mailhog)
- [ ] ASAAS_API_KEY (production), ASAAS_WEBHOOK_TOKEN gerado
- [ ] Webhook Asaas registrado para `https://app.dominio/webhooks/asaas` com header `asaas-access-token`
- [ ] SSL ativo via certbot, renovação confirmada (`certbot renew --dry-run`)
- [ ] Nginx config com gzip + headers de segurança (CSP)
- [ ] Supervisor rodando workers default + emails
- [ ] Cron rodando scheduler + backup
- [ ] `php artisan config:cache && route:cache && view:cache` aplicados
- [ ] Permissões `storage/` e `bootstrap/cache/` corretas (www-data:www-data)
- [ ] Primeiro deploy rodou migrations sem erro
- [ ] Smoke test: GET /, GET /login, register flow, login, dashboard
- [ ] Backup manual executado e verificado (restore em staging)
- [ ] Logs de aplicação rotacionados (logrotate)

## Variáveis de Ambiente Críticas (produção)

```env
APP_NAME=secadordecafe
APP_ENV=production
APP_KEY=base64:...        # gerar com php artisan key:generate
APP_DEBUG=false
APP_URL=https://app.dominio.com.br
APP_TIMEZONE=America/Sao_Paulo

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=secadordecafe
DB_USERNAME=secadordecafe
DB_PASSWORD=<senha-forte>

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=<senha-forte>
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
CACHE_STORE=redis

MAIL_MAILER=smtp
MAIL_HOST=<provedor>
MAIL_PORT=587
MAIL_USERNAME=<user>
MAIL_PASSWORD=<senha>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@dominio.com.br"

ASAAS_ENV=production
ASAAS_API_KEY=<chave-asaas>
ASAAS_WEBHOOK_TOKEN=<token-aleatorio-gerado>
ASAAS_BLOCK_AFTER_DAYS=7
```

## CI/CD

`/.github/workflows/ci.yml` — em cada push:
- PHP 8.3, MySQL 8 service
- composer install
- php artisan config:cache
- php artisan migrate
- php artisan test (Pest, todos)

Deploy via CI pode ser adicionado depois, com `appleboy/ssh-action` rodando `bin/deploy/deploy.sh` no VPS após CI verde em `main`.

## Riscos & Mitigações

| Risco | Mitigação |
|-------|-----------|
| Migration quebra produção | `migrate --pretend` antes em staging; backup pré-deploy |
| Worker crash silencioso | Supervisor autorestart + log monitoring |
| Disco cheio | Backup retention 14d, logrotate, alerta `df -h` em cron |
| Asaas webhook perdido | Idempotência via webhook_events; retry manual via re-envio Asaas |
| Hot DB lock em concluir secagem | Já usamos lockForUpdate em transação; em alto volume avaliar fila dedicada |
