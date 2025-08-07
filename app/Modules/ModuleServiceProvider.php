<?php

namespace App\Modules;

use Illuminate\Support\ServiceProvider;
use App\Modules\Auth\Services\AuthService;
use App\Modules\User\Services\UserService;
use App\Modules\Birthday\Services\BirthdayService;
use App\Modules\Onboarding\Services\OnboardingService;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar serviços como singletons
        $this->app->singleton(AuthService::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(OnboardingService::class);
        $this->app->singleton(BirthdayService::class);
        $this->app->singleton(\App\Modules\Simulado\Services\SimuladoService::class);
    }

    public function boot(): void
    {
        // Registrar aliases para o modelo User
        $this->app->alias(\App\Modules\User\Models\User::class, \App\Models\User::class);
    }
}