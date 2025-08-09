@extends('layouts.admin-layout')

@section('title', 'Histórico de Atividades - ' . $user->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with User Info -->
        <div class="mb-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            @if($user->avatar)
                                <img class="h-16 w-16 rounded-full object-cover" src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}">
                            @else
                                <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-xl font-medium text-gray-700">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                            <p class="text-sm text-gray-600">{{ $user->email }}</p>
                            <div class="flex items-center mt-2 space-x-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : ($user->status === 'inactive' ? 'bg-gray-100 text-gray-800' : ($user->status === 'blocked' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                                @if($user->last_login_at)
                                    <span class="text-xs text-gray-500">
                                        Último acesso: {{ $user->last_login_at->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-0 flex space-x-3">
                        <a href="{{ route('admin.users') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ← Voltar aos Usuários
                        </a>
                        <button id="exportUserActivity" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            📊 Exportar Relatório
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                <span class="text-blue-600 text-lg">📊</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Atividades</dt>
                                <dd class="text-lg font-medium text-gray-900" id="totalActivities">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                <span class="text-green-600 text-lg">🔐</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Logins</dt>
                                <dd class="text-lg font-medium text-gray-900" id="totalLogins">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                <span class="text-purple-600 text-lg">👁️</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Páginas Vistas</dt>
                                <dd class="text-lg font-medium text-gray-900" id="totalPageViews">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                                <span class="text-yellow-600 text-lg">📝</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Formulários</dt>
                                <dd class="text-lg font-medium text-gray-900" id="totalFormSubmits">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">🔍 Filtros de Atividade</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="activityType" class="block text-sm font-medium text-gray-700">Tipo de Atividade</label>
                        <select id="activityType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos os tipos</option>
                            <option value="login">🔐 Login</option>
                            <option value="logout">🚪 Logout</option>
                            <option value="page_view">👁️ Visualização de Página</option>
                            <option value="form_submit">📝 Envio de Formulário</option>
                            <option value="search">🔍 Pesquisa</option>
                            <option value="export">📊 Exportação</option>
                            <option value="status_change">🔄 Mudança de Status</option>
                            <option value="profile_update">👤 Atualização de Perfil</option>
                        </select>
                    </div>
                    <div>
                        <label for="dateFrom" class="block text-sm font-medium text-gray-700">Data Inicial</label>
                        <input type="date" id="dateFrom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="dateTo" class="block text-sm font-medium text-gray-700">Data Final</label>
                        <input type="date" id="dateTo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="ipAddress" class="block text-sm font-medium text-gray-700">Endereço IP</label>
                        <input type="text" id="ipAddress" placeholder="Ex: 192.168.1.1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="mt-4 flex justify-between">
                    <button id="applyFilters" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        🔍 Aplicar Filtros
                    </button>
                    <button id="clearFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        🗑️ Limpar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">📋 Timeline de Atividades</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Mostrando:</span>
                        <select id="perPage" class="text-sm rounded-md border-gray-300">
                            <option value="25">25 por página</option>
                            <option value="50" selected>50 por página</option>
                            <option value="100">100 por página</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div id="activityTimeline" class="space-y-4">
                    <!-- Loading state -->
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-2"></div>
                        <p class="text-gray-500">Carregando atividades...</p>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div id="pagination" class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4 hidden">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button id="prevPageMobile" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Anterior
                        </button>
                        <button id="nextPageMobile" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Próximo
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700" id="paginationInfo">
                                Mostrando <span class="font-medium">1</span> a <span class="font-medium">50</span> de <span class="font-medium">100</span> resultados
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" id="paginationButtons">
                                <!-- Pagination buttons will be inserted here -->
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Detail Modal -->
<div id="activityDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="activityDetailTitle">Detalhes da Atividade</h3>
                <button id="closeActivityModal" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Fechar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="activityDetailContent" class="text-sm text-gray-600">
                <!-- Activity details will be loaded here -->
            </div>
            <div class="mt-6 flex justify-end">
                <button id="closeActivityModalBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/user-activity-history.js') }}"></script>
@endsection
