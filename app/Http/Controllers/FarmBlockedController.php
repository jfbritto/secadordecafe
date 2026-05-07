<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmBlockedController extends Controller
{
    public function show(Request $request): View
    {
        return view('farm.blocked', [
            'farm' => $request->user()?->farm,
        ]);
    }
}
