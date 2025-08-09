<x-admin-layout>
    <x-slot name="header">
        <style>
            .activity-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 1rem;
                padding: 1.5rem;
                transition: all 0.3s ease;
            }
            
            .activity-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }
            
            .activity-timeline {
                max-height: 400px;
                overflow-y: auto;
            }
            
            .activity-item {
                border-left: 3px solid #e5e7eb;
                padding-left: 1rem;
                margin-bottom: 1rem;
                position: relative;
            }
            
            .activity-item::before {
                content: '';
                position: absolute;
                left: -6px;
                top: 0.5rem;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #3b82f6;
            }
            
            .activity-item.login::before {
                background: #10b981;
            }
            
            .activity-item.logout::before {
                background: #ef4444;
            }
            
            .chart-container {
                position: relative;
                height: 300px;
            }
        </style>
    </x-slot>

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-2xl p-6 mb-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <svg class="w-8 h-8 mr-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h2 class="text-3xl font-bold tracking-tight">📊 Dashboard de Atividades</h2>
                </div>
                <p class="text-blue-100 text-lg">Monitore atividades e acessos dos usuários em tempo real</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="exportReport()" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exportar Relatório
                </button>
                <button onclick="refreshData()" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Atualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Activities -->
        <div class="activity-card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total de Atividades</p>
                    <p class="text-blue-200 text-xs">Últimos 30 dias</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-3xl font-bold">{{ number_format($systemStats['total_activities']) }}</span>
                <span class="ml-2 text-sm bg-white/20 px-2 py-1 rounded-full">📊</span>
            </div>
        </div>

        <!-- Active Users -->
        <div class="activity-card bg-gradient-to-br from-green-500 to-green-600 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-green-100 text-sm font-medium">Usuários Ativos</p>
                    <p class="text-green-200 text-xs">Últimos 30 dias</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-3xl font-bold">{{ $systemStats['active_users'] }}</span>
                <span class="ml-2 text-sm bg-white/20 px-2 py-1 rounded-full">👥</span>
            </div>
        </div>

        <!-- Recent Logins -->
        <div class="activity-card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Logins Recentes</p>
                    <p class="text-purple-200 text-xs">Últimas 24 horas</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-3xl font-bold">{{ $recentLogins->count() }}</span>
                <span class="ml-2 text-sm bg-white/20 px-2 py-1 rounded-full">🔐</span>
            </div>
        </div>

        <!-- Inactive Users -->
        <div class="activity-card bg-gradient-to-br from-orange-500 to-orange-600 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Usuários Inativos</p>
                    <p class="text-orange-200 text-xs">Sem login há 30+ dias</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-3xl font-bold">{{ $inactiveUsers->count() }}</span>
                <span class="ml-2 text-sm bg-white/20 px-2 py-1 rounded-full">⏰</span>
            </div>
        </div>
    </div>

    <!-- Charts and Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Activity Chart -->
        <div class="activity-card bg-white">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">📈 Atividades por Dia</h3>
                <select id="chartPeriod" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30" selected>Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                </select>
            </div>
            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        <!-- Activity Types Distribution -->
        <div class="activity-card bg-white">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">📊 Tipos de Atividade</h3>
            </div>
            <div class="space-y-4">
                @foreach($systemStats['activity_types'] as $activityType)
                    @php
                        $activity = new \App\Models\UserActivity(['activity_type' => $activityType->activity_type]);
                        $percentage = $systemStats['total_activities'] > 0 ? round(($activityType->count / $systemStats['total_activities']) * 100, 1) : 0;
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-lg mr-2">{{ $activity->getActivityIcon() }}</span>
                            <span class="text-sm font-medium text-gray-700">{{ $activity->getActivityTypeLabel() }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12 text-right">{{ $activityType->count }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activities and Most Active Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activities Timeline -->
        <div class="activity-card bg-white">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">⏰ Atividades Recentes</h3>
                <button onclick="loadTimeline()" class="text-blue-600 hover:text-blue-800 text-sm">
                    Atualizar
                </button>
            </div>
            <div id="activityTimeline" class="activity-timeline">
                <!-- Timeline will be loaded via JavaScript -->
            </div>
        </div>

        <!-- Most Active Users -->
        <div class="activity-card bg-white">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">🏆 Usuários Mais Ativos</h3>
            </div>
            <div class="space-y-4">
                @foreach($systemStats['most_active_users']->take(10) as $index => $userActivity)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 mr-3">
                                @if($userActivity->user->avatar)
                                    <img src="{{ Storage::url($userActivity->user->avatar) }}" alt="{{ $userActivity->user->name }}" class="w-8 h-8 rounded-full">
                                @else
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                        {{ strtoupper(substr($userActivity->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $userActivity->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $userActivity->user->email }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-blue-600">{{ $userActivity->activity_count }}</p>
                            <p class="text-xs text-gray-500">atividades</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let activityChart;

            document.addEventListener('DOMContentLoaded', function() {
                initializeChart();
                loadTimeline();
                
                // Chart period change handler
                document.getElementById('chartPeriod').addEventListener('change', function() {
                    updateChart(this.value);
                });
            });

            function initializeChart() {
                const ctx = document.getElementById('activityChart').getContext('2d');
                activityChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Atividades',
                            data: [],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
                
                updateChart(30);
            }

            function updateChart(days) {
                fetch(`/admin/activity/chart-data?days=${days}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const dailyData = data.data.daily;
                            const labels = Object.keys(dailyData);
                            const values = Object.values(dailyData);
                            
                            activityChart.data.labels = labels;
                            activityChart.data.datasets[0].data = values;
                            activityChart.update();
                        }
                    })
                    .catch(error => console.error('Error updating chart:', error));
            }

            function loadTimeline() {
                fetch('/admin/activity/timeline?limit=20')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderTimeline(data.data);
                        }
                    })
                    .catch(error => console.error('Error loading timeline:', error));
            }

            function renderTimeline(activities) {
                const timeline = document.getElementById('activityTimeline');
                timeline.innerHTML = '';
                
                activities.forEach(activity => {
                    const item = document.createElement('div');
                    item.className = `activity-item ${activity.activity_type}`;
                    
                    const timeAgo = new Date(activity.created_at).toLocaleString('pt-BR');
                    
                    item.innerHTML = `
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-1">
                                    <span class="text-sm mr-2">${getActivityIcon(activity.activity_type)}</span>
                                    <span class="text-sm font-medium text-gray-900">${activity.user.name}</span>
                                </div>
                                <p class="text-sm text-gray-600">${activity.activity_description}</p>
                                <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                            </div>
                        </div>
                    `;
                    
                    timeline.appendChild(item);
                });
            }

            function getActivityIcon(activityType) {
                const icons = {
                    'login': '🔐',
                    'logout': '🚪',
                    'page_view': '👁️',
                    'action': '⚡',
                    'form_submit': '📝',
                    'search': '🔍',
                    'export': '📊',
                    'status_change': '🔄'
                };
                return icons[activityType] || '📋';
            }

            function refreshData() {
                location.reload();
            }

            function exportReport() {
                // TODO: Implement export functionality
                alert('Funcionalidade de exportação será implementada em breve.');
            }
        </script>
    </x-slot>
</x-admin-layout>
