<?php

namespace App\Http\Controllers;

use App\Actions\Colheitas\RegisterColheitaAction;
use App\Http\Requests\Colheitas\StoreColheitaRequest;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ColheitaController extends Controller
{
    public function create(): View
    {
        $areas = Area::ativo()->orderBy('nome')->get(['id', 'nome']);
        return view('colheitas.create', compact('areas'));
    }

    public function store(StoreColheitaRequest $request, RegisterColheitaAction $action): RedirectResponse
    {
        $area = Area::query()->findOrFail($request->validated('area_id'));

        $action->execute(
            area: $area,
            user: $request->user(),
            quantidadeKg: (float) $request->validated('quantidade_kg'),
            observacao: $request->validated('observacao'),
            occurredAt: $request->validated('occurred_at')
                ? new \DateTime($request->validated('occurred_at'))
                : null,
        );

        return redirect()->route('areas.show', $area)
            ->with('flash', 'Colheita registrada. Saldo de café côco atualizado em <strong>' . e($area->nome) . '</strong>.');
    }
}
