# Roadmap Mestre — secadordecafe

**Data:** 2026-05-06
**Status:** Em execução (Fase 1 ativa)

## Visão Geral

Plataforma SaaS multi-tenant para gestão de fazendas e secagem de café. Stack: Laravel + Blade + AdminLTE + MySQL + Redis + Mailhog. Deploy em VPS HostGator. Cobrança Asaas.

O escopo do prompt original (`prompt-secagem-cafe.md`) descreve **8 subsistemas independentes**. Cada um terá seu próprio spec → plan → implementação. Esta decomposição evita retrabalho e mantém ciclos curtos de validação.

## Fases

### Fase 1 — Fundação + Auth Multi-tenant **(em andamento)**
Entrega o menor incremento útil: alguém consegue cadastrar uma fazenda, criar conta admin, fazer login, e ver um dashboard vazio com middleware de tenant funcionando.

Inclui:
- Stack Docker completo (PHP-FPM, Nginx, MySQL, Redis, Mailhog)
- Laravel scaffolding com Vite e AdminLTE
- Spatie Permission + Spatie Activitylog instalados
- Migrações: `farms`, `users` (com `farm_id`), Spatie tables, `subscriptions` (apenas estrutura, integração Asaas vem na Fase 6)
- Models: `Farm`, `User`
- Cadastro custom: cria fazenda + admin atomicamente em transação
- Login + verificação de e-mail + reset de senha
- Middleware `EnsureFarmActive` e global scope `ScopeByFarm`
- Roles seed: `root`, `admin`, `operador`, `financeiro`, `visualizador`
- Usuário ROOT seed
- Layout AdminLTE base + dashboard placeholder
- Testes Pest cobrindo o fluxo

Spec: `docs/superpowers/specs/2026-05-06-phase-1-foundation-auth-design.md`

### Fase 2 — Cadastros Base
CRUD de fazendas (gestão pelo admin), usuários da fazenda, convites por e-mail, clientes (produtores).

Foco: telas funcionais, permissões via policies, busca e paginação.

### Fase 3 — Núcleo de Negócio (Secagens)
Feature principal:
- Saldo de café por cliente (kg + sacas computado)
- Movimentações (entrada, secagem, ajuste, saída) — append-only
- Secagens multi-cliente (1 secagem N clientes, cada um com quantidade própria)
- Cálculos automáticos (rendimento, comissão, saldo líquido) replicando planilha
- Validação dura: nunca permitir saldo negativo; quantidade ≤ saldo do cliente no momento da operação
- Geração automática de movimentação ao confirmar secagem (transacional)
- Extrato/histórico do cliente

### Fase 4 — Financeiro (Despesas)
Módulo simples: CRUD de despesas, categorias, filtros, totalizadores e relatórios mensais.

### Fase 5 — Dashboards
Dashboard fazenda (totais, gráficos, últimas movimentações). Dashboard ROOT (fazendas ativas, receita, inadimplência).

### Fase 6 — Cobrança Asaas
- Criação de assinatura ao registrar fazenda (trial)
- Cobrança recorrente
- Webhook Asaas (signature validation, idempotência via `event_id`)
- Atualização automática de status (`trial` → `ativo` → `atrasado` → `cancelado` / `bloqueado`)
- Bloqueio de acesso quando inadimplente (middleware já preparado na Fase 1)

### Fase 7 — Funcionalidades de Suporte
- Logs de auditoria (Activitylog) revisão e telas de auditoria
- Refinos de UX
- Relatórios PDF
- Hardening de segurança (rate limit por rota crítica, headers, CSP)

### Fase 8 — Deploy e Infra
- Provisionamento VPS HostGator (Ubuntu, Nginx, PHP-FPM, MySQL, Supervisor, certbot)
- Pipeline de deploy (script ou GitHub Actions, sem destrutivo)
- Ambientes staging/produção
- Backups automáticos (cron + dump + retenção)
- Monitoramento básico (logs centralizados, uptime, alertas por e-mail)

## Princípios Transversais

- **Multi-tenant simplificado:** coluna `farm_id` em entidades de tenant; global scope automático; root bypassa via gate.
- **Segurança:** `farm_id` nunca aceito do request — sempre derivado do usuário autenticado.
- **Saldos:** sempre derivados de movimentações (event-sourcing-like) ou mantidos com transação explícita; nunca atualização in-place sem trilha.
- **Idempotência:** webhook Asaas e operações de secagem precisam ser idempotentes.
- **Auditoria:** Activitylog em todas entidades sensíveis (farms, users, secagens, despesas, subscriptions).
- **Testes:** Pest com testes de feature cobrindo regras de negócio. Integração com banco real (não mocks).
- **YAGNI:** sem features marcadas em "Funcionalidades Extras Futuras" do prompt até pedido explícito.

## Estrutura de Pastas (alvo final)

```
app/
  Actions/         # casos de uso atômicos (RegisterFarm, RegisterSecagem, ...)
  Console/
  DTOs/
  Events/
  Exceptions/
  Http/
    Controllers/
    Middleware/
    Requests/      # Form Requests
    Resources/     # API resources se aplicável
  Jobs/
  Listeners/
  Mail/
  Models/
  Policies/
  Repositories/    # quando fizer sentido
  Services/        # serviços com mais de uma responsabilidade
  Support/         # helpers genéricos
docker/
  nginx/
  php/
docs/
  superpowers/
    specs/
    plans/
resources/
  views/
    layouts/
    auth/
    dashboard/
    ...
tests/
  Feature/
  Unit/
```

## Próximos Passos

1. Implementar Fase 1 completamente
2. Após validação, escrever spec + plan da Fase 2
3. Repetir para cada fase

Cada fase encerra com: testes passando, README atualizado, commit semântico.
