<x-admin-layout title="Painel Administrativo" active-menu="dashboard" page-title="Painel Administrativo">
    <x-slot name="styles">
        <style>
            /* Dashboard Specific Styles */
            .metric-card {
                @apply bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:scale-105;
            }
            
            .metric-icon {
                @apply w-12 h-12 rounded-lg flex items-center justify-center transition-all duration-300;
            }
            
            .metric-value {
                @apply text-3xl font-bold text-gray-900 mb-1;
            }
            
            .metric-trend {
                @apply text-sm font-medium;
            }
            
            .trend-positive {
                @apply text-green-600;
            }
            
            .trend-negative {
                @apply text-red-600;
            }
            
            .trend-neutral {
                @apply text-gray-500;
            }
            
            .chart-container {
                @apply bg-white rounded-xl p-6 shadow-sm border border-gray-100;
            }
            
            .activity-item {
                @apply flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200;
            }
            
            .quick-action-card {
                @apply bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 cursor-pointer group;
            }
            
            .quick-action-card:hover {
                @apply transform -translate-y-1;
            }
            
            .birthday-card {
                @apply bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 border border-blue-100 hover:shadow-md transition-all duration-300;
            }
            
            /* Responsive Animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .animate-fade-in-up {
                animation: fadeInUp 0.6s ease-out;
            }
            
            .animate-delay-100 { animation-delay: 0.1s; }
            .animate-delay-200 { animation-delay: 0.2s; }
            .animate-delay-300 { animation-delay: 0.3s; }
            .animate-delay-400 { animation-delay: 0.4s; }
            
            /* Mobile Responsive */
            @media (max-width: 768px) {
                .metric-card {
                    @apply p-4;
                }
                
                .metric-value {
                    @apply text-2xl;
                }
                
                .chart-container {
                    @apply p-4;
                }
            }
        </style>
    </x-slot>

    <!-- Welcome Section -->
    <div class="mb-8 animate-fade-in-up">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Bem-vindo ao Painel Administrativo</h1>
                    <p class="text-blue-100 text-sm md:text-base">Gerencie o sistema Hemera Capital Partners com facilidade</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold">{{ now()->format('H:i') }}</div>
                        <div class="text-xs text-blue-100">{{ now()->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <!-- Total Users -->
        <div class="metric-card animate-fade-in-up animate-delay-100" onclick="window.location.href='{{ route('admin.users') }}'">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-2">Total de Utilizadores</p>
                    <div class="metric-value" data-target="{{ $totalUsers }}">0</div>
                    <div class="flex items-center mt-2">
                        <span class="trend-positive metric-trend">↗ +12%</span>
                        <span class="text-xs text-gray-500 ml-2">vs mês anterior</span>
                    </div>
                </div>
                <div class="metric-icon bg-blue-100 group-hover:bg-blue-200">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Admins -->
        <div class="metric-card animate-fade-in-up animate-delay-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-2">Administradores</p>
                    <div class="metric-value" data-target="{{ $totalAdmins }}">0</div>
                    <div class="flex items-center mt-2">
                        <span class="trend-neutral metric-trend">→ 0%</span>
                        <span class="text-xs text-gray-500 ml-2">sem alterações</span>
                    </div>
                </div>
                <div class="metric-icon bg-purple-100 group-hover:bg-purple-200">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today Birthdays -->
        <div class="metric-card animate-fade-in-up animate-delay-300">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-2">Aniversários Hoje</p>
                    <div class="metric-value" data-target="{{ $todayBirthdays }}">0</div>
                    <div class="flex items-center mt-2">
                        <span class="trend-positive metric-trend">🎉 Celebrar!</span>
                    </div>
                </div>
                <div class="metric-icon bg-yellow-100 group-hover:bg-yellow-200">
                    <div class="text-2xl animate-bounce">🎂</div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="metric-card animate-fade-in-up animate-delay-400">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-2">Estado do Sistema</p>
                    <div class="flex items-center mb-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                        <span class="text-2xl font-bold text-green-600">Online</span>
                    </div>
                    <div class="flex items-center mt-2">
                        <span class="trend-positive metric-trend">99.9%</span>
                        <span class="text-xs text-gray-500 ml-2">uptime</span>
                    </div>
                </div>
                <div class="metric-icon bg-green-100 group-hover:bg-green-200">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 mb-8">
        <!-- Activity Chart -->
        <div class="lg:col-span-2 chart-container animate-fade-in-up animate-delay-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Atividade dos Utilizadores</h3>
                <select class="text-sm border border-gray-300 rounded-md px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option>Últimos 7 dias</option>
                    <option>Últimos 30 dias</option>
                    <option>Últimos 90 dias</option>
                </select>
            </div>
            <div class="h-64 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg flex items-center justify-center relative overflow-hidden">
                <!-- Animated Chart Placeholder -->
                <div class="absolute inset-0 flex items-end justify-center space-x-2 p-4">
                    <div class="bg-blue-400 rounded-t animate-pulse" style="height: 40%; width: 12px;"></div>
                    <div class="bg-blue-500 rounded-t animate-pulse" style="height: 60%; width: 12px; animation-delay: 0.1s;"></div>
                    <div class="bg-blue-600 rounded-t animate-pulse" style="height: 80%; width: 12px; animation-delay: 0.2s;"></div>
                    <div class="bg-purple-500 rounded-t animate-pulse" style="height: 45%; width: 12px; animation-delay: 0.3s;"></div>
                    <div class="bg-purple-600 rounded-t animate-pulse" style="height: 70%; width: 12px; animation-delay: 0.4s;"></div>
                    <div class="bg-blue-500 rounded-t animate-pulse" style="height: 55%; width: 12px; animation-delay: 0.5s;"></div>
                    <div class="bg-blue-400 rounded-t animate-pulse" style="height: 35%; width: 12px; animation-delay: 0.6s;"></div>
                </div>
                <div class="relative z-10 text-center">
                    <div class="text-2xl font-bold text-gray-700 mb-2">📊</div>
                    <p class="text-gray-600 text-sm">Gráfico Interativo</p>
                    <p class="text-gray-500 text-xs">Em desenvolvimento</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="chart-container animate-fade-in-up animate-delay-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Atividade Recente</h3>
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            </div>
            <div class="space-y-1" id="activity-feed">
                <div class="activity-item">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">Novo utilizador registado</p>
                        <p class="text-xs text-gray-500">João Silva • há 2 minutos</p>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">Sistema atualizado</p>
                        <p class="text-xs text-gray-500">Versão 1.2.0 • há 1 hora</p>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">Backup realizado</p>
                        <p class="text-xs text-gray-500">Automático • há 3 horas</p>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">Simulado criado</p>
                        <p class="text-xs text-gray-500">Avaliação Q1 • há 5 horas</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-100">
                <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Ver todas as atividades →
                </button>
            </div>
        </div>
    </div>

    <!-- Upcoming Birthdays -->
    @if($upcomingBirthdays->count() > 0)
    <div class="chart-container mb-8 animate-fade-in-up animate-delay-300">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">🎉 Próximos Aniversários</h3>
            <span class="text-sm text-gray-500">{{ $upcomingBirthdays->count() }} próximo(s)</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($upcomingBirthdays as $index => $upcoming)
                @php
                    $nextBirthday = \Carbon\Carbon::parse($upcoming->birth_date)->setYear(now()->year);
                    if ($nextBirthday->isPast()) {
                        $nextBirthday->addYear();
                    }
                    $daysUntil = now()->diffInDays($nextBirthday);
                @endphp
                
                <div class="birthday-card animate-fade-in-up" style="animation-delay: {{ 0.1 * $index }}s;">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4 shadow-lg">
                            {{ strtoupper(substr($upcoming->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $upcoming->name }}</p>
                            <p class="text-xs text-gray-600 flex items-center mt-1">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $nextBirthday->format('d/m') }} • 
                                @if($daysUntil == 0)
                                    <span class="text-green-600 font-medium">Hoje!</span>
                                @elseif($daysUntil == 1)
                                    <span class="text-blue-600 font-medium">Amanhã</span>
                                @else
                                    <span class="font-medium">{{ $daysUntil }} dias</span>
                                @endif
                            </p>
                        </div>
                        <div class="text-2xl animate-bounce">🎂</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900">Gerir Utilizadores</h4>
            </div>
            <p class="text-sm text-gray-600 mb-4">Adicionar, editar ou remover utilizadores do sistema</p>
            <a href="{{ route('admin.users') }}" class="btn-primary text-sm inline-block">Aceder</a>
        </div>

        <div class="card hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900">Relatórios</h4>
            </div>
            <p class="text-sm text-gray-600 mb-4">Visualizar estatísticas e relatórios do sistema</p>
            <button class="btn-secondary text-sm cursor-not-allowed">Em Breve</button>
        </div>

        <div class="card hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900">Configurações</h4>
            </div>
            <p class="text-sm text-gray-600 mb-4">Configurar parâmetros do sistema</p>
            <button class="btn-secondary text-sm cursor-not-allowed">Em Breve</button>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Animate counters
                function animateCounters() {
                    const counters = document.querySelectorAll('.metric-value[data-target]');
                    
                    counters.forEach(counter => {
                        const target = parseInt(counter.getAttribute('data-target'));
                        const duration = 2000;
                        const step = target / (duration / 16);
                        let current = 0;
                        
                        const timer = setInterval(() => {
                            current += step;
                            if (current >= target) {
                                counter.textContent = target;
                                clearInterval(timer);
                            } else {
                                counter.textContent = Math.floor(current);
                            }
                        }, 16);
                    });
                }
                
                // Animate progress bars
                function animateProgressBars() {
                    const progressBars = document.querySelectorAll('.progress-bar[data-width]');
                    
                    progressBars.forEach(bar => {
                        const targetWidth = bar.getAttribute('data-width');
                        setTimeout(() => {
                            bar.style.width = targetWidth;
                        }, 500);
                    });
                }
                
                // Initialize animations when elements come into view
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            if (entry.target.classList.contains('metric-value')) {
                                animateCounters();
                            }
                            if (entry.target.classList.contains('progress-bar')) {
                                animateProgressBars();
                            }
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                // Observe counter elements
                document.querySelectorAll('.metric-value[data-target]').forEach(counter => {
                    observer.observe(counter);
                });
                
                // Observe progress bar elements
                document.querySelectorAll('.progress-bar[data-width]').forEach(bar => {
                    observer.observe(bar);
                });
                
                // Real-time activity feed simulation
                function updateActivityFeed() {
                    const activities = [
                        {
                            icon: 'user',
                            color: 'blue',
                            title: 'Novo utilizador registado',
                            subtitle: 'Maria Santos • agora'
                        },
                        {
                            icon: 'check',
                            color: 'green',
                            title: 'Simulado completado',
                            subtitle: 'João Silva • há 1 minuto'
                        },
                        {
                            icon: 'document',
                            color: 'purple',
                            title: 'Relatório gerado',
                            subtitle: 'Sistema • há 2 minutos'
                        }
                    ];
                    
                    const activityFeed = document.getElementById('activity-feed');
                    if (activityFeed) {
                        // Add new activity occasionally
                        if (Math.random() > 0.7) {
                            const randomActivity = activities[Math.floor(Math.random() * activities.length)];
                            const newActivity = document.createElement('div');
                            newActivity.className = 'activity-item opacity-0 transform translate-y-4 transition-all duration-500';
                            
                            const iconSvg = randomActivity.icon === 'user' ? 
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>' :
                                randomActivity.icon === 'check' ?
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>' :
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>';
                            
                            newActivity.innerHTML = `
                                <div class="w-8 h-8 bg-${randomActivity.color}-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-${randomActivity.color}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        ${iconSvg}
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">${randomActivity.title}</p>
                                    <p class="text-xs text-gray-500">${randomActivity.subtitle}</p>
                                </div>
                            `;
                            
                            activityFeed.insertBefore(newActivity, activityFeed.firstChild);
                            
                            // Animate in
                            setTimeout(() => {
                                newActivity.classList.remove('opacity-0', 'translate-y-4');
                            }, 100);
                            
                            // Remove old activities if too many
                            const activities = activityFeed.querySelectorAll('.activity-item');
                            if (activities.length > 6) {
                                const oldActivity = activities[activities.length - 1];
                                oldActivity.classList.add('opacity-0', 'transform', 'translate-y-4');
                                setTimeout(() => {
                                    oldActivity.remove();
                                }, 500);
                            }
                        }
                    }
                }
                
                // Update activity feed every 10 seconds
                setInterval(updateActivityFeed, 10000);
                
                // Add click handlers for metric cards
                document.querySelectorAll('.metric-card[onclick]').forEach(card => {
                    card.addEventListener('click', function() {
                        // Add click animation
                        this.classList.add('transform', 'scale-95');
                        setTimeout(() => {
                            this.classList.remove('transform', 'scale-95');
                        }, 150);
                    });
                });
                
                // Initialize tooltips for status indicators
                const statusIndicators = document.querySelectorAll('[data-tooltip]');
                statusIndicators.forEach(indicator => {
                    indicator.addEventListener('mouseenter', function() {
                        // Create tooltip
                        const tooltip = document.createElement('div');
                        tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg -top-8 left-1/2 transform -translate-x-1/2';
                        tooltip.textContent = this.getAttribute('data-tooltip');
                        this.appendChild(tooltip);
                    });
                    
                    indicator.addEventListener('mouseleave', function() {
                        const tooltip = this.querySelector('.absolute');
                        if (tooltip) tooltip.remove();
                    });
                });
            });
        </script>
    </x-slot>
</x-admin-layout>