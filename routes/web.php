<?php

use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmBlockedController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\SecagemController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:5,10');

    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Aceitação de convite — pública (token-based)
Route::get('convite/{token}', [InvitationAcceptController::class, 'show'])->name('convite.show');
Route::post('convite/{token}', [InvitationAcceptController::class, 'store'])->name('convite.store');

// Webhook Asaas — público, autenticado por token de header
Route::post('webhooks/asaas', AsaasWebhookController::class)->name('webhooks.asaas');

Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::get('farm/blocked', [FarmBlockedController::class, 'show'])->name('farm.blocked');

    Route::middleware('farm.active')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('clientes', CustomerController::class)->parameters(['clientes' => 'cliente']);

        Route::get('clientes/{cliente}/movimentacoes', [MovementController::class, 'index'])
            ->name('clientes.movimentacoes.index');
        Route::post('clientes/{cliente}/movimentacoes', [MovementController::class, 'store'])
            ->name('clientes.movimentacoes.store');

        Route::get('secagens', [SecagemController::class, 'index'])->name('secagens.index');
        Route::get('secagens/criar', [SecagemController::class, 'create'])->name('secagens.create');
        Route::post('secagens', [SecagemController::class, 'store'])->name('secagens.store');
        Route::get('secagens/{secagem}', [SecagemController::class, 'show'])->name('secagens.show');
        Route::get('secagens/{secagem}/editar', [SecagemController::class, 'edit'])->name('secagens.edit');
        Route::put('secagens/{secagem}', [SecagemController::class, 'update'])->name('secagens.update');
        Route::delete('secagens/{secagem}', [SecagemController::class, 'destroy'])->name('secagens.destroy');
        Route::post('secagens/{secagem}/items', [SecagemController::class, 'storeItem'])->name('secagens.items.store');
        Route::delete('secagens/{secagem}/items/{item}', [SecagemController::class, 'destroyItem'])->name('secagens.items.destroy');
        Route::post('secagens/{secagem}/concluir', [SecagemController::class, 'conclude'])->name('secagens.conclude');
        Route::get('secagens/{secagem}/pdf', [SecagemController::class, 'pdf'])->name('secagens.pdf');

        Route::get('despesas', [ExpenseController::class, 'index'])->name('despesas.index');
        Route::get('despesas/criar', [ExpenseController::class, 'create'])->name('despesas.create');
        Route::post('despesas', [ExpenseController::class, 'store'])->name('despesas.store');
        Route::get('despesas/{despesa}/editar', [ExpenseController::class, 'edit'])->name('despesas.edit');
        Route::put('despesas/{despesa}', [ExpenseController::class, 'update'])->name('despesas.update');
        Route::delete('despesas/{despesa}', [ExpenseController::class, 'destroy'])->name('despesas.destroy');

        Route::get('fazenda', [FarmController::class, 'edit'])->name('fazenda.edit');
        Route::put('fazenda', [FarmController::class, 'update'])->name('fazenda.update');

        Route::get('assinatura', [BillingController::class, 'show'])->name('assinatura.show');

        Route::get('auditoria', [AuditController::class, 'index'])->name('auditoria.index');

        Route::get('usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::put('usuarios/{usuario}/role', [UserController::class, 'updateRole'])->name('usuarios.role.update');
        Route::delete('usuarios/{usuario}', [UserController::class, 'destroy'])->name('usuarios.destroy');

        Route::post('convites', [InvitationController::class, 'store'])->name('convites.store');
        Route::delete('convites/{convite}', [InvitationController::class, 'destroy'])->name('convites.destroy');
    });
});
