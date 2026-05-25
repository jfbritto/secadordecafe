<?php

use App\Models\Area;
use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;

function dryerFor($admin)
{
    return Dryer::factory()->forFarm($admin->farm)->create();
}

/**
 * Helper pra adicionar item completo (entrada + saída) numa secagem.
 * Encapsula o fluxo de 2 tempos pros testes que querem cenário pronto.
 */
function addItemComOutput($test, $admin, Secagem $s, Customer|Area $origin, float $recebida, float $seca, float $comissao = 0)
{
    $tipo = $origin instanceof Customer ? 'cliente' : 'area';
    $test->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'origin_type' => $tipo,
        'origin_id' => $origin->id,
        'quantidade_recebida_kg' => $recebida,
    ])->assertRedirect();

    $item = SecagemItem::orderByDesc('id')->first();
    $test->actingAs($admin)->patch("/secagens/{$s->id}/items/{$item->id}/saida", [
        'quantidade_seca_kg' => $seca,
        'comissao_percentual' => $comissao,
    ])->assertRedirect();

    return $item->fresh();
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

it('add item + registrar saída calcula comissao e saldo liquido', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 1000]);
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $item = addItemComOutput($this, $admin, $s, $c, 500, 300, 10);

    expect((float) $item->comissao_kg)->toBe(30.0);
    expect((float) $item->saldo_liquido_kg)->toBe(270.0);
    expect($item->rendimentoPercentual())->toBe(60.0);
});

it('conclude secagem debita côco e credita seco (cliente + comissão na fazenda)', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c1 = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 1000]);
    $c2 = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 500]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    addItemComOutput($this, $admin, $s, $c1, 600, 360, 5); // comissao = 18, líquido = 342
    addItemComOutput($this, $admin, $s, $c2, 200, 110, 5); // comissao = 5.5, líquido = 104.5

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/concluir")
        ->assertRedirect("/secagens/{$s->id}");

    // Côco debitado
    expect((float) $c1->fresh()->saldo_coco_kg)->toBe(400.0);
    expect((float) $c2->fresh()->saldo_coco_kg)->toBe(300.0);
    // Seco creditado (líquido)
    expect((float) $c1->fresh()->saldo_seco_kg)->toBe(342.0);
    expect((float) $c2->fresh()->saldo_seco_kg)->toBe(104.5);
    // Comissão acumulada na Farm
    $farm = Farm::find($admin->farm_id);
    expect((float) $farm->saldo_seco_comissao_kg)->toBe(23.5);

    expect(Movement::where('tipo', 'secagem')->count())->toBe(2);
    expect(Movement::where('tipo', 'producao')->count())->toBe(2);
    expect(Movement::where('tipo', 'comissao')->count())->toBe(2);

    $s->refresh();
    expect($s->status)->toBe('concluida');
    expect($s->concluida_at)->not->toBeNull();
});

it('conclude blocks if any owner has insufficient saldo de côco', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c1 = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 100]);
    $c2 = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 100]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    addItemComOutput($this, $admin, $s, $c1, 50, 30, 0);
    addItemComOutput($this, $admin, $s, $c2, 100, 60, 0);

    // Reduz saldo do c2 fora do fluxo
    $c2->update(['saldo_coco_kg' => 50]);

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/concluir")
        ->assertStatus(422);

    expect((float) $c1->fresh()->saldo_coco_kg)->toBe(100.0);
    expect((float) $c2->fresh()->saldo_coco_kg)->toBe(50.0);
    expect(Movement::count())->toBe(0);
    expect($s->fresh()->status)->toBe('rascunho');
});

it('cannot edit concluded secagem', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 1000]);
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    addItemComOutput($this, $admin, $s, $c, 100, 60, 0);
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

it('cannot conclude secagem com item sem saída registrada', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 500]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    // Só lança entrada, sem registrar saída
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'origin_type' => 'cliente', 'origin_id' => $c->id,
        'quantidade_recebida_kg' => 100,
    ])->assertRedirect();

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

it('rejeita adicionar o mesmo cliente duas vezes na mesma secagem', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'origin_type' => 'cliente', 'origin_id' => $c->id,
            'quantidade_recebida_kg' => 100,
        ])
        ->assertRedirect();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'origin_type' => 'cliente', 'origin_id' => $c->id,
            'quantidade_recebida_kg' => 50,
        ])
        ->assertSessionHasErrors('origin_id');

    expect(SecagemItem::where('secagem_id', $s->id)->count())->toBe(1);
});

it('mesmo cliente PODE estar em secagens diferentes', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 2000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s1 = Secagem::orderBy('id')->first();
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s2 = Secagem::orderByDesc('id')->first();

    $this->actingAs($admin)
        ->post("/secagens/{$s1->id}/items", [
            'origin_type' => 'cliente', 'origin_id' => $c->id,
            'quantidade_recebida_kg' => 100,
        ])
        ->assertRedirect();

    $this->actingAs($admin)
        ->post("/secagens/{$s2->id}/items", [
            'origin_type' => 'cliente', 'origin_id' => $c->id,
            'quantidade_recebida_kg' => 200,
        ])
        ->assertRedirect();

    expect(SecagemItem::count())->toBe(2);
});

it('rejeita item com quantidade_recebida_kg maior que o saldo de côco', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 100]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'origin_type' => 'cliente', 'origin_id' => $c->id,
            'quantidade_recebida_kg' => 150,
        ])
        ->assertSessionHasErrors('quantidade_recebida_kg');

    expect(SecagemItem::count())->toBe(0);
});

it('aceita item com quantidade_recebida_kg igual ao saldo do cliente', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 100]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'origin_type' => 'cliente', 'origin_id' => $c->id,
            'quantidade_recebida_kg' => 100,
        ])
        ->assertRedirect();

    expect(SecagemItem::count())->toBe(1);
});

it('apos remover, da pra readicionar o mesmo cliente', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'origin_type' => 'cliente', 'origin_id' => $c->id,
        'quantidade_recebida_kg' => 100,
    ])->assertRedirect();

    $item = SecagemItem::first();
    $this->actingAs($admin)->delete("/secagens/{$s->id}/items/{$item->id}")->assertRedirect();

    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'origin_type' => 'cliente', 'origin_id' => $c->id,
        'quantidade_recebida_kg' => 200,
    ])->assertRedirect();

    expect(SecagemItem::where('secagem_id', $s->id)->count())->toBe(1);
});

it('secagem mista: cliente + área no mesmo ciclo', function () {
    $admin = makeFarmUser('admin');
    $d = dryerFor($admin);
    $cliente = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 500]);
    $area = Area::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 300]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    addItemComOutput($this, $admin, $s, $cliente, 500, 120, 10);
    addItemComOutput($this, $admin, $s, $area, 300, 72, 0); // áreas não têm comissão

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/concluir")
        ->assertRedirect();

    expect((float) $cliente->fresh()->saldo_coco_kg)->toBe(0.0);
    expect((float) $cliente->fresh()->saldo_seco_kg)->toBe(108.0); // 120 - 12 comissão
    expect((float) $area->fresh()->saldo_coco_kg)->toBe(0.0);
    expect((float) $area->fresh()->saldo_seco_kg)->toBe(72.0); // integral
    $farm = Farm::find($admin->farm_id);
    expect((float) $farm->saldo_seco_comissao_kg)->toBe(12.0); // só do cliente
});
