<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Movement extends Model
{
    use HasFactory, BelongsToFarm;

    public const TIPO_ENTRADA = 'entrada';
    public const TIPO_SECAGEM = 'secagem';
    public const TIPO_AJUSTE  = 'ajuste';
    public const TIPO_SAIDA   = 'saida';

    protected $fillable = [
        'farm_id', 'customer_id', 'user_id',
        'tipo', 'quantidade_kg', 'observacao',
        'source_type', 'source_id', 'occurred_at',
    ];

    protected $casts = [
        'quantidade_kg' => 'decimal:3',
        'occurred_at' => 'datetime',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function source(): MorphTo { return $this->morphTo(); }
}
