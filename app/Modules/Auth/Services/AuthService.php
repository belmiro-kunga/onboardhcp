<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function attemptLogin(array $credentials): bool
    {
        $result = Auth::attempt($credentials);
        
        if ($result) {
            // Update last login timestamp
            $user = Auth::user();
            $user->update(['last_login_at' => now()]);
        }
        
        return $result;
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function user()
    {
        return Auth::user();
    }

    public function check(): bool
    {
        return Auth::check();
    }

    public function isAdmin(): bool
    {
        return $this->check() && $this->user()->is_admin;
    }
}