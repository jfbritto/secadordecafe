<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $farm = $user->farm;

        return view('dashboard', [
            'user' => $user,
            'farm' => $farm,
            'isRoot' => $user->isRoot(),
            'metrics' => [
                'clientes' => 0,
                'saldoCafeKg' => 0,
                'secagensMes' => 0,
                'despesasMes' => 0,
            ],
        ]);
    }
}
