<?php

use App\Models\User;

it('admin can directly create a user with password', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'Maria Souza',
            'email' => 'maria@x.test',
            'role' => 'operador',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ])
        ->assertRedirect('/usuarios');

    $u = User::where('email', 'maria@x.test')->first();
    expect($u)->not->toBeNull();
    expect($u->farm_id)->toBe($admin->farm_id);
    expect($u->name)->toBe('Maria Souza');
    expect($u->email_verified_at)->not->toBeNull(); // já verificado
    expect($u->hasRole('operador'))->toBeTrue();
});

it('normaliza nome do usuário ao cadastrar (capitaliza e respeita conectivos)', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'maria das dores',
            'email' => 'mdd@x.test',
            'role' => 'operador',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ])
        ->assertRedirect('/usuarios');

    expect(User::where('email', 'mdd@x.test')->first()->name)->toBe('Maria das Dores');
});

it('rejects duplicate email', function () {
    $admin = makeFarmUser('admin');
    User::factory()->forFarm($admin->farm)->create(['email' => 'taken@x.test']);

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'X',
            'email' => 'taken@x.test',
            'role' => 'operador',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ])
        ->assertSessionHasErrors('email');
});

it('rejects invalid role', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'X',
            'email' => 'novo@x.test',
            'role' => 'super-admin',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ])
        ->assertSessionHasErrors('role');
});

it('requires confirmed password', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'X',
            'email' => 'novo@x.test',
            'role' => 'operador',
            'password' => 'senha12345',
            'password_confirmation' => 'outra',
        ])
        ->assertSessionHasErrors('password');
});

it('non-admin cannot access create user form', function () {
    foreach (['operador', 'financeiro', 'visualizador'] as $role) {
        $u = makeFarmUser($role);
        $this->actingAs($u)->get('/usuarios/criar')->assertForbidden();
        $this->actingAs($u)
            ->post('/usuarios', [
                'name' => 'Hack', 'email' => 'h@x.test', 'role' => 'admin',
                'password' => 'senha12345', 'password_confirmation' => 'senha12345',
            ])
            ->assertForbidden();
    }
});

it('admin sees Cadastrar usuario button on index', function () {
    $admin = makeFarmUser('admin');
    $this->actingAs($admin)->get('/usuarios')
        ->assertSee('Cadastrar usuário')
        ->assertSee(route('usuarios.create'));
});

it('create form renders the permissions matrix with all roles', function () {
    $admin = makeFarmUser('admin');
    $html = $this->actingAs($admin)->get('/usuarios/criar')->getContent();

    foreach (\App\Support\PermissionsMatrix::ROLES as $r) {
        expect($html)->toContain($r['label']);
    }
    foreach (\App\Support\PermissionsMatrix::MODULES as $label) {
        expect($html)->toContain($label);
    }
    expect($html)->toContain('value="admin"');
    expect($html)->toContain('value="operador"');
    expect($html)->toContain('value="financeiro"');
    expect($html)->toContain('value="visualizador"');
});

it('newly created user can log in with the provided password', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)->post('/usuarios', [
        'name' => 'Joao Test',
        'email' => 'joao-login@x.test',
        'role' => 'operador',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
    ]);

    auth()->logout();

    $this->post('/login', ['email' => 'joao-login@x.test', 'password' => 'senha12345'])
        ->assertRedirect('/dashboard');
});

it('newly created user is scoped to admin farm only', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');

    $this->actingAs($a)->post('/usuarios', [
        'name' => 'Test', 'email' => 'scoped@x.test', 'role' => 'operador',
        'password' => 'senha12345', 'password_confirmation' => 'senha12345',
    ]);

    $u = User::where('email', 'scoped@x.test')->first();
    expect($u->farm_id)->toBe($a->farm_id);
    expect($u->farm_id)->not->toBe($b->farm_id);
});
