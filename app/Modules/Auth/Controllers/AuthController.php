<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Birthday\Services\BirthdayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;
    protected $birthdayService;

    public function __construct(AuthService $authService, BirthdayService $birthdayService)
    {
        $this->authService = $authService;
        $this->birthdayService = $birthdayService;
    }

    public function showLoginForm()
    {
        $todayBirthdays = $this->birthdayService->getTodayBirthdays();
        $upcomingBirthdays = $this->birthdayService->getUpcomingBirthdays(3);

        return view('auth.login', compact('todayBirthdays', 'upcomingBirthdays'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->authService->attemptLogin($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/funcionario');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registos.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}