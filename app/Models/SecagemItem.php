<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SecagemItem extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['secagem_id', 'customer_id', 'quantidade_recebida_kg', 'quantidade_seca_kg', 'comissao_percentual', 'comissao_kg', 'saldo_liquido_kg'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "item de secagem {$event}");
    }

    protected $fillable = [
        'farm_id', 'secagem_id', 'customer_id',
        'quantidade_recebida_kg', 'quantidade_seca_kg',
        'comissao_percentual', 'comissao_kg', 'saldo_liquido_kg',
    ];

    protected $casts = [
        'quantidade_recebida_kg' => 'decimal:3',
        'quantidade_seca_kg' => 'decimal:3',
        'comissao_percentual' => 'decimal:2',
        'comissao_kg' => 'decimal:3',
        'saldo_liquido_kg' => 'decimal:3',
    ];

    public function secagem(): BelongsTo { return $this->belongsTo(Secagem::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    public function rendimentoPercentual(): float
    {
        $r = (float) $this->quantidade_recebida_kg;
        if ($r <= 0) return 0.0;
        return round(((float) $this->quantidade_seca_kg / $r) * 100, 2);
    }

    public static function calcularComissao(float $seca, float $percentual): float
    {
        return round($seca * $percentual / 100, 3);
    }

    public static function calcularSaldoLiquido(float $seca, float $comissao): float
    {
        return round($seca - $comissao, 3);
    }
}
