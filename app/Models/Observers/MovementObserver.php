<?php

namespace App\Models\Observers;

use App\Http\Controllers\DashboardController;
use App\Models\Movement;
use Illuminate\Support\Facades\Cache;

class MovementObserver
{
    public function created(Movement $movement): void
    {
        Cache::forget(DashboardController::farmMetricsCacheKey($movement->farm_id));
    }

    public function deleted(Movement $movement): void
    {
        Cache::forget(DashboardController::farmMetricsCacheKey($movement->farm_id));
    }
}
