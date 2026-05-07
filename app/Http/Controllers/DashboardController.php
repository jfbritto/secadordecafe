<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isRoot()) {
            return view('dashboard.root', $this->rootMetrics());
        }

        return view('dashboard.farm', $this->farmMetrics($user));
    }

    private function farmMetrics($user): array
    {
        $farm = $user->farm;
        $startMonth = now()->startOfMonth();

        $totalClientes = Customer::where('farm_id', $user->farm_id)->count();
        $saldoTotal = (float) Customer::where('farm_id', $user->farm_id)->sum('saldo_cafe_kg');

        $secagensMes = Secagem::where('farm_id', $user->farm_id)
            ->where('data', '>=', $startMonth->toDateString())
            ->count();
        $secagensConcluidasMes = Secagem::where('farm_id', $user->farm_id)
            ->where('status', Secagem::STATUS_CONCLUIDA)
            ->where('data', '>=', $startMonth->toDateString())
            ->count();

        $despesasMes = (float) Expense::where('farm_id', $user->farm_id)
            ->whereDate('data', '>=', $startMonth->toDateString())
            ->sum('valor_total');

        $kgSecadosMes = (float) Movement::where('farm_id', $user->farm_id)
            ->where('tipo', Movement::TIPO_SECAGEM)
            ->where('occurred_at', '>=', $startMonth)
            ->sum('quantidade_kg'); // negativo

        $ultimasMovs = Movement::where('farm_id', $user->farm_id)
            ->with('customer:id,nome', 'user:id,name')
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();

        $topClientes = Customer::where('farm_id', $user->farm_id)
            ->orderByDesc('saldo_cafe_kg')
            ->limit(5)
            ->get(['id', 'nome', 'saldo_cafe_kg']);

        return [
            'farm' => $farm,
            'user' => $user,
            'metrics' => [
                'clientes' => $totalClientes,
                'saldoCafeKg' => $saldoTotal,
                'secagensMes' => $secagensMes,
                'secagensConcluidasMes' => $secagensConcluidasMes,
                'despesasMes' => $despesasMes,
                'kgSecadosMes' => abs($kgSecadosMes),
            ],
            'ultimasMovs' => $ultimasMovs,
            'topClientes' => $topClientes,
        ];
    }

    private function rootMetrics(): array
    {
        return [
            'totalFarms' => Farm::count(),
            'farmsActive' => Farm::where('status', Farm::STATUS_ACTIVE)->count(),
            'farmsTrial' => Farm::where('status', Farm::STATUS_TRIAL)->count(),
            'farmsPastDue' => Farm::where('status', Farm::STATUS_PAST_DUE)->count(),
            'farmsBlocked' => Farm::where('status', Farm::STATUS_BLOCKED)->count(),
            'totalUsers' => User::where('is_root', false)->count(),
            'newFarms30d' => Farm::where('created_at', '>=', now()->subDays(30))->count(),
            'subscriptions' => Subscription::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'recentFarms' => Farm::orderByDesc('created_at')->limit(10)->get(),
        ];
    }
}
