<?php

use App\Models\Area;
use App\Models\Customer;
use App\Models\Dryer;
use App\Models\ExpenseCategory;
use App\Models\Secagem;
use App\Models\SecagemItem;

/**
 * Smoke test: visita TODAS as telas GET autenticadas com dados reais e garante
 * que nenhuma estoura 500. Pega views quebradas (campo renomeado, relação que
 * sumiu) antes do deploy — exatamente o tipo de regressão que apareceu na
 * reestruturação do estoque.
 */
it('todas as telas GET principais abrem sem erro pra um admin com dados', function () {
    $admin = makeFarmUser('admin');
    $farm = $admin->farm;

    // Popular dados em cada entidade pra exercitar os loops das views
    $cliente = Customer::factory()->forFarm($farm)->create(['saldo_coco_kg' => 500, 'saldo_seco_kg' => 100]);
    $area = Area::factory()->forFarm($farm)->create();
    $dryer = Dryer::factory()->forFarm($farm)->create();
    ExpenseCategory::seedDefaultsForFarm($farm->id);

    // Colheita → estoque próprio + rastreio de área
    $this->actingAs($admin)->post('/colheitas', ['area_id' => $area->id, 'quantidade_kg' => 400]);

    // Secagem mista concluída (cliente + área) pra ter histórico em todas as telas
    $this->actingAs($admin)->post('/secagens', ['data' => '2026-05-20', 'dryer_id' => $dryer->id]);
    $s = Secagem::first();
    foreach ([['cliente', $cliente->id, 200], ['area', $area->id, 300]] as [$tipo, $id, $rec]) {
        $this->actingAs($admin)->post("/secagens/{$s->id}/items", [
            'origin_type' => $tipo, 'origin_id' => $id, 'quantidade_recebida_kg' => $rec,
        ]);
        $item = SecagemItem::orderByDesc('id')->first();
        $this->actingAs($admin)->patch("/secagens/{$s->id}/items/{$item->id}/saida", [
            'quantidade_seca_kg' => $rec * 0.6, 'comissao_percentual' => 10,
        ]);
    }
    $this->actingAs($admin)->post("/secagens/{$s->id}/concluir");

    // Despesa pra povoar listagem financeira
    $cat = ExpenseCategory::where('farm_id', $farm->id)->first();
    $this->actingAs($admin)->post('/despesas', [
        'data' => '2026-05-20', 'descricao' => 'Diesel', 'valor_total' => 350, 'expense_category_id' => $cat?->id,
    ]);

    $rotas = [
        '/dashboard',
        '/clientes', "/clientes/criar", "/clientes/{$cliente->id}", "/clientes/{$cliente->id}/editar",
        "/movimentacoes/cliente/{$cliente->id}",
        "/movimentacoes/area/{$area->id}",
        '/movimentacoes/fazenda',
        '/secagens', '/secagens/criar', "/secagens/{$s->id}", "/secagens/{$s->id}/editar",
        '/areas', '/areas/criar', "/areas/{$area->id}", "/areas/{$area->id}/editar",
        '/colheitas/criar',
        '/secadores', '/secadores/criar', "/secadores/{$dryer->id}/editar",
        '/despesas', '/despesas/criar', '/despesas/categorias', '/despesas/categorias/criar',
        '/fazenda', '/perfil', '/assinatura', '/auditoria', '/usuarios', '/usuarios/criar',
    ];

    foreach ($rotas as $rota) {
        $resp = $this->actingAs($admin)->get($rota);
        expect($resp->status())->toBeLessThan(500, "Rota {$rota} retornou {$resp->status()}");
    }
});

it('telas GET do root (painel da plataforma) abrem sem erro', function () {
    $root = makeFarmUser('admin', ['is_root' => true]);

    foreach (['/dashboard', '/admin/fazendas'] as $rota) {
        $resp = $this->actingAs($root)->get($rota);
        expect($resp->status())->toBeLessThan(500, "Rota {$rota} retornou {$resp->status()}");
    }
});
