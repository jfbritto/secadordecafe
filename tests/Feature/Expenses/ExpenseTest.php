<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;

function defaultCategory($admin, string $nome = 'Combustível'): ExpenseCategory
{
    // Como Farm::booted faz seed automático, basta buscar.
    return ExpenseCategory::query()
        ->withoutGlobalScopes()
        ->where('farm_id', $admin->farm_id)
        ->where('nome', $nome)
        ->firstOrFail();
}

it('admin can create expense', function () {
    $admin = makeFarmUser('admin');
    $cat = defaultCategory($admin, 'Combustível');

    $this->actingAs($admin)
        ->post('/despesas', [
            'data' => '2026-05-01',
            'descricao' => 'Diesel',
            'expense_category_id' => $cat->id,
            'unidade' => 'L',
            'quantidade' => 100,
            'valor_unitario' => 6.5,
            'valor_total' => 650,
        ])
        ->assertRedirect('/despesas');

    expect(Expense::count())->toBe(1);
    $e = Expense::first();
    expect((float) $e->valor_total)->toBe(650.0);
    expect($e->expense_category_id)->toBe($cat->id);
});

it('financeiro can create but not delete', function () {
    $fin = makeFarmUser('financeiro');
    $cat = defaultCategory($fin, 'Outros');
    $e = Expense::factory()->category($cat)->create();

    $this->actingAs($fin)
        ->post('/despesas', [
            'data' => '2026-05-01', 'descricao' => 'X',
            'expense_category_id' => $cat->id, 'valor_total' => 100,
        ])
        ->assertRedirect();

    $this->actingAs($fin)->delete("/despesas/{$e->id}")->assertForbidden();
});

it('operador cannot view despesas', function () {
    $op = makeFarmUser('operador');
    $this->actingAs($op)->get('/despesas')->assertForbidden();
});

it('lists expenses with totals by category', function () {
    $admin = makeFarmUser('admin');
    $combustivel = defaultCategory($admin, 'Combustível');
    $manutencao = defaultCategory($admin, 'Manutenção');

    Expense::factory()->category($combustivel)->state(['valor_total' => 100])->create();
    Expense::factory()->category($manutencao)->state(['valor_total' => 250])->create();

    // ?all=1 desativa o filtro padrão de mês corrente para ver o histórico todo.
    $this->actingAs($admin)
        ->get('/despesas?all=1')
        ->assertOk()
        ->assertSee('R$ 350,00');
});

it('filters by date range and category', function () {
    $admin = makeFarmUser('admin');
    $combustivel = defaultCategory($admin, 'Combustível');
    $manutencao = defaultCategory($admin, 'Manutenção');

    Expense::factory()->category($combustivel)->state(['data' => '2026-04-01', 'valor_total' => 100])->create();
    Expense::factory()->category($combustivel)->state(['data' => '2026-05-01', 'valor_total' => 200])->create();
    Expense::factory()->category($manutencao)->state(['data' => '2026-05-01', 'descricao' => 'OBRA-MANUTENCAO', 'valor_total' => 333])->create();

    $this->actingAs($admin)
        ->get("/despesas?from=2026-05-01&to=2026-05-31&cat={$combustivel->id}")
        ->assertOk()
        ->assertSee('R$ 200,00')
        ->assertDontSee('OBRA-MANUTENCAO');
});

it('expenses are tenant-scoped', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $catA = defaultCategory($a);
    $catB = defaultCategory($b);

    Expense::factory()->category($catA)->state(['descricao' => 'A only'])->create();
    Expense::factory()->category($catB)->state(['descricao' => 'B only'])->create();

    $this->actingAs($a)->get('/despesas?all=1')->assertSee('A only')->assertDontSee('B only');
    $this->actingAs($b)->get('/despesas?all=1')->assertSee('B only')->assertDontSee('A only');
});

it('rejects category from another farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $catB = defaultCategory($b);

    $this->actingAs($a)
        ->post('/despesas', [
            'data' => '2026-05-01', 'descricao' => 'X',
            'expense_category_id' => $catB->id, 'valor_total' => 100,
        ])
        ->assertSessionHasErrors('expense_category_id');
});

it('rejects inactive category', function () {
    $admin = makeFarmUser('admin');
    $cat = defaultCategory($admin);
    $cat->update(['ativo' => false]);

    $this->actingAs($admin)
        ->post('/despesas', [
            'data' => '2026-05-01', 'descricao' => 'X',
            'expense_category_id' => $cat->id, 'valor_total' => 100,
        ])
        ->assertSessionHasErrors('expense_category_id');
});

it('default sem filtro mostra apenas o mês corrente', function () {
    $admin = makeFarmUser('admin');
    $cat = defaultCategory($admin);

    // Despesa NO mês atual
    Expense::factory()->category($cat)->state([
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'descricao' => 'MES_ATUAL',
        'valor_total' => 111,
    ])->create();

    // Despesa em mês anterior
    Expense::factory()->category($cat)->state([
        'data' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'descricao' => 'MES_ANTERIOR',
        'valor_total' => 999,
    ])->create();

    $this->actingAs($admin)
        ->get('/despesas')
        ->assertOk()
        ->assertSee('MES_ATUAL')
        ->assertDontSee('MES_ANTERIOR');
});

it('botão "Todo o histórico" via ?all=1 mostra tudo', function () {
    $admin = makeFarmUser('admin');
    $cat = defaultCategory($admin);

    Expense::factory()->category($cat)->state([
        'data' => now()->subMonths(3)->toDateString(),
        'descricao' => 'TRES_MESES_ATRAS',
        'valor_total' => 50,
    ])->create();

    $this->actingAs($admin)
        ->get('/despesas?all=1')
        ->assertOk()
        ->assertSee('TRES_MESES_ATRAS')
        ->assertSee('Todo o histórico');
});

it('shows no-category page when there are no active categories', function () {
    $admin = makeFarmUser('admin');
    // Inativa todas as 6 categorias default
    ExpenseCategory::query()->where('farm_id', $admin->farm_id)->update(['ativo' => false]);

    $this->actingAs($admin)
        ->get('/despesas/criar')
        ->assertOk()
        ->assertSee('Nenhuma categoria ativa');
});
