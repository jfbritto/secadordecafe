<?php

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\User;

it('farm dashboard shows tenant metrics', function () {
    $admin = makeFarmUser('admin');
    Customer::factory()->forFarm($admin->farm)->count(3)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Clientes')
        ->assertSee('300'); // total saldo 3*100
});

it('root dashboard shows platform metrics', function () {
    Farm::factory()->count(2)->create();
    Farm::factory()->blocked()->create();
    $root = User::factory()->root()->create();

    $this->actingAs($root)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Painel da Plataforma')
        ->assertSee('Total fazendas')
        ->assertSee('Bloqueadas');
});

it('farm dashboard does not leak data from other farms', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    Customer::factory()->forFarm($a->farm)->create(['nome' => 'CLIENTE_A_UNIQUE']);
    Customer::factory()->forFarm($b->farm)->create(['nome' => 'CLIENTE_B_UNIQUE']);

    $this->actingAs($a)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('CLIENTE_B_UNIQUE');
});
