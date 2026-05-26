<?php

use App\Models\Farm;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * Cria fazenda + usuário com role já atribuído ao team correspondente.
 */
function makeFarmUser(string $role = 'admin', array $userAttrs = [], array $farmAttrs = []): User
{
    foreach (['admin','operador','financeiro','visualizador'] as $r) {
        Role::findOrCreate($r, 'web');
    }

    $farm = Farm::factory()->create($farmAttrs);
    $user = User::factory()->forFarm($farm)->create($userAttrs);

    app(PermissionRegistrar::class)->setPermissionsTeamId($farm->id);
    $user->assignRole($role);

    return $user->fresh('roles', 'farm');
}

/**
 * Atalho de teste: pré-popula o saldo de côco/seco da Farm (estoque próprio).
 * Replaceia o antigo `Area::factory()->create(['saldo_coco_kg' => N])` que
 * já não existe — agora o estoque é unificado na Farm.
 */
function popularEstoqueFazenda(Farm|User $target, float $coco = 0, float $seco = 0): Farm
{
    $farm = $target instanceof User ? $target->farm : $target;
    $farm->update([
        'saldo_coco_kg' => $farm->saldo_coco_kg + $coco,
        'saldo_seco_kg' => $farm->saldo_seco_kg + $seco,
    ]);
    return $farm->fresh();
}
