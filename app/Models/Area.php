<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Area extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    protected $table = 'areas';

    protected $fillable = [
        'farm_id',
        'nome',
        'observacoes',
        'latitude',
        'longitude',
        'ativo',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'ativo' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nome', 'observacoes', 'latitude', 'longitude', 'ativo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "area {$event}");
    }

    public function secagens(): HasMany
    {
        return $this->hasMany(Secagem::class);
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
