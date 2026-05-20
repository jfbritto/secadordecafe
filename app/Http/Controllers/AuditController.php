<?php

namespace App\Http\Controllers;

use App\Support\AuditFormatter;
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
        // whereJsonContains é cross-driver e imune a diferenças de serialização JSON
        // (MySQL serializa com espaços, SQLite sem — o LIKE textual antigo falhava em MySQL).
        if (! $user->isRoot()) {
            $farmId = (int) $user->farm_id;
            $query->where(function ($q) use ($farmId) {
                $q->whereJsonContains('properties->farm_id', $farmId)
                  ->orWhere(function ($q2) use ($farmId) {
                      $q2->where('subject_type', \App\Models\Farm::class)
                         ->where('subject_id', $farmId);
                  });
            });
        }

        $activities = $query->paginate(40);

        // Anexa estrutura humana e diff serializado pra cada item
        $activities->getCollection()->transform(function (Activity $a) {
            $a->humanized = AuditFormatter::describe($a);
            $a->diff_data = $a->humanized['has_diff'] ? AuditFormatter::diff($a) : [];
            return $a;
        });

        return view('audit.index', compact('activities'));
    }
}
