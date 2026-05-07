<?php

use App\Models\Customer;

it('operador can create and update but not delete customers', function () {
    $operador = makeFarmUser('operador');
    $c = Customer::factory()->forFarm($operador->farm)->create();

    $this->actingAs($operador)
        ->post('/clientes', ['nome' => 'OperadorCreated'])
        ->assertRedirect();

    $this->actingAs($operador)
        ->put("/clientes/{$c->id}", ['nome' => 'OperadorEdit'])
        ->assertRedirect();

    $this->actingAs($operador)
        ->delete("/clientes/{$c->id}")
        ->assertForbidden();
});

it('financeiro can only view customers (no write)', function () {
    $fin = makeFarmUser('financeiro');
    $c = Customer::factory()->forFarm($fin->farm)->create();

    $this->actingAs($fin)->get('/clientes')->assertOk();
    $this->actingAs($fin)->get("/clientes/{$c->id}")->assertOk();
    $this->actingAs($fin)->post('/clientes', ['nome' => 'X'])->assertForbidden();
    $this->actingAs($fin)->put("/clientes/{$c->id}", ['nome' => 'Y'])->assertForbidden();
    $this->actingAs($fin)->delete("/clientes/{$c->id}")->assertForbidden();
});

it('visualizador can only view', function () {
    $vis = makeFarmUser('visualizador');
    $c = Customer::factory()->forFarm($vis->farm)->create();

    $this->actingAs($vis)->get('/clientes')->assertOk();
    $this->actingAs($vis)->post('/clientes', ['nome' => 'X'])->assertForbidden();
    $this->actingAs($vis)->delete("/clientes/{$c->id}")->assertForbidden();
});
