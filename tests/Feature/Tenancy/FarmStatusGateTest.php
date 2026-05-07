<?php

use App\Models\Farm;
use App\Models\User;

it('redirects blocked farm user to blocked page', function () {
    $farm = Farm::factory()->create(['status' => Farm::STATUS_BLOCKED]);
    $user = User::factory()->forFarm($farm)->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/farm/blocked');
});

it('allows active farm user to reach dashboard', function () {
    $farm = Farm::factory()->active()->create();
    $user = User::factory()->forFarm($farm)->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('shows blocked page', function () {
    $farm = Farm::factory()->create(['status' => Farm::STATUS_BLOCKED]);
    $user = User::factory()->forFarm($farm)->create();

    $this->actingAs($user)
        ->get('/farm/blocked')
        ->assertOk()
        ->assertSee($farm->nome);
});
