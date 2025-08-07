<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Onboarding\Controllers\OnboardingController;
use App\Modules\Admin\Controllers\AdminController;

// Rota principal - login de funcionários
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']); // POST para a mesma rota

// Rotas de autenticação de funcionários
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas administrativas (login separado)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login']);
});

// Rotas protegidas para funcionários
Route::middleware('auth')->group(function () {
    Route::get('/funcionario', [OnboardingController::class, 'index'])->name('funcionario');
    Route::post('/funcionario/complete/{step}', [OnboardingController::class, 'completeStep'])->name('funcionario.complete');
    
    // Rotas de Simulados para Funcionários
    Route::prefix('simulados')->group(function () {
        Route::get('/', [\App\Modules\Simulado\Controllers\SimuladoController::class, 'index'])->name('simulados.index');
        Route::get('/{id}', [\App\Modules\Simulado\Controllers\SimuladoController::class, 'show'])->name('simulados.show');
        Route::post('/{id}/iniciar', [\App\Modules\Simulado\Controllers\SimuladoController::class, 'iniciar'])->name('simulados.iniciar');
        Route::get('/tentativa/{id}/executar', [\App\Modules\Simulado\Controllers\SimuladoController::class, 'executar'])->name('simulados.executar');
        Route::post('/tentativa/{id}/finalizar', [\App\Modules\Simulado\Controllers\SimuladoController::class, 'finalizar'])->name('simulados.finalizar');
        Route::get('/tentativa/{id}/resultado', [\App\Modules\Simulado\Controllers\SimuladoController::class, 'resultado'])->name('simulados.resultado');
    });
});

// Rotas protegidas para administradores
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    
    // Rotas de Simulados com Wizard
    Route::prefix('simulados')->name('admin.simulados.')->group(function () {
        Route::get('/', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'index'])->name('index');
        Route::get('/create', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'create'])->name('create');
        Route::post('/', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'destroy'])->name('destroy');
        
        // Wizard routes
        Route::get('/wizard/start', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'wizard'])->name('wizard');
        Route::post('/wizard/step1', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'wizardStep1'])->name('wizard.step1');
        Route::get('/wizard/step2', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'wizardStep2'])->name('wizard.step2');
        Route::post('/wizard/add-pergunta', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'wizardAddPergunta'])->name('wizard.add-pergunta');
        Route::delete('/wizard/remove-pergunta/{index}', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'wizardRemovePergunta'])->name('wizard.remove-pergunta');
        Route::get('/wizard/step3', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'wizardStep3'])->name('wizard.step3');
        Route::post('/wizard/finalize', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'wizardFinalize'])->name('wizard.finalize');
        
        // Question management
        Route::get('/{simuladoId}/questions/create', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'createQuestion'])->name('questions.create');
        Route::post('/{simuladoId}/questions', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'storeQuestion'])->name('questions.store');
        Route::get('/{simuladoId}/questions/{perguntaId}/edit', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'editQuestion'])->name('questions.edit');
        Route::put('/{simuladoId}/questions/{perguntaId}', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/{simuladoId}/questions/{perguntaId}', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'destroyQuestion'])->name('questions.destroy');
    });
    
    // Manter rota simples para compatibilidade
    Route::get('/simulados', [\App\Modules\Admin\Controllers\AdminSimuladoController::class, 'index'])->name('admin.simulados');
    
    // Outras rotas administrativas
    Route::get('/videos', [AdminController::class, 'videos'])->name('admin.videos');
    Route::get('/atribuicoes', [AdminController::class, 'atribuicoes'])->name('admin.atribuicoes');
    Route::get('/gamificacao', [AdminController::class, 'gamificacao'])->name('admin.gamificacao');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
    Route::get('/cms', [AdminController::class, 'cms'])->name('admin.cms');
    Route::get('/notificacoes', [AdminController::class, 'notificacoes'])->name('admin.notificacoes');
    Route::get('/relatorios', [AdminController::class, 'relatorios'])->name('admin.relatorios');
    Route::get('/certificados', [AdminController::class, 'certificados'])->name('admin.certificados');
    Route::get('/configuracoes', [AdminController::class, 'configuracoes'])->name('admin.configuracoes');
    Route::get('/suporte', [AdminController::class, 'suporte'])->name('admin.suporte');
});