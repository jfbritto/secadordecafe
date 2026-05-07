<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Secagem extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_CONCLUIDA = 'concluida';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'data', 'secador', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "secagem {$event}");
    }

    protected $table = 'secagens';

    protected $fillable = [
        'farm_id', 'user_id', 'numero', 'data', 'secador',
        'observacoes', 'status', 'concluida_at',
    ];

    protected $casts = [
        'data' => 'date',
        'concluida_at' => 'datetime',
    ];

    public function items(): HasMany { return $this->hasMany(SecagemItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function isRascunho(): bool { return $this->status === self::STATUS_RASCUNHO; }
    public function isConcluida(): bool { return $this->status === self::STATUS_CONCLUIDA; }

    public function totalRecebidoKg(): float
    {
        return (float) $this->items->sum('quantidade_recebida_kg');
    }
    public function totalSecoKg(): float
    {
        return (float) $this->items->sum('quantidade_seca_kg');
    }
    public function totalComissaoKg(): float
    {
        return (float) $this->items->sum('comissao_kg');
    }
}
