<?php

namespace App\Http\Controllers;

use App\Actions\Secagens\ConcludeSecagemAction;
use App\Actions\Secagens\CreateSecagemAction;
use App\Actions\Secagens\ReopenSecagemAction;
use App\Http\Requests\Secagens\RegisterSaidaItemRequest;
use App\Http\Requests\Secagens\StoreSecagemItemRequest;
use App\Http\Requests\Secagens\StoreSecagemRequest;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Secagem;
use App\Models\SecagemItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SecagemController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Secagem::class);

        $secagens = Secagem::query()
            ->with('user', 'dryer', 'items.origin')
            ->withCount('items')
            ->orderByDesc('data')
            ->orderByDesc('numero')
            ->paginate(20);

        return view('secagens.index', compact('secagens'));
    }

    public function create(): View
    {
        $this->authorize('create', Secagem::class);
        $dryers = Dryer::ativo()->orderBy('nome')->get(['id', 'nome']);

        if ($dryers->isEmpty()) {
            return view('secagens.no-dryer');
        }

        return view('secagens.create', compact('dryers'));
    }

    public function store(StoreSecagemRequest $request, CreateSecagemAction $action): RedirectResponse
    {
        $secagem = $action->execute($request->user(), $request->validated());
        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', '<strong>Secagem #' . $secagem->numero . '</strong> criada como rascunho.');
    }

    public function show(Secagem $secagem): View
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('view', $secagem);
        $secagem->load('items.origin', 'user', 'dryer');

        return view('secagens.show', compact('secagem'));
    }

    public function edit(Secagem $secagem): View
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);
        $secagem->load('items.origin', 'dryer');

        $customers = Customer::orderBy('nome')->get(['id', 'nome', 'saldo_coco_kg', 'saldo_seco_kg']);
        $dryers = Dryer::ativo()->orderBy('nome')->get(['id', 'nome']);
        if ($secagem->dryer && ! $secagem->dryer->ativo) {
            $dryers->push($secagem->dryer->only(['id', 'nome']));
        }
        $areas = Area::ativo()->orderBy('nome')->get(['id', 'nome']);
        // Estoque de côco da fazenda — é o disponível pra secar café próprio (de qualquer área).
        $saldoCocoFazenda = (float) \App\Models\Farm::whereKey($secagem->farm_id)->value('saldo_coco_kg');

        return view('secagens.edit', compact('secagem', 'customers', 'dryers', 'areas', 'saldoCocoFazenda'));
    }

    public function update(StoreSecagemRequest $request, Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);

        $secagem->update($request->validated());
        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', '<strong>Secagem #' . $secagem->numero . '</strong> atualizada.');
    }

    public function destroy(Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('delete', $secagem);
        $numero = $secagem->numero;
        $secagem->delete();

        return redirect()->route('secagens.index')
            ->with('flash', '<strong>Secagem #' . $numero . '</strong> excluída.');
    }

    public function storeItem(StoreSecagemItemRequest $request, Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);

        $data = $request->validated();
        $originClass = $request->resolvedOriginClass();

        SecagemItem::create([
            'farm_id' => $secagem->farm_id,
            'secagem_id' => $secagem->id,
            'origin_type' => $originClass,
            'origin_id' => $data['origin_id'],
            'quantidade_recebida_kg' => $data['quantidade_recebida_kg'],
            // Saída fica nula até o sogro registrar (PATCH /items/{i}/saida)
            'quantidade_seca_kg' => null,
            'comissao_percentual' => null,
            'comissao_kg' => null,
            'saldo_liquido_kg' => null,
        ]);

        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', 'Item adicionado à secagem. Registre a saída quando o café sair do secador.');
    }

    public function registerSaida(RegisterSaidaItemRequest $request, Secagem $secagem, SecagemItem $item): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);

        if ($item->secagem_id !== $secagem->id) {
            throw new AuthorizationException();
        }

        $data = $request->validated();
        $seca = (float) $data['quantidade_seca_kg'];
        // Comissão só faz sentido pra origem Customer; pra Area ignora
        $perc = $item->isCustomer() ? (float) ($data['comissao_percentual'] ?? 0) : 0;
        $comissao = SecagemItem::calcularComissao($seca, $perc);
        $liquido = SecagemItem::calcularSaldoLiquido($seca, $comissao);

        $item->update([
            'quantidade_seca_kg' => $seca,
            'comissao_percentual' => $perc,
            'comissao_kg' => $comissao,
            'saldo_liquido_kg' => $liquido,
        ]);

        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', 'Saída registrada. Conclua a secagem quando todos os itens tiverem saída.');
    }

    /**
     * Registra a saída de vários itens de uma vez (a secagem sai junta do secador).
     * Grava só os itens que vieram com quantidade seca preenchida — permite registro
     * parcial. Valida tudo antes de gravar; nenhum reload entre itens, então o valor
     * distribuído nos outros não se perde.
     */
    public function registrarSaidas(Request $request, Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);

        $secagem->load('items.origin');
        $entrada = (array) $request->input('itens', []);

        $validator = Validator::make([], []);
        $aGravar = [];

        foreach ($secagem->items as $item) {
            if ($item->hasSaida()) {
                continue;
            }
            $dados = $entrada[$item->id] ?? null;
            $secaRaw = $dados['quantidade_seca_kg'] ?? null;
            if ($secaRaw === null || $secaRaw === '') {
                continue; // item não preenchido — registro parcial
            }

            $seca = (float) $secaRaw;
            $recebida = (float) $item->quantidade_recebida_kg;

            if ($seca <= 0) {
                $validator->errors()->add("itens.{$item->id}.quantidade_seca_kg", "Informe um valor maior que zero para {$item->originLabel()}.");
                continue;
            }
            if ($seca > $recebida) {
                $validator->errors()->add(
                    "itens.{$item->id}.quantidade_seca_kg",
                    "O café seco de {$item->originLabel()} (" . number_format($seca, 2, ',', '.') . ' kg) não pode passar do café côco recebido ('
                    . number_format($recebida, 2, ',', '.') . ' kg).'
                );
                continue;
            }

            $perc = $item->isCustomer() ? (float) ($dados['comissao_percentual'] ?? 0) : 0;
            $aGravar[] = ['item' => $item, 'seca' => $seca, 'perc' => $perc];
        }

        if ($validator->errors()->isNotEmpty()) {
            return back()->withErrors($validator)->withInput();
        }
        if (empty($aGravar)) {
            return back()->with('error', 'Informe a quantidade seca de ao menos um item.');
        }

        DB::transaction(function () use ($aGravar) {
            foreach ($aGravar as $g) {
                $comissao = SecagemItem::calcularComissao($g['seca'], $g['perc']);
                $g['item']->update([
                    'quantidade_seca_kg' => $g['seca'],
                    'comissao_percentual' => $g['perc'],
                    'comissao_kg' => $comissao,
                    'saldo_liquido_kg' => SecagemItem::calcularSaldoLiquido($g['seca'], $comissao),
                ]);
            }
        });

        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', count($aGravar) . ' saída(s) registrada(s). Confira e conclua a secagem.');
    }

    public function destroyItem(Secagem $secagem, SecagemItem $item): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);
        if ($item->secagem_id !== $secagem->id) {
            throw new AuthorizationException();
        }
        $item->delete();
        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', 'Item removido da secagem.');
    }

    public function pdf(Secagem $secagem): Response
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('view', $secagem);
        $secagem->load('items.origin', 'farm', 'dryer');

        $pdf = Pdf::loadView('secagens.pdf', compact('secagem'))->setPaper('a4', 'landscape');
        return $pdf->download("secagem-{$secagem->numero}.pdf");
    }

    public function conclude(Secagem $secagem, ConcludeSecagemAction $action): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('conclude', $secagem);

        $action->execute($secagem, auth()->user());

        return redirect()->route('secagens.show', $secagem)
            ->with('flash', '<strong>Secagem #' . $secagem->numero . '</strong> concluída. Saldos atualizados.');
    }

    public function reopen(Secagem $secagem, ReopenSecagemAction $action): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('reopen', $secagem);

        $action->execute($secagem, auth()->user());

        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', '<strong>Secagem #' . $secagem->numero . '</strong> reaberta. Movimentações estornadas, corrija e conclua de novo.');
    }

    private function ensureSameFarm(Secagem $secagem): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $secagem->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
