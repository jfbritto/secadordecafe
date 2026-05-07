<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;

it('exposes a list of unidades with discrete flag', function () {
    expect(Expense::UNIDADES)->toHaveKey('L');
    expect(Expense::UNIDADES['L']['discreta'])->toBeFalse();
    expect(Expense::UNIDADES['un']['discreta'])->toBeTrue();
    expect(Expense::UNIDADES['cx']['discreta'])->toBeTrue();
});

it('unidadeEhDiscreta retorna true para discretas, false para contínuas', function () {
    expect(Expense::unidadeEhDiscreta('un'))->toBeTrue();
    expect(Expense::unidadeEhDiscreta('cx'))->toBeTrue();
    expect(Expense::unidadeEhDiscreta('pç'))->toBeTrue();
    expect(Expense::unidadeEhDiscreta('pct'))->toBeTrue();
    expect(Expense::unidadeEhDiscreta('L'))->toBeFalse();
    expect(Expense::unidadeEhDiscreta('kg'))->toBeFalse();
    expect(Expense::unidadeEhDiscreta('h'))->toBeFalse();
    expect(Expense::unidadeEhDiscreta(null))->toBeFalse();
    expect(Expense::unidadeEhDiscreta(''))->toBeFalse();
    expect(Expense::unidadeEhDiscreta('inexistente'))->toBeFalse();
});

it('aceita decimal em quantidade quando unidade é contínua (kg)', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::query()->where('farm_id', $admin->farm_id)->where('nome', 'Combustível')->first();

    $this->actingAs($admin)
        ->post('/despesas', [
            'data' => '2026-05-07', 'descricao' => 'Diesel', 'expense_category_id' => $cat->id,
            'unidade' => 'L', 'quantidade' => 12.5, 'valor_unitario' => 6.5, 'valor_total' => 81.25,
        ])
        ->assertRedirect('/despesas');
});

it('aceita inteiro em quantidade quando unidade é discreta (un)', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::query()->where('farm_id', $admin->farm_id)->where('nome', 'Equipamentos')->first();

    $this->actingAs($admin)
        ->post('/despesas', [
            'data' => '2026-05-07', 'descricao' => 'Sacos', 'expense_category_id' => $cat->id,
            'unidade' => 'un', 'quantidade' => 5, 'valor_unitario' => 12, 'valor_total' => 60,
        ])
        ->assertRedirect('/despesas');
});

it('rejeita decimal em quantidade quando unidade é discreta', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::query()->where('farm_id', $admin->farm_id)->where('nome', 'Equipamentos')->first();

    $resp = $this->actingAs($admin)
        ->post('/despesas', [
            'data' => '2026-05-07', 'descricao' => 'Sacos', 'expense_category_id' => $cat->id,
            'unidade' => 'cx', 'quantidade' => 3.5, 'valor_unitario' => 10, 'valor_total' => 35,
        ]);

    $resp->assertSessionHasErrors('quantidade');
});

it('aceita unidade vazia (sem regra de discreta)', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::query()->where('farm_id', $admin->farm_id)->where('nome', 'Outros')->first();

    $this->actingAs($admin)
        ->post('/despesas', [
            'data' => '2026-05-07', 'descricao' => 'Item sem unidade', 'expense_category_id' => $cat->id,
            'valor_total' => 100,
        ])
        ->assertRedirect('/despesas');
});

it('formulário de criar despesa renderiza select de unidades fechado', function () {
    $admin = makeFarmUser('admin');

    $html = $this->actingAs($admin)->get('/despesas/criar')->getContent();

    // Select fechado (não datalist)
    expect($html)->toContain('<select id="unidade"');
    expect($html)->toContain('— sem unidade —');
    expect($html)->toContain('value="L"');
    expect($html)->toContain('value="kg"');
    expect($html)->toContain('value="un"');
    expect($html)->toContain('Quilograma');
    expect($html)->toContain('(inteiro)'); // marcador nas discretas
});

it('rejeita unidade fora do catálogo (não permite valor custom)', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::query()->where('farm_id', $admin->farm_id)->first();

    $this->actingAs($admin)
        ->post('/despesas', [
            'data' => '2026-05-07', 'descricao' => 'Teste', 'expense_category_id' => $cat->id,
            'unidade' => 'unidade-inventada', 'quantidade' => 1, 'valor_total' => 100,
        ])
        ->assertSessionHasErrors('unidade');
});
