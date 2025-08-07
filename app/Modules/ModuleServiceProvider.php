<?php

namespace App\Modules;

use Illuminate\Support\ServiceProvider;
use App\Modules\Auth\Services\AuthService;
use App\Modules\User\Services\UserService;
use App\Modules\Birthday\Services\BirthdayService;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Services\VideoService;
use App\Services\StorageService;
use App\Contracts\StorageHandlerInterface;
use App\Services\Storage\LocalStorageHandler;
use App\Services\Storage\YouTubeHandler;
use App\Services\Storage\CloudflareR2Handler;

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
        $this->app->singleton(VideoService::class);
        
        // Registrar handlers de storage
        $this->app->singleton(LocalStorageHandler::class);
        $this->app->singleton(YouTubeHandler::class);
        $this->app->singleton(CloudflareR2Handler::class);
        
        // Registrar StorageService
        $this->app->singleton(StorageService::class);
    }

    public function boot(): void
    {
        // Registrar aliases para o modelo User
        $this->app->alias(\App\Modules\User\Models\User::class, \App\Models\User::class);
    }
}