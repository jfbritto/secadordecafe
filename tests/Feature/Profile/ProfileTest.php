<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('usuário logado acessa a tela do próprio perfil', function () {
    $u = makeFarmUser('operador');

    $this->actingAs($u)
        ->get('/perfil')
        ->assertOk()
        ->assertSee('Meu perfil')
        ->assertSee($u->name)
        ->assertSee($u->email);
});

it('guest não acessa o perfil', function () {
    $this->get('/perfil')->assertRedirect(route('login'));
});

it('atualiza nome e email do próprio user', function () {
    $u = makeFarmUser('operador');

    $this->actingAs($u)
        ->put('/perfil', ['name' => 'novo nome', 'email' => 'novo@x.test'])
        ->assertRedirect(route('perfil.edit'));

    $u->refresh();
    expect($u->name)->toBe('Novo Nome'); // normalizado pelo NameNormalizer
    expect($u->email)->toBe('novo@x.test');
});

it('marca email como não verificado ao trocar', function () {
    $u = makeFarmUser('admin');
    expect($u->email_verified_at)->not->toBeNull();

    $this->actingAs($u)
        ->put('/perfil', ['name' => $u->name, 'email' => 'outro@x.test'])
        ->assertRedirect();

    expect($u->fresh()->email_verified_at)->toBeNull();
});

it('NÃO invalida verificação se o email não mudou', function () {
    $u = makeFarmUser('admin');
    $verifiedAt = $u->email_verified_at;

    $this->actingAs($u)
        ->put('/perfil', ['name' => 'Outro Nome', 'email' => $u->email])
        ->assertRedirect();

    expect($u->fresh()->email_verified_at?->toIso8601String())
        ->toBe($verifiedAt?->toIso8601String());
});

it('rejeita email duplicado de outro user', function () {
    $u = makeFarmUser('admin');
    User::factory()->forFarm($u->farm)->create(['email' => 'usado@x.test']);

    $this->actingAs($u)
        ->put('/perfil', ['name' => $u->name, 'email' => 'usado@x.test'])
        ->assertSessionHasErrors('email');
});

it('aceita manter o próprio email no update (não conta como duplicado)', function () {
    $u = makeFarmUser('admin');

    $this->actingAs($u)
        ->put('/perfil', ['name' => 'Novo', 'email' => $u->email])
        ->assertRedirect();

    expect($u->fresh()->name)->toBe('Novo');
});

it('troca senha exige a atual e confirma a nova', function () {
    $u = makeFarmUser('operador', userAttrs: ['password' => 'senhaAtual1']);

    $this->actingAs($u)
        ->put('/perfil/senha', [
            'current_password' => 'senhaAtual1',
            'password' => 'novaSenha9',
            'password_confirmation' => 'novaSenha9',
        ])
        ->assertRedirect(route('perfil.edit'));

    expect(Hash::check('novaSenha9', $u->fresh()->password))->toBeTrue();
});

it('senha atual incorreta bloqueia troca', function () {
    $u = makeFarmUser('operador', userAttrs: ['password' => 'senhaAtual1']);

    $this->actingAs($u)
        ->put('/perfil/senha', [
            'current_password' => 'errada',
            'password' => 'novaSenha9',
            'password_confirmation' => 'novaSenha9',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('senhaAtual1', $u->fresh()->password))->toBeTrue();
});

it('confirmação de senha tem que bater', function () {
    $u = makeFarmUser('operador', userAttrs: ['password' => 'senhaAtual1']);

    $this->actingAs($u)
        ->put('/perfil/senha', [
            'current_password' => 'senhaAtual1',
            'password' => 'novaSenha9',
            'password_confirmation' => 'outra9999',
        ])
        ->assertSessionHasErrors('password');
});

it('sidebar mostra link clicável pra perfil', function () {
    $u = makeFarmUser('admin');

    $this->actingAs($u)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee(route('perfil.edit'), escape: false)
        ->assertSee('Editar meu perfil');
});
