# Fase 1 — Plano de Implementação

**Spec:** `docs/superpowers/specs/2026-05-06-phase-1-foundation-auth-design.md`
**Estratégia:** comitar em pontos lógicos, testes Pest no final mas escritos junto.

## Etapas

### E1 — Docker stack
1. Escrever `docker-compose.yml` (services: app, web, mysql, redis, mailhog).
2. `docker/php/Dockerfile` baseado em `php:8.3-fpm-alpine` com extensions (pdo_mysql, redis, gd, intl, opcache, bcmath, zip).
3. `docker/php/php.ini` (upload_max, memory_limit, opcache).
4. `docker/nginx/default.conf` apontando `/public/index.php`.
5. `bin/dev` script bash com subcomandos.
6. `.gitignore` + `.dockerignore`.

**Verificação E1:** `docker compose config` valida sem erro; build da imagem PHP roda.

### E2 — Laravel scaffold
1. Rodar `docker run --rm -v "$PWD":/app composer:2 create-project laravel/laravel:^11.0 /app/_laravel_tmp`.
2. Mover conteúdo de `_laravel_tmp` para a raiz, preservando arquivos já criados (compose, docker/, docs/).
3. Subir stack: `docker compose up -d`.
4. `composer install` dentro do container.
5. Configurar `.env` (DB, Redis, Mail, App).
6. `php artisan key:generate`.
7. `php artisan migrate` (sanity check).

**Verificação E2:** `curl http://localhost:8080` retorna welcome do Laravel.

### E3 — Pacotes
1. `composer require spatie/laravel-permission spatie/laravel-activitylog jeroennoten/laravel-adminlte`.
2. `composer require pestphp/pest pestphp/pest-plugin-laravel --dev`.
3. Publicar configs:
   - `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
   - `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"`
   - `php artisan adminlte:install --type=enhanced --with=auth_views --interactive=false` (depois customizar)
4. Inicializar Pest: `php artisan pest:install`.
5. Configurar Spatie Permission para teams (farm_id):
   - editar `config/permission.php` → `teams = true`, `team_foreign_key = farm_id`.

**Verificação E3:** `php artisan test` (suite vazia) passa.

### E4 — Migrations
1. `database/migrations/xxxx_create_farms_table.php`.
2. Editar migration de `users` (Laravel default) para adicionar `farm_id nullable`, `is_root bool`.
3. `database/migrations/xxxx_create_subscriptions_table.php`.
4. `php artisan migrate:fresh`.

**Verificação E4:** Schema bate com spec; `\Schema::hasColumn('users','farm_id')` true.

### E5 — Models e Relacionamentos
1. `app/Models/Farm.php` — fillable, casts (status, trial_ends_at), relations (users, subscription).
2. Editar `app/Models/User.php` — adicionar trait `HasRoles` (Spatie), fillable expandido, relation `farm()`, helper `isRoot()`, scope `nonRoot()`.
3. `app/Models/Subscription.php` — fillable, casts.
4. `app/Models/Concerns/BelongsToFarm.php` — trait conforme spec.
5. Factories.

**Verificação E5:** `php artisan tinker` cria Farm + User OK.

### E6 — Auth scaffolding
1. Customizar rotas `routes/auth.php` (Breeze ou manual).
2. `app/Http/Controllers/Auth/RegisterController.php`:
   - `showForm()` → view `auth.register`
   - `store(RegisterFarmRequest)` → chama action.
3. `app/Http/Requests/Auth/RegisterFarmRequest.php` — regras (farm_name required, email unique users).
4. `app/Actions/Auth/RegisterFarmAction.php` — transação criando Farm + User + Subscription, atribui role admin, dispara `FarmRegistered`.
5. `app/Events/FarmRegistered.php` + `app/Listeners/SendWelcomeEmail.php`.
6. `app/Mail/WelcomeFarmMail.php` (queued).
7. View `resources/views/auth/register.blade.php`.

**Verificação E6:** Cadastro pelo browser cria registros, e-mail aparece no Mailhog.

### E7 — Login/Logout/Reset
1. Login controller (Breeze deixa pronto, ajustar redirect).
2. Reset de senha (Breeze pronto).
3. Email verification (opcional Fase 1; deixar habilitado mas não bloqueante).

**Verificação E7:** Login funcional, logout funcional.

### E8 — Multi-tenant middleware
1. `app/Http/Middleware/SetTenantContext.php` — define `current_farm` no container, adiciona contexto ao logger.
2. `app/Http/Middleware/EnsureFarmActive.php` — bloqueia se `farm.status === 'blocked'`.
3. Registrar no kernel (`bootstrap/app.php` em Laravel 11) no grupo `auth`.
4. `Gate::before` para root.

**Verificação E8:** middleware aplicado, root bypassa, fazenda blocked redireciona.

### E9 — Seeders
1. `RoleSeeder` — cria roles `root`, `admin`, `operador`, `financeiro`, `visualizador` (sem team na criação; teams aplica em assignment).
2. `RootUserSeeder` — cria user `root@secadordecafe.test` / senha gerada exibida no console, `is_root = true`.
3. `DatabaseSeeder` chama os dois.

**Verificação E9:** `php artisan migrate:fresh --seed` popula corretamente.

### E10 — AdminLTE + Dashboard
1. `php artisan adminlte:install --only=auth_views` para layouts auth (substituir Breeze blade tailwind).
2. Configurar `config/adminlte.php`: nome, logo, menu (item Dashboard).
3. View `resources/views/dashboard.blade.php` estende `adminlte::page`.
4. `DashboardController@index` carrega dados básicos da fazenda.

**Verificação E10:** após login, `/dashboard` renderiza com layout AdminLTE.

### E11 — Tela "Fazenda Bloqueada"
1. Rota `GET /farm/blocked`.
2. View simples com mensagem e suporte (placeholder).

**Verificação E11:** acesso direto renderiza; usuário blocked é redirecionado.

### E12 — Testes Pest
1. Helper `tests/Pest.php` — `uses(RefreshDatabase::class)->in('Feature')`.
2. `tests/Feature/Auth/RegisterFarmTest.php`:
   - cadastra e cria 3 entidades + role admin
   - e-mail duplicado → 422
   - slug colidente → ajustado automaticamente
   - e-mail enfileirado (Mail::fake())
3. `tests/Feature/Auth/LoginTest.php`.
4. `tests/Feature/Tenancy/TenancyScopeTest.php` — duas farms, queries não vazam.
5. `tests/Feature/Tenancy/FarmStatusGateTest.php` — blocked redireciona.
6. `tests/Feature/Auth/RootUserTest.php` — root vê tudo.

**Verificação E12:** `bin/dev test` passa 100%.

### E13 — README + Commit
1. README.md com:
   - Visão geral
   - Pré-requisitos (Docker, git)
   - Setup (`bin/dev up && bin/dev fresh`)
   - URLs (app, mailhog, mysql, redis)
   - Comandos do `bin/dev`
   - Onde está documentação
2. Commit: `chore(phase-1): foundation + multi-tenant auth + docker stack`.

**Verificação final:** repo limpo, branch `main`, testes verdes, README renderiza, dashboard acessível.

## Ordem de Execução Real (otimizada para validações cedo)

E1 → E2 → E4 (migrations já cria schema) → E3 (pacotes) → E5 → E6+E7 (auth juntos) → E9 (seeders) → E8 (middleware tenancy) → E10 (layout) → E11 (blocked page) → E12 (testes) → E13 (commit).

## Pontos de Atenção Durante Execução

- **Permissões de arquivo Linux dentro do volume Docker:** rodar php-fpm com user mapeado para UID do host (1000). Documentar caso o host seja diferente.
- **`composer create-project` em diretório não vazio:** usar dir tmp e mover.
- **Spatie teams + roles:** ao atribuir role `admin`, setar `setPermissionsTeamId(farm.id)` antes do `assignRole`.
- **Activitylog:** habilitar `LogsActivity` apenas em models críticos para evitar barulho (Farm, User; Subscription quando entrar Fase 6).
- **Vite + Docker:** `vite.config.js` com `server.host = '0.0.0.0'` e HMR `host=localhost:5173`.

## Saídas Esperadas

- `docker-compose.yml`, `docker/`, `bin/dev`
- Laravel app completo
- Migrations + seeders rodando
- Auth funcional + e-mail
- Tenancy operante
- Layout AdminLTE
- Suite Pest verde
- README + commit
- Spec/plan atualizados se algo divergir
