<?php

use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Secagem;
use App\Support\PermissionsMatrix;

it('expoe roles, modulos e acoes', function () {
    expect(PermissionsMatrix::ROLES)->toHaveKeys(['admin', 'operador', 'financeiro', 'visualizador']);
    expect(PermissionsMatrix::MODULES)->toHaveKeys(['clientes', 'secagens', 'secadores', 'despesas', 'categorias']);
    expect(PermissionsMatrix::ACTIONS)->toHaveKeys(['view', 'create', 'edit', 'delete']);
});

it('admin pode tudo em qualquer modulo/acao', function () {
    foreach (array_keys(PermissionsMatrix::MODULES) as $module) {
        foreach (array_keys(PermissionsMatrix::ACTIONS) as $action) {
            expect(PermissionsMatrix::can('admin', $module, $action))
                ->toBeTrue("admin deveria poder {$action} em {$module}");
        }
    }
});

it('operador pode criar/editar clientes mas nao excluir', function () {
    expect(PermissionsMatrix::can('operador', 'clientes', 'view'))->toBeTrue();
    expect(PermissionsMatrix::can('operador', 'clientes', 'create'))->toBeTrue();
    expect(PermissionsMatrix::can('operador', 'clientes', 'edit'))->toBeTrue();
    expect(PermissionsMatrix::can('operador', 'clientes', 'delete'))->toBeFalse();
});

it('operador pode concluir secagem', function () {
    expect(PermissionsMatrix::can('operador', 'secagens', 'conclude'))->toBeTrue();
});

it('operador NAO pode acessar despesas/categorias/usuarios/fazenda', function () {
    foreach (['despesas', 'categorias', 'usuarios', 'fazenda', 'auditoria', 'assinatura'] as $module) {
        expect(PermissionsMatrix::can('operador', $module, 'view'))
            ->toBeFalse("operador nao deveria ver {$module}");
    }
});

it('financeiro mexe com despesas+categorias mas so VE outros modulos', function () {
    expect(PermissionsMatrix::can('financeiro', 'despesas', 'create'))->toBeTrue();
    expect(PermissionsMatrix::can('financeiro', 'despesas', 'edit'))->toBeTrue();
    expect(PermissionsMatrix::can('financeiro', 'categorias', 'create'))->toBeTrue();
    expect(PermissionsMatrix::can('financeiro', 'clientes', 'view'))->toBeTrue();
    expect(PermissionsMatrix::can('financeiro', 'clientes', 'create'))->toBeFalse();
    expect(PermissionsMatrix::can('financeiro', 'secagens', 'create'))->toBeFalse();
});

it('visualizador so vê clientes, secagens, secadores, despesas, categorias', function () {
    foreach (['clientes', 'secagens', 'secadores', 'despesas', 'categorias'] as $module) {
        expect(PermissionsMatrix::can('visualizador', $module, 'view'))->toBeTrue();
        foreach (['create', 'edit', 'delete'] as $action) {
            expect(PermissionsMatrix::can('visualizador', $module, $action))
                ->toBeFalse("visualizador nao deveria {$action} em {$module}");
        }
    }
});

it('matriz reflete o comportamento real das policies', function () {
    // Para cada role, cria um user e verifica que a matriz bate com $user->can() do Laravel
    foreach (array_keys(PermissionsMatrix::ROLES) as $role) {
        $user = makeFarmUser($role);
        $cliente = Customer::factory()->forFarm($user->farm)->create();
        $cat = ExpenseCategory::query()->where('farm_id', $user->farm_id)->first();
        $despesa = Expense::factory()->category($cat)->create();
        $secador = Dryer::factory()->forFarm($user->farm)->create();

        // Customer: view, create, edit, delete
        expect($user->can('viewAny', Customer::class))
            ->toBe(PermissionsMatrix::can($role, 'clientes', 'view'),
                "Mismatch role={$role} clientes/view");
        expect($user->can('create', Customer::class))
            ->toBe(PermissionsMatrix::can($role, 'clientes', 'create'),
                "Mismatch role={$role} clientes/create");
        expect($user->can('update', $cliente))
            ->toBe(PermissionsMatrix::can($role, 'clientes', 'edit'),
                "Mismatch role={$role} clientes/edit");
        expect($user->can('delete', $cliente))
            ->toBe(PermissionsMatrix::can($role, 'clientes', 'delete'),
                "Mismatch role={$role} clientes/delete");

        // Dryer
        expect($user->can('create', Dryer::class))
            ->toBe(PermissionsMatrix::can($role, 'secadores', 'create'),
                "Mismatch role={$role} secadores/create");
        expect($user->can('delete', $secador))
            ->toBe(PermissionsMatrix::can($role, 'secadores', 'delete'),
                "Mismatch role={$role} secadores/delete");

        // Area
        $area = \App\Models\Area::factory()->forFarm($user->farm)->create();
        expect($user->can('viewAny', \App\Models\Area::class))
            ->toBe(PermissionsMatrix::can($role, 'areas', 'view'),
                "Mismatch role={$role} areas/view");
        expect($user->can('create', \App\Models\Area::class))
            ->toBe(PermissionsMatrix::can($role, 'areas', 'create'),
                "Mismatch role={$role} areas/create");
        expect($user->can('update', $area))
            ->toBe(PermissionsMatrix::can($role, 'areas', 'edit'),
                "Mismatch role={$role} areas/edit");
        expect($user->can('delete', $area))
            ->toBe(PermissionsMatrix::can($role, 'areas', 'delete'),
                "Mismatch role={$role} areas/delete");

        // Expense
        expect($user->can('viewAny', Expense::class))
            ->toBe(PermissionsMatrix::can($role, 'despesas', 'view'),
                "Mismatch role={$role} despesas/view");
        expect($user->can('create', Expense::class))
            ->toBe(PermissionsMatrix::can($role, 'despesas', 'create'),
                "Mismatch role={$role} despesas/create");
        expect($user->can('delete', $despesa))
            ->toBe(PermissionsMatrix::can($role, 'despesas', 'delete'),
                "Mismatch role={$role} despesas/delete");

        // Secagem (conclude é especial)
        $secagem = Secagem::create([
            'farm_id' => $user->farm_id, 'user_id' => $user->id, 'dryer_id' => $secador->id,
            'numero' => 1, 'data' => '2026-05-07', 'status' => 'rascunho',
        ]);
        expect($user->can('conclude', $secagem))
            ->toBe(PermissionsMatrix::can($role, 'secagens', 'conclude'),
                "Mismatch role={$role} secagens/conclude");
    }
});
