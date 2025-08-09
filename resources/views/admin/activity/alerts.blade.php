@extends('layouts.admin-layout')

@section('title', 'Alertas de Segurança')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🚨 Alertas de Segurança</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Monitoramento de atividades suspeitas e padrões anômalos
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button id="refreshAlerts" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        🔄 Atualizar
                    </button>
                    <button id="processAlerts" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ⚡ Processar Alertas
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                                <span class="text-red-600 text-lg">🔥</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Alertas Críticos</dt>
                                <dd class="text-lg font-medium text-gray-900" id="highSeverityCount">-</dd>
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
                                <span class="text-yellow-600 text-lg">⚠️</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Alertas Médios</dt>
                                <dd class="text-lg font-medium text-gray-900" id="mediumSeverityCount">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                <span class="text-blue-600 text-lg">ℹ️</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Alertas Baixos</dt>
                                <dd class="text-lg font-medium text-gray-900" id="lowSeverityCount">-</dd>
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
                                <span class="text-green-600 text-lg">📊</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Alertas</dt>
                                <dd class="text-lg font-medium text-gray-900" id="totalAlertsCount">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Alerts -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">🚨 Alertas Ativos</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Alertas de segurança detectados nas últimas 24 horas
                </p>
            </div>
            <div class="border-t border-gray-200">
                <div id="alertsList" class="divide-y divide-gray-200">
                    <!-- Alerts will be loaded here -->
                    <div class="px-4 py-4 text-center text-gray-500">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-2"></div>
                        Carregando alertas...
                    </div>
                </div>
            </div>
        </div>

        <!-- Users with Alerts -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">👥 Usuários com Alertas</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Usuários que apresentaram atividades suspeitas recentemente
                </p>
            </div>
            <div class="border-t border-gray-200">
                <div id="usersWithAlerts" class="divide-y divide-gray-200">
                    <!-- Users will be loaded here -->
                    <div class="px-4 py-4 text-center text-gray-500">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-2"></div>
                        Carregando usuários...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Detail Modal -->
<div id="alertDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="alertDetailTitle">Detalhes do Alerta</h3>
                <button id="closeAlertModal" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Fechar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="alertDetailContent" class="text-sm text-gray-600">
                <!-- Alert details will be loaded here -->
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button id="dismissAlert" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Descartar
                </button>
                <button id="investigateAlert" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Investigar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadAlertStatistics();
    loadActiveAlerts();
    loadUsersWithAlerts();

    // Event listeners
    document.getElementById('refreshAlerts').addEventListener('click', function() {
        loadAlertStatistics();
        loadActiveAlerts();
        loadUsersWithAlerts();
    });

    document.getElementById('processAlerts').addEventListener('click', function() {
        processAlerts();
    });

    document.getElementById('closeAlertModal').addEventListener('click', function() {
        document.getElementById('alertDetailModal').classList.add('hidden');
    });

    // Auto-refresh every 5 minutes
    setInterval(function() {
        loadAlertStatistics();
        loadActiveAlerts();
    }, 300000);
});

function loadAlertStatistics() {
    fetch('/admin/activity/alert-statistics', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('totalAlertsCount').textContent = data.total_alerts || 0;
        document.getElementById('highSeverityCount').textContent = data.high_severity || 0;
        document.getElementById('mediumSeverityCount').textContent = data.medium_severity || 0;
        document.getElementById('lowSeverityCount').textContent = data.low_severity || 0;
    })
    .catch(error => {
        console.error('Error loading alert statistics:', error);
    });
}

function loadActiveAlerts() {
    fetch('/admin/activity/active-alerts', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        const alertsList = document.getElementById('alertsList');
        
        if (data.alerts && data.alerts.length > 0) {
            alertsList.innerHTML = data.alerts.map(alert => `
                <div class="px-4 py-4 hover:bg-gray-50 cursor-pointer" onclick="showAlertDetail(${JSON.stringify(alert).replace(/"/g, '&quot;')})">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                ${getSeverityIcon(alert.severity)}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    ${getAlertTypeLabel(alert.type)}
                                </div>
                                <div class="text-sm text-gray-500">
                                    ${alert.message}
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            ${formatTime(alert.timestamp)}
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            alertsList.innerHTML = `
                <div class="px-4 py-8 text-center text-gray-500">
                    <div class="text-4xl mb-2">✅</div>
                    <div class="text-lg font-medium">Nenhum alerta ativo</div>
                    <div class="text-sm">Todas as atividades estão normais</div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading active alerts:', error);
        document.getElementById('alertsList').innerHTML = `
            <div class="px-4 py-4 text-center text-red-500">
                Erro ao carregar alertas
            </div>
        `;
    });
}

function loadUsersWithAlerts() {
    fetch('/admin/activity/users-with-alerts', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        const usersList = document.getElementById('usersWithAlerts');
        
        if (data.users && data.users.length > 0) {
            usersList.innerHTML = data.users.map(userAlert => `
                <div class="px-4 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        ${userAlert.user.name.charAt(0).toUpperCase()}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    ${userAlert.user.name}
                                </div>
                                <div class="text-sm text-gray-500">
                                    ${userAlert.user.email}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getRiskLevelClass(userAlert.risk_level)}">
                                ${getRiskLevelLabel(userAlert.risk_level)}
                            </span>
                            <span class="text-sm text-gray-500">
                                ${userAlert.activity_count} atividades
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            usersList.innerHTML = `
                <div class="px-4 py-8 text-center text-gray-500">
                    <div class="text-4xl mb-2">👥</div>
                    <div class="text-lg font-medium">Nenhum usuário com alertas</div>
                    <div class="text-sm">Todos os usuários estão com atividade normal</div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading users with alerts:', error);
        document.getElementById('usersWithAlerts').innerHTML = `
            <div class="px-4 py-4 text-center text-red-500">
                Erro ao carregar usuários
            </div>
        `;
    });
}

function processAlerts() {
    const button = document.getElementById('processAlerts');
    const originalText = button.textContent;
    button.textContent = '⏳ Processando...';
    button.disabled = true;

    fetch('/admin/activity/process-alerts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        // Refresh data after processing
        loadAlertStatistics();
        loadActiveAlerts();
        loadUsersWithAlerts();
        
        // Show success message
        showNotification('Alertas processados com sucesso!', 'success');
    })
    .catch(error => {
        console.error('Error processing alerts:', error);
        showNotification('Erro ao processar alertas', 'error');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

function showAlertDetail(alert) {
    document.getElementById('alertDetailTitle').textContent = getAlertTypeLabel(alert.type);
    document.getElementById('alertDetailContent').innerHTML = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Severidade</label>
                <div class="mt-1">
                    ${getSeverityBadge(alert.severity)}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Mensagem</label>
                <div class="mt-1 text-sm text-gray-900">${alert.message}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Dados</label>
                <div class="mt-1">
                    <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(alert.data, null, 2)}</pre>
                </div>
            </div>
        </div>
    `;
    document.getElementById('alertDetailModal').classList.remove('hidden');
}

function getSeverityIcon(severity) {
    const icons = {
        'high': '<span class="text-red-500 text-lg">🔥</span>',
        'medium': '<span class="text-yellow-500 text-lg">⚠️</span>',
        'low': '<span class="text-blue-500 text-lg">ℹ️</span>'
    };
    return icons[severity] || icons['low'];
}

function getSeverityBadge(severity) {
    const badges = {
        'high': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">🔥 Crítico</span>',
        'medium': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⚠️ Médio</span>',
        'low': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">ℹ️ Baixo</span>'
    };
    return badges[severity] || badges['low'];
}

function getAlertTypeLabel(type) {
    const labels = {
        'multiple_ip_logins': '🌐 Múltiplos IPs de Login',
        'unusual_time_login': '🕐 Login Fora de Horário',
        'rapid_logins': '⚡ Logins Rápidos',
        'excessive_page_views': '👁️ Visualizações Excessivas',
        'excessive_form_submissions': '📝 Envios Excessivos',
        'reactivated_user': '🔄 Usuário Reativado'
    };
    return labels[type] || type;
}

function getRiskLevelClass(level) {
    const classes = {
        'high': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800'
    };
    return classes[level] || classes['low'];
}

function getRiskLevelLabel(level) {
    const labels = {
        'high': 'Alto Risco',
        'medium': 'Médio Risco',
        'low': 'Baixo Risco'
    };
    return labels[level] || level;
}

function formatTime(timestamp) {
    return new Date(timestamp).toLocaleString('pt-BR');
}

function showNotification(message, type) {
    // Simple notification implementation
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
@endsection
