<?php

use App\Models\Area;
use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Secagem;
use App\Models\SecagemItem;

it('adiciona item de área à secagem (em vez de marcar área na secagem inteira)', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $area = Area::factory()->forFarm($admin->farm)->create(['nome' => 'Talhão Sul', 'saldo_coco_kg' => 500]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-21', 'dryer_id' => $d->id])->assertRedirect();
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'origin_type' => 'area', 'origin_id' => $area->id, 'quantidade_recebida_kg' => 200,
        ])
        ->assertRedirect();

    $item = SecagemItem::first();
    expect($item->origin_type)->toBe(Area::class);
    expect($item->origin_id)->toBe($area->id);
    expect($item->isArea())->toBeTrue();
});

it('rejeita area_id de outra fazenda', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($a->farm)->create();
    $areaB = Area::factory()->forFarm($b->farm)->create(['saldo_coco_kg' => 500]);

    $this->actingAs($a)->post('/secagens', ['data' => '2026-05-21', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($a)
        ->post("/secagens/{$s->id}/items", [
            'origin_type' => 'area', 'origin_id' => $areaB->id, 'quantidade_recebida_kg' => 100,
        ])
        ->assertSessionHasErrors('origin_id');

    expect(SecagemItem::count())->toBe(0);
});

it('rejeita item de área sem saldo de côco suficiente', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $area = Area::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 50]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-21', 'dryer_id' => $d->id]);
    $s = Secagem::first();

    $this->actingAs($admin)
        ->post("/secagens/{$s->id}/items", [
            'origin_type' => 'area', 'origin_id' => $area->id, 'quantidade_recebida_kg' => 100,
        ])
        ->assertSessionHasErrors('quantidade_recebida_kg');
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

it('secagem show exibe nome da área no item', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $area = Area::factory()->forFarm($admin->farm)->create(['nome' => 'Cafezal do Morro', 'saldo_coco_kg' => 500]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-21', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'origin_type' => 'area', 'origin_id' => $area->id, 'quantidade_recebida_kg' => 200,
    ]);

    $this->actingAs($admin)
        ->get("/secagens/{$s->id}")
        ->assertOk()
        ->assertSee('Cafezal do Morro');
});

it('colheita aumenta saldo de côco da área', function () {
    $admin = makeFarmUser('admin');
    $area = Area::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 0]);

    $this->actingAs($admin)
        ->post('/colheitas', [
            'area_id' => $area->id,
            'quantidade_kg' => 800,
            'observacao' => 'Colheita do dia',
        ])
        ->assertRedirect(route('areas.show', $area));

    expect((float) $area->fresh()->saldo_coco_kg)->toBe(800.0);
});
