<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invitation extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        // token NUNCA é logado (sensível — quem tem o token aceita o convite).
        return LogOptions::defaults()
            ->logOnly(['email', 'role', 'expires_at', 'accepted_at', 'invited_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "convite {$event}");
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if ($this->farm_id) {
            $activity->properties = $activity->properties->put('farm_id', (int) $this->farm_id);
        }
    }

    protected $fillable = [
        'farm_id',
        'email',
        'role',
        'token',
        'expires_at',
        'accepted_at',
        'invited_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isAccepted(): bool
    {
        return ! is_null($this->accepted_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }
}
