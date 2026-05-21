<?php

use App\Http\Controllers\Admin\FarmController as AdminFarmController;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DryerController;
use App\Http\Controllers\FarmBlockedController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\SecagemController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/termos', [PublicSiteController::class, 'termos'])->name('termos');
Route::get('/privacidade', [PublicSiteController::class, 'privacidade'])->name('privacidade');

// Preview da OG image — pra gerar PNG via screenshot do navegador (uso pontual)
Route::view('/og-preview', 'og-preview')->name('og.preview');

// Preview dos e-mails (SÓ EM DEV LOCAL — bloqueado em prod por abort_unless).
// Acessa /dev/email/welcome e /dev/email/invitation no navegador pra ver o HTML
// renderizado dos templates sem precisar disparar e-mail real.
Route::prefix('dev/email')->group(function () {
    Route::get('welcome', function () {
        abort_unless(app()->environment('local'), 404);
        $farm = new \App\Models\Farm(['nome' => 'Fazenda Paraíso']);
        $user = new \App\Models\User(['name' => 'João da Silva']);
        $user->setRelation('farm', $farm);
        return new \App\Mail\WelcomeFarmMail($user);
    });
    Route::get('invitation', function () {
        abort_unless(app()->environment('local'), 404);
        $farm = new \App\Models\Farm(['nome' => 'Fazenda Paraíso']);
        $invitation = new \App\Models\Invitation([
            'email' => 'maria@exemplo.com',
            'role' => 'operador',
            'token' => 'token-de-exemplo-fake',
            'expires_at' => now()->addDays(7),
        ]);
        $invitation->setRelation('farm', $farm);
        return new \App\Mail\InvitationMail($invitation);
    });
});

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

        Route::get('secadores', [DryerController::class, 'index'])->name('secadores.index');
        Route::get('secadores/criar', [DryerController::class, 'create'])->name('secadores.create');
        Route::post('secadores', [DryerController::class, 'store'])->name('secadores.store');
        Route::get('secadores/{secador}/editar', [DryerController::class, 'edit'])->name('secadores.edit');
        Route::put('secadores/{secador}', [DryerController::class, 'update'])->name('secadores.update');
        Route::delete('secadores/{secador}', [DryerController::class, 'destroy'])->name('secadores.destroy');

        Route::get('areas', [AreaController::class, 'index'])->name('areas.index');
        Route::get('areas/criar', [AreaController::class, 'create'])->name('areas.create');
        Route::post('areas', [AreaController::class, 'store'])->name('areas.store');
        Route::get('areas/{area}', [AreaController::class, 'show'])->name('areas.show');
        Route::get('areas/{area}/editar', [AreaController::class, 'edit'])->name('areas.edit');
        Route::put('areas/{area}', [AreaController::class, 'update'])->name('areas.update');
        Route::delete('areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');

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
        Route::post('secagens/{secagem}/reabrir', [SecagemController::class, 'reopen'])->name('secagens.reopen');
        Route::get('secagens/{secagem}/pdf', [SecagemController::class, 'pdf'])->name('secagens.pdf');

        // IMPORTANTE: rotas de categorias declaradas ANTES das de despesa/{despesa}
        // para evitar match de "categorias" como id.
        Route::get('despesas/categorias', [ExpenseCategoryController::class, 'index'])->name('despesas.categorias.index');
        Route::get('despesas/categorias/criar', [ExpenseCategoryController::class, 'create'])->name('despesas.categorias.create');
        Route::post('despesas/categorias', [ExpenseCategoryController::class, 'store'])->name('despesas.categorias.store');
        Route::get('despesas/categorias/{categoria}/editar', [ExpenseCategoryController::class, 'edit'])->name('despesas.categorias.edit');
        Route::put('despesas/categorias/{categoria}', [ExpenseCategoryController::class, 'update'])->name('despesas.categorias.update');
        Route::delete('despesas/categorias/{categoria}', [ExpenseCategoryController::class, 'destroy'])->name('despesas.categorias.destroy');

        Route::get('despesas', [ExpenseController::class, 'index'])->name('despesas.index');
        Route::get('despesas/criar', [ExpenseController::class, 'create'])->name('despesas.create');
        Route::post('despesas', [ExpenseController::class, 'store'])->name('despesas.store');
        Route::get('despesas/{despesa}/editar', [ExpenseController::class, 'edit'])->name('despesas.edit');
        Route::put('despesas/{despesa}', [ExpenseController::class, 'update'])->name('despesas.update');
        Route::delete('despesas/{despesa}', [ExpenseController::class, 'destroy'])->name('despesas.destroy');

        Route::get('fazenda', [FarmController::class, 'edit'])->name('fazenda.edit');
        Route::put('fazenda', [FarmController::class, 'update'])->name('fazenda.update');

        // Perfil do usuário logado (qualquer role pode editar o próprio)
        Route::get('perfil', [ProfileController::class, 'edit'])->name('perfil.edit');
        Route::put('perfil', [ProfileController::class, 'update'])->name('perfil.update');
        Route::put('perfil/senha', [ProfileController::class, 'updatePassword'])->name('perfil.senha.update');

        Route::get('assinatura', [BillingController::class, 'show'])->name('assinatura.show');

        Route::get('auditoria', [AuditController::class, 'index'])->name('auditoria.index');

        Route::get('usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::get('usuarios/criar', [UserController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::put('usuarios/{usuario}/role', [UserController::class, 'updateRole'])->name('usuarios.role.update');
        Route::delete('usuarios/{usuario}', [UserController::class, 'destroy'])->name('usuarios.destroy');

        Route::post('convites', [InvitationController::class, 'store'])->name('convites.store');
        Route::delete('convites/{convite}', [InvitationController::class, 'destroy'])->name('convites.destroy');
    });

    // Painel ROOT — visão global, mudança de plano, etc. Acesso negado em controller.
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('fazendas', [AdminFarmController::class, 'index'])->name('fazendas.index');
        Route::get('fazendas/{farm}', [AdminFarmController::class, 'show'])->name('fazendas.show');
        Route::put('fazendas/{farm}/plano', [AdminFarmController::class, 'changePlan'])->name('fazendas.plano');
    });
});
