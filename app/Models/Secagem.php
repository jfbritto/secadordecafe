<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Secagem extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_CONCLUIDA = 'concluida';

    protected $table = 'secagens';

    protected $fillable = [
        'farm_id', 'user_id', 'dryer_id', 'numero', 'apelido', 'data',
        'observacoes', 'status', 'concluida_at',
    ];

    protected $casts = [
        'data' => 'date',
        'concluida_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'apelido', 'data', 'dryer_id', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "secagem {$event}");
    }

    public function items(): HasMany { return $this->hasMany(SecagemItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function dryer(): BelongsTo { return $this->belongsTo(Dryer::class); }

    public function secadorNome(): string
    {
        return $this->dryer?->nome ?? '—';
    }

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

    /**
     * Áreas envolvidas nesta secagem (derivado dos items com origin=Area).
     * Substitui o antigo $secagem->area_id (que era single-area).
     */
    public function areasEnvolvidas(): Collection
    {
        return $this->items
            ->filter(fn ($i) => $i->isArea())
            ->map(fn ($i) => $i->origin)
            ->filter()
            ->unique('id')
            ->values();
    }

    public function todosItemsTemSaida(): bool
    {
        return $this->items->isNotEmpty() && $this->items->every(fn ($i) => $i->hasSaida());
    }

    /**
     * Resumo dos participantes (clientes + áreas) pra dar contexto na listagem.
     * Requer items.origin carregado. Ex: "João Almeida, Maria + Área 3".
     */
    public function participantesResumo(int $max = 2): ?string
    {
        if ($this->items->isEmpty()) {
            return null;
        }
        $nomes = $this->items
            ->map(fn ($i) => $i->originLabel())
            ->filter(fn ($n) => $n && $n !== '—')
            ->unique()
            ->values();
        if ($nomes->isEmpty()) {
            return null;
        }
        $mostrados = $nomes->take($max);
        $resto = $nomes->count() - $mostrados->count();
        return $mostrados->implode(', ') . ($resto > 0 ? " +{$resto}" : '');
    }

    /**
     * Texto de referência da secagem pra UI: apelido manual ou, na falta dele,
     * o resumo dos participantes. Null quando não há nenhum (rascunho vazio).
     */
    public function referencia(): ?string
    {
        return $this->apelido ?: $this->participantesResumo();
    }
}
