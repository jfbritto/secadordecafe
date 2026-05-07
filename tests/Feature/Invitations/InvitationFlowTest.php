<?php

use App\Mail\InvitationMail;
use App\Models\Farm;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('admin can send invitation; email queued', function () {
    Mail::fake();
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)
        ->post('/convites', ['email' => 'novo@x.test', 'role' => 'operador'])
        ->assertRedirect('/usuarios');

    expect(Invitation::count())->toBe(1);
    $inv = Invitation::first();
    expect($inv->farm_id)->toBe($admin->farm_id);
    expect($inv->email)->toBe('novo@x.test');
    expect($inv->role)->toBe('operador');
    expect($inv->token)->not->toBeNull();

    Mail::assertQueued(InvitationMail::class, fn ($m) => $m->invitation->id === $inv->id);
});

it('non-admin cannot send invitation', function () {
    $op = makeFarmUser('operador');
    $this->actingAs($op)
        ->post('/convites', ['email' => 'x@y.test', 'role' => 'operador'])
        ->assertForbidden();
});

it('rejects invite for existing user of same farm', function () {
    $admin = makeFarmUser('admin');
    User::factory()->forFarm($admin->farm)->create(['email' => 'taken@x.test']);

    $this->actingAs($admin)
        ->post('/convites', ['email' => 'taken@x.test', 'role' => 'operador'])
        ->assertSessionHasErrors('email');
});

it('rejects duplicate pending invite', function () {
    $admin = makeFarmUser('admin');
    Invitation::factory()->create([
        'farm_id' => $admin->farm_id,
        'email' => 'pending@x.test',
    ]);

    $this->actingAs($admin)
        ->post('/convites', ['email' => 'pending@x.test', 'role' => 'operador'])
        ->assertSessionHasErrors('email');
});

it('shows invitation accept form', function () {
    $inv = Invitation::factory()->create();
    $this->get("/convite/{$inv->token}")
        ->assertOk()
        ->assertSee($inv->email);
});

it('shows expired page for expired invite', function () {
    $inv = Invitation::factory()->expired()->create();
    $this->get("/convite/{$inv->token}")
        ->assertOk()
        ->assertSee('expirado');
});

it('shows already-accepted page', function () {
    $inv = Invitation::factory()->accepted()->create();
    $this->get("/convite/{$inv->token}")
        ->assertOk()
        ->assertSee('aceito');
});

it('accepts invitation and creates user with role', function () {
    $farm = Farm::factory()->create();
    $inv = Invitation::factory()->create([
        'farm_id' => $farm->id,
        'email' => 'accept@x.test',
        'role' => 'operador',
    ]);
    \Spatie\Permission\Models\Role::findOrCreate('operador', 'web');

    $this->post("/convite/{$inv->token}", [
        'name' => 'Aceitante',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
    ])->assertRedirect('/dashboard');

    $user = User::where('email', 'accept@x.test')->first();
    expect($user)->not->toBeNull();
    expect($user->farm_id)->toBe($farm->id);
    expect($user->hasRole('operador'))->toBeTrue();
    expect($inv->fresh()->accepted_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

it('admin can cancel pending invitation', function () {
    $admin = makeFarmUser('admin');
    $inv = Invitation::factory()->create(['farm_id' => $admin->farm_id]);

    $this->actingAs($admin)
        ->delete("/convites/{$inv->id}")
        ->assertRedirect('/usuarios');

    expect(Invitation::find($inv->id))->toBeNull();
});

it('admin from different farm cannot cancel invitation', function () {
    $adminA = makeFarmUser('admin');
    $adminB = makeFarmUser('admin');
    $inv = Invitation::factory()->create(['farm_id' => $adminA->farm_id]);

    $this->actingAs($adminB)
        ->delete("/convites/{$inv->id}")
        ->assertForbidden();
});
