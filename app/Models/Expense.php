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
