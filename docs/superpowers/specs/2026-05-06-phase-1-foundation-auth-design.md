# Fase 1 — Fundação + Auth Multi-tenant — Spec

**Data:** 2026-05-06
**Escopo:** primeiro sub-projeto do roadmap mestre
**Status:** aprovado (autorização blanket do usuário em auto mode)

## Objetivo

Entregar uma base Laravel funcional, em Docker, onde:

1. Um visitante consegue cadastrar uma fazenda + conta admin (transação atômica).
2. Recebe e-mail de boas-vindas (capturado pelo Mailhog).
3. Faz login e cai num dashboard placeholder.
4. Toda query a entidades de tenant é automaticamente escopada pela `farm_id` do usuário autenticado.
5. Usuário ROOT da plataforma existe (seeded) e bypassa o scope.
6. Fazenda com `status != active` bloqueia acesso (preparado para inadimplência da Fase 6).

Não-objetivos (ficam para fases posteriores): CRUD de clientes, secagens, despesas, dashboards reais, integração Asaas, deploy.

## Stack

- Laravel 11.x (LTS estável atual em 2026-05)
- PHP 8.3 (php-fpm)
- MySQL 8.4
- Redis 7
- Mailhog (smtp 1025 / web 8025)
- Nginx 1.27 alpine
- Vite + Tailwind (já vem com Laravel kit)
- Blade + AdminLTE 3 via package `jeroennoten/laravel-adminlte`
- Spatie Permission v6
- Spatie Activitylog v4
- Pest v3 para testes

Justificativas:
- Laravel 11 vs 12: 11 está mais maduro com docs/AdminLTE compatibilidade testada. Migração para 12 fica trivial depois.
- AdminLTE via `jeroennoten/laravel-adminlte`: provê layout pronto, slots Blade, integração de menu config-driven. Alternativa: assets manuais → mais trabalho sem ganho.
- Pest > PHPUnit puro: sintaxe enxuta, BR-friendly, mantém compatibilidade com infra PHPUnit.

## Containers e Portas

| Serviço | Porta host | Porta container |
|---------|------------|-----------------|
| nginx (web) | 8080 | 80 |
| php-fpm | (interna) | 9000 |
| mysql | 3307 | 3306 |
| redis | 6380 | 6379 |
| mailhog smtp | 1025 | 1025 |
| mailhog web | 8025 | 8025 |
| vite (dev) | 5173 | 5173 |

Portas host deslocadas para evitar choque com serviços já rodando na máquina.

## Estrutura de Pastas (Fase 1)

```
.
├── docker-compose.yml
├── docker/
│   ├── nginx/default.conf
│   └── php/{Dockerfile,php.ini,opcache.ini}
├── docs/superpowers/{specs,plans}/
├── (Laravel scaffold completo após bootstrap)
│   ├── app/
│   │   ├── Actions/Auth/RegisterFarmAction.php
│   │   ├── Http/Controllers/Auth/RegisterController.php
│   │   ├── Http/Middleware/{EnsureFarmActive.php,SetTenantContext.php}
│   │   ├── Http/Requests/Auth/RegisterFarmRequest.php
│   │   ├── Models/{Farm.php,User.php,Subscription.php}
│   │   ├── Models/Concerns/BelongsToFarm.php
│   │   ├── Mail/WelcomeFarmMail.php
│   │   └── Providers/AuthServiceProvider.php
│   ├── database/
│   │   ├── migrations/...
│   │   ├── seeders/{DatabaseSeeder,RoleSeeder,RootUserSeeder}.php
│   │   └── factories/{FarmFactory,UserFactory}.php
│   ├── resources/views/
│   │   ├── auth/{register,login,...}.blade.php
│   │   ├── dashboard.blade.php
│   │   └── emails/welcome-farm.blade.php
│   ├── routes/{web.php,auth.php}
│   └── tests/Feature/{RegisterFarmTest,LoginTest,TenancyScopeTest,FarmStatusGateTest}.php
├── README.md
└── .gitignore
```

## Modelagem (Fase 1)

### Tabela `farms`
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint pk | |
| nome | string(150) | obrigatório |
| slug | string(180) unique | gerado de `nome`, único global |
| status | enum('active','blocked','trial') default 'trial' | |
| telefone | string(30) nullable | |
| cidade | string(120) nullable | |
| estado | char(2) nullable | UF |
| trial_ends_at | timestamp nullable | calculado: now + 14 dias |
| created_at, updated_at | timestamps | |

### Tabela `users`
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint pk | |
| farm_id | bigint nullable fk farms | nullable só para usuário ROOT |
| name | string | |
| email | string unique | |
| email_verified_at | timestamp nullable | |
| password | string | bcrypt |
| is_root | bool default false | usuário ROOT da plataforma |
| remember_token | string nullable | |
| timestamps | | |

Índices: `(farm_id)`, `(email)`.

### Tabela `subscriptions` (estrutura, integração na Fase 6)
| Campo | Tipo |
|-------|------|
| id | bigint pk |
| farm_id | bigint fk farms unique |
| status | enum('trial','active','past_due','canceled','blocked') |
| asaas_customer_id | string nullable |
| asaas_subscription_id | string nullable |
| trial_ends_at | timestamp nullable |
| current_period_end | timestamp nullable |
| timestamps | |

### Tabelas Spatie Permission
Padrão da package, com `team_foreign_key = farm_id` e `teams = true` para escopar roles por fazenda.

## Fluxo de Cadastro

1. `GET /register` → exibe form (nome fazenda, nome, e-mail, senha, confirmação).
2. `POST /register` → `RegisterFarmRequest` valida.
3. `RegisterFarmAction::execute(dto)`:
   - Abre transação.
   - Cria `farm` (status=`trial`, trial_ends_at = now()+14 dias, slug único).
   - Cria `user` (farm_id, password hash).
   - Atribui role `admin` ao user dentro do team `farm_id`.
   - Cria `subscription` (status=`trial`).
   - Dispara evento `FarmRegistered`.
   - Commit.
4. Listener `SendWelcomeEmail` envia `WelcomeFarmMail` (queued).
5. Login automático após cadastro → redirect `/dashboard`.

Erros que precisam ser tratados:
- email duplicado → ValidationException 422
- nome fazenda duplicado (slug colidir) → adicionar sufixo numérico até ficar único
- falha no envio do e-mail → não deve quebrar o cadastro (job em queue, retry)

## Multi-Tenancy

### Trait `BelongsToFarm`
Aplica em modelos de tenant (próximas fases: `Customer`, `Secagem`, ...). Em Fase 1 usamos no `User` parcialmente.

```php
trait BelongsToFarm {
    protected static function bootBelongsToFarm(): void {
        static::creating(function ($model) {
            if (auth()->check() && !auth()->user()->is_root && !$model->farm_id) {
                $model->farm_id = auth()->user()->farm_id;
            }
        });

        static::addGlobalScope('farm', function ($query) {
            if (auth()->check() && !auth()->user()->is_root) {
                $query->where($query->getModel()->getTable().'.farm_id', auth()->user()->farm_id);
            }
        });
    }

    public function farm(): BelongsTo {
        return $this->belongsTo(Farm::class);
    }
}
```

### Middleware `EnsureFarmActive`
Aplicado no grupo `auth`. Bloqueia acesso se `auth()->user()->farm->status === 'blocked'` (usuário ROOT bypassa). Redireciona para tela `/farm/blocked` com instrução de regularização.

### Middleware `SetTenantContext`
Define `app()->instance('current_farm', auth()->user()->farm)` para acesso conveniente em views/services. Logs incluem `farm_id` no contexto via `Log::withContext(['farm_id' => ...])`.

## Roles e Permissões (Fase 1)

Roles seed:
- `root` (sem team, ou flag `is_root` = true) — bypassa policies
- `admin` — acesso total dentro da fazenda
- `operador` — operações de café (placeholder, ativa em fases futuras)
- `financeiro` — financeiro (placeholder)
- `visualizador` — read-only (placeholder)

Permissões granulares são adicionadas conforme fases evoluem. Em Fase 1 só `admin` precisa estar funcional.

Gate global `before`:
```php
Gate::before(fn ($user) => $user->is_root ? true : null);
```

## E-mail

- Driver `smtp` apontando para `mailhog:1025` em dev.
- Mailable `WelcomeFarmMail` (queued, queue=`emails`).
- Template Blade simples com nome da fazenda e link para dashboard.
- Em produção: SMTP real configurado via env (Asaas tem provedores parceiros, decidir na Fase 8).

## Layout (AdminLTE)

- Layout pai `layouts.app` estende AdminLTE.
- Menu config em `config/adminlte.php` com itens placeholder (Dashboard).
- Tela `/dashboard` mostra: nome da fazenda, status da assinatura (trial X dias restantes), placeholders ("Clientes 0, Saldo 0kg, Secagens 0").

## Testes (Pest)

Arquivos:
- `tests/Feature/Auth/RegisterFarmTest.php` — fluxo feliz, e-mail duplicado, validação, slug colidente
- `tests/Feature/Auth/LoginTest.php` — login, logout, lembrar
- `tests/Feature/Tenancy/TenancyScopeTest.php` — usuários de fazendas diferentes não veem dados um do outro (cria 2 fazendas, 2 admins, faz query e confere)
- `tests/Feature/Tenancy/FarmStatusGateTest.php` — usuário de fazenda `blocked` não acessa dashboard
- `tests/Feature/Auth/RootUserTest.php` — root bypassa scope e enxerga ambos os tenants

Banco de teste: SQLite in-memory (rápido) ou MySQL container dedicado. Decisão: começar com SQLite para Fase 1; trocar para MySQL via `phpunit.xml` quando regras precisarem de features MySQL específicas.

## Dependências de Composer

```
laravel/framework: ^11.0
spatie/laravel-permission: ^6.9
spatie/laravel-activitylog: ^4.8
jeroennoten/laravel-adminlte: ^3.13
laravel/breeze: ^2.3 (apenas para scaffolding inicial; vamos customizar register)
pestphp/pest: ^3.0 (--dev)
pestphp/pest-plugin-laravel: ^3.0 (--dev)
```

## NPM (Vite)

`vite`, `laravel-vite-plugin`, `tailwindcss`, `axios`, `@popperjs/core`, `bootstrap` (para AdminLTE 3 baseado em Bootstrap 4).

## Variáveis de Ambiente Críticas

```
APP_NAME=secadordecafe
APP_ENV=local
APP_KEY=(php artisan key:generate)
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=secadordecafe
DB_USERNAME=secadordecafe
DB_PASSWORD=secret

REDIS_HOST=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
CACHE_STORE=redis

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS="no-reply@secadordecafe.test"

# Fase 6 (placeholders)
ASAAS_API_KEY=
ASAAS_ENV=sandbox
ASAAS_WEBHOOK_TOKEN=
```

## Comandos make-style (script `dev`)

Arquivo `bin/dev` (bash) com subcomandos:
- `up` — `docker compose up -d`
- `down` — `docker compose down`
- `art <cmd>` — `docker compose exec app php artisan <cmd>`
- `composer <cmd>` — `docker compose exec app composer <cmd>`
- `npm <cmd>` — `docker compose exec app npm <cmd>`
- `test` — `docker compose exec app php artisan test`
- `fresh` — `art migrate:fresh --seed`

## Critérios de Aceite Fase 1

- [x] Stack sobe com `docker compose up -d`
- [x] `bin/dev fresh` cria schema + roles + usuário root
- [x] `GET /register` renderiza
- [x] `POST /register` cria farm+user+subscription numa transação
- [x] E-mail de boas-vindas chega no Mailhog
- [x] Login funciona, redireciona pra `/dashboard`
- [x] Dashboard mostra info da fazenda
- [x] User de fazenda A não enxerga dados de fazenda B (verificado via teste)
- [x] User ROOT enxerga todas
- [x] User de fazenda `blocked` é redirecionado pra tela de bloqueio
- [x] Todos testes Pest passando

## Riscos e Mitigações

| Risco | Mitigação |
|-------|-----------|
| Build de imagens lento na primeira vez | Documentar no README; usar buildkit cache |
| Spatie Permission + global scope conflito | Testar `is_root` bypass cedo |
| AdminLTE assets quebrarem com Vite | Publicar assets via `php artisan adminlte:install` e ajustar `vite.config.js` |
| Conflito de portas host | Usar 8080/3307/6380 (já offset) |
| `farm_id` aceito do request | Form Requests não incluem `farm_id`; trait força via `creating` hook |

## Decisões Pendentes (ficam para fases posteriores)

- Estratégia exata de billing dunning (Fase 6)
- Pipeline CI/CD (Fase 8)
- Backup strategy (Fase 8)
