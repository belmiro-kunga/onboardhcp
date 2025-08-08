@extends('layouts.app')

@section('title', 'Login - Hemera Capital Partners')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100 flex items-center justify-center p-4 relative overflow-hidden" id="loginContainer">
    <!-- Enhanced Background -->
    <div class="absolute inset-0 overflow-hidden">
        <!-- Geometric Background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-100/30 via-indigo-50/20 to-purple-100/30"></div>
            
            <!-- Floating Geometric Shapes -->
            <div class="geometric-shapes">
                <div class="geometric-shape shape-1 bg-gradient-to-r from-blue-400/20 to-indigo-400/20"></div>
                <div class="geometric-shape shape-2 bg-gradient-to-r from-indigo-400/15 to-purple-400/15"></div>
                <div class="geometric-shape shape-3 bg-gradient-to-r from-purple-400/20 to-pink-400/20"></div>
                <div class="geometric-shape shape-4 bg-gradient-to-r from-blue-300/25 to-cyan-300/25"></div>
                <div class="geometric-shape shape-5 bg-gradient-to-r from-indigo-300/20 to-blue-300/20"></div>
            </div>
            
            <!-- Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, rgba(59, 130, 246, 0.5) 1px, transparent 0); background-size: 40px 40px;"></div>
            
            <!-- Subtle Particles -->
            <div class="particles-subtle">
                @for ($i = 0; $i < 30; $i++)
                    <div class="particle-subtle particle-subtle-{{ $i + 1 }}"></div>
                @endfor
            </div>
        </div>
    </div>
    
    <div class="w-full max-w-6xl mx-auto relative z-10">
        <div class="layout-container">
            <!-- Login Form -->
            <div class="login-section">
                <div class="login-card bg-white/95 backdrop-blur-sm border border-gray-200/50 rounded-2xl p-8 shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                <!-- Logo/Header -->
                <div class="text-center mb-8">
                    <div class="logo-container mb-6">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-center shadow-md transform hover:rotate-6 transition-all duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2 tracking-tight">Hemera Capital Partners</h1>
                    <h2 class="text-lg font-semibold text-gray-600 mb-2">Sistema de Integração</h2>
                    <p class="text-gray-500 text-sm">Faça login para iniciar o seu processo de integração</p>
                </div>

                <!-- Login Form -->
                <form method="POST" action="/" class="space-y-6" id="loginForm">
                    @csrf
                    
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-blue-500"></i>E-mail
                        </label>
                        <div class="relative">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                class="modern-input w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 @error('email') border-red-500 @enderror"
                                placeholder="seu@email.com"
                                required
                                autocomplete="email"
                                autofocus
                            >
                            <div class="input-glow"></div>
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-400 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-indigo-500"></i>Palavra-passe
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password"
                                class="modern-input w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 @error('password') border-red-500 @enderror"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                            <div class="input-glow"></div>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-400 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                                class="modern-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="remember" class="ml-3 block text-sm text-gray-700">
                                Lembrar-me
                            </label>
                        </div>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-700 transition-colors duration-300">
                            Esqueceu a palavra-passe?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="modern-button w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transform hover:scale-[1.02] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        id="submitBtn"
                    >
                        <span class="button-text">Entrar</span>
                        <div class="button-loader hidden">
                            <div class="spinner"></div>
                        </div>
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Não tem uma conta? 
                        <a href="#" class="text-blue-600 hover:text-blue-700 font-medium transition-colors duration-300">
                            Registe-se
                        </a>
                    </p>
                </div>
            </div>

            <!-- Seção de Aniversariantes -->
            <div class="birthday-section">
                <!-- Aniversariantes do Dia -->
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
                        
                        <div class="mb-4 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg">
                            <div class="w-12 h-12 mx-auto mb-2 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                M
                            </div>
                            <p class="font-semibold text-gray-800 animate-pulse">Maria Silva</p>
                            <p class="text-sm text-gray-600">
                                28 anos
                            </p>
                            <div class="mt-2 text-xs text-gray-500">
                                🎈 Parabéns! 🎈
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Próximos Aniversariantes -->
                <div class="card">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                        📅 Próximos Aniversários
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                J
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 text-sm">João Santos</p>
                                <p class="text-xs text-gray-600">
                                    15/08 - 7 dias
                                </p>
                            </div>
                            <div class="text-lg">🎂</div>
                        </div>
                        
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                A
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 text-sm">Ana Costa</p>
                                <p class="text-xs text-gray-600">
                                    22/08 - 14 dias
                                </p>
                            </div>
                            <div class="text-lg">🎂</div>
                        </div>
                        
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                P
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 text-sm">Pedro Lima</p>
                                <p class="text-xs text-gray-600">
                                    28/08 - 20 dias
                                </p>
                            </div>
                            <div class="text-lg">🎂</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
/* Modern 3D Login Styles */
/* Enhanced Background Styles */
.geometric-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.geometric-shape {
    position: absolute;
    border-radius: 20px;
    animation: geometricFloat 25s infinite ease-in-out;
    filter: blur(0.5px);
}

.shape-1 {
    width: 120px;
    height: 120px;
    top: 15%;
    left: 10%;
    animation-delay: 0s;
    transform: rotate(45deg);
}

.shape-2 {
    width: 80px;
    height: 80px;
    top: 25%;
    right: 15%;
    animation-delay: -8s;
    border-radius: 50%;
}

.shape-3 {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 15%;
    animation-delay: -15s;
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
}

.shape-4 {
    width: 150px;
    height: 150px;
    bottom: 30%;
    right: 20%;
    animation-delay: -5s;
    border-radius: 30px;
}

.shape-5 {
    width: 90px;
    height: 90px;
    top: 60%;
    left: 50%;
    animation-delay: -12s;
    transform: rotate(30deg);
}

@keyframes geometricFloat {
    0%, 100% {
        transform: translateY(0px) translateX(0px) rotate(0deg);
        opacity: 0.6;
    }
    25% {
        transform: translateY(-30px) translateX(20px) rotate(90deg);
        opacity: 0.8;
    }
    50% {
        transform: translateY(-60px) translateX(-10px) rotate(180deg);
        opacity: 0.4;
    }
    75% {
        transform: translateY(-30px) translateX(-30px) rotate(270deg);
        opacity: 0.7;
    }
}

/* Subtle Particle System */
.particles-subtle {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.particle-subtle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: linear-gradient(45deg, rgba(59, 130, 246, 0.6), rgba(99, 102, 241, 0.6));
    border-radius: 50%;
    animation: subtleParticleFloat 20s infinite linear;
}

.particle-subtle-1 { left: 5%; top: 10%; animation-delay: 0s; }
.particle-subtle-2 { left: 15%; top: 20%; animation-delay: -2s; }
.particle-subtle-3 { left: 25%; top: 15%; animation-delay: -4s; }
.particle-subtle-4 { left: 35%; top: 25%; animation-delay: -6s; }
.particle-subtle-5 { left: 45%; top: 10%; animation-delay: -8s; }
.particle-subtle-6 { left: 55%; top: 30%; animation-delay: -10s; }
.particle-subtle-7 { left: 65%; top: 15%; animation-delay: -12s; }
.particle-subtle-8 { left: 75%; top: 25%; animation-delay: -14s; }
.particle-subtle-9 { left: 85%; top: 20%; animation-delay: -16s; }
.particle-subtle-10 { left: 95%; top: 10%; animation-delay: -18s; }
.particle-subtle-11 { left: 10%; top: 40%; animation-delay: -1s; }
.particle-subtle-12 { left: 20%; top: 50%; animation-delay: -3s; }
.particle-subtle-13 { left: 30%; top: 45%; animation-delay: -5s; }
.particle-subtle-14 { left: 40%; top: 55%; animation-delay: -7s; }
.particle-subtle-15 { left: 50%; top: 40%; animation-delay: -9s; }
.particle-subtle-16 { left: 60%; top: 60%; animation-delay: -11s; }
.particle-subtle-17 { left: 70%; top: 45%; animation-delay: -13s; }
.particle-subtle-18 { left: 80%; top: 55%; animation-delay: -15s; }
.particle-subtle-19 { left: 90%; top: 50%; animation-delay: -17s; }
.particle-subtle-20 { left: 5%; top: 70%; animation-delay: -19s; }
.particle-subtle-21 { left: 15%; top: 80%; animation-delay: -1.5s; }
.particle-subtle-22 { left: 25%; top: 75%; animation-delay: -3.5s; }
.particle-subtle-23 { left: 35%; top: 85%; animation-delay: -5.5s; }
.particle-subtle-24 { left: 45%; top: 70%; animation-delay: -7.5s; }
.particle-subtle-25 { left: 55%; top: 90%; animation-delay: -9.5s; }
.particle-subtle-26 { left: 65%; top: 75%; animation-delay: -11.5s; }
.particle-subtle-27 { left: 75%; top: 85%; animation-delay: -13.5s; }
.particle-subtle-28 { left: 85%; top: 80%; animation-delay: -15.5s; }
.particle-subtle-29 { left: 95%; top: 70%; animation-delay: -17.5s; }
.particle-subtle-30 { left: 50%; top: 95%; animation-delay: -19.5s; }

@keyframes subtleParticleFloat {
    0% {
        transform: translateY(0px) scale(0);
        opacity: 0;
    }
    10% {
        transform: translateY(-20px) scale(1);
        opacity: 1;
    }
    90% {
        transform: translateY(-80px) scale(1);
        opacity: 1;
    }
    100% {
        transform: translateY(-100px) scale(0);
        opacity: 0;
    }
}

/* Login Card 3D Effects */
.login-card {
    perspective: 1000px;
    transform-style: preserve-3d;
}

.login-card:hover {
    transform: rotateY(2deg) rotateX(2deg);
}

/* Custom Layout Container */
.layout-container {
    display: flex;
    flex-direction: row;
    gap: 2rem;
    align-items: flex-start;
    width: 100%;
}

.login-section {
    flex: 1;
    max-width: 450px;
}

.birthday-section {
    flex: 1;
    max-width: 400px;
}

/* Login Card Enhanced */
.login-card {
    position: relative;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow: 
        0 20px 40px -12px rgba(0, 0, 0, 0.1),
        0 8px 32px -8px rgba(59, 130, 246, 0.1),
        0 0 0 1px rgba(255, 255, 255, 0.8);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.login-card:hover {
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.15),
        0 12px 40px -8px rgba(59, 130, 246, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.9);
    transform: translateY(-2px) scale(1.02);
}

.modern-input:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: translateY(-1px);
}

.input-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 0.75rem;
    background: linear-gradient(45deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.modern-input:focus + .input-glow {
    opacity: 1;
}

/* Modern Button Styles */
.modern-button {
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 8px 20px -5px rgba(59, 130, 246, 0.25),
        0 3px 6px -2px rgba(0, 0, 0, 0.08);
}

.modern-button:hover {
    box-shadow: 
        0 12px 30px -5px rgba(59, 130, 246, 0.35),
        0 5px 10px -2px rgba(0, 0, 0, 0.12);
    transform: translateY(-1px) scale(1.02);
}

.modern-button:active {
    transform: translateY(0) scale(0.98);
}

.modern-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
    transition: left 0.5s;
}

.modern-button:hover::before {
    left: 100%;
}

/* Loading Spinner */
.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Birthday Section Styles */
.card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 
        0 20px 40px -12px rgba(0, 0, 0, 0.1),
        0 8px 32px -8px rgba(59, 130, 246, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.15),
        0 12px 40px -8px rgba(59, 130, 246, 0.15);
}

/* Confetti Animation */
.confetti-container {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.confetti {
    position: absolute;
    width: 8px;
    height: 8px;
    animation: confettiFall 3s infinite linear;
}

.confetti-1 {
    background: #ff6b6b;
    left: 10%;
    animation-delay: 0s;
}

.confetti-2 {
    background: #4ecdc4;
    left: 30%;
    animation-delay: -0.5s;
}

.confetti-3 {
    background: #45b7d1;
    left: 50%;
    animation-delay: -1s;
}

.confetti-4 {
    background: #96ceb4;
    left: 70%;
    animation-delay: -1.5s;
}

.confetti-5 {
    background: #feca57;
    left: 90%;
    animation-delay: -2s;
}

@keyframes confettiFall {
    0% {
        transform: translateY(-100px) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(400px) rotate(720deg);
        opacity: 0;
    }
}

/* Form Group Animation */
.form-group {
    animation: slideInLeft 0.6s ease-out;
    animation-fill-mode: both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }
.form-group:nth-child(4) { animation-delay: 0.4s; }

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Responsive Adjustments - Enhanced */
@media (max-width: 1024px) {
    .geometric-shape {
        opacity: 0.5;
        animation-duration: 30s;
    }
    
    .login-card {
        padding: 1.5rem;
    }
    
    .card {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .layout-container {
        gap: 1.5rem;
    }
    
    .login-section,
    .birthday-section {
        max-width: none;
    }
}

@media (max-width: 768px) {
    .login-card {
        margin: 1rem;
        padding: 1.5rem;
        bg-white/20;
    }
    
    .particles-subtle {
        opacity: 0.3;
    }
    
    .geometric-shapes {
        opacity: 0.3;
    }
    

    
    h1 {
        font-size: 1.75rem;
    }
    
    h2 {
        font-size: 1.125rem;
    }
}

@media (max-width: 480px) {
    .login-card {
        padding: 1rem;
        margin: 0.5rem;
    }
    
    .modern-input {
        padding: 0.75rem;
    }
    
    .modern-button {
        padding: 0.75rem;
    }
}

@media (max-width: 768px) {
    .geometric-shape {
        opacity: 0.4;
    }
    
    .particle-subtle {
        opacity: 0.4;
    }
    
    .card {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }
    
    .confetti {
        width: 6px;
        height: 6px;
    }
    
    /* Mobile: stack vertically */
    .layout-container {
        flex-direction: column;
        gap: 1rem;
    }
    
    .birthday-section {
        order: -1; /* Birthday section on top on mobile */
    }
}

@media (prefers-reduced-motion: reduce) {
    .geometric-shape,
    .particle-subtle,
    .login-card,
    .modern-input,
    .modern-button,
    .confetti,
    .animate-bounce,
    .animate-pulse {
        animation: none;
        transition: none;
    }
}
</style>

<script>
// Password Toggle
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Form Submission with Loading
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const buttonText = submitBtn.querySelector('.button-text');
    const buttonLoader = submitBtn.querySelector('.button-loader');
    
    buttonText.classList.add('hidden');
    buttonLoader.classList.remove('hidden');
    submitBtn.disabled = true;
});



// Enhanced Input Validation and Feedback
function addInputValidation() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    emailInput.addEventListener('input', function() {
        const isValid = this.checkValidity();
        this.style.borderColor = isValid ? 'rgba(34, 197, 94, 0.5)' : 'rgba(239, 68, 68, 0.5)';
    });
    
    passwordInput.addEventListener('input', function() {
        const strength = this.value.length;
        let color = 'rgba(239, 68, 68, 0.5)';
        if (strength > 6) color = 'rgba(251, 191, 36, 0.5)';
        if (strength > 10) color = 'rgba(34, 197, 94, 0.5)';
        this.style.borderColor = color;
    });
}



// Performance Optimization
function optimizeAnimations() {
    // Reduce animations on low-end devices
    if (navigator.hardwareConcurrency < 4) {
        document.querySelectorAll('.shape').forEach(shape => {
            shape.style.animationDuration = '40s';
        });
        
        document.querySelectorAll('.particle').forEach(particle => {
            if (Math.random() > 0.5) {
                particle.style.display = 'none';
            }
        });
    }
}

// Initialize animations when page loads
document.addEventListener('DOMContentLoaded', function() {

    
    // Add enhanced input validation
    addInputValidation();
    

    
    // Optimize for performance
    optimizeAnimations();
    
    // Add hover effects to input fields
    const inputs = document.querySelectorAll('.modern-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
            this.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add particle animation variety
    const particles = document.querySelectorAll('.particle');
    particles.forEach((particle, index) => {
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * -15 + 's';
        particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
    });
    
    // Add entrance animation to login card
    const loginCard = document.querySelector('.login-card');
    if (loginCard) {
        loginCard.style.opacity = '0';
        loginCard.style.transform = 'translateY(30px) scale(0.95)';
        
        setTimeout(() => {
            loginCard.style.transition = 'all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            loginCard.style.opacity = '1';
            loginCard.style.transform = 'translateY(0) scale(1)';
        }, 200);
    }
});
</script>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection