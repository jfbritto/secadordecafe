<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExpenseCategory extends Model
{
    use HasFactory, BelongsToFarm, LogsActivity;

    protected $table = 'expense_categories';

    public const DEFAULTS = [
        'Combustível', 'Manutenção', 'Mão de obra',
        'Impostos', 'Equipamentos', 'Compra de café', 'Outros',
    ];

    /**
     * Nome da categoria reservada que recebe as compras de café feitas pelo dono
     * da fazenda. Lançamentos pela tela /compras criam Expense aqui.
     */
    public const COMPRA_CAFE = 'Compra de café';

    protected $fillable = ['farm_id', 'nome', 'ativo', 'observacoes'];

    protected $casts = ['ativo' => 'boolean'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nome', 'ativo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "categoria-despesa {$event}");
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeAtivo(Builder $q, bool $ativo = true): Builder
    {
        return $q->where('ativo', $ativo);
    }

    public static function seedDefaultsForFarm(int $farmId): void
    {
        foreach (self::DEFAULTS as $nome) {
            self::query()->withoutGlobalScopes()->firstOrCreate(
                ['farm_id' => $farmId, 'nome' => $nome],
                ['ativo' => true]
            );
        }
    }
}
