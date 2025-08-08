@extends('layouts.app')

@section('title', 'Dashboard - Hemera Capital Partners')

@section('content')
<div class="min-h-screen bg-gray-50" style="background-color: #F7F7F7;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header with Profile -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px;">
                <div class="flex items-center space-x-4">
                    <!-- Avatar -->
                    <div class="relative">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl border-2" 
                             style="border-color: #E5E5E5;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white" 
                             style="background-color: #5E81F4;"></div>
                    </div>
                    
                    <!-- Profile Info -->
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900" style="color: #333333; font-family: Roboto, sans-serif;">
                            {{ auth()->user()->name }}
                        </h1>
                        <p class="text-gray-600" style="color: #333333; font-size: 14px;">
                            {{ auth()->user()->email }}
                        </p>
                        <p class="text-sm font-medium" style="color: #333333;">
                            Funcionário
                        </p>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1" 
                              style="background-color: #5E81F4; color: white;">
                            Ativo
                        </span>
                    </div>
                    
                    <!-- Subscription Info -->
                    <div class="text-right">
                        <div class="bg-gray-50 rounded-lg p-4" style="border-radius: 8px;">
                            <h3 class="font-semibold text-gray-900" style="color: #333333;">Pro Plan</h3>
                            <p class="text-sm text-gray-600">$9.99 p/m</p>
                            <ul class="text-xs text-gray-500 mt-2 space-y-1">
                                <li>• More productivity with premium</li>
                                <li>• Extended analytics</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Welcome Message -->
        @if(session('welcome') || !session('welcomed'))
        <div class="mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-sm p-6 text-white" style="border-radius: 8px;">
                <div class="flex items-center space-x-4">
                    <div class="text-4xl">👋</div>
                    <div>
                        <h2 class="text-xl font-bold mb-2">Bem-vindo, {{ auth()->user()->name }}!</h2>
                        <p class="text-blue-100">Estamos felizes em tê-lo na equipe da Hemera Capital Partners. Explore seu dashboard e comece sua jornada de aprendizado!</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Birthday Section -->
        @php
            $today = now();
            $userBirthday = auth()->user()->birth_date;
            $isBirthday = $userBirthday && $userBirthday->format('m-d') === $today->format('m-d');
        @endphp
        
        @if($isBirthday)
        <div class="mb-6">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg shadow-sm p-6 text-white relative overflow-hidden" style="border-radius: 8px;">
                <div class="absolute inset-0 opacity-20">
                    <div class="confetti-animation"></div>
                </div>
                <div class="relative z-10 text-center">
                    <div class="text-6xl mb-4">🎉🎂🎉</div>
                    <h2 class="text-2xl font-bold mb-2">Feliz Aniversário!</h2>
                    <p class="text-yellow-100">Desejamos um dia maravilhoso e um ano repleto de sucessos!</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6" style="gap: 16px;">
            
            <!-- Left Column - Main Content -->
            <div class="lg:col-span-3 space-y-6" style="gap: 16px;">
                
                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4" style="gap: 16px;">
                    
                    <!-- Simulados -->
                    <div class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer" style="border-radius: 8px;" onclick="window.location.href='{{ route('simulados.index') }}'">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center" style="background-color: #7C4DFF;">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-sm" style="color: #333333;">Simulados</h3>
                            <p class="text-xs text-gray-500 mt-1">3 disponíveis</p>
                        </div>
                    </div>

                    <!-- Cursos -->
                    <div class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer" style="border-radius: 8px;" onclick="window.location.href='#cursos'">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center" style="background-color: #00C8F9;">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-sm" style="color: #333333;">Cursos</h3>
                            <p class="text-xs text-gray-500 mt-1">5 atribuídos</p>
                        </div>
                    </div>

                    <!-- Gamificação -->
                    <div class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer" style="border-radius: 8px;" onclick="window.location.href='#gamificacao'">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center" style="background-color: #FF7A00;">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-sm" style="color: #333333;">Ranking</h3>
                            <p class="text-xs text-gray-500 mt-1">250 pontos</p>
                        </div>
                    </div>

                    <!-- Perfil -->
                    <div class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer" style="border-radius: 8px;" onclick="window.location.href='#perfil'">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center" style="background-color: #5E81F4;">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-sm" style="color: #333333;">Perfil</h3>
                            <p class="text-xs text-gray-500 mt-1">85% completo</p>
                        </div>
                    </div>
                </div>

                <!-- Simulados Atribuídos -->
                <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold" style="color: #333333; font-family: Roboto, sans-serif;">
                            📝 Simulados Atribuídos
                        </h3>
                        <a href="{{ route('simulados.index') }}" class="text-sm font-medium hover:underline" style="color: #5E81F4;">
                            Ver todos
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Simulado 1 -->
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow" style="border-color: #E5E5E5; border-radius: 8px;">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: #7C4DFF;"></div>
                                    <h4 class="font-medium" style="color: #333333; font-size: 14px;">Integração Empresarial</h4>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">Disponível</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">Teste seus conhecimentos sobre processos de integração.</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">20 questões • 30 min</span>
                                <button class="px-3 py-1 text-xs font-medium text-white rounded hover:opacity-90" style="background-color: #7C4DFF;">
                                    Iniciar
                                </button>
                            </div>
                        </div>

                        <!-- Simulado 2 -->
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow" style="border-color: #E5E5E5; border-radius: 8px;">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: #00C8F9;"></div>
                                    <h4 class="font-medium" style="color: #333333; font-size: 14px;">Políticas da Empresa</h4>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">Em Progresso</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">Avaliação sobre normas e políticas internas.</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">15 questões • 25 min</span>
                                <button class="px-3 py-1 text-xs font-medium text-white rounded hover:opacity-90" style="background-color: #00C8F9;">
                                    Continuar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cursos Atribuídos -->
                <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold" style="color: #333333; font-family: Roboto, sans-serif;">
                            📚 Meus Cursos
                        </h3>
                        <a href="#cursos" class="text-sm font-medium hover:underline" style="color: #5E81F4;">
                            Ver todos
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <!-- Curso 1 -->
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow" style="border-color: #E5E5E5; border-radius: 8px;">
                            <div class="mb-3">
                                <h4 class="font-medium mb-2" style="color: #333333; font-size: 14px;">Onboarding Corporativo</h4>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="h-2 rounded-full" style="background-color: #7C4DFF; width: 75%;"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>75% completo</span>
                                    <span>3/4 módulos</span>
                                </div>
                            </div>
                            <button class="w-full px-3 py-2 text-xs font-medium text-white rounded hover:opacity-90" style="background-color: #7C4DFF;">
                                Continuar Curso
                            </button>
                        </div>

                        <!-- Curso 2 -->
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow" style="border-color: #E5E5E5; border-radius: 8px;">
                            <div class="mb-3">
                                <h4 class="font-medium mb-2" style="color: #333333; font-size: 14px;">Segurança da Informação</h4>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="h-2 rounded-full" style="background-color: #00C8F9; width: 30%;"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>30% completo</span>
                                    <span>1/5 módulos</span>
                                </div>
                            </div>
                            <button class="w-full px-3 py-2 text-xs font-medium text-white rounded hover:opacity-90" style="background-color: #00C8F9;">
                                Continuar Curso
                            </button>
                        </div>

                        <!-- Curso 3 -->
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow" style="border-color: #E5E5E5; border-radius: 8px;">
                            <div class="mb-3">
                                <h4 class="font-medium mb-2" style="color: #333333; font-size: 14px;">Compliance Financeiro</h4>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="h-2 rounded-full" style="background-color: #FF7A00; width: 0%;"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Não iniciado</span>
                                    <span>0/6 módulos</span>
                                </div>
                            </div>
                            <button class="w-full px-3 py-2 text-xs font-medium text-white rounded hover:opacity-90" style="background-color: #FF7A00;">
                                Iniciar Curso
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Gamificação -->
                <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold" style="color: #333333; font-family: Roboto, sans-serif;">
                            🏆 Gamificação
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        <!-- Pontos Totais -->
                        <div class="text-center p-4 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg text-white" style="border-radius: 8px;">
                            <div class="text-2xl font-bold">250</div>
                            <div class="text-sm opacity-90">Pontos Totais</div>
                        </div>

                        <!-- Ranking -->
                        <div class="text-center p-4 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg text-white" style="border-radius: 8px;">
                            <div class="text-2xl font-bold">#5</div>
                            <div class="text-sm opacity-90">Posição</div>
                        </div>

                        <!-- Badges -->
                        <div class="text-center p-4 bg-gradient-to-r from-purple-400 to-pink-500 rounded-lg text-white" style="border-radius: 8px;">
                            <div class="text-2xl font-bold">3</div>
                            <div class="text-sm opacity-90">Badges</div>
                        </div>

                        <!-- Streak -->
                        <div class="text-center p-4 bg-gradient-to-r from-red-400 to-yellow-500 rounded-lg text-white" style="border-radius: 8px;">
                            <div class="text-2xl font-bold">7</div>
                            <div class="text-sm opacity-90">Dias Seguidos</div>
                        </div>
                    </div>

                    <!-- Badges Conquistadas -->
                    <div class="mt-6">
                        <h4 class="font-medium mb-3" style="color: #333333;">Badges Conquistadas</h4>
                        <div class="flex space-x-4">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-2">
                                    <span class="text-2xl">🎯</span>
                                </div>
                                <span class="text-xs text-gray-600">Primeiro Simulado</span>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-2">
                                    <span class="text-2xl">📚</span>
                                </div>
                                <span class="text-xs text-gray-600">Estudante Dedicado</span>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-2">
                                    <span class="text-2xl">⚡</span>
                                </div>
                                <span class="text-xs text-gray-600">Sequência de 7 Dias</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6" style="gap: 16px;">
                    
                    <!-- Tracked Time -->
                    <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px;">
                        <div class="text-center">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Tracked Time</h3>
                            <p class="text-3xl font-bold" style="color: #333333;">28 h</p>
                        </div>
                    </div>

                    <!-- Finished Tasks -->
                    <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px;">
                        <div class="text-center">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Finished Tasks</h3>
                            <p class="text-3xl font-bold" style="color: #333333;">18</p>
                        </div>
                    </div>

                    <!-- New Widget -->
                    <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px;">
                        <div class="text-center">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">New Widget</h3>
                            <p class="text-3xl font-bold" style="color: #FFBF00;">Yes</p>
                        </div>
                    </div>
                </div>

                <!-- Task List -->
                <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px;">
                    <h3 class="text-lg font-semibold mb-4" style="color: #333333; font-family: Roboto, sans-serif;">
                        Task List
                    </h3>
                    <div class="space-y-3" style="gap: 8px;">
                        
                        <!-- Task 1 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg" style="border-radius: 8px;">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full" style="background-color: #7C4DFF;"></div>
                                <div>
                                    <h4 class="font-medium" style="color: #333333; font-size: 14px; font-family: Roboto, sans-serif;">
                                        Prepare Figma file
                                    </h4>
                                    <p class="text-sm text-gray-500">Mobile App</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">High</span>
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">In Progress</span>
                            </div>
                        </div>

                        <!-- Task 2 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg" style="border-radius: 8px;">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full" style="background-color: #00C8F9;"></div>
                                <div>
                                    <h4 class="font-medium" style="color: #333333; font-size: 14px; font-family: Roboto, sans-serif;">
                                        Design UX wireframes
                                    </h4>
                                    <p class="text-sm text-gray-500">UX wireframes</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">Medium</span>
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">In Progress</span>
                            </div>
                        </div>

                        <!-- Task 3 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg" style="border-radius: 8px;">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full" style="background-color: #FF7A00;"></div>
                                <div>
                                    <h4 class="font-medium" style="color: #333333; font-size: 14px; font-family: Roboto, sans-serif;">
                                        Research
                                    </h4>
                                    <p class="text-sm text-gray-500">Mobile App</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">Low</span>
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">Completed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Calendar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6" style="border-radius: 8px; width: 280px;">
                    <h3 class="text-lg font-semibold mb-4" style="color: #333333; font-family: Roboto, sans-serif;">
                        Calendar
                    </h3>
                    
                    <!-- Calendar Events -->
                    <div class="space-y-4">
                        
                        <!-- Event 1 -->
                        <div class="flex items-start space-x-3">
                            <div class="text-sm font-medium text-gray-500 w-12">10:00</div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <div class="w-2 h-2 rounded-full" style="background-color: #00C8F9;"></div>
                                    <h4 class="font-medium text-sm" style="color: #333333;">Dribbble shot</h4>
                                </div>
                                <p class="text-xs text-gray-500">Facebook Brand • Design</p>
                            </div>
                        </div>

                        <!-- Event 2 -->
                        <div class="flex items-start space-x-3">
                            <div class="text-sm font-medium text-gray-500 w-12">13:20</div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <div class="w-2 h-2 rounded-full" style="background-color: #FF7A00;"></div>
                                    <h4 class="font-medium text-sm" style="color: #333333;">Task Management</h4>
                                </div>
                                <p class="text-xs text-gray-500">Design</p>
                            </div>
                        </div>

                        <!-- Event 3 -->
                        <div class="flex items-start space-x-3">
                            <div class="text-sm font-medium text-gray-500 w-12">10:00</div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <div class="w-2 h-2 rounded-full" style="background-color: #5E81F4;"></div>
                                    <h4 class="font-medium text-sm" style="color: #333333;">UX Research</h4>
                                </div>
                                <p class="text-xs text-gray-500">Design</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mini Calendar -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="text-center">
                            <h4 class="font-semibold text-gray-900 mb-2">{{ now()->format('F Y') }}</h4>
                            <div class="grid grid-cols-7 gap-1 text-xs">
                                <div class="text-gray-500 font-medium">S</div>
                                <div class="text-gray-500 font-medium">M</div>
                                <div class="text-gray-500 font-medium">T</div>
                                <div class="text-gray-500 font-medium">W</div>
                                <div class="text-gray-500 font-medium">T</div>
                                <div class="text-gray-500 font-medium">F</div>
                                <div class="text-gray-500 font-medium">S</div>
                                
                                @php
                                    $startOfMonth = now()->startOfMonth();
                                    $endOfMonth = now()->endOfMonth();
                                    $startDate = $startOfMonth->copy()->startOfWeek();
                                    $endDate = $endOfMonth->copy()->endOfWeek();
                                    $today = now()->day;
                                @endphp
                                
                                @for($date = $startDate; $date <= $endDate; $date->addDay())
                                    <div class="p-1 text-center {{ $date->month === now()->month ? 'text-gray-900' : 'text-gray-300' }} {{ $date->day === $today && $date->month === now()->month ? 'bg-blue-500 text-white rounded' : '' }}">
                                        {{ $date->day }}
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles following the design system */
body {
    font-family: 'Roboto', sans-serif;
}

.progress-bar {
    transition: width 0.3s ease-in-out;
}

.task-item:hover {
    transform: translateY(-1px);
    transition: transform 0.2s ease-in-out;
}

.calendar-day:hover {
    background-color: #5E81F4;
    color: white;
    border-radius: 4px;
}

/* Confetti Animation for Birthday */
.confetti-animation {
    position: relative;
    width: 100%;
    height: 100%;
}

.confetti-animation::before,
.confetti-animation::after {
    content: '';
    position: absolute;
    width: 10px;
    height: 10px;
    background: #FFD700;
    animation: confetti-fall 3s infinite linear;
}

.confetti-animation::before {
    left: 20%;
    animation-delay: 0s;
    background: #FF6B6B;
}

.confetti-animation::after {
    left: 80%;
    animation-delay: 1s;
    background: #4ECDC4;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-100px) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(200px) rotate(360deg);
        opacity: 0;
    }
}

/* Card hover effects */
.hover\:shadow-md:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

/* Progress bar animations */
.progress-bar-animated {
    transition: width 1s ease-in-out;
    animation: progress-glow 2s infinite alternate;
}

@keyframes progress-glow {
    0% {
        box-shadow: 0 0 5px rgba(124, 77, 255, 0.3);
    }
    100% {
        box-shadow: 0 0 20px rgba(124, 77, 255, 0.6);
    }
}

/* Badge animations */
.badge-bounce {
    animation: badge-bounce 2s infinite;
}

@keyframes badge-bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

/* Quick action cards */
.quick-action-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.quick-action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

/* Welcome message animation */
.welcome-slide-in {
    animation: slideInFromTop 0.8s ease-out;
}

@keyframes slideInFromTop {
    0% {
        transform: translateY(-50px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Birthday animation */
.birthday-celebration {
    animation: birthday-pulse 2s infinite;
}

@keyframes birthday-pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .lg\:col-span-3 {
        grid-column: span 1;
    }
    
    .lg\:col-span-1 {
        grid-column: span 1;
    }
    
    .calendar-widget {
        width: 100% !important;
        max-width: none !important;
    }
}

@media (max-width: 768px) {
    .grid-cols-3 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    .md\:grid-cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .space-x-4 > * + * {
        margin-left: 0;
        margin-top: 1rem;
    }
    
    .flex-row {
        flex-direction: column;
    }
    
    .text-4xl {
        font-size: 2rem;
    }
    
    .text-6xl {
        font-size: 3rem;
    }
}

@media (max-width: 480px) {
    .md\:grid-cols-4 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    .md\:grid-cols-3 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    .md\:grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars on load
    const progressBars = document.querySelectorAll('[style*="width:"]');
    progressBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        bar.classList.add('progress-bar-animated');
        setTimeout(() => {
            bar.style.width = width;
        }, 300 + (index * 200));
    });
    
    // Add welcome animation
    const welcomeMessage = document.querySelector('.welcome-slide-in');
    if (welcomeMessage) {
        welcomeMessage.classList.add('welcome-slide-in');
    }
    
    // Add birthday animation
    const birthdaySection = document.querySelector('.birthday-celebration');
    if (birthdaySection) {
        birthdaySection.classList.add('birthday-celebration');
    }
    
    // Add hover effects to cards
    const cards = document.querySelectorAll('.hover\\:shadow-md');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.transition = 'all 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add click animations to buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Add badge bounce animation
    const badges = document.querySelectorAll('.badge-bounce');
    badges.forEach(badge => {
        badge.classList.add('badge-bounce');
    });
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading states to quick action cards
    const quickActions = document.querySelectorAll('.quick-action-card');
    quickActions.forEach(card => {
        card.addEventListener('click', function() {
            const icon = this.querySelector('svg');
            if (icon) {
                icon.style.animation = 'spin 1s linear infinite';
                setTimeout(() => {
                    icon.style.animation = '';
                }, 1000);
            }
        });
    });
    
    // Auto-hide welcome message after 10 seconds
    const welcomeMsg = document.querySelector('[data-welcome]');
    if (welcomeMsg) {
        setTimeout(() => {
            welcomeMsg.style.opacity = '0';
            welcomeMsg.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                welcomeMsg.style.display = 'none';
            }, 500);
        }, 10000);
    }
    
    // Add confetti animation for birthday
    if (document.querySelector('.confetti-animation')) {
        createConfetti();
    }
    
    // Update time every minute
    updateTime();
    setInterval(updateTime, 60000);
});

// Confetti animation function
function createConfetti() {
    const confettiContainer = document.querySelector('.confetti-animation');
    if (!confettiContainer) return;
    
    const colors = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FECA57'];
    
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.style.position = 'absolute';
        confetti.style.width = '10px';
        confetti.style.height = '10px';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.animationDelay = Math.random() * 3 + 's';
        confetti.style.animation = 'confetti-fall 3s infinite linear';
        confettiContainer.appendChild(confetti);
    }
}

// Update time function
function updateTime() {
    const timeElements = document.querySelectorAll('[data-time]');
    const now = new Date();
    const timeString = now.toLocaleTimeString('pt-BR', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    timeElements.forEach(element => {
        element.textContent = timeString;
    });
}

// Add CSS for ripple effect
const style = document.createElement('style');
style.textContent = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>
@endsection