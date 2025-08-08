@extends('layouts.app')

@section('title', 'Admin Login - Hemera Capital Partners')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-indigo-900 flex items-center justify-center p-4 relative overflow-hidden" id="adminLoginContainer">
    <!-- Enhanced Admin Background -->
    <div class="absolute inset-0 overflow-hidden">
        <!-- Admin Background Gradient -->
        <div class="absolute inset-0 bg-gradient-admin"></div>
        
        <!-- Admin Geometric Shapes -->
        <div class="absolute inset-0">
            <div class="geometric-shapes-admin">
                <div class="geometric-shape-admin shape-admin-1"></div>
                <div class="geometric-shape-admin shape-admin-2"></div>
                <div class="geometric-shape-admin shape-admin-3"></div>
            </div>
            
            <!-- Admin Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.8) 1px, transparent 0); background-size: 50px 50px;"></div>
            
            <!-- Admin Particles -->
            <div class="particles-admin">
                @for ($i = 0; $i < 15; $i++)
                    <div class="particle-admin particle-admin-{{ $i + 1 }}"></div>
                @endfor
            </div>
        </div>
    </div>
    
    <div class="w-full max-w-md mx-auto relative z-10">
        <div class="admin-login-card bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-8 shadow-2xl">
            <!-- Admin Logo/Header -->
            <div class="text-center mb-8">
                <div class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-gradient-to-r from-red-600 via-red-700 to-red-800 flex items-center justify-center shadow-2xl transform hover:rotate-3 transition-all duration-300">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2 tracking-tight">Hemera Capital Partners</h1>
                <h2 class="text-xl font-semibold text-red-300 mb-3">🛡️ Área Administrativa</h2>
                <p class="text-gray-300 text-sm bg-red-900/30 px-4 py-2 rounded-full border border-red-500/30">
                    🔒 Acesso restrito a administradores
                </p>
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
                <label for="email" class="block text-sm font-medium text-white mb-2">
                    📧 E-mail Administrativo
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    class="admin-input w-full bg-white/10 border border-white/30 rounded-lg px-4 py-3 text-white placeholder-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:bg-white/20 transition-all duration-300 @error('email') border-red-400 @enderror"
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
                <label for="password" class="block text-sm font-medium text-white mb-2">
                    🔑 Palavra-passe Administrativa
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                    class="admin-input w-full bg-white/10 border border-white/30 rounded-lg px-4 py-3 text-white placeholder-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:bg-white/20 transition-all duration-300 @error('password') border-red-400 @enderror"
                    placeholder="••••••••••••"
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
                class="admin-button w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-4 px-6 rounded-lg shadow-xl transform hover:scale-[1.02] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-transparent"
                id="adminSubmitBtn"
            >
                <span class="flex items-center justify-center">
                    🛡️ <span class="ml-2">Acesso Administrativo</span>
                </span>
            </button>
        </form>

            <!-- Admin Footer -->
            <div class="mt-8 text-center">
                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                    <p class="text-sm text-gray-300 mb-2">
                        ⚠️ Não é administrador?
                    </p>
                    <a href="{{ route('login') }}" class="text-blue-300 hover:text-blue-200 font-medium transition-colors duration-300 flex items-center justify-center">
                        👤 <span class="ml-1">Acesso de Funcionário</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Admin Login Specific Styles */
.bg-gradient-admin {
    background: linear-gradient(135deg, rgba(17, 24, 39, 0.8) 0%, rgba(30, 58, 138, 0.6) 50%, rgba(67, 56, 202, 0.8) 100%);
}

.geometric-shapes-admin {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.geometric-shape-admin {
    position: absolute;
    border-radius: 15px;
    animation: adminFloat 30s infinite ease-in-out;
    filter: blur(1px);
}

.shape-admin-1 {
    width: 100px;
    height: 100px;
    top: 20%;
    left: 15%;
    background: linear-gradient(45deg, rgba(239, 68, 68, 0.3), rgba(185, 28, 28, 0.3));
    animation-delay: 0s;
    transform: rotate(45deg);
}

.shape-admin-2 {
    width: 80px;
    height: 80px;
    top: 60%;
    right: 20%;
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(156, 163, 175, 0.1));
    animation-delay: -10s;
    border-radius: 50%;
}

.shape-admin-3 {
    width: 120px;
    height: 120px;
    bottom: 25%;
    left: 20%;
    background: linear-gradient(45deg, rgba(67, 56, 202, 0.2), rgba(99, 102, 241, 0.2));
    animation-delay: -20s;
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
}

@keyframes adminFloat {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
        opacity: 0.7;
    }
    25% {
        transform: translateY(-20px) rotate(5deg);
        opacity: 0.9;
    }
    50% {
        transform: translateY(-40px) rotate(-5deg);
        opacity: 0.5;
    }
    75% {
        transform: translateY(-20px) rotate(3deg);
        opacity: 0.8;
    }
}

.particles-admin {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.particle-admin {
    position: absolute;
    width: 3px;
    height: 3px;
    background: linear-gradient(45deg, rgba(239, 68, 68, 0.8), rgba(255, 255, 255, 0.6));
    border-radius: 50%;
    animation: adminParticleFloat 25s infinite linear;
}

@keyframes adminParticleFloat {
    0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateY(-100px) rotate(360deg);
        opacity: 0;
    }
}

.particle-admin-1 { left: 10%; animation-delay: 0s; }
.particle-admin-2 { left: 20%; animation-delay: -3s; }
.particle-admin-3 { left: 30%; animation-delay: -6s; }
.particle-admin-4 { left: 40%; animation-delay: -9s; }
.particle-admin-5 { left: 50%; animation-delay: -12s; }
.particle-admin-6 { left: 60%; animation-delay: -15s; }
.particle-admin-7 { left: 70%; animation-delay: -18s; }
.particle-admin-8 { left: 80%; animation-delay: -21s; }
.particle-admin-9 { left: 90%; animation-delay: -24s; }
.particle-admin-10 { left: 15%; animation-delay: -2s; }
.particle-admin-11 { left: 25%; animation-delay: -5s; }
.particle-admin-12 { left: 35%; animation-delay: -8s; }
.particle-admin-13 { left: 45%; animation-delay: -11s; }
.particle-admin-14 { left: 55%; animation-delay: -14s; }
.particle-admin-15 { left: 65%; animation-delay: -17s; }

.admin-login-card {
    position: relative;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-login-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.admin-input::placeholder {
    color: rgba(209, 213, 219, 0.7);
}

.admin-button:hover {
    box-shadow: 0 20px 40px -12px rgba(239, 68, 68, 0.4);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .geometric-shape-admin {
        opacity: 0.3;
    }
    
    .particle-admin {
        opacity: 0.5;
    }
}
</style>
@endsection