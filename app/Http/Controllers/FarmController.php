<?php

namespace App\Http\Controllers;

use App\Http\Requests\Farms\UpdateFarmRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmController extends Controller
{
    public function edit(Request $request): View
    {
        $farm = $request->user()->farm;
        $this->authorize('update', $farm);
        return view('farms.edit', compact('farm'));
    }

    public function update(UpdateFarmRequest $request): RedirectResponse
    {
        $farm = $request->user()->farm;
        $farm->update($request->validated());

        return redirect()->route('fazenda.edit')
            ->with('flash', '<strong>' . e($farm->nome) . '</strong> atualizada.');
    }
}
