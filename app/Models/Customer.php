<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, BelongsToFarm;

    protected $fillable = [
        'farm_id',
        'nome',
        'telefone',
        'cpf_cnpj',
        'observacoes',
        'saldo_cafe_kg',
    ];

    protected $casts = [
        'saldo_cafe_kg' => 'decimal:3',
    ];

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
