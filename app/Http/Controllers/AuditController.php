<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $user->hasRole('admin') && ! $user->isRoot()) {
            abort(403);
        }

        $query = Activity::query()
            ->with('causer:id,name')
            ->orderByDesc('id');

        // Filtra por fazenda quando não-root: properties contém farm_id injetado via tapActivity.
        // LIKE cross-driver (SQLite/MySQL) sobre o JSON serializado.
        if (! $user->isRoot()) {
            $farmId = (int) $user->farm_id;
            $needle = '%"farm_id":' . $farmId . '%';
            $query->where(function ($q) use ($needle, $farmId) {
                $q->where('properties', 'like', $needle)
                  ->orWhere(function ($q2) use ($farmId) {
                      $q2->where('subject_type', \App\Models\Farm::class)
                         ->where('subject_id', $farmId);
                  });
            });
        }

        $activities = $query->paginate(40);

        return view('audit.index', compact('activities'));
    }
}
