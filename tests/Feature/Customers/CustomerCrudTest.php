<?php

use App\Models\Customer;
use App\Models\Movement;

it('lists customers paginated', function () {
    $admin = makeFarmUser('admin');
    Customer::factory()->forFarm($admin->farm)->count(3)->create();

    $this->actingAs($admin)
        ->get('/clientes')
        ->assertOk()
        ->assertSee(Customer::first()->nome);
});

it('listagem NAO tem link editar — abre o cliente para decidir lá dentro', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create();

    $this->actingAs($admin)
        ->get('/clientes')
        ->assertOk()
        ->assertDontSee(route('clientes.edit', $c), escape: false)
        ->assertSee(route('clientes.show', $c), escape: false)
        ->assertSee('Abrir');
});

it('tela do cliente mostra botao Editar para admin', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create();

    $this->actingAs($admin)
        ->get(route('clientes.show', $c))
        ->assertOk()
        ->assertSee(route('clientes.edit', $c), escape: false)
        ->assertSee('Editar');
});

it('tela do cliente expõe stats agregadas (entradas, secado, saídas, qtd secagens)', function () {
    $admin = makeFarmUser('admin');
    $dryer = \App\Models\Dryer::factory()->forFarm($admin->farm)->create();
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 0]);

    // 2 entradas (100 + 200 = 300)
    $this->actingAs($admin)->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'entrada', 'quantidade' => 100]);
    $this->actingAs($admin)->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'entrada', 'quantidade' => 200]);

    // 1 secagem (debita 150)
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $dryer->id]);
    $s = \App\Models\Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 150,
        'quantidade_seca_kg' => 90, 'comissao_percentual' => 0,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/concluir");

    // 1 saída (50)
    $this->actingAs($admin)->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'saida', 'quantidade' => 50]);

    $resp = $this->actingAs($admin)->get(route('clientes.show', $c));
    $resp->assertOk();
    $stats = $resp->viewData('stats');

    expect($stats['total_entradas'])->toBe(300.0);
    expect($stats['total_secado'])->toBe(150.0);
    expect($stats['total_saidas'])->toBe(50.0);
    expect($stats['qtd_secagens'])->toBe(1);
    expect($stats['qtd_movimentacoes'])->toBe(4);
});

it('tela do cliente lista últimas movimentações (até 5) e secagens recentes', function () {
    $admin = makeFarmUser('admin');
    $dryer = \App\Models\Dryer::factory()->forFarm($admin->farm)->create();
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $dryer->id]);
    $s = \App\Models\Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 200,
        'quantidade_seca_kg' => 120, 'comissao_percentual' => 5,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/concluir");

    $this->actingAs($admin)
        ->get(route('clientes.show', $c))
        ->assertOk()
        ->assertSee("Secagem #{$s->numero}")
        ->assertSee('200,000') // recebido na secagem listada
        ->assertSee('Cliente desde');
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

it('cria movement de entrada "Saldo inicial" quando saldo informado > 0', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/clientes', ['nome' => 'Maria', 'saldo_cafe_kg' => 100])
        ->assertRedirect('/clientes');

    $c = Customer::first();
    expect((float) $c->saldo_cafe_kg)->toBe(100.0);
    expect(Movement::where('customer_id', $c->id)->count())->toBe(1);

    $m = Movement::where('customer_id', $c->id)->first();
    expect($m->tipo)->toBe('entrada');
    expect((float) $m->quantidade_kg)->toBe(100.0);
    expect($m->observacao)->toBe('Saldo inicial');
    expect($m->user_id)->toBe($admin->id);
});

it('normaliza nome no cadastro (capitaliza palavras, preserva conectivos)', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/clientes', ['nome' => 'joão da silva', 'saldo_cafe_kg' => 0])
        ->assertRedirect('/clientes');

    expect(Customer::first()->nome)->toBe('João da Silva');
});

it('normaliza nome na edição', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['nome' => 'João da Silva']);

    $this->actingAs($admin)
        ->put("/clientes/{$c->id}", ['nome' => 'maria dos santos'])
        ->assertRedirect('/clientes');

    expect($c->fresh()->nome)->toBe('Maria dos Santos');
});

it('NAO cria movement quando saldo inicial é 0', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/clientes', ['nome' => 'Sem saldo', 'saldo_cafe_kg' => 0])
        ->assertRedirect('/clientes');

    expect(Movement::count())->toBe(0);
    expect((float) Customer::first()->saldo_cafe_kg)->toBe(0.0);
});

it('updates a customer', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['nome' => 'A']);

    $this->actingAs($admin)
        ->put("/clientes/{$c->id}", [
            'nome' => 'Atualizado',
        ])
        ->assertRedirect('/clientes');

    expect($c->fresh()->nome)->toBe('Atualizado');
});

it('update NAO altera saldo mesmo se saldo_cafe_kg vier no payload', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['nome' => 'Original', 'saldo_cafe_kg' => 80]);

    $this->actingAs($admin)
        ->put("/clientes/{$c->id}", [
            'nome' => 'Original',
            'saldo_cafe_kg' => 9999, // ignorado: saldo só muda via extrato
        ])
        ->assertRedirect('/clientes');

    expect((float) $c->fresh()->saldo_cafe_kg)->toBe(80.0);
    expect(Movement::count())->toBe(0);
});

it('formulario de edicao NAO mostra campo Saldo inicial', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create();

    $this->actingAs($admin)
        ->get("/clientes/{$c->id}/edit")
        ->assertOk()
        ->assertDontSee('name="saldo_cafe_kg"', escape: false)
        ->assertSee('Abrir extrato');
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
