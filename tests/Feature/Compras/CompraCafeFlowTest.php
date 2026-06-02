<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Movement;

it('cria expense + movement de entrada na fazenda ao comprar café côco', function () {
    $admin = makeFarmUser('admin');
    $farm = $admin->farm;
    ExpenseCategory::seedDefaultsForFarm($farm->id);

    $resp = $this->actingAs($admin)->post('/compras', [
        'data' => '2026-06-01',
        'produto' => 'coco',
        'quantidade_kg' => 120,
        'valor_unitario' => 8.5,
        'valor_total' => 1020,
        'fornecedor' => 'João Roça',
    ]);

    $resp->assertRedirect('/compras');

    $expense = Expense::query()->first();
    expect($expense)->not->toBeNull()
        ->and((float) $expense->valor_total)->toBe(1020.0)
        ->and($expense->category->nome)->toBe('Compra de café')
        ->and($expense->descricao)->toContain('120,00 kg')
        ->and($expense->descricao)->toContain('João Roça');

    $movement = Movement::query()->first();
    expect($movement)->not->toBeNull()
        ->and($movement->tipo)->toBe('compra')
        ->and($movement->produto)->toBe('coco')
        ->and((float) $movement->quantidade_kg)->toBe(120.0)
        ->and($movement->source_id)->toBe($expense->id);

    expect((float) $farm->fresh()->saldo_coco_kg)->toBe(120.0);
});

it('aceita compra de café seco e credita o saldo correto', function () {
    $admin = makeFarmUser('admin');
    $farm = $admin->farm;
    ExpenseCategory::seedDefaultsForFarm($farm->id);

    $this->actingAs($admin)->post('/compras', [
        'data' => '2026-06-01',
        'produto' => 'seco',
        'quantidade_kg' => 60,
        'valor_total' => 1800,
    ])->assertRedirect('/compras');

    expect((float) $farm->fresh()->saldo_seco_kg)->toBe(60.0)
        ->and((float) $farm->fresh()->saldo_coco_kg)->toBe(0.0);
});

it('rejeita compra com quantidade zero ou negativa', function () {
    $admin = makeFarmUser('admin');
    ExpenseCategory::seedDefaultsForFarm($admin->farm->id);

    $this->actingAs($admin)->post('/compras', [
        'data' => '2026-06-01',
        'produto' => 'coco',
        'quantidade_kg' => 0,
        'valor_total' => 100,
    ])->assertSessionHasErrors('quantidade_kg');

    $this->actingAs($admin)->post('/compras', [
        'data' => '2026-06-01',
        'produto' => 'coco',
        'quantidade_kg' => -10,
        'valor_total' => 100,
    ])->assertSessionHasErrors('quantidade_kg');
});

it('rejeita produto inválido', function () {
    $admin = makeFarmUser('admin');
    ExpenseCategory::seedDefaultsForFarm($admin->farm->id);

    $this->actingAs($admin)->post('/compras', [
        'data' => '2026-06-01',
        'produto' => 'banana',
        'quantidade_kg' => 50,
        'valor_total' => 500,
    ])->assertSessionHasErrors('produto');
});

it('rejeita data no futuro', function () {
    $admin = makeFarmUser('admin');
    ExpenseCategory::seedDefaultsForFarm($admin->farm->id);

    $amanha = now()->addDay()->format('Y-m-d');

    $this->actingAs($admin)->post('/compras', [
        'data' => $amanha,
        'produto' => 'coco',
        'quantidade_kg' => 50,
        'valor_total' => 500,
    ])->assertSessionHasErrors('data');
});

it('cria categoria Compra de café se ainda não existe na farm', function () {
    $admin = makeFarmUser('admin');
    $farm = $admin->farm;

    // Apaga a categoria pra simular fazenda pre-feature
    ExpenseCategory::query()
        ->where('farm_id', $farm->id)
        ->where('nome', 'Compra de café')
        ->delete();

    $this->actingAs($admin)->post('/compras', [
        'data' => '2026-06-01',
        'produto' => 'coco',
        'quantidade_kg' => 30,
        'valor_total' => 300,
    ])->assertRedirect('/compras');

    expect(ExpenseCategory::query()->where('farm_id', $farm->id)->where('nome', 'Compra de café')->exists())->toBeTrue();
});

it('listagem de compras só mostra Expenses da categoria Compra de café', function () {
    $admin = makeFarmUser('admin');
    ExpenseCategory::seedDefaultsForFarm($admin->farm->id);

    // 1 compra de café (gera Expense + Movement)
    $this->actingAs($admin)->post('/compras', [
        'data' => '2026-06-01', 'produto' => 'coco',
        'quantidade_kg' => 100, 'valor_total' => 800,
        'fornecedor' => 'FORNECEDOR_VISIVEL',
    ]);

    // 1 despesa comum (não compra) — cria direto pra não poluir flash da sessão
    $catDiesel = ExpenseCategory::query()->where('farm_id', $admin->farm->id)->where('nome', 'Combustível')->first();
    \App\Models\Expense::create([
        'farm_id' => $admin->farm->id,
        'user_id' => $admin->id,
        'expense_category_id' => $catDiesel->id,
        'data' => '2026-06-01',
        'descricao' => 'DESPESA_NAO_COMPRA',
        'valor_total' => 200,
    ]);

    $this->actingAs($admin)->get('/compras')
        ->assertOk()
        ->assertSee('FORNECEDOR_VISIVEL')
        ->assertDontSee('DESPESA_NAO_COMPRA');
});
