<?php

use App\Models\Customer;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;

it('admin can create secagem (rascunho) with sequential numero', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/secagens', [
            'data' => '2026-05-06',
            'secador' => 'Secador 1',
        ])
        ->assertRedirect();

    expect(Secagem::count())->toBe(1);
    $s1 = Secagem::first();
    expect($s1->numero)->toBe(1);
    expect($s1->status)->toBe('rascunho');

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'B']);
    expect(Secagem::orderByDesc('id')->first()->numero)->toBe(2);
});

it('numero is per-farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');

    $this->actingAs($a)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'A']);
    $this->actingAs($b)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'B']);

    expect(Secagem::withoutGlobalScopes()->where('farm_id', $a->farm_id)->first()->numero)->toBe(1);
    expect(Secagem::withoutGlobalScopes()->where('farm_id', $b->farm_id)->first()->numero)->toBe(1);
});

it('add item calculates comissao and saldo liquido', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'A']);
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
    expect((float) $item->comissao_kg)->toBe(30.0); // 300 * 10% = 30
    expect((float) $item->saldo_liquido_kg)->toBe(270.0); // 300 - 30
    expect($item->rendimentoPercentual())->toBe(60.0); // 300/500 * 100
});

it('conclude secagem debits customer saldos and creates movements', function () {
    $admin = makeFarmUser('admin');
    $c1 = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);
    $c2 = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 500]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'A']);
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

    $s->refresh();
    expect($s->status)->toBe('concluida');
    expect($s->concluida_at)->not->toBeNull();
});

it('conclude blocks if any customer has insufficient saldo', function () {
    $admin = makeFarmUser('admin');
    $c1 = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);
    $c2 = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 50]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'A']);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c1->id, 'quantidade_recebida_kg' => 50, 'quantidade_seca_kg' => 30, 'comissao_percentual' => 0,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c2->id, 'quantidade_recebida_kg' => 100, 'quantidade_seca_kg' => 60, 'comissao_percentual' => 0,
    ]);

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/concluir")
        ->assertStatus(422);

    // Saldos preservados
    expect((float) $c1->fresh()->saldo_cafe_kg)->toBe(100.0);
    expect((float) $c2->fresh()->saldo_cafe_kg)->toBe(50.0);
    expect(Movement::count())->toBe(0);
    expect($s->fresh()->status)->toBe('rascunho');
});

it('cannot edit concluded secagem', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'A']);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 100, 'quantidade_seca_kg' => 60, 'comissao_percentual' => 0,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/concluir");

    $this->actingAs($admin)
        ->put("/secagens/{$s->id}", ['data' => '2026-05-06', 'secador' => 'X'])
        ->assertForbidden();
});

it('cannot conclude empty secagem', function () {
    $admin = makeFarmUser('admin');
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'A']);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/concluir")
        ->assertStatus(422);
});

it('tenancy: cannot see secagem from other farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $this->actingAs($a)->post('/secagens', ['data' => '2026-05-06', 'secador' => 'X']);
    $sa = Secagem::withoutGlobalScopes()->first();

    $this->actingAs($b)->get("/secagens/{$sa->id}")->assertNotFound();
});
