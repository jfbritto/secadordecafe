<?php

use App\Models\Expense;

it('admin can create expense', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/despesas', [
            'data' => '2026-05-01',
            'descricao' => 'Diesel',
            'categoria' => 'combustivel',
            'unidade' => 'L',
            'quantidade' => 100,
            'valor_unitario' => 6.5,
            'valor_total' => 650,
        ])
        ->assertRedirect('/despesas');

    expect(Expense::count())->toBe(1);
    expect((float) Expense::first()->valor_total)->toBe(650.0);
});

it('financeiro can create but not delete', function () {
    $fin = makeFarmUser('financeiro');
    $e = Expense::factory()->create(['farm_id' => $fin->farm_id]);

    $this->actingAs($fin)
        ->post('/despesas', ['data'=>'2026-05-01','descricao'=>'X','categoria'=>'outros','valor_total'=>100])
        ->assertRedirect();

    $this->actingAs($fin)
        ->delete("/despesas/{$e->id}")
        ->assertForbidden();
});

it('operador cannot create expense', function () {
    $op = makeFarmUser('operador');
    $this->actingAs($op)
        ->get('/despesas')
        ->assertForbidden();
});

it('lists expenses with totals by category', function () {
    $admin = makeFarmUser('admin');
    Expense::factory()->forFarm($admin->farm ?? null)->state(['categoria' => 'combustivel', 'valor_total' => 100, 'farm_id' => $admin->farm_id])->create();
    Expense::factory()->state(['categoria' => 'manutencao', 'valor_total' => 250, 'farm_id' => $admin->farm_id])->create();

    $this->actingAs($admin)
        ->get('/despesas')
        ->assertOk()
        ->assertSee('R$ 350,00'); // total geral
});

it('filters by date range and categoria', function () {
    $admin = makeFarmUser('admin');
    Expense::factory()->state(['data' => '2026-04-01', 'categoria' => 'combustivel', 'valor_total' => 100, 'farm_id' => $admin->farm_id])->create();
    Expense::factory()->state(['data' => '2026-05-01', 'categoria' => 'combustivel', 'valor_total' => 200, 'farm_id' => $admin->farm_id])->create();
    Expense::factory()->state(['data' => '2026-05-01', 'categoria' => 'manutencao', 'descricao' => 'OBRA-MANUTENCAO', 'valor_total' => 333, 'farm_id' => $admin->farm_id])->create();

    $this->actingAs($admin)
        ->get('/despesas?from=2026-05-01&to=2026-05-31&cat=combustivel')
        ->assertOk()
        ->assertSee('R$ 200,00')
        ->assertDontSee('OBRA-MANUTENCAO');
});

it('expenses are tenant-scoped', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    Expense::factory()->state(['descricao' => 'A only', 'farm_id' => $a->farm_id])->create();
    Expense::factory()->state(['descricao' => 'B only', 'farm_id' => $b->farm_id])->create();

    $this->actingAs($a)->get('/despesas')->assertSee('A only')->assertDontSee('B only');
    $this->actingAs($b)->get('/despesas')->assertSee('B only')->assertDontSee('A only');
});
