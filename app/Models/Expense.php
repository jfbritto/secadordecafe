<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory, BelongsToFarm;

    public const CATEGORIAS = [
        'combustivel' => 'Combustível',
        'manutencao' => 'Manutenção',
        'mao_de_obra' => 'Mão de obra',
        'impostos' => 'Impostos',
        'equipamentos' => 'Equipamentos',
        'outros' => 'Outros',
    ];

    protected $fillable = [
        'farm_id', 'user_id', 'data', 'descricao', 'categoria',
        'unidade', 'quantidade', 'valor_unitario', 'valor_total', 'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'quantidade' => 'decimal:3',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeBetween(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) $q->whereDate('data', '>=', $from);
        if ($to) $q->whereDate('data', '<=', $to);
        return $q;
    }

    public function scopeCategoria(Builder $q, ?string $cat): Builder
    {
        return $cat ? $q->where('categoria', $cat) : $q;
    }
}
