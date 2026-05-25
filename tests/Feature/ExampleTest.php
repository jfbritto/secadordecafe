<?php

it('renders public landing for guests at /', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Roça Nossa', false)
        ->assertSee('Criar minha roça', false);
});

it('redirects authenticated user from / to dashboard', function () {
    $u = makeFarmUser('admin');
    $this->actingAs($u)->get('/')->assertRedirect('/dashboard');
});

it('protects /dashboard for guests', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

it('renders termos and privacidade publicly', function () {
    $this->get('/termos')->assertOk()->assertSee('Termos de uso');
    $this->get('/privacidade')->assertOk()->assertSee('Política de privacidade');
});
