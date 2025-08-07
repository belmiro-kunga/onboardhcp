<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use App\Modules\User\Services\UserService;
use App\Modules\Birthday\Services\BirthdayService;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $authService;
    protected $userService;
    protected $birthdayService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        BirthdayService $birthdayService
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->birthdayService = $birthdayService;
        $this->middleware(['auth', 'admin'])->except(['showLoginForm', 'login']);
    }

    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->authService->attemptLogin($credentials)) {
            if ($this->authService->isAdmin()) {
                $request->session()->regenerate();
                return redirect()->intended('/admin/dashboard');
            } else {
                $this->authService->logout();
                return back()->withErrors([
                    'email' => 'Acesso negado. Esta área é restrita a administradores.',
                ])->onlyInput('email');
            }
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registos.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        $totalUsers = $this->userService->getTotalUsersCount();
        $totalAdmins = $this->userService->getAdminsCount();
        $todayBirthdays = $this->birthdayService->getTodayBirthdaysCount();
        $upcomingBirthdays = $this->birthdayService->getUpcomingBirthdays(5);

        return view('admin.dashboard', compact('totalUsers', 'totalAdmins', 'todayBirthdays', 'upcomingBirthdays'));
    }

    public function users()
    {
        $users = $this->userService->getAllUsers();
        return view('admin.users', compact('users'));
    }

    public function createUser(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'birth_date' => 'required|date',
            'is_admin' => 'boolean',
        ]);

        $this->userService->createUser($validatedData);

        return redirect()->route('admin.users')->with('success', 'Utilizador criado com sucesso!');
    }

    public function updateUser(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'birth_date' => 'required|date',
            'is_admin' => 'boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $this->userService->updateUser($user, $validatedData);

        return redirect()->route('admin.users')->with('success', 'Utilizador actualizado com sucesso!');
    }

    public function deleteUser(User $user)
    {
        $this->userService->deleteUser($user);
        return redirect()->route('admin.users')->with('success', 'Utilizador eliminado com sucesso!');
    }
}