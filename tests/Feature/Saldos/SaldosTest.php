<?php

use App\Models\Customer;

it('saldos.clientes lista só clientes com saldo > 0 do produto filtrado', function () {
    $admin = makeFarmUser('admin');
    $farm = $admin->farm;

    Customer::factory()->forFarm($farm)->create(['nome' => 'COM_SALDO_COCO', 'saldo_coco_kg' => 100, 'saldo_seco_kg' => 0]);
    Customer::factory()->forFarm($farm)->create(['nome' => 'SEM_SALDO', 'saldo_coco_kg' => 0, 'saldo_seco_kg' => 0]);
    Customer::factory()->forFarm($farm)->create(['nome' => 'SO_SECO', 'saldo_coco_kg' => 0, 'saldo_seco_kg' => 50]);

    $this->actingAs($admin)->get('/saldos/clientes?produto=coco')
        ->assertOk()
        ->assertSee('COM_SALDO_COCO')
        ->assertDontSee('SEM_SALDO')
        ->assertDontSee('SO_SECO');
});

it('saldos.geral mostra fazenda no topo + clientes', function () {
    $admin = makeFarmUser('admin');
    $farm = popularEstoqueFazenda($admin, coco: 500);

    Customer::factory()->forFarm($admin->farm)->create(['nome' => 'CLI_VISIVEL', 'saldo_coco_kg' => 200]);

    $resp = $this->actingAs($admin)->get('/saldos/geral?produto=coco');
    $resp->assertOk()
        ->assertSee($farm->nome)
        ->assertSee('CLI_VISIVEL')
        ->assertSee('500,00')
        ->assertSee('700,00'); // total
});

it('saldos.clientes não vaza dados de outra fazenda', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    Customer::factory()->forFarm($a->farm)->create(['nome' => 'CLI_A_UNIQUE', 'saldo_coco_kg' => 100]);
    Customer::factory()->forFarm($b->farm)->create(['nome' => 'CLI_B_UNIQUE', 'saldo_coco_kg' => 100]);

    $this->actingAs($a)->get('/saldos/clientes?produto=coco')
        ->assertOk()
        ->assertSee('CLI_A_UNIQUE')
        ->assertDontSee('CLI_B_UNIQUE');
});

it('saldos default cai pra coco se produto inválido', function () {
    $admin = makeFarmUser('admin');
    Customer::factory()->forFarm($admin->farm)->create(['nome' => 'CLI_COCO', 'saldo_coco_kg' => 100]);

    $this->actingAs($admin)->get('/saldos/clientes')
        ->assertOk()
        ->assertSee('CLI_COCO');

    $this->actingAs($admin)->get('/saldos/clientes?produto=invalido')
        ->assertOk()
        ->assertSee('CLI_COCO');
});
