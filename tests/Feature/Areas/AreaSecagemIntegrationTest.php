<?php

use App\Models\Area;
use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Secagem;

it('cria secagem vinculando a uma área', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $area = Area::factory()->forFarm($admin->farm)->create(['nome' => 'Talhão Sul']);

    $this->actingAs($admin)
        ->post('/secagens', [
            'data' => '2026-05-21', 'dryer_id' => $d->id, 'area_id' => $area->id,
        ])
        ->assertRedirect();

    expect(Secagem::first()->area_id)->toBe($area->id);
});

it('cria secagem SEM área (secagem pra cliente — café de outra roça)', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();

    $this->actingAs($admin)
        ->post('/secagens', [
            'data' => '2026-05-21', 'dryer_id' => $d->id, 'area_id' => '',
        ])
        ->assertRedirect();

    expect(Secagem::first()->area_id)->toBeNull();
});

it('rejeita area_id de outra fazenda', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($a->farm)->create();
    $areaB = Area::factory()->forFarm($b->farm)->create();

    $this->actingAs($a)
        ->post('/secagens', [
            'data' => '2026-05-21', 'dryer_id' => $d->id, 'area_id' => $areaB->id,
        ])
        ->assertSessionHasErrors('area_id');

    expect(Secagem::count())->toBe(0);
});

it('rejeita area_id de área inativa', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $areaInativa = Area::factory()->forFarm($admin->farm)->inativo()->create();

    $this->actingAs($admin)
        ->post('/secagens', [
            'data' => '2026-05-21', 'dryer_id' => $d->id, 'area_id' => $areaInativa->id,
        ])
        ->assertSessionHasErrors('area_id');
});

it('admin de outra farm não acessa área alheia', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $areaB = Area::factory()->forFarm($b->farm)->create();

    $this->actingAs($a)->get("/areas/{$areaB->id}")->assertNotFound();
    $this->actingAs($a)->get("/areas/{$areaB->id}/editar")->assertNotFound();
    $this->actingAs($a)->put("/areas/{$areaB->id}", ['nome' => 'Hack'])->assertNotFound();
    $this->actingAs($a)->delete("/areas/{$areaB->id}")->assertNotFound();
});

it('secagem show exibe link pra área quando vinculada', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $area = Area::factory()->forFarm($admin->farm)->create(['nome' => 'Cafezal do Morro']);

    $this->actingAs($admin)->post('/secagens', [
        'data' => '2026-05-21', 'dryer_id' => $d->id, 'area_id' => $area->id,
    ]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->get("/secagens/{$s->id}")
        ->assertOk()
        ->assertSee('Cafezal do Morro');
});
