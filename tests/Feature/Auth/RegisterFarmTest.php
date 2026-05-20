<?php

use App\Mail\WelcomeFarmMail;
use App\Models\Farm;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('operador', 'web');
    Role::findOrCreate('financeiro', 'web');
    Role::findOrCreate('visualizador', 'web');
});

it('normaliza nome do admin no registro da fazenda', function () {
    Mail::fake();

    $this->post('/register', [
        'farm_name' => 'Fazenda Teste',
        'name' => 'joão da silva',
        'email' => 'jds@fazenda.test',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
    ])->assertRedirect('/dashboard');

    expect(User::where('email', 'jds@fazenda.test')->first()->name)->toBe('João da Silva');
});

it('registers a farm with admin user atomically', function () {
    Mail::fake();

    $response = $this->post('/register', [
        'farm_name' => 'Fazenda Teste',
        'name' => 'Joao da Silva',
        'email' => 'joao@fazenda.test',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
    ]);

    $response->assertRedirect('/dashboard');

    expect(Farm::count())->toBe(1);
    expect(User::count())->toBe(1);
    expect(Subscription::count())->toBe(1);

    $farm = Farm::first();
    expect($farm->nome)->toBe('Fazenda Teste');
    expect($farm->slug)->toBe('fazenda-teste');
    expect($farm->status)->toBe(Farm::STATUS_TRIAL);
    expect($farm->trial_ends_at)->not->toBeNull();

    $user = User::first();
    expect($user->farm_id)->toBe($farm->id);
    expect($user->email)->toBe('joao@fazenda.test');
    expect($user->is_root)->toBeFalse();
    expect($user->hasRole('admin'))->toBeTrue();

    $subscription = Subscription::first();
    expect($subscription->farm_id)->toBe($farm->id);
    expect($subscription->status)->toBe(Subscription::STATUS_TRIAL);

    Mail::assertQueued(WelcomeFarmMail::class, fn ($mail) => $mail->user->email === 'joao@fazenda.test');

    $this->assertAuthenticatedAs($user);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@x.test']);

    $response = $this->post('/register', [
        'farm_name' => 'Outra',
        'name' => 'Maria',
        'email' => 'taken@x.test',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Farm::count())->toBe(1); // Apenas a do factory User
});

it('generates unique slug when name collides', function () {
    Mail::fake();

    Farm::factory()->create(['nome' => 'Fazenda Teste', 'slug' => 'fazenda-teste']);

    $this->post('/register', [
        'farm_name' => 'Fazenda Teste',
        'name' => 'Maria',
        'email' => 'maria@x.test',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
    ])->assertRedirect('/dashboard');

    // Em MySQL com RefreshDatabase (transactions), auto_increment não volta a 1.
    // Resolvemos a nova fazenda pelo email do usuário criado no /register.
    $newFarm = User::where('email', 'maria@x.test')->first()->farm;
    expect($newFarm->slug)->toBe('fazenda-teste-2');
});

it('validates required fields', function () {
    $this->post('/register', [])
        ->assertSessionHasErrors(['farm_name', 'name', 'email', 'password']);
});

it('validates password confirmation', function () {
    $this->post('/register', [
        'farm_name' => 'X',
        'name' => 'X',
        'email' => 'x@y.test',
        'password' => 'senha12345',
        'password_confirmation' => 'outra',
    ])->assertSessionHasErrors('password');
});
