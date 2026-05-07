<?php

use App\Models\Customer;
use App\Models\Movement;

it('entrada incrementa saldo e cria movement', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($admin)
        ->post("/clientes/{$c->id}/movimentacoes", [
            'tipo' => 'entrada',
            'quantidade' => 50.5,
            'observacao' => 'Recebido',
        ])
        ->assertRedirect("/clientes/{$c->id}/movimentacoes");

    expect((float) $c->fresh()->saldo_cafe_kg)->toBe(150.5);
    expect(Movement::where('customer_id', $c->id)->count())->toBe(1);
    expect((float) Movement::first()->quantidade_kg)->toBe(50.5);
});

it('saida decrementa saldo (admin only)', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($admin)
        ->post("/clientes/{$c->id}/movimentacoes", [
            'tipo' => 'saida',
            'quantidade' => 30,
        ])
        ->assertRedirect();

    expect((float) $c->fresh()->saldo_cafe_kg)->toBe(70.0);
    expect((float) Movement::where('tipo', 'saida')->first()->quantidade_kg)->toBe(-30.0);
});

it('operador nao pode dar saida (admin only)', function () {
    $op = makeFarmUser('operador');
    $c = Customer::factory()->forFarm($op->farm)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($op)
        ->post("/clientes/{$c->id}/movimentacoes", [
            'tipo' => 'saida',
            'quantidade' => 10,
        ])
        ->assertForbidden();
});

it('ajuste positivo e negativo', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 50]);

    $this->actingAs($admin)
        ->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'ajuste', 'direcao' => '+', 'quantidade' => 10])
        ->assertRedirect();
    expect((float) $c->fresh()->saldo_cafe_kg)->toBe(60.0);

    $this->actingAs($admin)
        ->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'ajuste', 'direcao' => '-', 'quantidade' => 5])
        ->assertRedirect();
    expect((float) $c->fresh()->saldo_cafe_kg)->toBe(55.0);
});

it('saldo nao pode ficar negativo', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 10]);

    $this->actingAs($admin)
        ->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'saida', 'quantidade' => 11])
        ->assertStatus(422);

    expect((float) $c->fresh()->saldo_cafe_kg)->toBe(10.0);
    expect(Movement::count())->toBe(0);
});

it('extrato lista movimentacoes do cliente', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($admin)->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'entrada', 'quantidade' => 30, 'observacao' => 'Lote A']);

    $this->actingAs($admin)
        ->get("/clientes/{$c->id}/movimentacoes")
        ->assertOk()
        ->assertSee('Lote A');
});

it('extrato calcula saldo após cada movimentação (running balance)', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 0]);

    // Fluxo: +100 (saldo 100) → +50 (saldo 150) → ajuste -20 (saldo 130)
    $this->actingAs($admin)->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'entrada', 'quantidade' => 100]);
    $this->actingAs($admin)->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'entrada', 'quantidade' => 50]);
    $this->actingAs($admin)->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'ajuste', 'direcao' => '-', 'quantidade' => 20]);

    $resp = $this->actingAs($admin)->get("/clientes/{$c->id}/movimentacoes");
    $resp->assertOk();

    $movements = $resp->viewData('movements');
    // Ordem DESC: o mais recente vem primeiro
    expect((float) $movements[0]->saldo_apos)->toBe(130.0);
    expect((float) $movements[1]->saldo_apos)->toBe(150.0);
    expect((float) $movements[2]->saldo_apos)->toBe(100.0);

    // Saldo atual do cliente bate com o saldo após o mais recente
    expect((float) $c->fresh()->saldo_cafe_kg)->toBe(130.0);
});

it('extrato mostra link clicável pra Secagem como origem', function () {
    $admin = makeFarmUser('admin');
    $dryer = \App\Models\Dryer::factory()->forFarm($admin->farm)->create();
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $dryer->id]);
    $s = \App\Models\Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 500,
        'quantidade_seca_kg' => 300, 'comissao_percentual' => 5,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/concluir");

    $this->actingAs($admin)
        ->get("/clientes/{$c->id}/movimentacoes")
        ->assertOk()
        ->assertSee("Secagem #{$s->numero}")
        ->assertSee("/secagens/{$s->id}", escape: false);
});
