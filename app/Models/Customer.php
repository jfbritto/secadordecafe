<?php

namespace App\Models;

use App\Exceptions\DomainException;
use App\Models\Concerns\BelongsToFarm;
use App\Models\Concerns\HasStockMovements;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity, HasStockMovements;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nome', 'telefone', 'cpf_cnpj', 'saldo_coco_kg', 'saldo_seco_kg'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "cliente {$event}");
    }

    protected $fillable = [
        'farm_id',
        'nome',
        'telefone',
        'cpf_cnpj',
        'observacoes',
        'saldo_coco_kg',
        'saldo_seco_kg',
    ];

    protected $casts = [
        'saldo_coco_kg' => 'decimal:2',
        'saldo_seco_kg' => 'decimal:2',
    ];

    public function saldoColumnFor(string $produto): string
    {
        return match ($produto) {
            'coco' => 'saldo_coco_kg',
            'seco' => 'saldo_seco_kg',
            default => throw new DomainException("Produto inválido para Customer: {$produto}"),
        };
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        return $query->where(function ($q) use ($like) {
            $q->where('nome', 'like', $like)
              ->orWhere('telefone', 'like', $like)
              ->orWhere('cpf_cnpj', 'like', $like);
        });
    }
}
