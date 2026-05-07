<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Roles globais (sem team) — atribuídos por team via setPermissionsTeamId no momento do assign.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['root', 'admin', 'operador', 'financeiro', 'visualizador'] as $name) {
            Role::findOrCreate($name, 'web');
        }
    }
}
