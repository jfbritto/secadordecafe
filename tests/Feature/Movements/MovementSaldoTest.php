<?php

use App\Models\Customer;
use App\Models\Movement;

// Helper pra POST nas rotas polimórficas novas
function postMov(\App\Models\User $user, Customer $c, array $payload, ?\Tests\TestCase $t = null)
{
    return ($t ?? test())->actingAs($user)
        ->post("/movimentacoes/cliente/{$c->id}", array_merge(['produto' => 'coco'], $payload));
}

it('entrada incrementa saldo e cria movement', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 100]);

    $this->actingAs($admin)
        ->post("/movimentacoes/cliente/{$c->id}", [
            'tipo' => 'entrada',
            'produto' => 'coco',
            'quantidade' => 50.5,
            'observacao' => 'Recebido',
        ])
        ->assertRedirect("/movimentacoes/cliente/{$c->id}");

    expect((float) $c->fresh()->saldo_coco_kg)->toBe(150.5);
    expect(Movement::count())->toBe(1);
    expect((float) Movement::first()->quantidade_kg)->toBe(50.5);
});

it('saida decrementa saldo (admin only)', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 100]);

    $this->actingAs($admin)
        ->post("/movimentacoes/cliente/{$c->id}", [
            'tipo' => 'saida',
            'produto' => 'coco',
            'quantidade' => 30,
        ])
        ->assertRedirect();

    expect((float) $c->fresh()->saldo_coco_kg)->toBe(70.0);
    expect((float) Movement::where('tipo', 'saida')->first()->quantidade_kg)->toBe(-30.0);
});

it('operador nao pode dar saida (admin only)', function () {
    $op = makeFarmUser('operador');
    $c = Customer::factory()->forFarm($op->farm)->create(['saldo_coco_kg' => 100]);

    $this->actingAs($op)
        ->post("/movimentacoes/cliente/{$c->id}", [
            'tipo' => 'saida',
            'produto' => 'coco',
            'quantidade' => 10,
        ])
        ->assertForbidden();
});

it('ajuste positivo e negativo', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 50]);

    $this->actingAs($admin)
        ->post("/movimentacoes/cliente/{$c->id}", ['tipo' => 'ajuste', 'produto' => 'coco', 'direcao' => '+', 'quantidade' => 10])
        ->assertRedirect();
    expect((float) $c->fresh()->saldo_coco_kg)->toBe(60.0);

    $this->actingAs($admin)
        ->post("/movimentacoes/cliente/{$c->id}", ['tipo' => 'ajuste', 'produto' => 'coco', 'direcao' => '-', 'quantidade' => 5])
        ->assertRedirect();
    expect((float) $c->fresh()->saldo_coco_kg)->toBe(55.0);
});

it('saldo nao pode ficar negativo', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 10]);

    $this->actingAs($admin)
        ->post("/movimentacoes/cliente/{$c->id}", ['tipo' => 'saida', 'produto' => 'coco', 'quantidade' => 11])
        ->assertStatus(422);

    expect((float) $c->fresh()->saldo_coco_kg)->toBe(10.0);
    expect(Movement::count())->toBe(0);
});

it('extrato lista movimentacoes do cliente', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 100]);

    $this->actingAs($admin)->post("/movimentacoes/cliente/{$c->id}", ['tipo' => 'entrada', 'produto' => 'coco', 'quantidade' => 30, 'observacao' => 'Lote A']);

    $this->actingAs($admin)
        ->get("/movimentacoes/cliente/{$c->id}")
        ->assertOk()
        ->assertSee('Lote A');
});

it('extrato calcula saldo após cada movimentação (running balance)', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 0]);

    $this->actingAs($admin)->post("/movimentacoes/cliente/{$c->id}", ['tipo' => 'entrada', 'produto' => 'coco', 'quantidade' => 100]);
    $this->actingAs($admin)->post("/movimentacoes/cliente/{$c->id}", ['tipo' => 'entrada', 'produto' => 'coco', 'quantidade' => 50]);
    $this->actingAs($admin)->post("/movimentacoes/cliente/{$c->id}", ['tipo' => 'ajuste', 'produto' => 'coco', 'direcao' => '-', 'quantidade' => 20]);

    $resp = $this->actingAs($admin)->get("/movimentacoes/cliente/{$c->id}");
    $resp->assertOk();

    $movements = $resp->viewData('movements');
    // Ordem DESC: o mais recente vem primeiro
    expect((float) $movements[0]->saldo_apos)->toBe(130.0);
    expect((float) $movements[1]->saldo_apos)->toBe(150.0);
    expect((float) $movements[2]->saldo_apos)->toBe(100.0);

    expect((float) $c->fresh()->saldo_coco_kg)->toBe(130.0);
});

it('extrato da fazenda abre sem id e mostra estoque próprio', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->get('/movimentacoes/fazenda')
        ->assertOk()
        ->assertSee('Estoque seco');
});

it('extrato mostra link clicável pra Secagem como origem', function () {
    $admin = makeFarmUser('admin');
    $dryer = \App\Models\Dryer::factory()->forFarm($admin->farm)->create();
    $c = Customer::factory()->forFarm($admin->farm)->create(['saldo_coco_kg' => 1000]);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $dryer->id]);
    $s = \App\Models\Secagem::first();
    $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
        'origin_type' => 'cliente',
        'origin_id' => $c->id,
        'quantidade_recebida_kg' => 500,
    ]);
    // Registra saída antes de concluir
    $item = \App\Models\SecagemItem::first();
    $this->actingAs($admin)->patch("/secagens/{$s->id}/items/{$item->id}/saida", [
        'quantidade_seca_kg' => 300,
        'comissao_percentual' => 5,
    ]);
    $this->actingAs($admin)->post("/secagens/{$s->id}/concluir");

    $this->actingAs($admin)
        ->get("/movimentacoes/cliente/{$c->id}")
        ->assertOk()
        ->assertSee("Secagem #{$s->numero}")
        ->assertSee("/secagens/{$s->id}", escape: false);
});
