<?php

use App\Models\Customer;

it('dashboard mostra os 3 valores (próprio, terceiros, total) pra café côco e seco', function () {
    $admin = makeFarmUser('admin');
    popularEstoqueFazenda($admin, coco: 300, seco: 50);
    Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 200, 'saldo_seco_kg' => 30]);

    $resp = $this->actingAs($admin)->get('/dashboard');
    $resp->assertOk()
        ->assertSee('Café côco em estoque')
        ->assertSee('Café seco em estoque')
        ->assertSee('Próprio')
        ->assertSee('Terceiros')
        ->assertSee('Total')
        // Próprio côco = 300
        ->assertSee('300,00')
        // Total côco = 500
        ->assertSee('500,00')
        // Próprio seco = 50
        ->assertSee('50,00');
});

it('dashboard linka cada saldo pra rota correta', function () {
    $admin = makeFarmUser('admin');
    popularEstoqueFazenda($admin, coco: 100);

    $this->actingAs($admin)->get('/dashboard')
        ->assertOk()
        ->assertSee(route('movimentacoes.fazenda.index', ['produto' => 'coco']), false)
        ->assertSee(route('saldos.clientes', ['produto' => 'coco']), false)
        ->assertSee(route('saldos.geral', ['produto' => 'coco']), false);
});

it('dashboard não mostra mais a seção "Últimas movimentações"', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Últimas movimentações');
});
