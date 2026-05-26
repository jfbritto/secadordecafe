<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Talhão da fazenda. NÃO tem estoque próprio: o café colhido vai pro
 * estoque da Farm. A Area existe pra rastrear o histórico de produção
 * (via movements.area_id) e como origem polimórfica de itens de secagem
 * de café próprio.
 */
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

    /**
     * Items de secagem que tiveram essa Area como origem.
     */
    public function secagemItems(): MorphMany
    {
        return $this->morphMany(SecagemItem::class, 'origin');
    }

    /**
     * Histórico de movimentações da fazenda relacionadas a essa Area
     * (colheita, secagem, produção). Mesmo dono é Farm — Area só rotula.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
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
