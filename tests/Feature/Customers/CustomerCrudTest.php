<?php

use App\Models\Customer;

it('lists customers paginated', function () {
    $admin = makeFarmUser('admin');
    Customer::factory()->forFarm($admin->farm)->count(3)->create();

    $this->actingAs($admin)
        ->get('/clientes')
        ->assertOk()
        ->assertSee(Customer::first()->nome);
});

it('creates a customer', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/clientes', [
            'nome' => 'João Produtor',
            'telefone' => '31999999999',
            'cpf_cnpj' => '111.222.333-44',
            'saldo_cafe_kg' => 250.5,
        ])
        ->assertRedirect('/clientes');

    expect(Customer::count())->toBe(1);
    $c = Customer::first();
    expect($c->farm_id)->toBe($admin->farm_id);
    expect($c->nome)->toBe('João Produtor');
    expect((float) $c->saldo_cafe_kg)->toBe(250.5);
});

it('updates a customer', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['nome' => 'A']);

    $this->actingAs($admin)
        ->put("/clientes/{$c->id}", [
            'nome' => 'Atualizado',
            'saldo_cafe_kg' => 0,
        ])
        ->assertRedirect('/clientes');

    expect($c->fresh()->nome)->toBe('Atualizado');
});

it('deletes a customer (admin only)', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create();

    $this->actingAs($admin)
        ->delete("/clientes/{$c->id}")
        ->assertRedirect('/clientes');

    expect(Customer::count())->toBe(0);
});

it('searches customers by nome', function () {
    $admin = makeFarmUser('admin');
    Customer::factory()->forFarm($admin->farm)->create(['nome' => 'Marcos da Silva']);
    Customer::factory()->forFarm($admin->farm)->create(['nome' => 'Outro nome']);

    $this->actingAs($admin)
        ->get('/clientes?q=Marcos')
        ->assertOk()
        ->assertSee('Marcos da Silva')
        ->assertDontSee('Outro nome');
});

it('rejects duplicate cpf_cnpj within same farm', function () {
    $admin = makeFarmUser('admin');
    Customer::factory()->forFarm($admin->farm)->create(['cpf_cnpj' => '12345678900']);

    $this->actingAs($admin)
        ->post('/clientes', ['nome' => 'X', 'cpf_cnpj' => '12345678900'])
        ->assertSessionHasErrors('cpf_cnpj');
});

it('allows same cpf_cnpj across different farms', function () {
    $adminA = makeFarmUser('admin');
    Customer::factory()->forFarm($adminA->farm)->create(['cpf_cnpj' => '99999999999']);

    $adminB = makeFarmUser('admin');

    $this->actingAs($adminB)
        ->post('/clientes', ['nome' => 'Cliente Z', 'cpf_cnpj' => '99999999999'])
        ->assertRedirect('/clientes');

    expect(Customer::withoutGlobalScopes()->count())->toBe(2);
});
