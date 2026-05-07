<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    /**
     * Catálogo de unidades de medida sugeridas.
     * `discreta = true` -> só aceita quantidades inteiras (un, cx, pç, pct).
     */
    public const UNIDADES = [
        'L'    => ['label' => 'Litro',          'discreta' => false],
        'mL'   => ['label' => 'Mililitro',      'discreta' => false],
        'kg'   => ['label' => 'Quilograma',     'discreta' => false],
        'g'    => ['label' => 'Grama',          'discreta' => false],
        't'    => ['label' => 'Tonelada',       'discreta' => false],
        'saca' => ['label' => 'Saca (60 kg)',   'discreta' => false],
        'h'    => ['label' => 'Hora',           'discreta' => false],
        'dia'  => ['label' => 'Dia',            'discreta' => false],
        'un'   => ['label' => 'Unidade',        'discreta' => true],
        'pç'   => ['label' => 'Peça',           'discreta' => true],
        'cx'   => ['label' => 'Caixa',          'discreta' => true],
        'pct'  => ['label' => 'Pacote',         'discreta' => true],
        'm'    => ['label' => 'Metro',          'discreta' => false],
        'm²'   => ['label' => 'Metro quadrado', 'discreta' => false],
        'm³'   => ['label' => 'Metro cúbico',   'discreta' => false],
        'km'   => ['label' => 'Quilômetro',     'discreta' => false],
    ];

    public static function unidadesDiscretas(): array
    {
        return array_keys(array_filter(self::UNIDADES, fn ($u) => $u['discreta']));
    }

    public static function unidadeEhDiscreta(?string $unidade): bool
    {
        if (! $unidade) return false;
        return self::UNIDADES[$unidade]['discreta'] ?? false;
    }

    protected $fillable = [
        'farm_id', 'user_id', 'expense_category_id',
        'data', 'descricao',
        'unidade', 'quantidade', 'valor_unitario', 'valor_total', 'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'quantidade' => 'decimal:3',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['data', 'descricao', 'expense_category_id', 'valor_total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "despesa {$event}");
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(ExpenseCategory::class, 'expense_category_id'); }

    public function categoriaNome(): string
    {
        return $this->category?->nome ?? '—';
    }

    public function scopeBetween(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) $q->whereDate('data', '>=', $from);
        if ($to) $q->whereDate('data', '<=', $to);
        return $q;
    }

    public function scopeCategory(Builder $q, ?int $categoryId): Builder
    {
        return $categoryId ? $q->where('expense_category_id', $categoryId) : $q;
    }
}
