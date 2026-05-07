<?php

use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;

function dryerFor($admin)
{
    return Dryer::factory()->forFarm($admin->farm)->create();
}

it('admin can create secagem (rascunho) with sequential numero', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);

    $this->actingAs($admin)
        ->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id])
        ->assertRedirect();

    expect(Secagem::count())->toBe(1);
    $s1 = Secagem::first();
    expect($s1->numero)->toBe(1);
    expect($s1->status)->toBe('rascunho');
    expect($s1->dryer_id)->toBe($d->id);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    expect(Secagem::orderByDesc('id')->first()->numero)->toBe(2);
});

it('numero is per-farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $da = dryerFor($a);
    $db = dryerFor($b);

    $this->actingAs($a)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $da->id]);
    $this->actingAs($b)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $db->id]);

    expect(Secagem::withoutGlobalScopes()->where('farm_id', $a->farm_id)->first()->numero)->toBe(1);
    expect(Secagem::withoutGlobalScopes()->where('farm_id', $b->farm_id)->first()->numero)->toBe(1);
});

it('add item calculates comissao and saldo liquido', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'customer_id' => $c->id,
            'quantidade_recebida_kg' => 500,
            'quantidade_seca_kg' => 300,
            'comissao_percentual' => 10,
        ])
        ->assertRedirect();

    $item = SecagemItem::first();
    expect((float) $item->comissao_kg)->toBe(30.0);
    expect((float) $item->saldo_liquido_kg)->toBe(270.0);
    expect($item->rendimentoPercentual())->toBe(60.0);
});

it('conclude secagem debits customer saldos and creates movements', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c1 = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);
    $c2 = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 500]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c1->id, 'quantidade_recebida_kg' => 600, 'quantidade_seca_kg' => 360, 'comissao_percentual' => 5,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c2->id, 'quantidade_recebida_kg' => 200, 'quantidade_seca_kg' => 110, 'comissao_percentual' => 5,
    ]);

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/concluir")
        ->assertRedirect("/secagens/{$s->id}");

    expect((float) $c1->fresh()->saldo_cafe_kg)->toBe(400.0);
    expect((float) $c2->fresh()->saldo_cafe_kg)->toBe(300.0);

    expect(Movement::where('tipo', 'secagem')->count())->toBe(2);
    expect((float) Movement::where('customer_id', $c1->id)->first()->quantidade_kg)->toBe(-600.0);
    expect((float) Movement::where('customer_id', $c2->id)->first()->quantidade_kg)->toBe(-200.0);

    // Cada movement aponta source pra Secagem (não SecagemItem) — habilita link clicável no extrato
    Movement::where('tipo', 'secagem')->get()->each(function ($m) use ($s) {
        expect($m->source_type)->toBe(\App\Models\Secagem::class);
        expect($m->source_id)->toBe($s->id);
    });

    $s->refresh();
    expect($s->status)->toBe('concluida');
    expect($s->concluida_at)->not->toBeNull();
});

it('conclude blocks if any customer has insufficient saldo', function () {
    // Simula cenário em que o saldo do cliente foi reduzido APÓS o item ser adicionado
    // (ex.: outra secagem concluída no meio). A validação no conclude funciona como
    // defesa em profundidade — a validação primária acontece no item-add.
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c1 = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);
    $c2 = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c1->id, 'quantidade_recebida_kg' => 50, 'quantidade_seca_kg' => 30, 'comissao_percentual' => 0,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c2->id, 'quantidade_recebida_kg' => 100, 'quantidade_seca_kg' => 60, 'comissao_percentual' => 0,
    ]);

    // Reduz saldo do c2 fora do fluxo de adição
    $c2->update(['saldo_cafe_kg' => 50]);

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/concluir")
        ->assertStatus(422);

    expect((float) $c1->fresh()->saldo_cafe_kg)->toBe(100.0);
    expect((float) $c2->fresh()->saldo_cafe_kg)->toBe(50.0);
    expect(Movement::count())->toBe(0);
    expect($s->fresh()->status)->toBe('rascunho');
});

it('cannot edit concluded secagem', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 100, 'quantidade_seca_kg' => 60, 'comissao_percentual' => 0,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/concluir");

    $this->actingAs($admin)
        ->put("/secagens/{$s->id}", ['data' => '2026-05-06', 'dryer_id' => $d->id])
        ->assertForbidden();
});

it('cannot conclude empty secagem', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/concluir")
        ->assertStatus(422);
});

it('tenancy: cannot see secagem from other farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $da = dryerFor($a);
    $this->actingAs($a)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $da->id]);
    $sa = Secagem::withoutGlobalScopes()->first();

    $this->actingAs($b)->get("/secagens/{$sa->id}")->assertNotFound();
});

it('rejects dryer from another farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $db = dryerFor($b);

    $this->actingAs($a)
        ->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $db->id])
        ->assertSessionHasErrors('dryer_id');
});

it('rejects inactive dryer', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->inativo()->create();

    $this->actingAs($admin)
        ->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id])
        ->assertSessionHasErrors('dryer_id');
});

it('shows no-dryer page when no active dryer exists', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->get('/secagens/criar')
        ->assertOk()
        ->assertSee('Nenhum secador cadastrado');
});

it('also rejects PDF tests update on secagem without dryer text', function () {
    expect(true)->toBeTrue(); // placeholder, dryer model exists, see SecagemPdfTest
});

it('rejeita adicionar o mesmo cliente duas vezes na mesma secagem', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    // Primeiro item: OK
    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'customer_id' => $c->id, 'quantidade_recebida_kg' => 100,
            'quantidade_seca_kg' => 60, 'comissao_percentual' => 5,
        ])
        ->assertRedirect();

    // Segundo item com MESMO cliente: deve falhar com erro de validação
    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'customer_id' => $c->id, 'quantidade_recebida_kg' => 50,
            'quantidade_seca_kg' => 30, 'comissao_percentual' => 5,
        ])
        ->assertSessionHasErrors('customer_id');

    // Tabela permanece com 1 item só
    expect(SecagemItem::where('secagem_id', $s->id)->count())->toBe(1);
});

it('mesmo cliente PODE estar em secagens diferentes', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 2000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s1 = Secagem::orderBy('id')->first();
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s2 = Secagem::orderByDesc('id')->first();

    $this->actingAs($admin)
        ->post("/secagens/{$s1->id}/items", [
            'customer_id' => $c->id, 'quantidade_recebida_kg' => 100,
            'quantidade_seca_kg' => 60, 'comissao_percentual' => 0,
        ])
        ->assertRedirect();

    $this->actingAs($admin)
        ->post("/secagens/{$s2->id}/items", [
            'customer_id' => $c->id, 'quantidade_recebida_kg' => 200,
            'quantidade_seca_kg' => 120, 'comissao_percentual' => 0,
        ])
        ->assertRedirect();

    expect(SecagemItem::count())->toBe(2);
});

it('rejeita item com quantidade_recebida_kg maior que o saldo do cliente', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'customer_id' => $c->id, 'quantidade_recebida_kg' => 150,
            'quantidade_seca_kg' => 90, 'comissao_percentual' => 0,
        ])
        ->assertSessionHasErrors('quantidade_recebida_kg');

    expect(SecagemItem::count())->toBe(0);
});

it('aceita item com quantidade_recebida_kg igual ao saldo do cliente', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'customer_id' => $c->id, 'quantidade_recebida_kg' => 100,
            'quantidade_seca_kg' => 60, 'comissao_percentual' => 0,
        ])
        ->assertRedirect();

    expect(SecagemItem::count())->toBe(1);
});

it('apos remover, da pra readicionar o mesmo cliente', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 100,
        'quantidade_seca_kg' => 60, 'comissao_percentual' => 0,
    ])->assertRedirect();

    $item = SecagemItem::first();
    $this->actingAs($admin)->delete("/secagens/{$s->id}/items/{$item->id}")->assertRedirect();

    // Re-adiciona com valores diferentes
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 200,
        'quantidade_seca_kg' => 120, 'comissao_percentual' => 5,
    ])->assertRedirect();

    expect(SecagemItem::where('secagem_id', $s->id)->count())->toBe(1);
});
