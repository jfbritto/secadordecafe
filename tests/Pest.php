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
