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
});

// Rotas protegidas para administradores
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
});
