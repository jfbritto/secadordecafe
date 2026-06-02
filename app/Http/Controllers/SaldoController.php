<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Farm;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Visões agregadas de saldo. Servem aos cards clicáveis do dashboard:
 *  - porCliente: lista de clientes com saldo > 0 (filtrado por produto)
 *  - geral: o mesmo + linha "Fazenda" no topo, pra ver tudo num lugar só
 */
class SaldoController extends Controller
{
    public function porCliente(Request $request): View
    {
        return $this->render($request, incluiFazenda: false);
    }

    public function geral(Request $request): View
    {
        return $this->render($request, incluiFazenda: true);
    }

    private function render(Request $request, bool $incluiFazenda): View
    {
        $produto = $request->string('produto')->toString();
        if (! in_array($produto, [Movement::PRODUTO_COCO, Movement::PRODUTO_SECO], true)) {
            $produto = Movement::PRODUTO_COCO;
        }
        $coluna = $produto === Movement::PRODUTO_COCO ? 'saldo_coco_kg' : 'saldo_seco_kg';

        $user = $request->user();

        $clientes = Customer::query()
            ->where('farm_id', $user->farm_id)
            ->where($coluna, '>', 0)
            ->orderByDesc($coluna)
            ->get(['id', 'nome', 'saldo_coco_kg', 'saldo_seco_kg']);

        $fazenda = null;
        if ($incluiFazenda) {
            $fazenda = Farm::query()->where('id', $user->farm_id)->first(['id', 'nome', 'saldo_coco_kg', 'saldo_seco_kg']);
        }

        $totalClientes = (float) $clientes->sum($coluna);
        $totalFazenda = $fazenda ? (float) $fazenda->{$coluna} : 0.0;
        $totalGeral = $totalClientes + $totalFazenda;

        return view('saldos.index', [
            'produto' => $produto,
            'coluna' => $coluna,
            'clientes' => $clientes,
            'fazenda' => $fazenda,
            'incluiFazenda' => $incluiFazenda,
            'totalClientes' => $totalClientes,
            'totalFazenda' => $totalFazenda,
            'totalGeral' => $totalGeral,
        ]);
    }
}
