# Fase 2 — Cadastros Base — Spec

**Data:** 2026-05-06
**Depende de:** Fase 1
**Status:** em execução

## Objetivo

Entregar os cadastros que sustentam o resto do sistema:
- **Clientes (produtores)** da fazenda — entidade principal usada nas secagens
- **Configurações da fazenda** — admin pode editar dados da própria fazenda
- **Usuários da fazenda** — admin lista/altera role/remove
- **Convites por e-mail** — admin convida novos usuários (operador/financeiro/visualizador)

Não-objetivos (próximas fases):
- Movimentações e secagens (Fase 3)
- Despesas (Fase 4)
- Histórico/extrato do cliente (Fase 3 com movimentações)

## Entidades

### Customer (`customers`)
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint pk | |
| farm_id | bigint fk farms cascade | scopado por tenant |
| nome | string(150) | required, indexado para busca |
| telefone | string(30) | nullable |
| cpf_cnpj | string(20) | nullable; índice único composto `(farm_id, cpf_cnpj)` ignora null |
| observacoes | text | nullable |
| saldo_cafe_kg | decimal(12,3) default 0 | mutável só via movimentações na Fase 3 (Fase 2 permite valor inicial) |
| timestamps | | |

`Customer` usa trait `BelongsToFarm` (auto scope + auto fill `farm_id`).

**`saldo_cafe_kg` em Fase 2:** lido na listagem, atribuído na criação como saldo inicial (representando estoque já existente no momento de adoção). A partir da Fase 3, edição direta é bloqueada — só movimentações alteram.

### Invitation (`invitations`)
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint pk | |
| farm_id | bigint fk farms cascade | |
| email | string | indexado |
| role | enum('admin','operador','financeiro','visualizador') | |
| token | string(64) unique | sha256 hex |
| expires_at | timestamp | now+7d |
| accepted_at | timestamp nullable | |
| invited_by | bigint fk users nullable set null | |
| timestamps | | |

Único por `(farm_id, email)` quando não aceito.

## Roles e Permissões

| Ação | admin | operador | financeiro | visualizador |
|------|:----:|:---:|:---:|:---:|
| Listar clientes | ✓ | ✓ | ✓ | ✓ |
| Criar/editar/excluir cliente | ✓ | ✓ | — | — |
| Editar fazenda | ✓ | — | — | — |
| Listar usuários | ✓ | — | — | — |
| Convidar/remover usuário | ✓ | — | — | — |
| Alterar role de usuário | ✓ | — | — | — |

Root continua bypassando tudo via `Gate::before`.

## Rotas (sob `auth + tenant.context + farm.active`)

```
GET    /clientes                (index)        → CustomerController@index
GET    /clientes/criar          (create)       → CustomerController@create
POST   /clientes                (store)        → CustomerController@store
GET    /clientes/{cliente}      (show)         → CustomerController@show
GET    /clientes/{cliente}/editar (edit)       → CustomerController@edit
PUT    /clientes/{cliente}      (update)       → CustomerController@update
DELETE /clientes/{cliente}      (destroy)      → CustomerController@destroy

GET    /fazenda                 (edit)         → FarmController@edit
PUT    /fazenda                 (update)       → FarmController@update

GET    /usuarios                (index)        → UserController@index
PUT    /usuarios/{user}/role    (update role)  → UserController@updateRole
DELETE /usuarios/{user}         (destroy)      → UserController@destroy

POST   /convites                (send)         → InvitationController@store
DELETE /convites/{invitation}   (cancel)       → InvitationController@destroy

# Públicas (token-based)
GET    /convite/{token}         (show form)    → InvitationAcceptController@show
POST   /convite/{token}         (accept)       → InvitationAcceptController@store
```

URLs em português (BR-friendly), nomes em inglês internos (model `Customer` etc).

## Fluxo de Convite

1. Admin chama `POST /convites` com `{email, role}`.
2. `SendInvitationAction`:
   - Valida que email não pertence a usuário da fazenda.
   - Verifica/cria `Invitation` com token aleatório (Str::random(64) → sha256), `expires_at = now+7d`.
   - Dispara `InvitationCreated` → listener envia `InvitationMail` (queued, queue `emails`).
3. Convidado recebe e-mail com link `https://app/convite/{token}`.
4. `GET /convite/{token}` mostra form (nome, senha, confirmar senha).
5. `POST /convite/{token}`:
   - Valida convite não-expirado e não-aceito.
   - Cria `User` na `farm_id` do convite com role do convite.
   - Marca `accepted_at`.
   - Faz login automático.
   - Redirect `/dashboard`.

Edge cases:
- Email já é usuário da fazenda → 422.
- Convite expirado → tela "expirado".
- Convite já aceito → tela "já aceito, faça login".
- Usuário cria conta independente com mesmo e-mail antes de aceitar → ao aceitar, retorna 422 (preferível UX simples).

## Validações

### CustomerStoreRequest
```
nome: required string min:2 max:150
telefone: nullable string max:30
cpf_cnpj: nullable string max:20 unique by farm
observacoes: nullable string max:2000
saldo_cafe_kg: nullable numeric min:0 (default 0 se omitido)
```

### CustomerUpdateRequest
Mesmas regras; em Fase 2, saldo é editável; Fase 3 vamos remover do formulário.

### FarmUpdateRequest
```
nome: required string min:2 max:150
telefone: nullable
cidade: nullable
estado: nullable size:2
```

### InvitationStoreRequest
```
email: required email; not be admin self; not already user of farm
role: required in:admin,operador,financeiro,visualizador
```

### AcceptInvitationRequest
```
name: required min:2 max:120
password: required confirmed Password::min(8)->letters()->numbers()
```

## Policies

`CustomerPolicy` viewAny/view (auth + same farm), create/update/delete (admin or operador), force isso pelo `farm_id` automaticamente pelo trait.

`UserPolicy` viewAny/manage: admin only, e nunca pode mexer em si mesmo (na destroy/role-edit) ou no último admin.

`FarmPolicy` update: admin only.

`InvitationPolicy` create/delete: admin only.

## Layout e Menu

Adicionar itens no header do `layouts.app`:
- Dashboard
- Clientes
- Usuários (visível só pra admin)
- Fazenda (visível só pra admin)

Manter visual atual (CSS inline). Migrar para AdminLTE completo depois (Fase 7 hardening/UX).

## Testes (Pest)

Novos arquivos:
- `tests/Feature/Customers/CustomerCrudTest.php` — list, create, edit, delete + paginação + busca + saldo
- `tests/Feature/Customers/CustomerTenancyTest.php` — admin de A não vê/edita cliente de B; root vê ambos
- `tests/Feature/Customers/CustomerPolicyTest.php` — visualizador 403 em create/update/delete
- `tests/Feature/Farms/FarmSettingsTest.php` — admin edita; operador 403
- `tests/Feature/Users/UserManagementTest.php` — listar, atualizar role, deletar, proteção último admin/self
- `tests/Feature/Invitations/InvitationFlowTest.php` — convidar, aceitar, expirado, já aceito, e-mail enfileirado
- `tests/Feature/Tenancy/CustomerScopeTest.php` — global scope automático no Customer

## Critérios de Aceite

- [ ] Migrations rodam limpas
- [ ] Cliente CRUD funcional pelo browser, com busca por nome
- [ ] Usuário convidado recebe e-mail (Mailhog), aceita e cai no dashboard com role correta
- [ ] Admin não consegue se rebaixar/remover sozinho
- [ ] Operador acessa clientes mas não usuários/fazenda
- [ ] Visualizador apenas lê
- [ ] Tenancy: cliente de A nunca aparece para B
- [ ] Todos os testes Pest passando (Fase 1 + Fase 2)

## Decisões de Implementação

- Sem AdminLTE views ainda — manter visual leve com CSS inline (entrega rápido). Refactor visual ficará para Fase 7.
- Sem soft deletes em Fase 2 — manter simplicidade. Reavaliar quando aparecer demanda de "lixeira".
- Busca de clientes via simples `LIKE` no `nome` — sem Scout/full-text por enquanto.
- Paginação padrão Laravel (15 por página).
