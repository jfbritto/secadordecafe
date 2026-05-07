# Fase 3 — Núcleo de Negócio — Spec

**Data:** 2026-05-06
**Depende de:** Fases 1, 2

## Objetivo

A feature principal do sistema:

1. **Movimentações** de café (ledger append-only) — toda alteração de saldo gera movimento auditável.
2. **Secagens multi-cliente** — uma secagem agrega N clientes, cada um com sua quantidade; ao concluir, debita o saldo dos clientes envolvidos.
3. **Cálculos automáticos** replicando a planilha original (rendimento, comissão, saldo líquido).
4. Saldo do cliente **nunca pode ser negativo**.

## Modelagem

### `movements` (ledger)
| campo | tipo |
|-------|------|
| id | bigint pk |
| farm_id | bigint fk farms cascade |
| customer_id | bigint fk customers cascade |
| user_id | bigint fk users nullOnDelete |
| tipo | enum('entrada','secagem','ajuste','saida') |
| quantidade_kg | decimal(12,3) — sinalizado: positivo = entrada/credit, negativo = débito |
| observacao | text nullable |
| source_type | string nullable (morph) |
| source_id | bigint nullable (morph) |
| occurred_at | timestamp — quando o movimento aconteceu (default now) |
| timestamps | |

Index: `(customer_id, occurred_at)`, `(farm_id, tipo, occurred_at)`.

Convenção de sinal: campo armazena positivo para entrada/ajuste-positivo, negativo para secagem/saída/ajuste-negativo. Saldo = sum(quantidade_kg) por cliente.

### `secagens`
| campo | tipo |
|-------|------|
| id | bigint pk |
| farm_id | bigint fk farms cascade |
| user_id | bigint fk users nullOnDelete |
| numero | int (sequencial por fazenda) |
| data | date — dia da secagem |
| secador | string(80) — identificador do secador |
| observacoes | text nullable |
| status | enum('rascunho','concluida') default 'rascunho' |
| concluida_at | timestamp nullable |
| timestamps | |

Unique `(farm_id, numero)`.

### `secagem_items`
| campo | tipo |
|-------|------|
| id | bigint pk |
| farm_id | bigint fk farms cascade (denormalizado para scope) |
| secagem_id | bigint fk secagens cascade |
| customer_id | bigint fk customers restrict |
| quantidade_recebida_kg | decimal(12,3) — café côco bruto entregue |
| quantidade_seca_kg | decimal(12,3) — pilado/seco resultante |
| comissao_percentual | decimal(5,2) default 0 |
| comissao_kg | decimal(12,3) — calculado: seca * percentual/100 |
| saldo_liquido_kg | decimal(12,3) — calculado: seca - comissao |
| timestamps | |

Index: `(secagem_id)`, `(customer_id)`.

## Regras

### Saldo do cliente
Mantemos `customers.saldo_cafe_kg` como denormalização, **sempre atualizado em transação com a movimentação**. Pode ser reconstruído via `SUM(movements.quantidade_kg) WHERE customer_id`.

### Movimentação manual (entrada/ajuste/saída)
- `RegisterMovementAction::execute(customer, tipo, quantidade, observacao, occurredAt, user)`:
  - Em transação:
    - Trava cliente com `lockForUpdate()`.
    - Calcula sinal do `quantidade_kg` baseado no tipo:
      - `entrada`, `ajuste` (positivo): valor positivo enviado como positivo
      - `saida`, `ajuste` (negativo): valor positivo enviado como negativo
    - Para débitos: valida `cliente.saldo_cafe_kg + signed_qty >= 0`, senão `DomainException`.
    - Cria `movement`.
    - Atualiza `cliente.saldo_cafe_kg += signed_qty`.

UI: Form único com radio (entrada / saída / ajuste +/-) e quantidade. Operadores podem entrada/ajuste; admin pode tudo.

### Secagem
- Criar (rascunho): admin/operador. Apenas dados básicos (data, secador, observações).
- Adicionar/editar items no rascunho: cliente, quantidade_recebida, quantidade_seca, comissao_percentual.
  - Cálculos aplicados ao salvar item:
    - `comissao_kg = round(quantidade_seca_kg * comissao_percentual / 100, 3)`
    - `saldo_liquido_kg = quantidade_seca_kg - comissao_kg` (informativo; debitar usa quantidade_recebida_kg)
  - **Decisão de débito:** o saldo do cliente representa **café côco em estoque**. Ao concluir, debitamos `quantidade_recebida_kg` do saldo. O resultado seco (saldo_liquido_kg) pode posteriormente virar nova entrada no extrato (Fase futura) ou é apenas informacional para emissão. Para manter Fase 3 enxuta: **debitamos quantidade_recebida_kg apenas**; o seco não retorna ao saldo.
- Excluir item (somente em rascunho).
- Excluir secagem em rascunho.
- **Concluir secagem** (`ConcludeSecagemAction`):
  - Em transação:
    - Trava todos os clientes envolvidos (`lockForUpdate()`).
    - Para cada item, valida saldo suficiente (`cliente.saldo_cafe_kg >= quantidade_recebida_kg`).
    - Para cada item, cria `movement` (tipo `secagem`, quantidade `-quantidade_recebida_kg`, source = secagem_item).
    - Decrementa `cliente.saldo_cafe_kg`.
    - `secagem.status = concluida; concluida_at = now()`.
- Secagens **concluídas são imutáveis** em Fase 3 (sem cancelamento). Reverso ficará para fase de auditoria/correção.
- Numeração `numero` é sequencial por fazenda — calculada na criação como `max(numero)+1` dentro de transação.

## Rotas

```
GET    /clientes/{cliente}/movimentacoes        list movements (extrato)
POST   /clientes/{cliente}/movimentacoes        create entrada/ajuste/saida

GET    /secagens                                index
GET    /secagens/criar                          create form
POST   /secagens                                store (rascunho)
GET    /secagens/{secagem}                      show
GET    /secagens/{secagem}/editar               edit (header + items)
PUT    /secagens/{secagem}                      update header
DELETE /secagens/{secagem}                      destroy (apenas rascunho)
POST   /secagens/{secagem}/items                add item
PUT    /secagens/{secagem}/items/{item}         update item
DELETE /secagens/{secagem}/items/{item}         remove item
POST   /secagens/{secagem}/concluir             conclude (debita)
```

## Policies

- `MovementPolicy`: create — admin / operador (entrada, ajuste); admin only para `saida` (regulação extra para evitar drenagens).
- `SecagemPolicy`: viewAny/view (qualquer auth na farm), create/update — admin / operador, delete — admin (somente rascunho), concluir — admin / operador.

## Tests (Pest)

- `MovementSaldoTest` — entrada incrementa, saída decrementa, ajuste +/- funciona, saldo não vai negativo.
- `MovementPolicyTest` — operador 403 em saída.
- `SecagemDraftTest` — criar/editar/excluir rascunho.
- `SecagemConcludeTest` — concluir debita saldos, gera movements vinculados, status=concluida; bloqueia se saldo insuficiente; concluída não pode ser editada.
- `SecagemTenancyTest` — sem vazamento entre fazendas.
- `RendimentoCalculationTest` — comissão/saldo líquido calculados corretamente em diferentes cenários.

## Critérios de aceite

- [x] Toda alteração de saldo passa por movement (auditável)
- [x] Saldo nunca fica negativo (testado)
- [x] Secagem multi-cliente funcional
- [x] Cálculos automáticos batem com a planilha original
- [x] Concluir gera 1 movement por item, transacional
- [x] Tenancy intocável

## Decisões adiadas (futuras fases)

- Reverter / cancelar secagem
- Saldo seco retornar ao cliente como crédito (entrada automática)
- Saídas para venda com preço/contraparte
- Importação CSV histórico
