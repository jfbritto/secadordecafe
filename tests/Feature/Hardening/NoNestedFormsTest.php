<?php

use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Expense;
use App\Models\ExpenseCategory;

/**
 * Forms HTML aninhados são inválidos: o navegador ignora a tag <form> interna,
 * fazendo com que atributos como data-confirm sejam perdidos. Isso causou um
 * bug onde botões de excluir submetiam sem confirmação.
 *
 * Este teste renderiza cada página de edição e verifica via varredura de
 * profundidade de tags <form> que nunca há aninhamento.
 */

function assertNoNestedForms(string $html, string $context): void
{
    $depth = 0;
    $maxDepth = 0;
    $offset = 0;
    while (preg_match('/<\/?form\b[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $tag = $m[0][0];
        $offset = $m[0][1] + strlen($tag);
        if (str_starts_with(strtolower($tag), '</form')) {
            $depth--;
        } else {
            $depth++;
            if ($depth > $maxDepth) $maxDepth = $depth;
        }
    }
    expect($maxDepth)->toBeLessThan(2, "Form aninhado detectado em {$context} (profundidade máxima: {$maxDepth})");
    expect($depth)->toBe(0, "Tags <form> desbalanceadas em {$context}");
}

it('customers/edit não tem forms aninhados', function () {
    $admin = makeFarmUser('admin');
    $c = Customer::factory()->forFarm($admin->farm)->create();

    $html = $this->actingAs($admin)->get("/clientes/{$c->id}/edit")->getContent();
    assertNoNestedForms($html, 'customers/edit');
});

it('dryers/edit não tem forms aninhados', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();

    $html = $this->actingAs($admin)->get("/secadores/{$d->id}/editar")->getContent();
    assertNoNestedForms($html, 'dryers/edit');
});

it('expense-categories/edit não tem forms aninhados', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::query()->where('farm_id', $admin->farm_id)->first();

    $html = $this->actingAs($admin)->get("/despesas/categorias/{$cat->id}/editar")->getContent();
    assertNoNestedForms($html, 'expense-categories/edit');
});

it('expenses/edit não tem forms aninhados', function () {
    $admin = makeFarmUser('admin');
    $cat = ExpenseCategory::query()->where('farm_id', $admin->farm_id)->first();
    $e = Expense::factory()->category($cat)->create();

    $html = $this->actingAs($admin)->get("/despesas/{$e->id}/editar")->getContent();
    assertNoNestedForms($html, 'expenses/edit');
});

it('secagens/edit não tem forms aninhados', function () {
    $admin = makeFarmUser('admin');
    $d = Dryer::factory()->forFarm($admin->farm)->create();
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-07', 'dryer_id' => $d->id]);
    $s = \App\Models\Secagem::first();

    $html = $this->actingAs($admin)->get("/secagens/{$s->id}/editar")->getContent();
    assertNoNestedForms($html, 'secagens/edit');
});
