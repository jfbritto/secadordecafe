<?php

use App\Models\Farm;
use App\Models\User;

it('logs in valid user and redirects to dashboard', function () {
    $farm = Farm::factory()->create(['status' => Farm::STATUS_TRIAL]);
    $user = User::factory()->forFarm($farm)->create([
        'email' => 'a@b.test',
        'password' => bcrypt('senha12345'),
    ]);

    $this->post('/login', ['email' => 'a@b.test', 'password' => 'senha12345'])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $this->post('/login', ['email' => 'x@y.test', 'password' => 'wrong'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('logs out', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});
