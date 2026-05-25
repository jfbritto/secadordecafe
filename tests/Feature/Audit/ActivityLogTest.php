<?php

use App\Models\Customer;
use Spatie\Activitylog\Models\Activity;

it('creates activity log when customer is created', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/clientes', ['nome' => 'Cliente Logged'])
        ->assertRedirect();

    expect(Activity::where('description', 'cliente created')->count())->toBe(1);
    $a = Activity::where('description', 'cliente created')->first();
    expect($a->subject_type)->toBe(Customer::class);
});

it('creates activity log on customer update with diff', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['nome' => 'Old']);

    $this->actingAs($admin)
        ->put("/clientes/{$c->id}", ['nome' => 'New', 'saldo_coco_kg' => 0])
        ->assertRedirect();

    $a = Activity::where('description', 'cliente updated')->first();
    expect($a)->not->toBeNull();
    expect($a->properties->get('attributes')['nome'])->toBe('New');
    expect($a->properties->get('old')['nome'])->toBe('Old');
});

it('admin can view audit page', function () {
    $admin = makeFarmUser('admin');
    Customer::factory()->forFarm($admin->farm)->create(['nome' => 'AuditCli']);

    $this->actingAs($admin)->put("/clientes/" . Customer::first()->id, ['nome' => 'Atualizado', 'saldo_coco_kg' => 0]);

    $this->actingAs($admin)
        ->get('/auditoria')
        ->assertOk()
        ->assertSee('editou')      // verbo humanizado em PT-BR
        ->assertSee('Cliente')     // label da entidade em PT-BR
        ->assertSee('Atualizado'); // nome do cliente após edição
});

it('non-admin cannot view audit page', function () {
    $op = makeFarmUser('operador');
    $this->actingAs($op)->get('/auditoria')->assertForbidden();
});
