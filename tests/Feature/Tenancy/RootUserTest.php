<?php

use App\Models\Farm;
use App\Models\User;

it('root user is unaffected by farm status', function () {
    $root = User::factory()->root()->create();

    $this->actingAs($root)
        ->get('/dashboard')
        ->assertOk();
});

it('root user has no farm but reaches dashboard', function () {
    $root = User::factory()->root()->create();

    expect($root->farm_id)->toBeNull();
    expect($root->isRoot())->toBeTrue();

    $this->actingAs($root)->get('/dashboard')->assertOk();
});

it('Gate::before grants every ability to root', function () {
    $root = User::factory()->root()->create();
    $this->actingAs($root);

    expect(\Illuminate\Support\Facades\Gate::allows('any-arbitrary-ability'))->toBeTrue();
});
