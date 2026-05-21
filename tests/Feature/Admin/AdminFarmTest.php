<?php

use App\Models\Farm;
use App\Models\Subscription;
use App\Models\User;

beforeEach(function () {
    $this->root = User::factory()->root()->create();
});

function farmWithAdmin(string $nome = 'Fazenda Teste', string $status = 'trial'): Farm
{
    $admin = makeFarmUser('admin', [], ['nome' => $nome]);
    $farm = $admin->farm;
    $farm->status = $status;
    $farm->save();
    // makeFarmUser não cria Subscription (só o RegisterFarmAction faz isso em prod) —
    // garantimos aqui pro setup do teste ficar realista.
    Subscription::firstOrCreate(
        ['farm_id' => $farm->id],
        ['status' => $status, 'trial_ends_at' => now()->addDays(14)]
    )->update(['status' => $status]);
    return $farm->fresh('subscription');
}

it('root vê listagem completa de fazendas em /admin/fazendas', function () {
    $f1 = farmWithAdmin('Fazenda Alpha');
    $f2 = farmWithAdmin('Fazenda Beta');

    $this->actingAs($this->root)
        ->get('/admin/fazendas')
        ->assertOk()
        ->assertSee('Fazendas da plataforma')
        ->assertSee('Fazenda Alpha')
        ->assertSee('Fazenda Beta');
});

it('admin de fazenda NÃO acessa o painel root', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->get('/admin/fazendas')
        ->assertForbidden();
});

it('busca filtra por nome da fazenda', function () {
    farmWithAdmin('Fazenda Alpha');
    farmWithAdmin('Fazenda Beta');

    $this->actingAs($this->root)
        ->get('/admin/fazendas?q=Alpha')
        ->assertOk()
        ->assertSee('Fazenda Alpha')
        ->assertDontSee('Fazenda Beta');
});

it('filtro de status segmenta corretamente', function () {
    farmWithAdmin('Trial One', 'trial');
    farmWithAdmin('Partner One', 'partner');

    $resp = $this->actingAs($this->root)->get('/admin/fazendas?status=partner');
    $resp->assertSee('Partner One')->assertDontSee('Trial One');
});

it('root abre detalhe da fazenda e vê stats + botões de transição', function () {
    $farm = farmWithAdmin('Detalhe Teste', 'trial');

    $this->actingAs($this->root)
        ->get("/admin/fazendas/{$farm->id}")
        ->assertOk()
        ->assertSee('Detalhe Teste')
        ->assertSee('Mudar plano')
        ->assertSee('Parceira') // botão de transição
        ->assertSee('Ativa');
});

it('root muda plano de trial pra parceira', function () {
    $farm = farmWithAdmin('Pra Parceira', 'trial');

    $this->actingAs($this->root)
        ->put("/admin/fazendas/{$farm->id}/plano", ['status' => 'partner'])
        ->assertRedirect("/admin/fazendas/{$farm->id}");

    $farm->refresh()->load('subscription');
    expect($farm->subscription->status)->toBe('partner');
    expect($farm->status)->toBe('partner');
    expect($farm->subscription->current_period_end)->toBeNull();
    expect($farm->subscription->asaas_subscription_id)->toBeNull();
});

it('root muda partner de volta pra trial e renova trial_ends_at', function () {
    $farm = farmWithAdmin('Volta Trial', 'partner');

    $this->actingAs($this->root)
        ->put("/admin/fazendas/{$farm->id}/plano", ['status' => 'trial'])
        ->assertRedirect();

    $farm->refresh()->load('subscription');
    expect($farm->subscription->status)->toBe('trial');
    expect($farm->subscription->trial_ends_at)->not->toBeNull();
    expect($farm->subscription->trial_ends_at->isFuture())->toBeTrue();
});

it('admin de fazenda NÃO consegue mudar plano (403)', function () {
    $farm = farmWithAdmin('Bloqueada Admin', 'trial');
    $admin = $farm->users()->first();

    $this->actingAs($admin)
        ->put("/admin/fazendas/{$farm->id}/plano", ['status' => 'partner'])
        ->assertForbidden();

    expect($farm->fresh()->subscription->status)->not->toBe('partner');
});

it('fazenda parceira mantém acesso ao sistema (não é bloqueada pelo middleware)', function () {
    $admin = makeFarmUser('admin');
    $farm = $admin->farm;

    // Marca como parceira (root)
    $this->actingAs($this->root)
        ->put("/admin/fazendas/{$farm->id}/plano", ['status' => 'partner']);

    // O admin da farm consegue acessar normalmente
    $this->actingAs($admin)->get('/dashboard')->assertOk();
});

it('rejeita status inválido', function () {
    $farm = farmWithAdmin('Inválido', 'trial');

    $this->actingAs($this->root)
        ->put("/admin/fazendas/{$farm->id}/plano", ['status' => 'inexistente'])
        ->assertSessionHasErrors('status');
});

it('mudança de plano gera entry de auditoria', function () {
    $farm = farmWithAdmin('Audit Test', 'trial');

    $this->actingAs($this->root)
        ->put("/admin/fazendas/{$farm->id}/plano", ['status' => 'partner']);

    $a = \Spatie\Activitylog\Models\Activity::query()
        ->where('description', 'assinatura updated')
        ->where('subject_id', $farm->subscription->id)
        ->latest('id')->first();

    expect($a)->not->toBeNull();
    expect($a->properties['old']['status'])->toBe('trial');
    expect($a->properties['attributes']['status'])->toBe('partner');
});

it('dashboard root mostra contador de Parceiras', function () {
    farmWithAdmin('Parceira A', 'partner');
    farmWithAdmin('Parceira B', 'partner');
    farmWithAdmin('Trial X', 'trial');

    $this->actingAs($this->root)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Parceiras');
});

it('sidebar mostra link Fazendas pra root', function () {
    $this->actingAs($this->root)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee(route('admin.fazendas.index'), escape: false);
});

it('sidebar NÃO mostra link Fazendas pra admin comum', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee(route('admin.fazendas.index'), escape: false);
});

it('block-overdue não bloqueia fazenda parceira', function () {
    $farm = farmWithAdmin('Parceira Old', 'partner');
    // Simula que ficou anos sem mudança — não deve afetar (status != past_due)
    $farm->subscription->update(['updated_at' => now()->subDays(100)]);

    $this->artisan('subscriptions:block-overdue')->assertExitCode(0);

    expect($farm->fresh()->status)->toBe('partner');
});
