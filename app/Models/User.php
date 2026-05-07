<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        // password / remember_token NUNCA são logados (sensíveis).
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'farm_id', 'is_root', 'email_verified_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "usuario {$event}");
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if ($this->farm_id) {
            $activity->properties = $activity->properties->put('farm_id', (int) $this->farm_id);
        }
    }

    protected $fillable = [
        'farm_id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'is_root',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_root' => 'boolean',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function isRoot(): bool
    {
        return (bool) $this->is_root;
    }
}
