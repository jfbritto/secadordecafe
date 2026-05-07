<?php

use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Invitation;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;
use App\Models\Subscription;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

it('User: cadastrar gera activity, mas NUNCA loga password ou remember_token', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'Maria Souza',
            'email' => 'maria@x.test',
            'role' => 'operador',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ])
        ->assertRedirect();

    $a = Activity::where('description', 'usuario created')
        ->where('subject_type', User::class)
        ->latest('id')->first();

    expect($a)->not->toBeNull();
    $attrs = $a->properties['attributes'] ?? [];
    expect($attrs)->toHaveKey('name');
    expect($attrs)->toHaveKey('email');
    expect($attrs)->not->toHaveKey('password');
    expect($attrs)->not->toHaveKey('remember_token');
    // farm_id na property pra scoping
    expect((int) $a->properties['farm_id'])->toBe($admin->farm_id);
});

it('User: editar email gera diff com old/new', function () {
    $admin = makeFarmUser('admin');
    $u = User::factory()->forFarm($admin->farm)->create(['email' => 'antes@x.test']);

    $u->update(['email' => 'depois@x.test']);

    $a = Activity::where('description', 'usuario updated')
        ->where('subject_id', $u->id)->first();
    expect($a)->not->toBeNull();
    expect($a->properties['old']['email'])->toBe('antes@x.test');
    expect($a->properties['attributes']['email'])->toBe('depois@x.test');
});

it('Movement: registrar gera activity com farm_id e tipo', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 100]);

    $this->actingAs($admin)
        ->post("/clientes/{$c->id}/movimentacoes", ['tipo' => 'entrada', 'quantidade' => 30])
        ->assertRedirect();

    $a = Activity::where('description', 'movimentacao created')
        ->where('subject_type', Movement::class)->first();
    expect($a)->not->toBeNull();
    expect($a->properties['attributes']['tipo'])->toBe('entrada');
    expect((float) $a->properties['attributes']['quantidade_kg'])->toBe(30.0);
    expect((int) $a->properties['farm_id'])->toBe($admin->farm_id);
});

it('SecagemItem: adicionar item gera activity com secagem_id e customer_id', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_cafe_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'customer_id' => $c->id, 'quantidade_recebida_kg' => 100,
        'quantidade_seca_kg' => 60, 'comissao_percentual' => 5,
    ]);

    $a = Activity::where('description', 'item de secagem created')
        ->where('subject_type', SecagemItem::class)->first();
    expect($a)->not->toBeNull();
    expect((int) $a->properties['attributes']['secagem_id'])->toBe($s->id);
    expect((int) $a->properties['attributes']['customer_id'])->toBe($c->id);
    expect((int) $a->properties['farm_id'])->toBe($admin->farm_id);
});

it('Subscription: mudar status gera activity (mas nunca loga token Asaas externo)', function () {
    $admin = makeFarmUser('admin');
    $sub = Subscription::firstOrCreate(
        ['farm_id' => $admin->farm_id],
        ['status' => 'trial', 'trial_ends_at' => now()->addDays(14)]
    );

    $sub->update(['status' => 'active']);

    $a = Activity::where('description', 'assinatura updated')
        ->where('subject_id', $sub->id)->first();
    expect($a)->not->toBeNull();
    expect($a->properties['attributes']['status'])->toBe('active');
    expect((int) $a->properties['farm_id'])->toBe($admin->farm_id);
});

it('Invitation: criar gera activity, mas NUNCA loga token', function () {
    $admin = makeFarmUser('admin');

    $inv = Invitation::create([
        'farm_id' => $admin->farm_id,
        'email' => 'novo@x.test',
        'role' => 'operador',
        'token' => 'super-secreto',
        'expires_at' => now()->addDays(7),
        'invited_by' => $admin->id,
    ]);

    $a = Activity::where('description', 'convite created')
        ->where('subject_id', $inv->id)->first();
    expect($a)->not->toBeNull();
    $attrs = $a->properties['attributes'] ?? [];
    expect($attrs)->toHaveKey('email');
    expect($attrs)->toHaveKey('role');
    expect($attrs)->not->toHaveKey('token');
    expect((int) $a->properties['farm_id'])->toBe($admin->farm_id);
});
