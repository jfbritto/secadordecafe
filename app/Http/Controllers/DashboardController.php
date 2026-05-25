<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public const FARM_METRICS_TTL = 60;

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isRoot()) {
            return view('dashboard.root', $this->rootMetrics());
        }

        return view('dashboard.farm', $this->farmMetrics($user));
    }

    public static function farmMetricsCacheKey(int $farmId): string
    {
        return "dashboard:farm:{$farmId}:metrics";
    }

    private function farmMetrics($user): array
    {
        $farm = $user->farm;

        $metrics = Cache::remember(
            self::farmMetricsCacheKey($user->farm_id),
            self::FARM_METRICS_TTL,
            fn () => $this->computeFarmMetrics($user->farm_id),
        );

        // Listas limitadas (limit 5-8 com índice em farm_id+ordem) são rápidas, fora do cache
        // pra refletir mudanças imediatas que o usuário acabou de fazer.
        $ultimasMovs = Movement::where('farm_id', $user->farm_id)
            ->with('owner', 'user:id,name')
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();

        $topClientes = Customer::where('farm_id', $user->farm_id)
            ->orderByDesc(DB::raw('saldo_coco_kg + saldo_seco_kg'))
            ->limit(5)
            ->get(['id', 'nome', 'saldo_coco_kg', 'saldo_seco_kg']);

        return [
            'farm' => $farm,
            'user' => $user,
            'metrics' => $metrics,
            'ultimasMovs' => $ultimasMovs,
            'topClientes' => $topClientes,
        ];
    }

    private function computeFarmMetrics(int $farmId): array
    {
        $startMonth = now()->startOfMonth();
        $startMonthDate = $startMonth->toDateString();

        $custAgg = DB::table('customers')
            ->where('farm_id', $farmId)
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(saldo_coco_kg), 0) as saldo_coco,
                COALESCE(SUM(saldo_seco_kg), 0) as saldo_seco
            ')
            ->first();

        $areaAgg = DB::table('areas')
            ->where('farm_id', $farmId)
            ->selectRaw('
                COALESCE(SUM(saldo_coco_kg), 0) as saldo_coco,
                COALESCE(SUM(saldo_seco_kg), 0) as saldo_seco
            ')
            ->first();

        $farmRow = DB::table('farms')->where('id', $farmId)->first();
        $saldoComissao = (float) ($farmRow->saldo_seco_comissao_kg ?? 0);

        $secAgg = DB::table('secagens')
            ->where('farm_id', $farmId)
            ->where('data', '>=', $startMonthDate)
            ->selectRaw(
                'COUNT(*) as total, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as concluidas',
                [Secagem::STATUS_CONCLUIDA]
            )
            ->first();

        $despesasMes = (float) DB::table('expenses')
            ->where('farm_id', $farmId)
            ->whereDate('data', '>=', $startMonthDate)
            ->sum('valor_total');

        $kgSecadosMes = (float) DB::table('movements')
            ->where('farm_id', $farmId)
            ->where('tipo', Movement::TIPO_SECAGEM)
            ->where('produto', Movement::PRODUTO_COCO)
            ->where('occurred_at', '>=', $startMonth)
            ->sum('quantidade_kg');

        return [
            'clientes' => (int) $custAgg->total,
            'saldoCocoClientesKg' => (float) $custAgg->saldo_coco,
            'saldoSecoClientesKg' => (float) $custAgg->saldo_seco,
            'saldoCocoAreasKg' => (float) $areaAgg->saldo_coco,
            'saldoSecoAreasKg' => (float) $areaAgg->saldo_seco,
            'saldoSecoComissaoKg' => $saldoComissao,
            'secagensMes' => (int) $secAgg->total,
            'secagensConcluidasMes' => (int) $secAgg->concluidas,
            'despesasMes' => $despesasMes,
            'kgSecadosMes' => abs($kgSecadosMes),
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
            'farmsPartner' => Farm::where('status', Farm::STATUS_PARTNER)->count(),
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
