@extends('layouts.app')

@section('title', 'Login - Hemera Capital Partners')

@section('content')
<div class="w-full max-w-6xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Seção de Login -->
        <div class="lg:col-span-2">
            <div class="card max-w-sm mx-auto">
        <!-- Logo/Header -->
        <div class="text-center mb-5">
            <div class="w-14 h-14 mx-auto mb-3 rounded-full gradient-bg flex items-center justify-center">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800 mb-1">Hemera Capital Partners</h1>
            <h2 class="text-base font-semibold text-gray-700 mb-1">Sistema de Integração</h2>
            <p class="text-gray-600 text-xs">Faça login para iniciar o seu processo de integração</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="/" class="space-y-4">
            @csrf
            
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    E-mail
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    class="input-field w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                    placeholder="seu@email.com"
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

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Lembrar-me
                    </label>
                </div>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-500">
                    Esqueceu a palavra-passe?
                </a>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="btn-primary w-full text-center block"
            >
                Entrar
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-4 text-center">
            <p class="text-xs text-gray-600">
                Não tem uma conta? 
                <a href="#" class="text-blue-600 hover:text-blue-500 font-medium">
                    Registe-se
                </a>
            </p>
        </div>
            </div>
        </div>

        <!-- Seção de Aniversariantes -->
        <div class="lg:col-span-1">
            <!-- Aniversariantes do Dia -->
            @if(isset($todayBirthdays) && $todayBirthdays->count() > 0)
                <div class="card mb-6 relative overflow-hidden">
                    <!-- Animação de Confetes -->
                    <div class="absolute inset-0 pointer-events-none">
                        <div class="confetti-container">
                            @for($i = 0; $i < 20; $i++)
                                <div class="confetti confetti-{{ $i % 5 + 1 }}"></div>
                            @endfor
                        </div>
                    </div>
                    
                    <div class="text-center relative z-10">
                        <div class="animate-bounce mb-4">
                            <div class="w-16 h-16 mx-auto bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
                                <span class="text-2xl">🎂</span>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">🎉 Aniversariante do Dia!</h3>
                        
                        @foreach($todayBirthdays as $birthday)
                            <div class="mb-4 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg">
                                <div class="w-12 h-12 mx-auto mb-2 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($birthday->name, 0, 1)) }}
                                </div>
                                <p class="font-semibold text-gray-800 animate-pulse">{{ $birthday->name }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($birthday->birth_date)->age }} anos
                                </p>
                                <div class="mt-2 text-xs text-gray-500">
                                    🎈 Parabéns! 🎈
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Próximos Aniversariantes -->
            @if(isset($upcomingBirthdays) && $upcomingBirthdays->count() > 0)
                <div class="card">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                        📅 Próximos Aniversários
                    </h3>
                    
                    <div class="space-y-3">
                        @foreach($upcomingBirthdays as $upcoming)
                            @php
                                $nextBirthday = \Carbon\Carbon::parse($upcoming->birth_date)->setYear(now()->year);
                                if ($nextBirthday->isPast()) {
                                    $nextBirthday->addYear();
                                }
                                $daysUntil = now()->diffInDays($nextBirthday);
                            @endphp
                            
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                    {{ strtoupper(substr($upcoming->name, 0, 1)) }}
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800 text-sm">{{ $upcoming->name }}</p>
                                    <p class="text-xs text-gray-600">
                                        {{ $nextBirthday->format('d/m') }} - 
                                        @if($daysUntil == 1)
                                            Amanhã
                                        @else
                                            {{ $daysUntil }} dias
                                        @endif
                                    </p>
                                </div>
                                <div class="text-lg">🎂</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Animações de Confetes */
.confetti-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.confetti {
    position: absolute;
    width: 8px;
    height: 8px;
    animation: confetti-fall 3s linear infinite;
}

.confetti-1 { background: #ff6b6b; left: 10%; animation-delay: 0s; }
.confetti-2 { background: #4ecdc4; left: 30%; animation-delay: 0.5s; }
.confetti-3 { background: #45b7d1; left: 50%; animation-delay: 1s; }
.confetti-4 { background: #f9ca24; left: 70%; animation-delay: 1.5s; }
.confetti-5 { background: #f0932b; left: 90%; animation-delay: 2s; }

@keyframes confetti-fall {
    0% {
        transform: translateY(-100px) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(400px) rotate(720deg);
        opacity: 0;
    }
}

/* Animação de Pulsação para Aniversariante */
@keyframes birthday-glow {
    0%, 100% {
        box-shadow: 0 0 5px rgba(255, 193, 7, 0.5);
    }
    50% {
        box-shadow: 0 0 20px rgba(255, 193, 7, 0.8);
    }
}

.birthday-glow {
    animation: birthday-glow 2s ease-in-out infinite;
}
</style>
@endsection