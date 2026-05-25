<?php

use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invitation;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;
use App\Models\User;

/**
 * Garantia de tenancy: tudo que pertence a uma fazenda fica isolado dela.
 * Cada teste cria 2 fazendas com 2 admins e tenta cruzar — deve falhar.
 */

it('Customer: admin da farm A NAO vê cliente da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $cb = Customer::factory()->forFarm($b->farm)->create();

    $this->actingAs($a)->get(route('clientes.show', $cb))->assertNotFound();
    $this->actingAs($a)->get(route('clientes.edit', $cb))->assertNotFound();
});

it('Customer: admin da farm A NAO atualiza cliente da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $cb = Customer::factory()->forFarm($b->farm)->create(['nome' => 'Original']);

    $this->actingAs($a)->put(route('clientes.update', $cb), ['nome' => 'Hackeado'])->assertNotFound();
    expect($cb->fresh()->nome)->toBe('Original');
});

it('Customer: admin da farm A NAO exclui cliente da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $cb = Customer::factory()->forFarm($b->farm)->create();

    $this->actingAs($a)->delete(route('clientes.destroy', $cb))->assertNotFound();
    expect(Customer::withoutGlobalScopes()->whereKey($cb->id)->exists())->toBeTrue();
});

it('Movimentações: admin da farm A NAO acessa extrato de cliente da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $cb = Customer::factory()->forFarm($b->farm)->create();

    $this->actingAs($a)->get("/movimentacoes/cliente/{$cb->id}")->assertNotFound();
    $this->actingAs($a)
        ->post("/movimentacoes/cliente/{$cb->id}", ['tipo' => 'entrada', 'produto' => 'coco', 'quantidade' => 10])
        ->assertNotFound();
});

it('Dryer: admin da farm A NAO atualiza/exclui secador da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $db = Dryer::factory()->forFarm($b->farm)->create(['nome' => 'Secador B']);

    $this->actingAs($a)->put(route('secadores.update', $db), ['nome' => 'Hack', 'ativo' => true])->assertNotFound();
    $this->actingAs($a)->delete(route('secadores.destroy', $db))->assertNotFound();
    expect($db->fresh()->nome)->toBe('Secador B');
});

it('Secagem: admin da farm A NAO acessa secagem da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $db = Dryer::factory()->forFarm($b->farm)->create();
    $this->actingAs($b)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $db->id]);
    $sb = Secagem::withoutGlobalScopes()->first();

    $this->actingAs($a)->get("/secagens/{$sb->id}")->assertNotFound();
    $this->actingAs($a)->put("/secagens/{$sb->id}", ['data' => '2026-05-07', 'dryer_id' => $db->id])->assertNotFound();
    $this->actingAs($a)->post("/secagens/{$sb->id}/concluir")->assertNotFound();
});

it('SecagemItem: admin da farm A NAO adiciona item com cliente da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $da = Dryer::factory()->forFarm($a->farm)->create();
    $cb = Customer::factory()->forFarm($b->farm)->create(['saldo_coco_kg' => 1000]);

    $this->actingAs($a)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $da->id]);
    $sa = Secagem::first();

    $this->actingAs($a)
        ->post("/secagens/{$sa->id}/items", [
            'origin_type' => 'cliente', 'origin_id' => $cb->id,
            'quantidade_recebida_kg' => 100,
        ])
        ->assertSessionHasErrors('origin_id');

    expect(SecagemItem::count())->toBe(0);
});

it('Expense: admin da farm A NAO atualiza/exclui despesa da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $eb = Expense::factory()->forFarm($b->farm)->create(['descricao' => 'Original']);

    $this->actingAs($a)
        ->put(route('despesas.update', $eb), [
            'data' => '2026-05-07',
            'descricao' => 'Hack',
            'valor_total' => 100,
            'expense_category_id' => ExpenseCategory::factory()->forFarm($a->farm)->create()->id,
        ])
        ->assertNotFound();
    $this->actingAs($a)->delete(route('despesas.destroy', $eb))->assertNotFound();
    expect($eb->fresh()->descricao)->toBe('Original');
});

it('Expense: NAO aceita expense_category_id de outra farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $catB = ExpenseCategory::factory()->forFarm($b->farm)->create(['ativo' => true]);

    $this->actingAs($a)
        ->post('/despesas', [
            'data' => '2026-05-07',
            'descricao' => 'Teste',
            'valor_total' => 50,
            'expense_category_id' => $catB->id,
        ])
        ->assertSessionHasErrors('expense_category_id');

    expect(Expense::count())->toBe(0);
});

it('ExpenseCategory: admin da farm A NAO atualiza/exclui categoria da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $catB = ExpenseCategory::factory()->forFarm($b->farm)->create(['nome' => 'Adubo']);

    $this->actingAs($a)->put(route('despesas.categorias.update', $catB), ['nome' => 'Hack', 'ativo' => true])->assertNotFound();
    $this->actingAs($a)->delete(route('despesas.categorias.destroy', $catB))->assertNotFound();
    expect($catB->fresh()->nome)->toBe('Adubo');
});

it('Secagem: NAO aceita dryer_id de outra farm na criação', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $db = Dryer::factory()->forFarm($b->farm)->create();

    $this->actingAs($a)
        ->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $db->id])
        ->assertSessionHasErrors('dryer_id');
});

it('User: admin da farm A NAO altera role/exclui usuário da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $opB = User::factory()->forFarm($b->farm)->create();

    $this->actingAs($a)->put(route('usuarios.role.update', $opB), ['role' => 'visualizador'])->assertForbidden();
    $this->actingAs($a)->delete(route('usuarios.destroy', $opB))->assertForbidden();
    expect(User::withoutGlobalScopes()->whereKey($opB->id)->exists())->toBeTrue();
});

it('Invitation: admin da farm A NAO exclui convite da farm B', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $invB = Invitation::factory()->forFarm($b->farm)->create();

    $this->actingAs($a)->delete(route('convites.destroy', $invB))->assertForbidden();
    expect(Invitation::whereKey($invB->id)->exists())->toBeTrue();
});

it('RegisterMovementAction: defesa em profundidade rejeita cliente de outra farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    $cb = Customer::factory()->forFarm($b->farm)->create(['saldo_coco_kg' => 100]);

    expect(fn () => app(\App\Actions\Movements\RegisterMovementAction::class)->execute(
        owner: $cb,
        user: $a,
        tipo: Movement::TIPO_ENTRADA,
        produto: 'coco',
        quantidade: 10,
    ))->toThrow(\App\Exceptions\DomainException::class, 'fora da fazenda');

    expect(Movement::count())->toBe(0);
    expect((float) $cb->fresh()->saldo_coco_kg)->toBe(100.0);
});

it('Listagens: cada admin só vê dados da própria farm', function () {
    $a = makeFarmUser('admin');
    $b = makeFarmUser('admin');
    Customer::factory()->forFarm($a->farm)->create(['nome' => 'Cliente da A']);
    Customer::factory()->forFarm($b->farm)->create(['nome' => 'Cliente da B']);
    Dryer::factory()->forFarm($a->farm)->create(['nome' => 'Secador da A']);
    Dryer::factory()->forFarm($b->farm)->create(['nome' => 'Secador da B']);
    ExpenseCategory::factory()->forFarm($a->farm)->create(['nome' => 'Categoria da A']);
    ExpenseCategory::factory()->forFarm($b->farm)->create(['nome' => 'Categoria da B']);

    $this->actingAs($a)->get('/clientes')->assertSee('Cliente da A')->assertDontSee('Cliente da B');
    $this->actingAs($a)->get('/secadores')->assertSee('Secador da A')->assertDontSee('Secador da B');
    $this->actingAs($a)->get('/despesas/categorias')->assertSee('Categoria da A')->assertDontSee('Categoria da B');
});
