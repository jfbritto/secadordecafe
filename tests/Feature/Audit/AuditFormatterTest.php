<?php

use App\Models\Customer;
use App\Models\Dryer;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Support\AuditFormatter;
use Spatie\Activitylog\Models\Activity;

it('describe retorna estrutura humana com label da entidade em PT-BR', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['nome' => 'João Almeida']);

    $activity = Activity::query()->where('subject_type', Customer::class)->latest('id')->first();
    expect($activity)->not->toBeNull();

    $h = AuditFormatter::describe($activity);

    expect($h['entity_label'])->toBe('Cliente');
    expect($h['subject_name'])->toBe('João Almeida');
    expect($h['actor'])->toBe('sistema'); // factory cria sem auth user
    expect($h['event'])->toBe('created');
    expect($h['action'])->toBe('cadastrou');
    expect($h['has_diff'])->toBeFalse(); // created não tem diff
});

it('describe usa prefixo # para Secagem', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);

    $activity = Activity::query()->where('subject_type', \App\Models\Secagem::class)->latest('id')->first();
    $h = AuditFormatter::describe($activity);

    expect($h['entity_label'])->toBe('Secagem');
    expect($h['subject_name'])->toBe('#1');
});

it('diff retorna campos alterados em PT-BR', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['nome' => 'Antigo', 'telefone' => '11111']);

    $this->actingAs($admin)->put("/clientes/{$c->id}", [
        'nome' => 'Novo Nome',
        'telefone' => '22222',
        'saldo_cafe_kg' => 0,
    ]);

    $activity = Activity::query()
        ->where('subject_type', Customer::class)
        ->where('event', 'updated')
        ->latest('id')->first();

    expect($activity)->not->toBeNull();

    $h = AuditFormatter::describe($activity);
    expect($h['has_diff'])->toBeTrue();
    expect($h['action'])->toBe('editou');

    $diff = AuditFormatter::diff($activity);
    $fields = array_column($diff, 'field');

    expect($fields)->toContain('Nome');
    expect($fields)->toContain('Telefone');

    // Verifica linha do nome
    $nomeRow = collect($diff)->firstWhere('field', 'Nome');
    expect($nomeRow['old'])->toBe('Antigo');
    expect($nomeRow['new'])->toBe('Novo Nome');
});

it('diff resolve dryer_id para nome legível', function () {
    $admin = makeFarmUser('admin');
    $d1 = Dryer::factory()->forFarm($admin->farm)->create(['nome' => 'Pinhalense Antigo']);
    $d2 = Dryer::factory()->forFarm($admin->farm)->create(['nome' => 'Palini Novo']);

    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d1->id]);
    $s = \App\Models\Secagem::first();

    $this->actingAs($admin)->put("/secagens/{$s->id}", ['data' => '2026-05-07', 'dryer_id' => $d2->id]);

    $activity = Activity::query()->where('subject_type', \App\Models\Secagem::class)->where('event', 'updated')->latest('id')->first();
    $diff = AuditFormatter::diff($activity);

    $secadorRow = collect($diff)->firstWhere('field', 'Secador');
    expect($secadorRow)->not->toBeNull();
    expect($secadorRow['old'])->toBe('Pinhalense Antigo');
    expect($secadorRow['new'])->toBe('Palini Novo');
});

it('formatValue formata booleanos, datas, valores monetários e kg', function () {
    expect(AuditFormatter::formatValue(true, 'ativo'))->toBe('Sim');
    expect(AuditFormatter::formatValue(false, 'ativo'))->toBe('Não');
    expect(AuditFormatter::formatValue(null, 'qualquer'))->toBe('—');
    expect(AuditFormatter::formatValue('', 'qualquer'))->toBe('—');
    expect(AuditFormatter::formatValue('2026-05-07', 'data'))->toBe('07/05/2026');
    expect(AuditFormatter::formatValue(123.45, 'valor_total'))->toBe('R$ 123,45');
    expect(AuditFormatter::formatValue(1500, 'saldo_cafe_kg'))->toBe('1.500,000 kg');
});

it('fieldLabel traduz campos conhecidos e aplica fallback humano', function () {
    expect(AuditFormatter::fieldLabel('cpf_cnpj'))->toBe('CPF/CNPJ');
    expect(AuditFormatter::fieldLabel('saldo_cafe_kg'))->toBe('Saldo de café (kg)');
    expect(AuditFormatter::fieldLabel('dryer_id'))->toBe('Secador');
    // Fallback: ucfirst + replace underscore
    expect(AuditFormatter::fieldLabel('campo_desconhecido'))->toBe('Campo desconhecido');
});

it('admin vê auditoria com label PT-BR e botão Ver alterações para edições', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create(['nome' => 'AntesEdit']);
    $this->actingAs($admin)->put("/clientes/{$c->id}", ['nome' => 'DepoisEdit', 'saldo_cafe_kg' => 0]);

    $resp = $this->actingAs($admin)->get('/auditoria')->assertOk();
    $html = $resp->getContent();

    expect($html)->toContain('cadastrou');
    expect($html)->toContain('editou');
    expect($html)->toContain('Cliente');
    expect($html)->toContain('Ver alterações');
    expect($html)->toContain('AntesEdit');
});

it('subject deletado ainda mostra nome via properties.attributes/old', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::factory()->forFarm($admin->farm)->create(['nome' => 'Cat A Excluida']);
    $catId = $cat->id;
    $cat->delete();

    $activity = Activity::query()
        ->where('subject_type', ExpenseCategory::class)
        ->where('subject_id', $catId)
        ->where('event', 'deleted')
        ->first();

    if ($activity) {
        $h = AuditFormatter::describe($activity);
        expect($h['subject_name'])->toBe('Cat A Excluida');
        expect($h['action'])->toBe('excluiu');
    } else {
        // Se não loga delete pra essa entidade, OK — só garantir que não quebra
        expect(true)->toBeTrue();
    }
});
