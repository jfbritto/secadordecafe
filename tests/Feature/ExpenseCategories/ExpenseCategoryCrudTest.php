<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;

it('seeds default categories when farm is created', function () {
    $admin = makeFarmUser('admin');

    expect(ExpenseCategory::count())->toBe(count(ExpenseCategory::DEFAULTS));
    foreach (ExpenseCategory::DEFAULTS as $nome) {
        expect(ExpenseCategory::where('farm_id', $admin->farm_id)->where('nome', $nome)->exists())->toBeTrue();
    }
});

it('admin can create new category', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/despesas/categorias', [
            'nome' => 'Insumos',
            'observacoes' => 'Adubo, defensivos',
            'ativo' => 1,
        ])
        ->assertRedirect('/despesas/categorias');

    expect(ExpenseCategory::where('nome', 'Insumos')->exists())->toBeTrue();
});

it('rejects duplicate category nome within same farm', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/despesas/categorias', ['nome' => 'Combustível'])
        ->assertSessionHasErrors('nome');
});

it('allows same category nome across different farms', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');

    // Ambas já têm "Combustível" das defaults — basta verificar
    expect(ExpenseCategory::withoutGlobalScopes()->where('nome', 'Combustível')->count())->toBe(2);
});

it('financeiro can create and edit, admin only delete', function () {
    $fin = makeFarmUser('financeiro');
    $cat = ExpenseCategory::where('farm_id', $fin->farm_id)->where('nome', 'Outros')->first();

    $this->actingAs($fin)
        ->post('/despesas/categorias', ['nome' => 'NovaCat'])
        ->assertRedirect();

    $this->actingAs($fin)
        ->put("/despesas/categorias/{$cat->id}", ['nome' => 'OutrosEditado'])
        ->assertRedirect();

    $this->actingAs($fin)
        ->delete("/despesas/categorias/{$cat->id}")
        ->assertForbidden();
});

it('cannot delete category with expenses linked', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::where('farm_id', $admin->farm_id)->where('nome', 'Combustível')->first();
    Expense::factory()->category($cat)->create();

    $this->actingAs($admin)
        ->delete("/despesas/categorias/{$cat->id}")
        ->assertRedirect('/despesas/categorias');

    expect(ExpenseCategory::find($cat->id))->not->toBeNull();
});

it('lists categories tenant-scoped', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');

    ExpenseCategory::create(['farm_id' => $a->farm_id, 'nome' => 'CAT-A-UNIQUE', 'ativo' => true]);
    ExpenseCategory::create(['farm_id' => $b->farm_id, 'nome' => 'CAT-B-UNIQUE', 'ativo' => true]);

    $this->actingAs($a)->get('/despesas/categorias')->assertSee('CAT-A-UNIQUE')->assertDontSee('CAT-B-UNIQUE');
});

it('toggles ativo via update', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::where('farm_id', $admin->farm_id)->where('nome', 'Outros')->first();
    expect($cat->ativo)->toBeTrue();

    $this->actingAs($admin)
        ->put("/despesas/categorias/{$cat->id}", ['nome' => $cat->nome, 'ativo' => 0])
        ->assertRedirect();

    expect($cat->fresh()->ativo)->toBeFalse();
});

it('visualizador can view list', function () {
    $vis = makeFarmUser('visualizador');
    $this->actingAs($vis)->get('/despesas/categorias')->assertOk();
});

it('operador cannot view categories', function () {
    $op = makeFarmUser('operador');
    $this->actingAs($op)->get('/despesas/categorias')->assertForbidden();
});
