<?php

use App\Models\Dryer;

it('admin can create dryer', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/secadores', [
            'nome' => 'Secador 1',
            'capacidade_kg' => 2500,
            'modelo' => 'Pinhalense',
            'ativo' => 1,
        ])
        ->assertRedirect('/secadores');

    expect(Dryer::count())->toBe(1);
    $d = Dryer::first();
    expect($d->farm_id)->toBe($admin->farm_id);
    expect($d->nome)->toBe('Secador 1');
    expect((float) $d->capacidade_kg)->toBe(2500.0);
    expect($d->ativo)->toBeTrue();
});

it('rejects duplicate dryer name within same farm', function () {
    $admin = makeFarmUser('admin');
    Dryer::factory()->forFarm($admin->farm)->create(['nome' => 'Secador 1']);

    $this->actingAs($admin)
        ->post('/secadores', ['nome' => 'Secador 1'])
        ->assertSessionHasErrors('nome');
});

it('allows same dryer name across different farms', function () {
    $a = makeFarmUser('admin');
    Dryer::factory()->forFarm($a->farm)->create(['nome' => 'Secador X']);

    $b = makeFarmUser('admin');
    $this->actingAs($b)
        ->post('/secadores', ['nome' => 'Secador X'])
        ->assertRedirect();

    expect(Dryer::withoutGlobalScopes()->count())->toBe(2);
});

it('operador can create and edit but not delete', function () {
    $op = makeFarmUser('operador');
    $d = Dryer::factory()->forFarm($op->farm)->create();

    $this->actingAs($op)->post('/secadores', ['nome' => 'Novo'])->assertRedirect();
    $this->actingAs($op)->put("/secadores/{$d->id}", ['nome' => 'Editado'])->assertRedirect();
    $this->actingAs($op)->delete("/secadores/{$d->id}")->assertForbidden();
});

it('cannot delete dryer with secagens linked', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    \App\Models\Secagem::create([
        'farm_id' => $admin->farm_id,
        'user_id' => $admin->id,
        'dryer_id' => $d->id,
        'numero' => 1,
        'data' => '2026-05-06',
        'status' => 'rascunho',
    ]);

    $this->actingAs($admin)
        ->delete("/secadores/{$d->id}")
        ->assertRedirect('/secadores');

    expect(Dryer::find($d->id))->not->toBeNull();
});

it('lists dryers tenant-scoped', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    Dryer::factory()->forFarm($a->farm)->create(['nome' => 'DRYER-A']);
    Dryer::factory()->forFarm($b->farm)->create(['nome' => 'DRYER-B']);

    $this->actingAs($a)->get('/secadores')->assertSee('DRYER-A')->assertDontSee('DRYER-B');
});

it('toggles ativo via update', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create(['ativo' => true]);

    $this->actingAs($admin)
        ->put("/secadores/{$d->id}", ['nome' => $d->nome, 'ativo' => 0])
        ->assertRedirect();

    expect($d->fresh()->ativo)->toBeFalse();
});
