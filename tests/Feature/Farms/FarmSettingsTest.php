<?php

it('admin can edit farm settings', function () {
    $admin = makeFarmUser('admin');

    $this->actingAs($admin)->get('/fazenda')->assertOk();

    $this->actingAs($admin)
        ->put('/fazenda', [
            'nome' => 'Fazenda Renomeada',
            'cidade' => 'Belo Horizonte',
            'estado' => 'MG',
        ])
        ->assertRedirect('/fazenda');

    expect($admin->farm->fresh()->nome)->toBe('Fazenda Renomeada');
});

it('non-admin cannot edit farm', function () {
    $op = makeFarmUser('operador');

    $this->actingAs($op)->get('/fazenda')->assertForbidden();
    $this->actingAs($op)->put('/fazenda', ['nome' => 'Hack'])->assertForbidden();
});
