<?php

use App\Models\Customer;
use App\Models\User;

it('admin cannot see customers from other farms', function () {
    $adminA = makeFarmUser('admin');
    $adminB = makeFarmUser('admin');

    Customer::factory()->forFarm($adminA->farm)->create(['nome' => 'Cliente A1']);
    Customer::factory()->forFarm($adminB->farm)->create(['nome' => 'Cliente B1']);

    $this->actingAs($adminA)
        ->get('/clientes')
        ->assertOk()
        ->assertSee('Cliente A1')
        ->assertDontSee('Cliente B1');
});

it('admin cannot view a customer from another farm directly', function () {
    $adminA = makeFarmUser('admin');
    $adminB = makeFarmUser('admin');

    $cB = Customer::factory()->forFarm($adminB->farm)->create();

    $this->actingAs($adminA)
        ->get("/clientes/{$cB->id}")
        ->assertNotFound(); // global scope hides it -> 404
});

it('global scope auto-fills farm_id on create from auth user', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)->post('/clientes', ['nome' => 'AutoTenant']);

    $c = Customer::withoutGlobalScopes()->first();
    expect($c->farm_id)->toBe($admin->farm_id);
});

it('root user sees customers from all farms', function () {
    $adminA = makeFarmUser('admin');
    $adminB = makeFarmUser('admin');
    Customer::factory()->forFarm($adminA->farm)->create(['nome' => 'C-A']);
    Customer::factory()->forFarm($adminB->farm)->create(['nome' => 'C-B']);

    $root = User::factory()->root()->create();

    $this->actingAs($root)
        ->get('/clientes')
        ->assertOk()
        ->assertSee('C-A')
        ->assertSee('C-B');
});
