<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmBlockedController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Aceitação de convite — pública (token-based)
Route::get('convite/{token}', [InvitationAcceptController::class, 'show'])->name('convite.show');
Route::post('convite/{token}', [InvitationAcceptController::class, 'store'])->name('convite.store');

Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::get('farm/blocked', [FarmBlockedController::class, 'show'])->name('farm.blocked');

    Route::middleware('farm.active')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('clientes', CustomerController::class)->parameters(['clientes' => 'cliente']);

        Route::get('fazenda', [FarmController::class, 'edit'])->name('fazenda.edit');
        Route::put('fazenda', [FarmController::class, 'update'])->name('fazenda.update');

        Route::get('usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::put('usuarios/{usuario}/role', [UserController::class, 'updateRole'])->name('usuarios.role.update');
        Route::delete('usuarios/{usuario}', [UserController::class, 'destroy'])->name('usuarios.destroy');

        Route::post('convites', [InvitationController::class, 'store'])->name('convites.store');
        Route::delete('convites/{convite}', [InvitationController::class, 'destroy'])->name('convites.destroy');
    });
});
