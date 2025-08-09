<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\User\Models\User;
use App\Observers\UserObserver;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
}