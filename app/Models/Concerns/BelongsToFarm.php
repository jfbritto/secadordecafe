<?php

namespace App\Models\Concerns;

use App\Models\Farm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;

trait BelongsToFarm
{
    public static function bootBelongsToFarm(): void
    {
        static::creating(function ($model) {
            if ($model->farm_id) {
                return;
            }
            $user = auth()->user();
            if ($user && ! $user->is_root && $user->farm_id) {
                $model->farm_id = $user->farm_id;
            }
        });

        static::addGlobalScope(new FarmScope);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Spatie Activitylog hook — anexa farm_id como property pra scoping.
     */
    public function tapActivity(\Spatie\Activitylog\Contracts\Activity $activity, string $eventName): void
    {
        if (isset($this->farm_id) && $this->farm_id) {
            $activity->properties = $activity->properties->put('farm_id', (int) $this->farm_id);
        }
    }
}

class FarmScope implements Scope
{
    public function apply(Builder $builder, $model): void
    {
        $user = auth()->user();
        if (! $user || $user->is_root) {
            return;
        }
        if (! $user->farm_id) {
            return;
        }
        $builder->where($model->getTable() . '.farm_id', $user->farm_id);
    }
}
