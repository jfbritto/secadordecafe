<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Dryer extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    protected $table = 'dryers';

    protected $fillable = [
        'farm_id',
        'nome',
        'capacidade_kg',
        'modelo',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'capacidade_kg' => 'decimal:3',
        'ativo' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nome', 'capacidade_kg', 'modelo', 'ativo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "secador {$event}");
    }

    public function secagens(): HasMany
    {
        return $this->hasMany(Secagem::class);
    }

    public function scopeAtivo(Builder $q, bool $ativo = true): Builder
    {
        return $q->where('ativo', $ativo);
    }
}
