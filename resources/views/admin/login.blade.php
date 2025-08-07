@extends('layouts.app')

@section('title', 'Admin Login - Hemera Capital Partners')

@section('content')
<div class="w-full max-w-md mx-auto">
    <div class="card">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full gradient-bg flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Hemera Capital Partners</h1>
            <h2 class="text-xl font-semibold text-gray-700 mb-3">Área Administrativa</h2>
            <p class="text-gray-600 text-sm">Acesso restrito a administradores</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
            @csrf
            
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    E-mail Administrativo
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    class="input-field w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                    placeholder="admin@hemeracapital.com"
                    required
                    autocomplete="email"
                    autofocus
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Palavra-passe
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                    class="input-field w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="btn-primary w-full text-center block"
            >
                🔐 Entrar como Administrador
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Não é administrador? 
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-500 font-medium">
                    Acesso de Funcionário
                </a>
            </p>
        </div>
    </div>
</div>
@endsection