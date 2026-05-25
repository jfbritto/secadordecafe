<?php

namespace App\Models;

use App\Exceptions\DomainException;
use App\Models\Concerns\BelongsToFarm;
use App\Models\Concerns\HasStockMovements;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Area extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity, HasStockMovements;

    protected $table = 'areas';

    protected $fillable = [
        'farm_id',
        'nome',
        'observacoes',
        'latitude',
        'longitude',
        'ativo',
        'saldo_coco_kg',
        'saldo_seco_kg',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'ativo' => 'boolean',
        'saldo_coco_kg' => 'decimal:2',
        'saldo_seco_kg' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nome', 'observacoes', 'latitude', 'longitude', 'ativo', 'saldo_coco_kg', 'saldo_seco_kg'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "area {$event}");
    }

    public function saldoColumnFor(string $produto): string
    {
        return match ($produto) {
            'coco' => 'saldo_coco_kg',
            'seco' => 'saldo_seco_kg',
            default => throw new DomainException("Produto inválido para Area: {$produto}"),
        };
    }

    public function secagemItems(): MorphMany
    {
        return $this->morphMany(SecagemItem::class, 'origin');
    }

    public function scopeAtivo(Builder $q, bool $ativo = true): Builder
    {
        return $q->where('ativo', $ativo);
    }

    public function hasLocation(): bool
    {
        return ! is_null($this->latitude) && ! is_null($this->longitude);
    }
}
