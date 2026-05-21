# Deploy do Roça Nossa

Servidor: VPS HostGator `129.121.50.200` (mesmo do treinaedu, helpflux etc.) · Ubuntu 22.04 · SSH na porta `22022`.

CI/CD: cada push em `main` dispara o workflow [.github/workflows/deploy.yml](../.github/workflows/deploy.yml) — roda os 202 testes do Pest contra MySQL e Redis reais e, se passar, faz SSH no servidor e executa `/home/deploy/rocanossa-deploy.sh` ([conteúdo](./deploy.sh)).

Este README é o passo a passo do **setup inicial** (uma vez só). Depois o deploy é automático.

---

## 0. Pré-requisitos no servidor

Como o VPS já hospeda outros Laravel (treinaedu, helpflux), o que segue **já deve estar instalado** — vale conferir:

```bash
php -v                          # esperado: 8.3+
composer -V                     # esperado: 2.x
mysql --version                 # esperado: 8.x
redis-cli ping                  # esperado: PONG
nginx -v                        # esperado: 1.18+
which supervisorctl certbot     # esperado: existir
id deploy                       # esperado: usuário 'deploy' existe
```

Se algo faltar, instalar antes de prosseguir. Tudo o que segue assume usuário `deploy` com chave SSH configurada (mesma do treinaedu).

---

## 1. DNS no registro.br

Painel do `rocanossa.com.br` → **Configurar Zona DNS** (Modo Avançado) → **Nova Entrada** pra cada linha:

| Tipo  | Nome                       | Dados                           |
|-------|----------------------------|---------------------------------|
| A     | `rocanossa.com.br`         | `129.121.50.200`                |
| CNAME | `www.rocanossa.com.br`     | `rocanossa.com.br`              |

Email (Resend) — adicione **depois** de criar o domínio no painel do Resend (que devolve os tokens DKIM exatos):

| Tipo  | Nome                                   | Dados                                                      |
|-------|----------------------------------------|------------------------------------------------------------|
| TXT   | `resend._domainkey.rocanossa.com.br`   | *(token DKIM que o Resend gera)*                           |
| MX    | `send.rocanossa.com.br`                | `10 feedback-smtp.sa-east-1.amazonses.com`                 |
| TXT   | `send.rocanossa.com.br`                | `"v=spf1 include:amazonses.com ~all"`                      |
| TXT   | `_dmarc.rocanossa.com.br`              | `"v=DMARC1; p=none; rua=mailto:jf.britto@hotmail.com"`     |

Propagação: 15min a 4h.

---

## 2. Banco de dados

Como `deploy` ou `root`, no MySQL:

```sql
CREATE DATABASE rocanossa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rocanossa'@'localhost' IDENTIFIED BY 'TROQUE_POR_SENHA_FORTE';
GRANT ALL PRIVILEGES ON rocanossa.* TO 'rocanossa'@'localhost';
FLUSH PRIVILEGES;
```

Guarda a senha — vai pro `.env` no próximo passo.

---

## 3. Clonar e configurar a aplicação

Como usuário `deploy`:

```bash
cd /var/www
git clone https://github.com/jfbritto/secadordecafe.git rocanossa
cd rocanossa

# .env de produção
cp .env.production.example .env
nano .env                              # ajuste APP_KEY, DB_PASSWORD, MAIL_*, ASAAS_*

composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate                # gera APP_KEY se vazio
php artisan migrate --force             # cria schema
php artisan db:seed --force             # cria roles Spatie (admin/operador/financeiro/visualizador/root) + usuário root
                                        # ⚠️ NÃO use --class=RootUserSeeder isolado — vai pular o RoleSeeder
                                        #    e o cadastro de fazenda quebra com "Role admin doesn't exist"

# Permissões
sudo chown -R deploy:www-data /var/www/rocanossa
sudo chmod -R 775 storage bootstrap/cache

# Cache pra produção
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Variáveis críticas no `.env` (já vêm preenchidas pelo `.env.production.example`):

```
APP_URL=https://rocanossa.com.br
DB_DATABASE=rocanossa
DB_USERNAME=rocanossa
DB_PASSWORD=<a senha do passo 2>
MAIL_FROM_ADDRESS="no-reply@rocanossa.com.br"
ASAAS_ENV=sandbox          # troca pra production quando tiver a key real
```

---

## 4. Nginx

```bash
sudo cp /var/www/rocanossa/.deploy/nginx/rocanossa.conf /etc/nginx/sites-available/rocanossa
sudo ln -s /etc/nginx/sites-available/rocanossa /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

Testar HTTP: `curl -I http://rocanossa.com.br` (deve responder 200 ou redirecionar pra login).

---

## 5. SSL com Let's Encrypt

```bash
sudo certbot --nginx -d rocanossa.com.br -d www.rocanossa.com.br
```

Certbot edita o vhost automaticamente, adicionando o bloco `:443` e o redirect 80→443.

---

## 6. Supervisor (queue worker)

```bash
sudo cp /var/www/rocanossa/.deploy/supervisor/rocanossa-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start rocanossa-worker:*
sudo supervisorctl status                  # confirma RUNNING
```

Pra `deploy.sh` reiniciar o supervisor sem senha, garante que o `/etc/sudoers.d/deploy` (do treinaedu) já tem `deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl`.

---

## 7. Cron (Laravel scheduler)

Como `deploy`:

```bash
crontab -e
```

Adiciona:

```
* * * * * cd /var/www/rocanossa && php artisan schedule:run >> /dev/null 2>&1
```

Isso garante que o `subscriptions:block-overdue` (e qualquer outro job agendado) roda no horário.

---

## 8. GitHub Secrets

No repo (`Settings → Secrets and variables → Actions`):

| Nome              | Valor                                           |
|-------------------|-------------------------------------------------|
| `SSH_HOST`        | `129.121.50.200`                                |
| `SSH_USER`        | `deploy`                                        |
| `SSH_PORT`        | `22022`                                         |
| `SSH_PRIVATE_KEY` | conteúdo da chave privada do `deploy` (`-----BEGIN OPENSSH PRIVATE KEY-----...`) |

Se ainda não tem chave dedicada pro CI, gere uma:

```bash
ssh-keygen -t ed25519 -C "github-actions-rocanossa" -f ~/.ssh/rocanossa_deploy
cat ~/.ssh/rocanossa_deploy.pub >> /home/deploy/.ssh/authorized_keys    # no servidor
cat ~/.ssh/rocanossa_deploy                                              # cole no secret
```

---

## 9. Instalar o `deploy.sh` no servidor

```bash
sudo cp /var/www/rocanossa/.deploy/deploy.sh /home/deploy/rocanossa-deploy.sh
sudo chmod +x /home/deploy/rocanossa-deploy.sh
sudo chown deploy:deploy /home/deploy/rocanossa-deploy.sh
```

Testa manualmente uma vez:

```bash
sudo -u deploy /home/deploy/rocanossa-deploy.sh
```

---

## 10. Webhook Asaas (depois)

Quando trocar `ASAAS_ENV=production`, no painel do Asaas:

- URL: `https://rocanossa.com.br/webhooks/asaas`
- Header: `asaas-access-token` = valor de `ASAAS_WEBHOOK_TOKEN` no `.env`

---

## Validação final

```bash
# No servidor:
sudo supervisorctl status rocanossa-worker:*       # 2 workers RUNNING
sudo systemctl status nginx                         # active
sudo certbot certificates                           # cert válido

# No navegador:
https://rocanossa.com.br                            # landing pública
https://rocanossa.com.br/register                   # registro
```

Faz um `git push` qualquer em `main` e acompanha o workflow no GitHub → quando ficar verde, o `php artisan up` deve voltar a aplicação no ar com a última versão.
