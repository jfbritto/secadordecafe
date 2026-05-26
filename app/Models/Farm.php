<?php

namespace App\Models;

use App\Exceptions\DomainException;
use App\Models\Concerns\HasStockMovements;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Farm extends Model
{
    use HasFactory, LogsActivity, HasStockMovements;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nome', 'status', 'cidade', 'estado', 'telefone', 'saldo_coco_kg', 'saldo_seco_kg'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "fazenda {$event}");
    }

    /**
     * Farm é o único dono do estoque próprio do produtor (côco + seco).
     * Inclui produção própria (colheita das áreas) e comissões recebidas
     * de serviços de secagem pra clientes externos.
     */
    public function saldoColumnFor(string $produto): string
    {
        return match ($produto) {
            'coco' => 'saldo_coco_kg',
            'seco' => 'saldo_seco_kg',
            default => throw new DomainException("Produto inválido para Farm: {$produto}"),
        };
    }

    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_BLOCKED = 'blocked';
    /** Cortesia concedida pelo root — sem cobrança, sem expiração. */
    public const STATUS_PARTNER = 'partner';

    protected $fillable = [
        'nome',
        'slug',
        'status',
        'telefone',
        'cidade',
        'estado',
        'trial_ends_at',
        'saldo_coco_kg',
        'saldo_seco_kg',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'saldo_coco_kg' => 'decimal:2',
        'saldo_seco_kg' => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function isOnTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    protected static function booted(): void
    {
        static::created(function (Farm $farm) {
            ExpenseCategory::seedDefaultsForFarm($farm->id);
        });
    }
}
