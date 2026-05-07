# secadordecafe

Plataforma SaaS multi-tenant para gestão de fazendas e secagem de café.

> **Status:** Fase 1 (Fundação + Auth Multi-tenant) concluída. Roadmap completo em [docs/superpowers/specs/2026-05-06-master-roadmap.md](docs/superpowers/specs/2026-05-06-master-roadmap.md).

## Stack

- Laravel 11 + PHP 8.3 + Blade
- MySQL 8.4
- Redis 7 (cache + queue + session)
- Mailhog (dev mail capture)
- Nginx 1.27
- Spatie Permission (com teams scopados por `farm_id`)
- Spatie Activitylog
- AdminLTE (jeroennoten/laravel-adminlte)
- Pest 3 (testes)
- Tudo em containers Docker

## Pré-requisitos

- Docker Desktop (ou compatível)
- git
- macOS / Linux (testado em macOS arm64)

## Setup inicial

```bash
git clone <repo> secadordecafe
cd secadordecafe
cp .env.example .env
bin/dev build
bin/dev up
bin/dev composer install      # se vendor não foi commitado
bin/dev art key:generate
bin/dev fresh                 # migrate:fresh --seed (cria root user)
```

URLs:

| Serviço | URL |
|---------|-----|
| Aplicação | http://localhost:8080 |
| Mailhog (inbox dev) | http://localhost:8025 |
| MySQL | localhost:3307 (user `secadordecafe` / pw `secret`) |
| Redis | localhost:6380 |

Usuário root padrão (apenas em `local`/`testing`):
- e-mail: `root@secadordecafe.test`
- senha: `root12345`

## Comandos do `bin/dev`

```
bin/dev up              # sobe stack
bin/dev down            # derruba
bin/dev shell           # shell no container app
bin/dev art <args>      # php artisan <args>
bin/dev composer <args> # composer <args>
bin/dev npm <args>      # npm <args> no container node
bin/dev test            # php artisan test
bin/dev pest            # ./vendor/bin/pest
bin/dev fresh           # migrate:fresh --seed
bin/dev logs [svc]      # logs (default app)
bin/dev build           # rebuild imagens
bin/dev bootstrap       # cria projeto Laravel inicial (uso único — já feito)
```

## Workers de Queue

E-mails (boas-vindas etc) e jobs assíncronos rodam em queue Redis. Em dev, rode num terminal separado:

```bash
bin/dev art queue:work --queue=emails,default
```

Em produção, isso será gerenciado pelo Supervisor (Fase 8).

## Testes

```bash
bin/dev test
# ou diretamente
bin/dev pest
```

Testes usam SQLite in-memory (configurado em [phpunit.xml](phpunit.xml)) com `RefreshDatabase`. Suite atual: 16 testes / 58 assertions.

- Auth/RegisterFarm (cadastro atômico, slug único, validações)
- Auth/Login (login/logout/credenciais inválidas)
- Tenancy/FarmStatusGate (bloqueio de fazenda inativa)
- Tenancy/RootUser (root bypassa scope e gates)

## Documentação

- [Roadmap mestre (8 fases)](docs/superpowers/specs/2026-05-06-master-roadmap.md)
- [Spec da Fase 1](docs/superpowers/specs/2026-05-06-phase-1-foundation-auth-design.md)
- [Plano de implementação Fase 1](docs/superpowers/plans/2026-05-06-phase-1-implementation-plan.md)
- [Prompt original do projeto](prompt-secagem-cafe.md)

## Arquitetura

```
app/
├── Actions/Auth/RegisterFarmAction.php   # caso de uso atômico
├── DTOs/RegisterFarmData.php
├── Events/FarmRegistered.php
├── Listeners/SendWelcomeEmail.php
├── Mail/WelcomeFarmMail.php
├── Models/
│   ├── Farm.php
│   ├── User.php
│   ├── Subscription.php
│   └── Concerns/BelongsToFarm.php        # trait + global scope para multi-tenant
├── Http/
│   ├── Controllers/Auth/{Register,Login}Controller.php
│   ├── Controllers/{Dashboard,FarmBlocked}Controller.php
│   ├── Middleware/{SetTenantContext,EnsureFarmActive}.php
│   └── Requests/Auth/RegisterFarmRequest.php
└── Providers/AppServiceProvider.php       # Gate::before para is_root + event wiring
```

### Multi-tenancy

- Coluna `farm_id` em todas as entidades de tenant (próximas fases: customers, secagens, despesas).
- Trait `BelongsToFarm` aplica global scope automático: queries só retornam linhas do `auth()->user()->farm_id` — exceto root.
- `farm_id` nunca aceito do request — derivado do usuário autenticado em `creating`.
- Spatie Permission com teams habilitado e `team_foreign_key = farm_id` — roles scopadas por fazenda.
- `Gate::before` retorna `true` se `$user->is_root` — root vê tudo.
- Middleware `farm.active` bloqueia farms com `status=blocked` (preparado para inadimplência da Fase 6).

### Cadastro

Fluxo: `POST /register` → `RegisterFarmRequest` → `RegisterFarmAction::execute()`:
1. Em transação: cria `farm` (status `trial`), `user`, `subscription`.
2. Atribui role `admin` ao usuário no team da fazenda.
3. Dispara evento `FarmRegistered`.
4. Listener `SendWelcomeEmail` (queued, queue `emails`) envia `WelcomeFarmMail`.
5. Auto-login + redirect `/dashboard`.

## Deploy

Pendente — coberto na Fase 8 (VPS HostGator com Ubuntu, Nginx, PHP-FPM, Supervisor, Let's Encrypt).
