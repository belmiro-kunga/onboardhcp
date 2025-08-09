document.addEventListener('DOMContentLoaded', function() {
    const userId = window.location.pathname.split('/')[3]; // Extract user ID from URL
    let currentPage = 1;
    let currentFilters = {};

    // Load initial data
    loadUserActivityStats();
    loadUserActivities();

    // Event listeners
    document.getElementById('applyFilters').addEventListener('click', applyFilters);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    document.getElementById('exportUserActivity').addEventListener('click', exportUserActivity);
    document.getElementById('perPage').addEventListener('change', function() {
        currentPage = 1;
        loadUserActivities();
    });

    // Modal event listeners
    document.getElementById('closeActivityModal').addEventListener('click', closeActivityModal);
    document.getElementById('closeActivityModalBtn').addEventListener('click', closeActivityModal);

    function loadUserActivityStats() {
        fetch(`/admin/users/${userId}/activity/stats`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalActivities').textContent = data.stats.total_activities || 0;
                document.getElementById('totalLogins').textContent = data.stats.total_logins || 0;
                document.getElementById('totalPageViews').textContent = data.stats.total_page_views || 0;
                document.getElementById('totalFormSubmits').textContent = data.stats.total_form_submits || 0;
            }
        })
        .catch(error => {
            console.error('Error loading user activity stats:', error);
        });
    }

    function loadUserActivities(page = 1) {
        const perPage = document.getElementById('perPage').value;
        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            ...currentFilters
        });

        fetch(`/admin/users/${userId}/activity/data?${params}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderActivityTimeline(data.activities.data);
                renderPagination(data.activities);
            } else {
                showError('Erro ao carregar atividades: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading user activities:', error);
            showError('Erro ao carregar atividades');
        });
    }

    function renderActivityTimeline(activities) {
        const timeline = document.getElementById('activityTimeline');
        
        if (activities.length === 0) {
            timeline.innerHTML = `
                <div class="text-center py-8">
                    <div class="text-4xl mb-2">📭</div>
                    <div class="text-lg font-medium text-gray-900">Nenhuma atividade encontrada</div>
                    <div class="text-sm text-gray-500">Tente ajustar os filtros ou período</div>
                </div>
            `;
            return;
        }

        timeline.innerHTML = activities.map(activity => `
            <div class="flex items-start space-x-4 p-4 hover:bg-gray-50 rounded-lg cursor-pointer" onclick="showActivityDetail(${JSON.stringify(activity).replace(/"/g, '&quot;')})">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center ${getActivityColor(activity.activity_type)}">
                        <span class="text-lg">${getActivityIcon(activity.activity_type)}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">
                            ${getActivityLabel(activity.activity_type)}
                        </p>
                        <p class="text-sm text-gray-500">
                            ${formatDateTime(activity.created_at)}
                        </p>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        ${activity.description}
                    </p>
                    <div class="flex items-center mt-2 space-x-4 text-xs text-gray-500">
                        <span>🌐 ${activity.ip_address}</span>
                        <span>💻 ${activity.browser || 'N/A'}</span>
                        <span>📱 ${activity.device_type || 'N/A'}</span>
                        ${activity.url ? `<span>🔗 ${activity.url.length > 50 ? activity.url.substring(0, 50) + '...' : activity.url}</span>` : ''}
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>
        `).join('');
    }

    function renderPagination(paginationData) {
        const pagination = document.getElementById('pagination');
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationButtons = document.getElementById('paginationButtons');

        if (paginationData.last_page <= 1) {
            pagination.classList.add('hidden');
            return;
        }

        pagination.classList.remove('hidden');
        
        // Update pagination info
        paginationInfo.innerHTML = `
            Mostrando <span class="font-medium">${paginationData.from || 0}</span> a 
            <span class="font-medium">${paginationData.to || 0}</span> de 
            <span class="font-medium">${paginationData.total}</span> resultados
        `;

        // Generate pagination buttons
        let buttonsHtml = '';
        
        // Previous button
        if (paginationData.current_page > 1) {
            buttonsHtml += `
                <button onclick="loadUserActivities(${paginationData.current_page - 1})" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Anterior</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            `;
        }

        // Page numbers
        const startPage = Math.max(1, paginationData.current_page - 2);
        const endPage = Math.min(paginationData.last_page, paginationData.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === paginationData.current_page;
            buttonsHtml += `
                <button onclick="loadUserActivities(${i})" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                    isActive 
                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                }">
                    ${i}
                </button>
            `;
        }

        // Next button
        if (paginationData.current_page < paginationData.last_page) {
            buttonsHtml += `
                <button onclick="loadUserActivities(${paginationData.current_page + 1})" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Próximo</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            `;
        }

        paginationButtons.innerHTML = buttonsHtml;
    }

    function applyFilters() {
        currentFilters = {
            activity_type: document.getElementById('activityType').value,
            date_from: document.getElementById('dateFrom').value,
            date_to: document.getElementById('dateTo').value,
            ip_address: document.getElementById('ipAddress').value
        };

        // Remove empty filters
        Object.keys(currentFilters).forEach(key => {
            if (!currentFilters[key]) {
                delete currentFilters[key];
            }
        });

        currentPage = 1;
        loadUserActivities();
    }

    function clearFilters() {
        document.getElementById('activityType').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('ipAddress').value = '';
        
        currentFilters = {};
        currentPage = 1;
        loadUserActivities();
    }

    function exportUserActivity() {
        const params = new URLSearchParams(currentFilters);
        window.open(`/admin/users/${userId}/activity/export?${params}`, '_blank');
    }

    window.showActivityDetail = function(activity) {
        document.getElementById('activityDetailTitle').textContent = `${getActivityIcon(activity.activity_type)} ${getActivityLabel(activity.activity_type)}`;
        
        document.getElementById('activityDetailContent').innerHTML = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descrição</label>
                    <div class="mt-1 text-sm text-gray-900">${activity.description}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data/Hora</label>
                        <div class="mt-1 text-sm text-gray-900">${formatDateTime(activity.created_at)}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Endereço IP</label>
                        <div class="mt-1 text-sm text-gray-900">${activity.ip_address}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Navegador</label>
                        <div class="mt-1 text-sm text-gray-900">${activity.browser || 'N/A'}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dispositivo</label>
                        <div class="mt-1 text-sm text-gray-900">${activity.device_type || 'N/A'}</div>
                    </div>
                </div>
                ${activity.url ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">URL</label>
                        <div class="mt-1 text-sm text-gray-900 break-all">${activity.url}</div>
                    </div>
                ` : ''}
                ${activity.method ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Método HTTP</label>
                        <div class="mt-1 text-sm text-gray-900">${activity.method}</div>
                    </div>
                ` : ''}
                ${activity.session_id ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ID da Sessão</label>
                        <div class="mt-1 text-sm text-gray-900 font-mono text-xs">${activity.session_id}</div>
                    </div>
                ` : ''}
                ${activity.metadata ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dados Adicionais</label>
                        <div class="mt-1">
                            <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(JSON.parse(activity.metadata), null, 2)}</pre>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
        
        document.getElementById('activityDetailModal').classList.remove('hidden');
    }

    window.loadUserActivities = function(page) {
        currentPage = page;
        loadUserActivities(page);
    }

    function closeActivityModal() {
        document.getElementById('activityDetailModal').classList.add('hidden');
    }

    function getActivityIcon(type) {
        const icons = {
            'login': '🔐',
            'logout': '🚪',
            'page_view': '👁️',
            'form_submit': '📝',
            'search': '🔍',
            'export': '📊',
            'status_change': '🔄',
            'profile_update': '👤',
            'file_upload': '📤',
            'file_download': '📥'
        };
        return icons[type] || '📋';
    }

    function getActivityLabel(type) {
        const labels = {
            'login': 'Login',
            'logout': 'Logout',
            'page_view': 'Visualização de Página',
            'form_submit': 'Envio de Formulário',
            'search': 'Pesquisa',
            'export': 'Exportação',
            'status_change': 'Mudança de Status',
            'profile_update': 'Atualização de Perfil',
            'file_upload': 'Upload de Arquivo',
            'file_download': 'Download de Arquivo'
        };
        return labels[type] || type;
    }

    function getActivityColor(type) {
        const colors = {
            'login': 'bg-green-100 text-green-600',
            'logout': 'bg-red-100 text-red-600',
            'page_view': 'bg-blue-100 text-blue-600',
            'form_submit': 'bg-purple-100 text-purple-600',
            'search': 'bg-yellow-100 text-yellow-600',
            'export': 'bg-indigo-100 text-indigo-600',
            'status_change': 'bg-orange-100 text-orange-600',
            'profile_update': 'bg-pink-100 text-pink-600',
            'file_upload': 'bg-teal-100 text-teal-600',
            'file_download': 'bg-cyan-100 text-cyan-600'
        };
        return colors[type] || 'bg-gray-100 text-gray-600';
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    function showError(message) {
        const timeline = document.getElementById('activityTimeline');
        timeline.innerHTML = `
            <div class="text-center py-8">
                <div class="text-4xl mb-2">❌</div>
                <div class="text-lg font-medium text-red-900">${message}</div>
                <button onclick="loadUserActivities()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-500">
                    Tentar novamente
                </button>
            </div>
        `;
    }
});
