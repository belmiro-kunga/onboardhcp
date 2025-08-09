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
    // Rota raiz do admin - redireciona baseado na autenticação
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    })->name('admin.index');
    
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login']);
});

// Rotas protegidas para funcionários
Route::middleware('auth')->group(function () {
    Route::get('/funcionario', [OnboardingController::class, 'index'])->name('funcionario');
    Route::post('/funcionario/complete/{step}', [OnboardingController::class, 'completeStep'])->name('funcionario.complete');
    
    // Rotas de Onboarding
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/', [OnboardingController::class, 'onboardingIndex'])->name('index');
        Route::get('/boas-vindas', [OnboardingController::class, 'boasVindas'])->name('boas-vindas');
        Route::get('/sobre-empresa', [OnboardingController::class, 'sobreEmpresa'])->name('sobre-empresa');
        Route::get('/historia', [OnboardingController::class, 'historia'])->name('historia');
        Route::get('/departamentos', [OnboardingController::class, 'departamentos'])->name('departamentos');
        Route::get('/cultura-valores', [OnboardingController::class, 'culturaValores'])->name('cultura-valores');
        Route::get('/organograma', [OnboardingController::class, 'organograma'])->name('organograma');
    });
    
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
    
    // Rotas de perfil
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile.edit');
    Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('admin.users.create');
    Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('admin.users.delete');
    
    // User Status Control Routes
    Route::patch('/users/{user}/status', [\App\Http\Controllers\UserController::class, 'updateStatus'])->name('admin.users.status');
    Route::patch('/users/bulk-status', [\App\Http\Controllers\UserController::class, 'bulkUpdateStatus'])->name('admin.users.bulk-status');
    Route::get('/users/status-statistics', [\App\Http\Controllers\UserController::class, 'getStatusStatistics'])->name('admin.users.status-statistics');
    
    // Advanced User Search Routes
    Route::prefix('users/search')->name('admin.users.search.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserSearchController::class, 'search'])->name('index');
        Route::get('/suggestions', [\App\Http\Controllers\UserSearchController::class, 'suggestions'])->name('suggestions');
        Route::get('/filter-options', [\App\Http\Controllers\UserSearchController::class, 'filterOptions'])->name('filter-options');
        Route::post('/save-config', [\App\Http\Controllers\UserSearchController::class, 'saveSearchConfig'])->name('save-config');
        Route::get('/saved-configs', [\App\Http\Controllers\UserSearchController::class, 'getSavedConfigs'])->name('saved-configs');
        Route::get('/search-export', [\App\Http\Controllers\UserSearchController::class, 'export'])->name('search-export');
    });
    
    // Import/Export Routes
    Route::prefix('users/import-export')->name('admin.users.import-export.')->group(function () {
        Route::get('/export', [\App\Http\Controllers\ImportExportController::class, 'exportUsers'])->name('export');
        Route::post('/export-selected', [\App\Http\Controllers\ImportExportController::class, 'exportUsers'])->name('export-selected');
        Route::get('/template', [\App\Http\Controllers\ImportExportController::class, 'downloadTemplate'])->name('template');
        Route::post('/preview', [\App\Http\Controllers\ImportExportController::class, 'previewImport'])->name('preview');
        Route::post('/import', [\App\Http\Controllers\ImportExportController::class, 'importUsers'])->name('import');
        Route::get('/history', [\App\Http\Controllers\ImportExportController::class, 'getHistory'])->name('history');
        Route::post('/cancel/{jobId}', [\App\Http\Controllers\ImportExportController::class, 'cancelOperation'])->name('cancel');
    });
    
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
    
    // Rotas de vídeos com CRUD completo
    Route::prefix('videos')->name('admin.videos.')->group(function () {
        Route::get('/', [AdminController::class, 'videos'])->name('index');
        Route::get('/create', [AdminController::class, 'videosCreate'])->name('create');
        Route::post('/', [AdminController::class, 'videosStore'])->name('store');
        Route::get('/{id}', [AdminController::class, 'videosShow'])->name('show');
        Route::get('/{id}/edit', [AdminController::class, 'videosEdit'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'videosUpdate'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'videosDestroy'])->name('destroy');
    });
    
    // Manter rota simples para compatibilidade
    Route::get('/videos', [AdminController::class, 'videos'])->name('admin.videos');
    
    // Rotas de cursos com CRUD completo
    Route::prefix('courses')->name('admin.courses.')->group(function () {
        Route::get('/', [AdminController::class, 'courses'])->name('index');
        Route::get('/create', [AdminController::class, 'coursesCreate'])->name('create');
        Route::post('/', [AdminController::class, 'coursesStore'])->name('store');
        Route::get('/{id}', [AdminController::class, 'coursesShow'])->name('show');
        Route::get('/{id}/edit', [AdminController::class, 'coursesEdit'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'coursesUpdate'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'coursesDestroy'])->name('destroy');
    });
    
    // Rotas de Roles e Permissões
    Route::prefix('roles')->name('admin.roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RoleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\RoleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [\App\Http\Controllers\RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [\App\Http\Controllers\RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [\App\Http\Controllers\RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [\App\Http\Controllers\RoleController::class, 'destroy'])->name('destroy');
        
        // Permission management for roles
        Route::post('/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'assignPermissions'])->name('permissions.assign');
        Route::delete('/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'removePermissions'])->name('permissions.remove');
        Route::get('/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'permissions'])->name('permissions');
        Route::get('/{role}/users', [\App\Http\Controllers\RoleController::class, 'users'])->name('users');
    });
    
    // Rotas de Grupos de Utilizadores
    Route::prefix('groups')->name('admin.groups.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserGroupController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\UserGroupController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\UserGroupController::class, 'store'])->name('store');
        Route::get('/{group}', [\App\Http\Controllers\UserGroupController::class, 'show'])->name('show');
        Route::get('/{group}/edit', [\App\Http\Controllers\UserGroupController::class, 'edit'])->name('edit');
        Route::put('/{group}', [\App\Http\Controllers\UserGroupController::class, 'update'])->name('update');
        Route::delete('/{group}', [\App\Http\Controllers\UserGroupController::class, 'destroy'])->name('destroy');
        
        // User management for groups
        Route::post('/{group}/users', [\App\Http\Controllers\UserGroupController::class, 'addUsers'])->name('users.add');
        Route::delete('/{group}/users', [\App\Http\Controllers\UserGroupController::class, 'removeUsers'])->name('users.remove');
        
        // Permission management for groups
        Route::post('/{group}/permissions', [\App\Http\Controllers\UserGroupController::class, 'assignPermissions'])->name('permissions.assign');
        Route::delete('/{group}/permissions', [\App\Http\Controllers\UserGroupController::class, 'removePermissions'])->name('permissions.remove');
        
        // Helper routes
        Route::get('/api/available-users', [\App\Http\Controllers\UserGroupController::class, 'availableUsers'])->name('api.available-users');
        Route::get('/api/departments', [\App\Http\Controllers\UserGroupController::class, 'departments'])->name('api.departments');
    });
    
    // Rotas de Gestão de Roles de Utilizadores
    Route::prefix('users/{user}/roles')->name('admin.user-roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserRoleController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\UserRoleController::class, 'store'])->name('store');
        Route::delete('/{role}', [\App\Http\Controllers\UserRoleController::class, 'destroy'])->name('destroy');
        Route::put('/sync', [\App\Http\Controllers\UserRoleController::class, 'sync'])->name('sync');
        Route::get('/permissions', [\App\Http\Controllers\UserRoleController::class, 'permissions'])->name('permissions');
        Route::get('/permissions/view', [\App\Http\Controllers\UserRoleController::class, 'showPermissions'])->name('permissions.view');
        Route::post('/check-permission', [\App\Http\Controllers\UserRoleController::class, 'checkPermission'])->name('check-permission');
    });
    
    // Rotas de Ações em Lote para Roles
    Route::prefix('bulk-roles')->name('admin.bulk-roles.')->group(function () {
        Route::post('/assign', [\App\Http\Controllers\UserRoleController::class, 'bulkAssign'])->name('assign');
        Route::post('/remove', [\App\Http\Controllers\UserRoleController::class, 'bulkRemove'])->name('remove');
    });

    // Outras rotas administrativas
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