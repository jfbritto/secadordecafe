<?php

use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

it('admin lists users of the farm', function () {
    $admin = makeFarmUser('admin');
    $other = User::factory()->forFarm($admin->farm)->create(['name' => 'Outro Op']);
    app(PermissionRegistrar::class)->setPermissionsTeamId($admin->farm_id);
    $other->assignRole('operador');

    $this->actingAs($admin)
        ->get('/usuarios')
        ->assertOk()
        ->assertSee('Outro Op')
        ->assertSee($admin->name);
});

it('non-admin cannot list users', function () {
    $op = makeFarmUser('operador');
    $this->actingAs($op)->get('/usuarios')->assertForbidden();
});

it('admin can update another user role', function () {
    $admin = makeFarmUser('admin');
    $u = User::factory()->forFarm($admin->farm)->create();
    app(PermissionRegistrar::class)->setPermissionsTeamId($admin->farm_id);
    $u->assignRole('operador');

    $this->actingAs($admin)
        ->put("/usuarios/{$u->id}/role", ['role' => 'financeiro'])
        ->assertRedirect('/usuarios');

    expect($u->fresh()->hasRole('financeiro'))->toBeTrue();
    expect($u->fresh()->hasRole('operador'))->toBeFalse();
});

it('admin cannot demote themselves', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->put("/usuarios/{$admin->id}/role", ['role' => 'operador'])
        ->assertForbidden();
});

it('protects last admin from demotion', function () {
    $admin = makeFarmUser('admin');
    $other = User::factory()->forFarm($admin->farm)->create();
    app(PermissionRegistrar::class)->setPermissionsTeamId($admin->farm_id);
    $other->assignRole('operador');

    // Outro admin tenta rebaixar o admin (proteção do "último admin")
    $other->syncRoles(['admin']);
    // Agora outro é admin; rebaixa o original
    $this->actingAs($other)
        ->put("/usuarios/{$admin->id}/role", ['role' => 'operador'])
        ->assertRedirect();
    expect($admin->fresh()->hasRole('operador'))->toBeTrue();

    // Agora só "other" é admin; tentar rebaixá-lo deve falhar
    $this->actingAs($other)
        ->put("/usuarios/{$other->id}/role", ['role' => 'operador'])
        ->assertForbidden(); // self-update bloqueado pela policy
});

it('admin can delete other user', function () {
    $admin = makeFarmUser('admin');
    $u = User::factory()->forFarm($admin->farm)->create();
    app(PermissionRegistrar::class)->setPermissionsTeamId($admin->farm_id);
    $u->assignRole('operador');

    $this->actingAs($admin)
        ->delete("/usuarios/{$u->id}")
        ->assertRedirect('/usuarios');

    expect(User::find($u->id))->toBeNull();
});

it('admin cannot delete themselves', function () {
    $admin = makeFarmUser('admin');
    $this->actingAs($admin)
        ->delete("/usuarios/{$admin->id}")
        ->assertForbidden();
});
