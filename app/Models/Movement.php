<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use App\Models\Observers\MovementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([MovementObserver::class])]
class Movement extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tipo', 'produto', 'quantidade_kg', 'owner_type', 'owner_id', 'area_id', 'observacao', 'source_type', 'source_id', 'occurred_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "movimentacao {$event}");
    }

    public const TIPO_ENTRADA  = 'entrada';
    public const TIPO_SAIDA    = 'saida';
    public const TIPO_AJUSTE   = 'ajuste';
    public const TIPO_SECAGEM  = 'secagem';
    public const TIPO_COLHEITA = 'colheita';
    public const TIPO_PRODUCAO = 'producao';
    public const TIPO_COMISSAO = 'comissao';

    public const PRODUTO_COCO = 'coco';
    public const PRODUTO_SECO = 'seco';

    protected $fillable = [
        'farm_id', 'owner_type', 'owner_id', 'area_id', 'user_id',
        'tipo', 'produto', 'quantidade_kg', 'observacao',
        'source_type', 'source_id', 'occurred_at',
    ];

    protected $casts = [
        'quantidade_kg' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
