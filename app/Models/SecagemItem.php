<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SecagemItem extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['secagem_id', 'origin_type', 'origin_id', 'quantidade_recebida_kg', 'quantidade_seca_kg', 'comissao_percentual', 'comissao_kg', 'saldo_liquido_kg'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "item de secagem {$event}");
    }

    protected $fillable = [
        'farm_id', 'secagem_id', 'origin_type', 'origin_id',
        'quantidade_recebida_kg', 'quantidade_seca_kg',
        'comissao_percentual', 'comissao_kg', 'saldo_liquido_kg',
    ];

    protected $casts = [
        'quantidade_recebida_kg' => 'decimal:2',
        'quantidade_seca_kg' => 'decimal:2',
        'comissao_percentual' => 'decimal:2',
        'comissao_kg' => 'decimal:2',
        'saldo_liquido_kg' => 'decimal:2',
    ];

    public function secagem(): BelongsTo
    {
        return $this->belongsTo(Secagem::class);
    }

    public function origin(): MorphTo
    {
        return $this->morphTo();
    }

    public function isCustomer(): bool
    {
        return $this->origin_type === Customer::class;
    }

    public function isArea(): bool
    {
        return $this->origin_type === Area::class;
    }

    public function hasSaida(): bool
    {
        return $this->quantidade_seca_kg !== null;
    }

    public function originLabel(): string
    {
        return $this->origin?->nome ?? '—';
    }

    public function rendimentoPercentual(): float
    {
        $r = (float) $this->quantidade_recebida_kg;
        if ($r <= 0 || ! $this->hasSaida()) return 0.0;
        return round(((float) $this->quantidade_seca_kg / $r) * 100, 2);
    }

    /**
     * Proporção "X sacos de côco rendem 1 saco de seco" — métrica preferida do produtor.
     * Ex: 900kg côco / 220kg seco = 4,09 (4,09 sc côco → 1 sc seco).
     */
    public function proporcaoCocoSeco(): float
    {
        $s = (float) $this->quantidade_seca_kg;
        if ($s <= 0 || ! $this->hasSaida()) return 0.0;
        return round((float) $this->quantidade_recebida_kg / $s, 2);
    }

    public static function calcularComissao(float $seca, float $percentual): float
    {
        return round($seca * $percentual / 100, 2);
    }

    public static function calcularSaldoLiquido(float $seca, float $comissao): float
    {
        return round($seca - $comissao, 2);
    }
}
