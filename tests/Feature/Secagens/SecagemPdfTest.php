<?php

use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Secagem;

it('generates PDF for a secagem', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 100, 'quantidade_seca_kg' => 60, 'comissao_percentual' => 5,
    ]);

    $r = $this->actingAs($admin)->get("/secagens/{$s->id}/pdf");
    $r->assertOk();
    $r->assertHeader('content-type', 'application/pdf');
    $r->assertDownload("secagem-{$s->numero}.pdf");
});

it('rejects PDF for secagem from another farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $da = Dryer::factory()->forFarm($a->farm)->create();
    $this->actingAs($a)->post('/secagens', ['data' => '2026-05-06', 'dryer_id' => $da->id]);
    $sa = Secagem::withoutGlobalScopes()->first();

    $this->actingAs($b)->get("/secagens/{$sa->id}/pdf")->assertNotFound();
});
