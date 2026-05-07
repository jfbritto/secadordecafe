<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'farm_id',
        'status',
        'asaas_customer_id',
        'asaas_subscription_id',
        'trial_ends_at',
        'current_period_end',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_end' => 'datetime',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
