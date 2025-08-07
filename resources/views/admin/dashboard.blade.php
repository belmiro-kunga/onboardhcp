<x-admin-layout title="Painel Administrativo" active-menu="dashboard" page-title="Painel Administrativo">
    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="widget">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total de Utilizadores</p>
                    <p class="metric-value">{{ $totalUsers }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="trend-positive">+12%</span> vs mês anterior
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Admins -->
        <div class="widget">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Administradores</p>
                    <p class="metric-value">{{ $totalAdmins }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="text-gray-500">Sem alterações</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today Birthdays -->
        <div class="widget">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Aniversários Hoje</p>
                    <p class="metric-value">{{ $todayBirthdays }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="trend-positive">🎉 Celebrar!</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">🎂</span>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="widget">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Estado do Sistema</p>
                    <p class="text-2xl font-bold text-green-600">Online</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="trend-positive">99.9%</span> uptime
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Activity Chart -->
        <div class="lg:col-span-2 card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Actividade dos Utilizadores</h3>
                <select class="text-sm border border-gray-300 rounded-md px-3 py-1">
                    <option>Últimos 7 dias</option>
                    <option>Últimos 30 dias</option>
                    <option>Últimos 90 dias</option>
                </select>
            </div>
            <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
                <p class="text-gray-500">Gráfico de actividade (Em desenvolvimento)</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Actividade Recente</h3>
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Novo utilizador registado</p>
                        <p class="text-xs text-gray-500">há 2 minutos</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Sistema actualizado</p>
                        <p class="text-xs text-gray-500">há 1 hora</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Backup realizado</p>
                        <p class="text-xs text-gray-500">há 3 horas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Birthdays -->
    @if($upcomingBirthdays->count() > 0)
    <div class="card mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">📅 Próximos Aniversários</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($upcomingBirthdays as $upcoming)
                @php
                    $nextBirthday = \Carbon\Carbon::parse($upcoming->birth_date)->setYear(now()->year);
                    if ($nextBirthday->isPast()) {
                        $nextBirthday->addYear();
                    }
                    $daysUntil = now()->diffInDays($nextBirthday);
                @endphp
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="avatar mr-3">
                            {{ strtoupper(substr($upcoming->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 text-sm">{{ $upcoming->name }}</p>
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
</x-admin-layout>