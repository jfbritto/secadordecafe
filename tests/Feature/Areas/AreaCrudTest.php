<?php

use App\Models\Area;
use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Secagem;

it('admin pode cadastrar área', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/areas', [
            'nome' => 'Talhão Norte',
            'observacoes' => 'Catuaí amarelo, plantado em 2018',
            'latitude' => '-19.9166810',
            'longitude' => '-43.9344930',
            'ativo' => 1,
        ])
        ->assertRedirect('/areas');

    expect(Area::count())->toBe(1);
    $a = Area::first();
    expect($a->farm_id)->toBe($admin->farm_id);
    expect($a->nome)->toBe('Talhão Norte');
    expect((float) $a->latitude)->toBe(-19.916681);
    expect((float) $a->longitude)->toBe(-43.934493);
    expect($a->ativo)->toBeTrue();
});

it('cria área sem localização (latitude/longitude opcionais)', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/areas', ['nome' => 'Sem GPS', 'latitude' => '', 'longitude' => ''])
        ->assertRedirect('/areas');

    $a = Area::first();
    expect($a->latitude)->toBeNull();
    expect($a->longitude)->toBeNull();
    expect($a->hasLocation())->toBeFalse();
});

it('rejeita nome duplicado na mesma farm', function () {
    $admin = makeFarmUser('admin');
    Area::factory()->forFarm($admin->farm)->create(['nome' => 'Cafezal Velho']);

    $this->actingAs($admin)
        ->post('/areas', ['nome' => 'Cafezal Velho'])
        ->assertSessionHasErrors('nome');
});

it('permite mesmo nome em farms diferentes', function () {
    $a = makeFarmUser('admin');
    Area::factory()->forFarm($a->farm)->create(['nome' => 'Quadra A']);

    $b = makeFarmUser('admin');
    $this->actingAs($b)
        ->post('/areas', ['nome' => 'Quadra A'])
        ->assertRedirect();

    expect(Area::withoutGlobalScopes()->count())->toBe(2);
});

it('rejeita latitude/longitude fora dos limites', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/areas', ['nome' => 'Inválida', 'latitude' => '91', 'longitude' => '-43'])
        ->assertSessionHasErrors('latitude');

    $this->actingAs($admin)
        ->post('/areas', ['nome' => 'Inválida', 'latitude' => '-19', 'longitude' => '181'])
        ->assertSessionHasErrors('longitude');
});

it('operador cria e edita mas não exclui', function () {
    $op = makeFarmUser('operador');
    $a = Area::factory()->forFarm($op->farm)->create();

    $this->actingAs($op)->post('/areas', ['nome' => 'Nova'])->assertRedirect();
    $this->actingAs($op)->put("/areas/{$a->id}", ['nome' => 'Editada'])->assertRedirect();
    $this->actingAs($op)->delete("/areas/{$a->id}")->assertForbidden();
});

it('não permite excluir área com secagens vinculadas', function () {
    $admin = makeFarmUser('admin');
    $a = Area::factory()->forFarm($admin->farm)->create();
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    Secagem::create([
        'farm_id' => $admin->farm_id, 'user_id' => $admin->id,
        'dryer_id' => $d->id, 'area_id' => $a->id,
        'numero' => 1, 'data' => '2026-05-21', 'status' => 'rascunho',
    ]);

    $this->actingAs($admin)
        ->delete("/areas/{$a->id}")
        ->assertRedirect('/areas');

    expect(Area::find($a->id))->not->toBeNull();
});

it('lista áreas por tenant', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    Area::factory()->forFarm($a->farm)->create(['nome' => 'AREA-DA-A']);
    Area::factory()->forFarm($b->farm)->create(['nome' => 'AREA-DA-B']);

    $this->actingAs($a)->get('/areas')->assertSee('AREA-DA-A')->assertDontSee('AREA-DA-B');
});

it('show da área expõe stats agregadas no período padrão (este ano)', function () {
    $admin = makeFarmUser('admin');
    $area = Area::factory()->forFarm($admin->farm)->create();
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);

    // Cria 1 secagem completa vinculada à área
    $this->actingAs($admin)->post('/secagens', [
        'data' => now()->format('Y-m-d'), 'dryer_id' => $d->id, 'area_id' => $area->id,
    ]);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 500,
        'quantidade_seca_kg' => 300, 'comissao_percentual' => 10,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/concluir");

    $resp = $this->actingAs($admin)->get("/areas/{$area->id}");
    $resp->assertOk();
    $stats = $resp->viewData('stats');

    expect($stats['qtd_secagens'])->toBe(1);
    expect($stats['total_recebido'])->toBe(500.0);
    expect($stats['total_seco'])->toBe(300.0);
    expect($stats['total_comissao'])->toBe(30.0);
    expect($stats['periodo_label'])->toBe('Este ano');
});

it('filtro de período "tudo" retorna todas as secagens da área', function () {
    $admin = makeFarmUser('admin');
    $area = Area::factory()->forFarm($admin->farm)->create();

    $resp = $this->actingAs($admin)->get("/areas/{$area->id}?periodo=tudo");
    $resp->assertOk();
    expect($resp->viewData('stats')['periodo_label'])->toBe('Todo o histórico');
});
