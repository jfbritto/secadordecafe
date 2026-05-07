# secadordecafe

Plataforma SaaS multi-tenant para gestão de fazendas e secagem de café.

> **Status:** Fases 1–8 concluídas. MVP completo, com integração Asaas, auditoria, PDFs e infra de deploy. Roadmap: [docs/superpowers/specs/2026-05-06-master-roadmap.md](docs/superpowers/specs/2026-05-06-master-roadmap.md).

## Funcionalidades

| Fase | Entregue |
|------|---------|
| **1 — Fundação + Auth** | Docker stack, Laravel 11, Spatie Permission (teams), cadastro fazenda+admin atômico, login, multi-tenancy, root user |
| **2 — Cadastros** | Clientes (CRUD + busca), gestão de usuários, convites por e-mail, settings da fazenda |
| **3 — Núcleo** | Movimentações (ledger append-only), secagens multi-cliente, cálculos de rendimento/comissão, saldos protegidos |
| **4 — Financeiro** | CRUD de despesas, filtros, totais por categoria |
| **5 — Dashboards** | Dashboard fazenda (saldo, secagens, despesas, top clientes, últimas mov.) + dashboard ROOT |
| **6 — Cobrança Asaas** | Webhook idempotente, sync de status, comando para bloquear inadimplentes, tela de assinatura |
| **7 — Auditoria + PDF + Hardening** | Activitylog scopado, página de auditoria, PDF de secagem, headers de segurança, rate limit |
| **8 — Deploy + Infra** | Scripts de provisão Ubuntu, deploy/rollback/backup, Nginx, Supervisor, Cron, CI GitHub Actions |

## Stack

- Laravel 11 + PHP 8.3 + Blade
- MySQL 8.4
- Redis 7 (cache + queue + session)
- Spatie Permission (com teams scopados por `farm_id`)
- Spatie Activitylog
- Barryvdh DomPDF
- Pest 3 — **86 testes / 265 assertions passando**
- Mailhog (dev mail capture)
- Docker (dev) + Ubuntu 22.04 nativo (produção, ver Fase 8)

## Pré-requisitos (dev)

- Docker Desktop (ou compatível)
- git
- macOS / Linux

## Setup local

```bash
git clone https://github.com/jfbritto/secadordecafe.git
cd secadordecafe
cp .env.example .env
bin/dev build
bin/dev up
bin/dev composer install
bin/dev art key:generate
bin/dev fresh                  # migrate:fresh --seed
```

URLs:

| Serviço | URL |
|---------|-----|
| Aplicação | http://localhost:8080 |
| Mailhog | http://localhost:8025 |
| MySQL | localhost:3307 |
| Redis | localhost:6380 |

Credenciais root (dev/test apenas):
- e-mail: `root@secadordecafe.test`
- senha: `root12345`

Worker de queue (em terminal separado):
```bash
bin/dev art queue:work --queue=emails,default
```

## Comandos `bin/dev`

```
up | down | restart | ps | logs | shell | art <cmd> | composer <cmd> | npm <cmd>
test | pest | fresh | build | bootstrap
```

## Testes

```bash
bin/dev test     # ou bin/dev pest
```

86 testes / 265 assertions passando, cobrindo:
- Auth (cadastro fazenda atômico, login, slugs únicos)
- Tenancy (scope automático, root bypass, fazenda bloqueada)
- Customers (CRUD, policies por role, busca)
- Users + Invitations (convites, aceite, expirado, last-admin protection)
- Movements (saldos, sinais, débito/crédito, prevenção de saldo negativo)
- Secagens (rascunho, conclusão atômica multi-cliente, números sequenciais por fazenda)
- Expenses (CRUD, filtros, policies)
- Billing (webhook Asaas, idempotência, bloqueio por inadimplência)
- Audit + PDF + Security headers

## Deploy (produção)

Documentação completa: [docs/superpowers/specs/2026-05-06-phase-8-deploy-infra.md](docs/superpowers/specs/2026-05-06-phase-8-deploy-infra.md)

Resumo:

```bash
# 1. No VPS Ubuntu 22.04+ como root:
sudo bash bin/deploy/provision.sh

# 2. Crie DB + usuário deploy + cole .env em /var/www/secadordecafe/shared/.env

# 3. Como deploy:
bin/deploy/setup-app.sh

# 4. Configure infra/nginx, infra/supervisor, infra/cron e SSL via certbot

# 5. Releases subsequentes (do desenvolvedor, via SSH):
bin/deploy/deploy.sh
bin/deploy/rollback.sh         # volta uma release
```

Webhook Asaas: configure no painel para `https://app.dominio/webhooks/asaas` com header `asaas-access-token` igual a `ASAAS_WEBHOOK_TOKEN`.

CI: pipeline em `.github/workflows/ci.yml` roda Pest em cada push/PR contra `main`.

## Documentação

- [Roadmap mestre (8 fases)](docs/superpowers/specs/2026-05-06-master-roadmap.md)
- [Spec Fase 1 — Fundação + Auth](docs/superpowers/specs/2026-05-06-phase-1-foundation-auth-design.md)
- [Plano Fase 1](docs/superpowers/plans/2026-05-06-phase-1-implementation-plan.md)
- [Spec Fase 2 — Cadastros](docs/superpowers/specs/2026-05-06-phase-2-base-crud-design.md)
- [Spec Fase 3 — Núcleo](docs/superpowers/specs/2026-05-06-phase-3-core-secagens-design.md)
- [Spec Fase 8 — Deploy](docs/superpowers/specs/2026-05-06-phase-8-deploy-infra.md)
- [Prompt original](prompt-secagem-cafe.md)

## Arquitetura (resumo)

```
app/
├── Actions/                  # casos de uso atômicos (RegisterFarm, ConcludeSecagem, RegisterMovement, SendInvitation)
├── DTOs/
├── Events/Listeners/Mail/
├── Exceptions/DomainException.php
├── Http/
│   ├── Controllers/          # Auth, Customer, Secagem, Movement, Expense, Dashboard, Billing, Audit, AsaasWebhook
│   ├── Middleware/           # SetTenantContext, EnsureFarmActive, SecurityHeaders
│   └── Requests/             # Form Requests (validação + authorize)
├── Models/                   # Farm, User, Customer, Movement, Secagem, SecagemItem, Expense, Subscription, Invitation, WebhookEvent
│   └── Concerns/BelongsToFarm.php   # global scope + auto-fill + activitylog inject
├── Policies/
├── Services/Asaas/           # AsaasClient, AsaasWebhookHandler
└── Console/Commands/         # subscriptions:block-overdue (scheduled daily)

bin/
├── dev                       # atalhos Docker (dev)
└── deploy/                   # provision/setup/deploy/rollback/backup/restore (prod)

infra/
├── nginx/                    # site config
├── supervisor/               # workers
└── cron/                     # scheduler + backup

docker/                       # imagens dev (PHP-FPM, php.ini, opcache.ini, nginx)
.github/workflows/ci.yml      # CI: composer + npm + Pest
```

Multi-tenancy:
- Coluna `farm_id` em todas as entidades de tenant
- Trait `BelongsToFarm` aplica global scope automático e injeta `farm_id` em activity logs
- `Gate::before` para `is_root`
- Spatie Permission com `team_foreign_key = farm_id`

## Próximos passos sugeridos (não cobertos no MVP)

- App mobile (API REST/GraphQL ainda não exposta)
- Importação de planilhas históricas (CSV)
- Saldo seco retornar ao cliente como crédito automático
- Reverso de secagem (cancelamento)
- Integração WhatsApp para notificações
- Multi-unidade (uma fazenda com várias filiais)
- Refinos UX completos com AdminLTE / Tailwind UI

## Licença

Privado — propriedade do cliente.
