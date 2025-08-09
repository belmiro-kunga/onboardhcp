<x-admin-layout title="Utilizadores" active-menu="users" page-title="Utilizadores">
    <x-slot name="styles">
        <style>
            .status-badge {
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 500;
            }
            
            .status-active {
                background-color: #E6F7FF;
                color: var(--info);
            }
            
            .role-admin {
                background-color: #F6FFED;
                color: var(--success);
            }
            
            .role-user {
                background-color: #FFF7E6;
                color: var(--warning);
            }
            
            /* Following Simulados Button Pattern */
            .users-btn-primary {
                background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 1rem;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
                display: flex;
                align-items: center;
            }
            
            .users-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(251, 191, 36, 0.4);
            }
            
            .users-btn-secondary {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
                color: #0f172a;
                padding: 0.75rem 1.5rem;
                border-radius: 1rem;
                font-weight: 600;
                border: 1px solid rgba(255, 255, 255, 0.3);
                cursor: pointer;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
                display: flex;
                align-items: center;
            }
            
            .users-btn-secondary:hover {
                transform: translateY(-2px);
                background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0.9) 100%);
            }

            /* Filter Tags */
            .filter-tag {
                display: inline-flex;
                align-items: center;
                padding: 0.5rem 0.75rem;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
                border: 1px solid rgba(0, 0, 0, 0.1);
                border-radius: 0.75rem;
                font-size: 0.875rem;
                font-weight: 500;
                color: #374151;
                cursor: pointer;
                transition: all 0.2s ease;
                backdrop-filter: blur(10px);
            }

            .filter-tag:hover {
                transform: translateY(-1px);
                background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0.9) 100%);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .filter-tag.active {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: white;
                border-color: #2563eb;
            }

            .filter-tag.active:hover {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            }

            /* Active Filter Tags */
            .active-filter-tag {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.5rem;
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
                border-radius: 0.5rem;
                font-size: 0.75rem;
                font-weight: 500;
            }

            .active-filter-tag button {
                margin-left: 0.25rem;
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                padding: 0;
                display: flex;
                align-items: center;
            }

            /* Search Suggestions */
            .suggestion-item {
                padding: 0.75rem;
                cursor: pointer;
                border-bottom: 1px solid #f3f4f6;
                transition: background-color 0.2s ease;
            }

            .suggestion-item:hover {
                background-color: #f9fafb;
            }

            .suggestion-item:last-child {
                border-bottom: none;
            }

            /* Loading States */
            .loading {
                opacity: 0.6;
                pointer-events: none;
            }

            .loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* Sortable Headers */
            .sortable-header {
                cursor: pointer;
                user-select: none;
                position: relative;
                transition: background-color 0.2s ease;
            }

            .sortable-header:hover {
                background-color: #f9fafb;
            }

            .sortable-header.sorted-asc::after {
                content: '↑';
                position: absolute;
                right: 0.5rem;
                color: #3b82f6;
                font-weight: bold;
            }

            .sortable-header.sorted-desc::after {
                content: '↓';
                position: absolute;
                right: 0.5rem;
                color: #3b82f6;
                font-weight: bold;
            }

            /* Pagination */
            .pagination-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 1rem;
                padding: 1rem 0;
                border-top: 1px solid #e5e7eb;
            }

            .pagination-info {
                color: #6b7280;
                font-size: 0.875rem;
            }

            .pagination-controls {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .pagination-btn {
                padding: 0.5rem 0.75rem;
                border: 1px solid #d1d5db;
                background: white;
                color: #374151;
                border-radius: 0.375rem;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .pagination-btn:hover:not(:disabled) {
                background: #f9fafb;
                border-color: #9ca3af;
            }

            .pagination-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .pagination-btn.active {
                background: #3b82f6;
                color: white;
                border-color: #3b82f6;
            }

            /* Responsive Improvements */
            @media (max-width: 768px) {
                .users-stat-card {
                    padding: 1rem;
                }
                
                .filter-tag {
                    padding: 0.375rem 0.625rem;
                    font-size: 0.8rem;
                }
                
                .users-btn-primary, .users-btn-secondary {
                    padding: 0.625rem 1.25rem;
                    font-size: 0.9rem;
                }
            }
            
            @media (max-width: 640px) {
                .users-btn-primary, .users-btn-secondary {
                    padding: 0.5rem 1rem;
                    font-size: 0.875rem;
                }
                
                .filter-tag {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.75rem;
                }
                
                .card {
                    padding: 1rem;
                    margin: 0.5rem;
                }
                
                .input-field {
                    padding: 0.5rem 0.75rem;
                }
                
                .users-stat-card {
                    padding: 0.875rem;
                }
                
                .pagination-container {
                    flex-direction: column;
                    gap: 1rem;
                    text-align: center;
                }
                
                .pagination-controls {
                    justify-content: center;
                }
            }
            
            /* Avatar responsive sizing */
            .avatar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-weight: 600;
            }
            
            /* Users Stat Cards */
            .users-stat-card {
                padding: 1.5rem;
                border-radius: 1rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .users-stat-card:hover {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            
            /* Mobile card improvements */
            @media (max-width: 1024px) {
                .mobile-card {
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
                    border: 1px solid #e5e7eb;
                }
            }
        </style>
    </x-slot>

    <!-- Enhanced Page Header - Responsive -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-4 sm:p-6 mb-6 sm:mb-8 text-white shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="flex-1">
                <div class="flex items-center mb-2">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight">👥 Gestão de Usuários</h2>
                </div>
                <p class="text-emerald-100 text-sm sm:text-base lg:text-lg">Gerencie usuários, roles e permissões com ferramentas avançadas</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 lg:space-x-4">
                <button onclick="openBulkStatusModal()" class="users-btn-secondary group w-full sm:w-auto" id="bulkStatusBtn" style="display: none;">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="hidden sm:inline">🔄 Status em Massa</span>
                    <span class="sm:hidden">🔄 Status</span>
                </button>
                <button onclick="openExportModal()" class="users-btn-secondary group w-full sm:w-auto">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="hidden sm:inline">📊 Exportar</span>
                    <span class="sm:hidden">📊 Export</span>
                </button>
                <button onclick="openImportModal()" class="users-btn-secondary group w-full sm:w-auto">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    <span class="hidden sm:inline">📥 Importar</span>
                    <span class="sm:hidden">📥 Import</span>
                </button>
                <button onclick="openNewUserModal()" class="users-btn-primary group w-full sm:w-auto">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="hidden sm:inline">➕ Novo Usuário</span>
                    <span class="sm:hidden">➕ Novo</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards - Responsive Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Total Users - Following Simulados Card Pattern -->
        <div class="users-stat-card bg-gradient-to-br from-emerald-500 to-emerald-600 text-white transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-emerald-100 text-sm font-medium">Total de Usuários</p>
                    <p class="text-emerald-200 text-xs">Usuários cadastrados</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-3xl font-bold counter" data-target="{{ $users->count() }}">0</span>
                <span class="ml-2 text-sm bg-white/20 px-2 py-1 rounded-full">👥</span>
            </div>
        </div>

        <!-- Admins - Following Simulados Card Pattern -->
        <div class="users-stat-card bg-gradient-to-br from-purple-500 to-purple-600 text-white transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Administradores</p>
                    <p class="text-purple-200 text-xs">Usuários com acesso admin</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-3xl font-bold counter" data-target="{{ $users->where('is_admin', true)->count() }}">0</span>
                <span class="ml-2 text-sm bg-white/20 px-2 py-1 rounded-full">🛡️</span>
            </div>
        </div>

        <!-- Employees - Following Simulados Card Pattern -->
        <div class="users-stat-card bg-gradient-to-br from-blue-500 to-blue-600 text-white transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Funcionários</p>
                    <p class="text-blue-200 text-xs">Usuários funcionários</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-3xl font-bold counter" data-target="{{ $users->where('is_admin', false)->count() }}">0</span>
                <span class="ml-2 text-sm bg-white/20 px-2 py-1 rounded-full">💼</span>
            </div>
        </div>

        <!-- Status - Following Simulados Card Pattern -->
        <div class="users-stat-card bg-gradient-to-br from-green-500 to-green-600 text-white transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-green-100 text-sm font-medium">Status do Sistema</p>
                    <p class="text-green-200 text-xs">Usuários ativos</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-3xl font-bold counter" data-target="{{ $users->where('status', 'active')->count() }}">0</span>
                <span class="ml-2 text-sm bg-white/20 px-2 py-1 rounded-full">✅</span>
            </div>
        </div>
    </div>

    <!-- Advanced Search and Filters - Responsive -->
    <div class="card mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 space-y-3 sm:space-y-0">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">🔍 Pesquisa e Filtros</h3>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <button id="clearFiltersBtn" class="users-btn-secondary text-sm py-2 px-3 w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Limpar
                </button>
                <button id="saveFiltersBtn" class="users-btn-secondary text-sm py-2 px-3 w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <span class="hidden sm:inline">Guardar</span>
                    <span class="sm:hidden">Save</span>
                </button>
                <button id="toggleAdvancedBtn" class="users-btn-primary text-sm py-2 px-3 w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                    <span class="hidden sm:inline">Filtros Avançados</span>
                    <span class="sm:hidden">Filtros</span>
                </button>
            </div>
        </div>

        <!-- Main Search Bar - Responsive -->
        <div class="mb-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="searchInput" 
                       placeholder="🔍 Pesquisar utilizadores..." 
                       class="input-field pl-10 pr-4 text-sm sm:text-base" autocomplete="off">
                <div id="searchSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto"></div>
            </div>
        </div>

        <!-- Quick Filters - Responsive -->
        <div class="flex flex-wrap gap-1 sm:gap-2 mb-4">
            <button class="filter-tag" data-filter="status" data-value="active">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                Ativos
            </button>
            <button class="filter-tag" data-filter="status" data-value="inactive">
                <span class="w-2 h-2 bg-red-500 rounded-full mr-1"></span>
                Inativos
            </button>
            <button class="filter-tag" data-filter="user_type" data-value="admin">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Administradores
            </button>
            <button class="filter-tag" data-filter="user_type" data-value="employee">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                </svg>
                Funcionários
            </button>
            <button class="filter-tag" data-filter="activity" data-value="recent">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recentes
            </button>
        </div>

        <!-- Advanced Filters Panel - Responsive -->
        <div id="advancedFilters" class="hidden border-t pt-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter" class="input-field" multiple>
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                        <option value="pending">Pendente</option>
                        <option value="blocked">Bloqueado</option>
                        <option value="suspended">Suspenso</option>
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                    <select id="departmentFilter" class="input-field" multiple>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="roleFilter" class="input-field" multiple>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>

                <!-- Activity Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Atividade</label>
                    <select id="activityFilter" class="input-field">
                        <option value="">Todos</option>
                        <option value="active">Ativos (últimos 30 dias)</option>
                        <option value="recent">Recentes (últimos 7 dias)</option>
                        <option value="inactive">Inativos</option>
                        <option value="never_logged">Nunca fizeram login</option>
                    </select>
                </div>

                <!-- Date Range Filters -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data de Criação (De)</label>
                    <input type="date" id="createdFromFilter" class="input-field">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data de Criação (Até)</label>
                    <input type="date" id="createdToFilter" class="input-field">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data de Admissão (De)</label>
                    <input type="date" id="hireDateFromFilter" class="input-field">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data de Admissão (Até)</label>
                    <input type="date" id="hireDateToFilter" class="input-field">
                </div>
            </div>
        </div>

        <!-- Active Filters Display -->
        <div id="activeFilters" class="mt-4 hidden">
            <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-700">Filtros ativos:</span>
                <div id="activeFilterTags" class="flex flex-wrap gap-1"></div>
            </div>
        </div>
    </div>

    <!-- Users Table - Responsive -->
    <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-2 sm:space-y-0">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Lista de Utilizadores</h3>
            <p class="text-xs sm:text-sm text-gray-500">{{ $users->count() }} utilizador(s) encontrado(s)</p>
        </div>

        <!-- Mobile Card Layout (Hidden on Desktop) -->
        <div class="block lg:hidden space-y-4">
            @foreach($users as $user)
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center">
                        @if($user->avatar)
                            <img class="w-12 h-12 rounded-full object-cover mr-3" 
                                 src="{{ asset('storage/' . $user->avatar) }}" 
                                 alt="{{ $user->name }}">
                        @else
                            <div class="avatar mr-3 w-12 h-12 text-lg">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">{{ $user->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-1">
                        <button onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->birth_date }}', '{{ $user->phone }}', '{{ $user->department }}', '{{ $user->position }}', '{{ $user->hire_date }}', '{{ $user->status }}', {{ $user->is_admin ? 'true' : 'false' }})" 
                                class="text-blue-600 hover:text-blue-800 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="font-medium text-gray-600">Cargo:</span>
                        <span class="text-gray-900">{{ $user->position ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Depto:</span>
                        <span class="text-gray-900">{{ $user->department ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Telefone:</span>
                        <span class="text-gray-900">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Status:</span>
                        @php
                            $statusClass = match($user->status ?? 'active') {
                                'active' => 'bg-green-100 text-green-800',
                                'inactive' => 'bg-red-100 text-red-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'blocked' => 'bg-red-100 text-red-800',
                                'suspended' => 'bg-orange-100 text-orange-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $statusText = match($user->status ?? 'active') {
                                'active' => '✅ Ativo',
                                'inactive' => '❌ Inativo',
                                'pending' => '⏳ Pendente',
                                'blocked' => '🚫 Bloqueado',
                                'suspended' => '⏸️ Suspenso',
                                default => 'Desconhecido'
                            };
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>Tipo: 
                            @if($user->is_admin)
                                <span class="text-green-600 font-medium">👑 Admin</span>
                            @else
                                <span class="text-blue-600 font-medium">👤 Funcionário</span>
                            @endif
                        </span>
                        <span>{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Desktop Table Layout (Hidden on Mobile) -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable-header" data-sort="name">
                            Nome
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable-header" data-sort="email">
                            Email
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable-header" data-sort="department">
                            Departamento
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable-header" data-sort="position">
                            Cargo
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable-header" data-sort="phone">
                            Telefone
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable-header" data-sort="status">
                            Status
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable-header" data-sort="created_at">
                            Criado em
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($user->avatar)
                                    <img class="w-8 h-8 rounded-full object-cover mr-2" 
                                         src="{{ asset('storage/' . $user->avatar) }}" 
                                         alt="{{ $user->name }}">
                                @else
                                    <div class="avatar mr-2 w-8 h-8 text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</div>
                                    @if($user->position)
                                        <div class="text-xs text-gray-500 truncate">{{ $user->position }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                            <div class="truncate max-w-xs">{{ $user->email }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            @if($user->is_admin)
                                <span class="status-badge role-admin text-xs">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Admin
                                </span>
                            @else
                                <span class="status-badge role-user text-xs">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Func.
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                            <div class="truncate max-w-24">{{ $user->department ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                            <div class="truncate max-w-24">{{ $user->position ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->phone ?? '-' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            @php
                                $statusClass = match($user->status ?? 'active') {
                                    'active' => 'status-badge bg-green-100 text-green-800',
                                    'inactive' => 'status-badge bg-red-100 text-red-800',
                                    'pending' => 'status-badge bg-yellow-100 text-yellow-800',
                                    'blocked' => 'status-badge bg-red-100 text-red-800',
                                    'suspended' => 'status-badge bg-orange-100 text-orange-800',
                                    default => 'status-badge bg-green-100 text-green-800'
                                };
                                
                                $statusLabel = match($user->status ?? 'active') {
                                    'active' => '✅',
                                    'inactive' => '❌',
                                    'pending' => '⏳',
                                    'blocked' => '🚫',
                                    'suspended' => '⏸️',
                                    default => '✅'
                                };
                            @endphp
                            <span class="{{ $statusClass }} text-xs">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at ? $user->created_at->format('d/m/Y') : '07/08/2025' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-1">
                                <button onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->birth_date }}', '{{ $user->phone ?? '' }}', '{{ $user->department ?? '' }}', '{{ $user->position ?? '' }}', '{{ $user->hire_date ?? '' }}', '{{ $user->status ?? 'active' }}', {{ $user->is_admin ? 1 : 0 }})" 
                                        class="text-blue-600 hover:text-blue-900 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- New User Modal - Improved Responsive Layout -->
    <div id="newUserModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-t-2xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <h3 class="text-2xl font-bold">Novo Utilizador</h3>
                    </div>
                    <button type="button" onclick="closeNewUserModal()" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-emerald-100 mt-2">Preencha os dados para criar um novo utilizador no sistema</p>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <form method="POST" action="{{ route('admin.users.create') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Personal Information Section -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Informações Pessoais
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                                <input type="text" name="name" class="input-field" required placeholder="Ex: João Silva">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
                                <input type="email" name="email" class="input-field" required placeholder="joao.silva@empresa.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento *</label>
                                <input type="date" name="birth_date" class="input-field" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                                <input type="tel" name="phone" class="input-field" placeholder="+351 912 345 678">
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information Section -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h8z"></path>
                            </svg>
                            Informações Profissionais
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                                <select name="department" class="input-field">
                                    <option value="">Selecionar departamento...</option>
                                    <option value="Recursos Humanos">🧑‍💼 Recursos Humanos</option>
                                    <option value="Tecnologia">💻 Tecnologia</option>
                                    <option value="Financeiro">💰 Financeiro</option>
                                    <option value="Marketing">📢 Marketing</option>
                                    <option value="Vendas">📊 Vendas</option>
                                    <option value="Operações">⚙️ Operações</option>
                                    <option value="Jurídico">⚖️ Jurídico</option>
                                    <option value="Administração">📋 Administração</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cargo</label>
                                <input type="text" name="position" class="input-field" placeholder="Ex: Analista, Gestor, Diretor...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Contratação</label>
                                <input type="date" name="hire_date" class="input-field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" class="input-field">
                                    <option value="active">✅ Ativo</option>
                                    <option value="inactive">❌ Inativo</option>
                                    <option value="pending">⏳ Pendente</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- System Access Section -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Acesso ao Sistema
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Palavra-passe *</label>
                                <input type="password" name="password" class="input-field" required placeholder="Mínimo 6 caracteres">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Utilizador</label>
                                <select name="is_admin" class="input-field">
                                    <option value="0">👤 Funcionário</option>
                                    <option value="1">👑 Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Photo Section -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Foto de Perfil
                        </h4>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-emerald-400 transition-colors">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <input type="file" name="avatar" class="input-field" accept="image/*" id="avatarInput">
                            <p class="text-sm text-gray-500 mt-2">Formatos aceites: JPG, PNG, GIF (máx. 2MB)</p>
                            <p class="text-xs text-gray-400">Arraste e solte ou clique para selecionar</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeNewUserModal()" class="btn-secondary w-full sm:w-auto px-6 py-3">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary w-full sm:w-auto px-6 py-3">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Criar Utilizador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal - Improved Responsive Layout -->
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-2xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <h3 class="text-2xl font-bold">Editar Utilizador</h3>
                    </div>
                    <button type="button" onclick="closeEditUserModal()" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-blue-100 mt-2">Atualize as informações do utilizador conforme necessário</p>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <form id="editUserForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Personal Information Section -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Informações Pessoais
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                                <input type="text" name="name" id="edit_name" class="input-field" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
                                <input type="email" name="email" id="edit_email" class="input-field" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento *</label>
                                <input type="date" name="birth_date" id="edit_birth_date" class="input-field" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                                <input type="tel" name="phone" id="edit_phone" class="input-field" placeholder="+351 912 345 678">
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information Section -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h8z"></path>
                            </svg>
                            Informações Profissionais
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                                <select name="department" id="edit_department" class="input-field">
                                    <option value="">Selecionar departamento...</option>
                                    <option value="Recursos Humanos">🧑‍💼 Recursos Humanos</option>
                                    <option value="Tecnologia">💻 Tecnologia</option>
                                    <option value="Financeiro">💰 Financeiro</option>
                                    <option value="Marketing">📢 Marketing</option>
                                    <option value="Vendas">📊 Vendas</option>
                                    <option value="Operações">⚙️ Operações</option>
                                    <option value="Jurídico">⚖️ Jurídico</option>
                                    <option value="Administração">📋 Administração</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cargo</label>
                                <input type="text" name="position" id="edit_position" class="input-field" placeholder="Ex: Analista, Gestor, Diretor...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Contratação</label>
                                <input type="date" name="hire_date" id="edit_hire_date" class="input-field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="edit_status" class="input-field">
                                    <option value="active">✅ Ativo</option>
                                    <option value="inactive">❌ Inativo</option>
                                    <option value="pending">⏳ Pendente</option>
                                    <option value="blocked">🚫 Bloqueado</option>
                                    <option value="suspended">⏸️ Suspenso</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- System Access Section -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Acesso ao Sistema
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nova Palavra-passe (opcional)</label>
                                <input type="password" name="password" class="input-field" placeholder="Deixe em branco para manter a atual">
                                <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres se preenchido</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Utilizador</label>
                                <select name="is_admin" id="edit_is_admin" class="input-field">
                                    <option value="0">👤 Funcionário</option>
                                    <option value="1">👑 Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Photo Section -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Foto de Perfil
                        </h4>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <input type="file" name="avatar" class="input-field" accept="image/*" id="editAvatarInput">
                            <p class="text-sm text-gray-500 mt-2">Formatos aceites: JPG, PNG, GIF (máx. 2MB)</p>
                            <p class="text-xs text-gray-400">Deixe em branco para manter a foto atual</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeEditUserModal()" class="btn-secondary w-full sm:w-auto px-6 py-3">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary w-full sm:w-auto px-6 py-3">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Atualizar Utilizador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <x-slot name="scripts">
        <script>
            // Advanced Search and Filter System
            class UserSearchManager {
                constructor() {
                    this.currentFilters = {};
                    this.currentSort = { field: 'created_at', direction: 'desc' };
                    this.currentPage = 1;
                    this.searchTimeout = null;
                    this.init();
                }

                init() {
                    this.bindEvents();
                    this.loadFilterOptions();
                    this.performSearch(); // Initial load
                }

                bindEvents() {
                    // Search input with debounce
                    const searchInput = document.getElementById('searchInput');
                    searchInput.addEventListener('input', (e) => {
                        clearTimeout(this.searchTimeout);
                        this.searchTimeout = setTimeout(() => {
                            this.currentFilters.search = e.target.value;
                            this.currentPage = 1;
                            this.performSearch();
                        }, 300);
                    });

                    // Quick filter tags
                    document.querySelectorAll('.filter-tag').forEach(tag => {
                        tag.addEventListener('click', (e) => {
                            const filter = e.currentTarget.dataset.filter;
                            const value = e.currentTarget.dataset.value;
                            this.toggleQuickFilter(filter, value, e.currentTarget);
                        });
                    });

                    // Advanced filters toggle
                    document.getElementById('toggleAdvancedBtn').addEventListener('click', () => {
                        this.toggleAdvancedFilters();
                    });

                    // Clear filters
                    document.getElementById('clearFiltersBtn').addEventListener('click', () => {
                        this.clearAllFilters();
                    });

                    // Save filters
                    document.getElementById('saveFiltersBtn').addEventListener('click', () => {
                        this.saveCurrentFilters();
                    });

                    // Advanced filter inputs
                    ['statusFilter', 'departmentFilter', 'roleFilter', 'activityFilter',
                     'createdFromFilter', 'createdToFilter', 'hireDateFromFilter', 'hireDateToFilter'].forEach(id => {
                        const element = document.getElementById(id);
                        if (element) {
                            element.addEventListener('change', () => {
                                this.updateAdvancedFilters();
                            });
                        }
                    });

                    // Sortable headers
                    document.querySelectorAll('.sortable-header').forEach(header => {
                        header.addEventListener('click', (e) => {
                            const field = e.currentTarget.dataset.sort;
                            this.toggleSort(field);
                        });
                    });
                }

                async loadFilterOptions() {
                    try {
                        const response = await fetch('/admin/users/search/filter-options', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        // Check if response is actually JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            console.warn('Filter options endpoint returned non-JSON response, using fallback options');
                            this.useFallbackFilterOptions();
                            return;
                        }
                        
                        const options = await response.json();
                        
                        // Validate response structure
                        if (options && typeof options === 'object') {
                            this.populateSelectOptions('departmentFilter', options.departments || []);
                            this.populateSelectOptions('roleFilter', options.roles || []);
                        } else {
                            throw new Error('Invalid response structure');
                        }
                    } catch (error) {
                        console.error('Error loading filter options:', error);
                        console.warn('Using fallback filter options due to error');
                        this.useFallbackFilterOptions();
                    }
                }
                
                useFallbackFilterOptions() {
                    // Fallback departments and roles if API fails
                    const fallbackDepartments = [
                        'Recursos Humanos', 'Tecnologia', 'Financeiro', 
                        'Marketing', 'Vendas', 'Jurídico', 'Administração'
                    ];
                    
                    const fallbackRoles = [
                        'admin', 'user', 'manager', 'employee'
                    ];
                    
                    this.populateSelectOptions('departmentFilter', fallbackDepartments);
                    this.populateSelectOptions('roleFilter', fallbackRoles);
                }

                populateSelectOptions(selectId, options) {
                    const select = document.getElementById(selectId);
                    if (!select) return;
                    
                    select.innerHTML = '';
                    options.forEach(option => {
                        const optionElement = document.createElement('option');
                        optionElement.value = option;
                        optionElement.textContent = option;
                        select.appendChild(optionElement);
                    });
                }

                toggleQuickFilter(filter, value, element) {
                    if (element.classList.contains('active')) {
                        // Remove filter
                        if (Array.isArray(this.currentFilters[filter])) {
                            this.currentFilters[filter] = this.currentFilters[filter].filter(v => v !== value);
                            if (this.currentFilters[filter].length === 0) {
                                delete this.currentFilters[filter];
                            }
                        } else {
                            delete this.currentFilters[filter];
                        }
                        element.classList.remove('active');
                    } else {
                        // Add filter
                        if (this.currentFilters[filter]) {
                            if (Array.isArray(this.currentFilters[filter])) {
                                this.currentFilters[filter].push(value);
                            } else {
                                this.currentFilters[filter] = [this.currentFilters[filter], value];
                            }
                        } else {
                            this.currentFilters[filter] = value;
                        }
                        element.classList.add('active');
                    }
                    
                    this.currentPage = 1;
                    this.performSearch();
                    this.updateActiveFiltersDisplay();
                }

                toggleAdvancedFilters() {
                    const panel = document.getElementById('advancedFilters');
                    const button = document.getElementById('toggleAdvancedBtn');
                    
                    if (panel.classList.contains('hidden')) {
                        panel.classList.remove('hidden');
                        button.innerHTML = button.innerHTML.replace('Filtros Avançados', 'Ocultar Filtros');
                    } else {
                        panel.classList.add('hidden');
                        button.innerHTML = button.innerHTML.replace('Ocultar Filtros', 'Filtros Avançados');
                    }
                }

                updateAdvancedFilters() {
                    const filters = {
                        status: this.getMultiSelectValues('statusFilter'),
                        department: this.getMultiSelectValues('departmentFilter'),
                        role: this.getMultiSelectValues('roleFilter'),
                        activity: document.getElementById('activityFilter').value,
                        created_from: document.getElementById('createdFromFilter').value,
                        created_to: document.getElementById('createdToFilter').value,
                        hire_date_from: document.getElementById('hireDateFromFilter').value,
                        hire_date_to: document.getElementById('hireDateToFilter').value
                    };

                    // Update current filters
                    Object.keys(filters).forEach(key => {
                        if (filters[key] && filters[key].length > 0) {
                            this.currentFilters[key] = filters[key];
                        } else {
                            delete this.currentFilters[key];
                        }
                    });

                    this.currentPage = 1;
                    this.performSearch();
                    this.updateActiveFiltersDisplay();
                }

                getMultiSelectValues(selectId) {
                    const select = document.getElementById(selectId);
                    if (!select) return [];
                    
                    return Array.from(select.selectedOptions).map(option => option.value);
                }

                toggleSort(field) {
                    if (this.currentSort.field === field) {
                        this.currentSort.direction = this.currentSort.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.currentSort.field = field;
                        this.currentSort.direction = 'asc';
                    }
                    
                    this.updateSortHeaders();
                    this.performSearch();
                }

                updateSortHeaders() {
                    document.querySelectorAll('.sortable-header').forEach(header => {
                        header.classList.remove('sorted-asc', 'sorted-desc');
                        if (header.dataset.sort === this.currentSort.field) {
                            header.classList.add(`sorted-${this.currentSort.direction}`);
                        }
                    });
                }

                clearAllFilters() {
                    this.currentFilters = {};
                    this.currentPage = 1;
                    
                    // Clear UI
                    document.getElementById('searchInput').value = '';
                    document.querySelectorAll('.filter-tag').forEach(tag => {
                        tag.classList.remove('active');
                    });
                    
                    // Clear advanced filters
                    document.querySelectorAll('#advancedFilters select, #advancedFilters input').forEach(input => {
                        if (input.type === 'date') {
                            input.value = '';
                        } else {
                            input.selectedIndex = -1;
                        }
                    });
                    
                    this.performSearch();
                    this.updateActiveFiltersDisplay();
                }

                async saveCurrentFilters() {
                    const name = prompt('Nome para esta configuração de filtros:');
                    if (!name) return;
                    
                    try {
                        const response = await fetch('/admin/users/search/save-config', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                name: name,
                                filters: this.currentFilters
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            alert('Configuração guardada com sucesso!');
                        } else {
                            alert('Erro ao guardar configuração: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error saving filters:', error);
                        alert('Erro ao guardar configuração');
                    }
                }

                async performSearch() {
                    const searchParams = {
                        ...this.currentFilters,
                        sort_by: this.currentSort.field,
                        sort_direction: this.currentSort.direction,
                        page: this.currentPage,
                        per_page: 25
                    };

                    try {
                        // Show loading state
                        this.setLoadingState(true);
                        
                        const response = await fetch('/admin/users/search?' + new URLSearchParams(searchParams));
                        const data = await response.json();
                        
                        if (data.success) {
                            this.updateUserTable(data.data.users);
                            this.updatePagination(data.data.pagination);
                            this.updateStatistics(data.data.statistics);
                        } else {
                            console.error('Search failed:', data.message);
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                    } finally {
                        this.setLoadingState(false);
                    }
                }

                updateUserTable(users) {
                    const tbody = document.querySelector('table tbody');
                    if (!tbody) return;
                    
                    tbody.innerHTML = users.map(user => this.renderUserRow(user)).join('');
                }

                renderUserRow(user) {
                    const roleBadge = user.is_admin
                        ? '<span class="status-badge role-admin text-xs"><svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>Admin</span>'
                        : '<span class="status-badge role-user text-xs"><svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>Func.</span>';

                    const statusMap = {
                        active: { cls: 'status-badge bg-green-100 text-green-800', label: '✅' },
                        inactive: { cls: 'status-badge bg-red-100 text-red-800', label: '❌' },
                        pending: { cls: 'status-badge bg-yellow-100 text-yellow-800', label: '⏳' },
                        blocked: { cls: 'status-badge bg-red-100 text-red-800', label: '🚫' },
                        suspended: { cls: 'status-badge bg-orange-100 text-orange-800', label: '⏸️' },
                    };
                    const st = statusMap[user.status || 'active'] || statusMap.active;
                    const statusBadge = `<span class="${st.cls} text-xs">${st.label}</span>`;

                    const safe = s => (s ?? '').toString().replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                    return `
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="avatar mr-2 w-8 h-8 text-sm">${safe(user.name).charAt(0).toUpperCase()}</div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-medium text-gray-900 truncate">${safe(user.name)}</div>
                                        ${user.position ? `<div class=\"text-xs text-gray-500 truncate\">${safe(user.position)}</div>` : ''}
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><div class="truncate max-w-xs">${safe(user.email)}</div></td>
                            <td class="px-3 py-3 whitespace-nowrap">${roleBadge}</td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><div class="truncate max-w-24">${safe(user.department) || '-'}</div></td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><div class="truncate max-w-24">${safe(user.position) || '-'}</div></td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">${safe(user.phone) || '-'}</td>
                            <td class="px-3 py-3 whitespace-nowrap">${statusBadge}</td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">${this.formatDate(user.created_at)}</td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-1">
                                    <button onclick="changeUserStatus(${user.id}, '${safe(user.name)}', '${safe(user.status || 'active')}')" class="text-green-600 hover:text-green-900 p-1" title="Alterar Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                    <button onclick="editUser(${user.id}, '${safe(user.name)}', '${safe(user.email)}', '${safe(user.birth_date || '')}', '${safe(user.phone || '')}', '${safe(user.department || '')}', '${safe(user.position || '')}', '${safe(user.hire_date || '')}', '${safe(user.status || 'active')}', ${user.is_admin ? 1 : 0})" class="text-blue-600 hover:text-blue-900 p-1" title="Editar Usuário">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-900 p-1" title="Excluir Usuário">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }

                updatePagination(pagination) {
                    const resultText = document.querySelector('.card h3 + p');
                    if (resultText) {
                        resultText.textContent = `${pagination.total} utilizador(s) encontrado(s)`;
                    }
                }

                updateStatistics(statistics) {
                    // Update counter animations if needed
                    document.querySelectorAll('.counter').forEach(counter => {
                        const target = parseInt(counter.dataset.target);
                        this.animateCounter(counter, target);
                    });
                }

                updateActiveFiltersDisplay() {
                    const activeFiltersDiv = document.getElementById('activeFilters');
                    const activeFilterTags = document.getElementById('activeFilterTags');
                    
                    const filterCount = Object.keys(this.currentFilters).length;
                    
                    if (filterCount > 0) {
                        activeFiltersDiv.classList.remove('hidden');
                        activeFilterTags.innerHTML = Object.entries(this.currentFilters)
                            .map(([key, value]) => this.renderActiveFilterTag(key, value))
                            .join('');
                    } else {
                        activeFiltersDiv.classList.add('hidden');
                    }
                }

                renderActiveFilterTag(key, value) {
                    const displayValue = Array.isArray(value) ? value.join(', ') : value;
                    const displayKey = this.getFilterDisplayName(key);
                    
                    return `
                        <span class="active-filter-tag">
                            ${displayKey}: ${displayValue}
                            <button onclick="userSearchManager.removeActiveFilter('${key}')">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </span>
                    `;
                }

                getFilterDisplayName(key) {
                    const names = {
                        search: 'Pesquisa',
                        status: 'Status',
                        user_type: 'Tipo',
                        role: 'Role',
                        department: 'Departamento',
                        activity: 'Atividade',
                        created_from: 'Criado após',
                        created_to: 'Criado antes',
                        hire_date_from: 'Admitido após',
                        hire_date_to: 'Admitido antes'
                    };
                    return names[key] || key;
                }

                removeActiveFilter(key) {
                    delete this.currentFilters[key];
                    this.currentPage = 1;
                    this.performSearch();
                    this.updateActiveFiltersDisplay();
                    
                    // Update UI elements
                    if (key === 'search') {
                        document.getElementById('searchInput').value = '';
                    }
                    
                    // Update quick filter tags
                    document.querySelectorAll('.filter-tag').forEach(tag => {
                        if (tag.dataset.filter === key) {
                            tag.classList.remove('active');
                        }
                    });
                }

                setLoadingState(loading) {
                    const table = document.querySelector('table');
                    if (loading) {
                        table.classList.add('loading');
                    } else {
                        table.classList.remove('loading');
                    }
                }

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('pt-PT');
                }

                animateCounter(element, target) {
                    const current = parseInt(element.textContent) || 0;
                    const increment = target / 20;
                    let currentValue = current;
                    
                    const timer = setInterval(() => {
                        currentValue += increment;
                        if (currentValue >= target) {
                            element.textContent = target;
                            clearInterval(timer);
                        } else {
                            element.textContent = Math.floor(currentValue);
                        }
                    }, 50);
                }
            }

            // Initialize search manager
            let userSearchManager;
            // Original modal functions
            function openNewUserModal() {
                document.getElementById('newUserModal').classList.remove('hidden');
                document.getElementById('newUserModal').classList.add('flex');
            }

            function closeNewUserModal() {
                document.getElementById('newUserModal').classList.add('hidden');
                document.getElementById('newUserModal').classList.remove('flex');
            }

            function editUser(id, name, email, birthDate, phone, department, position, hireDate, status, isAdmin) {
                const form = document.getElementById('editUserForm');
                form.action = `/admin/users/${id}`;

                document.getElementById('edit_name').value = name || '';
                document.getElementById('edit_email').value = email || '';
                document.getElementById('edit_birth_date').value = birthDate || '';
                document.getElementById('edit_phone').value = phone || '';
                document.getElementById('edit_department').value = department || '';
                document.getElementById('edit_position').value = position || '';
                document.getElementById('edit_hire_date').value = hireDate || '';
                document.getElementById('edit_status').value = status || 'active';
                document.getElementById('edit_is_admin').value = isAdmin ? 1 : 0;

                const modal = document.getElementById('editUserModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeEditUserModal() {
                const modal = document.getElementById('editUserModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function deleteUser(id) {
                if (confirm('Tem a certeza que deseja eliminar este utilizador?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/users/${id}`;
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            // Import/Export functionality
            let selectedUsers = new Set();
            let currentImportFile = null;
            
            // Export Modal Functions
            function openExportModal() {
                document.getElementById('exportModal').classList.remove('hidden');
                document.getElementById('exportModal').classList.add('flex');
                updateSelectedUsersInfo();
            }
            
            function closeExportModal() {
                document.getElementById('exportModal').classList.add('hidden');
                document.getElementById('exportModal').classList.remove('flex');
            }
            
            function updateSelectedUsersInfo() {
                const selectedCount = selectedUsers.size;
                document.getElementById('selectedCount').textContent = selectedCount;
                
                const selectedInfo = document.getElementById('selectedUsersInfo');
                const selectedRadio = document.querySelector('input[name="export_type"][value="selected"]');
                
                if (selectedCount > 0) {
                    selectedInfo.classList.remove('hidden');
                    selectedRadio.disabled = false;
                } else {
                    selectedInfo.classList.add('hidden');
                    selectedRadio.disabled = true;
                    document.querySelector('input[name="export_type"][value="all"]').checked = true;
                }
            }

            // Get current filters for export
            function getCurrentFilters() {
                return userSearchManager ? userSearchManager.currentFilters : {};
            }
            
            // Export form submission and initialization
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize search manager
                userSearchManager = new UserSearchManager();
                
                const exportForm = document.getElementById('exportForm');
                if (exportForm) {
                    exportForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        const exportType = formData.get('export_type');
                        const format = formData.get('format');
                        
                        let url = '{{ route("admin.users.import-export.export") }}';
                        let params = new URLSearchParams();
                        params.append('format', format);
                        
                        // Add current filters to export
                        const currentFilters = getCurrentFilters();
                        Object.keys(currentFilters).forEach(key => {
                            if (currentFilters[key]) {
                                params.append(key, currentFilters[key]);
                            }
                        });
                        
                        if (exportType === 'selected' && selectedUsers.size > 0) {
                            // For selected users, use POST request
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route("admin.users.import-export.export-selected") }}';
                            form.style.display = 'none';
                            
                            // Add CSRF token
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = '{{ csrf_token() }}';
                            form.appendChild(csrfInput);
                            
                            // Add format
                            const formatInput = document.createElement('input');
                            formatInput.type = 'hidden';
                            formatInput.name = 'format';
                            formatInput.value = format;
                            form.appendChild(formatInput);
                            
                            // Add selected user IDs
                            selectedUsers.forEach(userId => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'user_ids[]';
                                input.value = userId;
                                form.appendChild(input);
                            });
                            
                            document.body.appendChild(form);
                            form.submit();
                            document.body.removeChild(form);
                        } else {
                            // For all users with filters, use GET request
                            window.location.href = url + '?' + params.toString();
                        }
                        
                        closeExportModal();
                    });
                }
            });
            
            // Import Modal Functions
            function openImportModal() {
                document.getElementById('importModal').classList.remove('hidden');
                document.getElementById('importModal').classList.add('flex');
                resetImportModal();
            }
            
            function closeImportModal() {
                document.getElementById('importModal').classList.add('hidden');
                document.getElementById('importModal').classList.remove('flex');
                resetImportModal();
            }
            
            function resetImportModal() {
                // Reset to step 1
                showImportStep(1);
                
                // Clear file input
                document.getElementById('importFile').value = '';
                document.getElementById('fileInfo').classList.add('hidden');
                document.getElementById('previewBtn').disabled = true;
                currentImportFile = null;
                
                // Clear preview and results
                document.getElementById('previewContent').innerHTML = '';
                document.getElementById('importResults').innerHTML = '';
            }
            
            function showImportStep(step) {
                // Hide all steps
                document.querySelectorAll('.import-step').forEach(el => el.classList.add('hidden'));
                
                // Show current step
                document.getElementById(`importStep${step}`).classList.remove('hidden');
                
                // Update step indicators
                for (let i = 1; i <= 3; i++) {
                    const indicator = document.getElementById(`step${i}-indicator`);
                    const label = indicator.nextElementSibling;
                    
                    if (i < step) {
                        // Completed step
                        indicator.className = 'w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium';
                        indicator.innerHTML = '✓';
                        label.className = 'ml-2 text-sm font-medium text-green-600';
                    } else if (i === step) {
                        // Current step
                        indicator.className = 'w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium';
                        indicator.textContent = i;
                        label.className = 'ml-2 text-sm font-medium text-gray-900';
                    } else {
                        // Future step
                        indicator.className = 'w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-medium';
                        indicator.textContent = i;
                        label.className = 'ml-2 text-sm font-medium text-gray-500';
                    }
                }
            }

            // File upload handling
            document.addEventListener('DOMContentLoaded', function() {
                const importFileInput = document.getElementById('importFile');
                if (importFileInput) {
                    importFileInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            currentImportFile = file;
                            showFileInfo(file);
                            document.getElementById('previewBtn').disabled = false;
                        } else {
                            currentImportFile = null;
                            document.getElementById('fileInfo').classList.add('hidden');
                            document.getElementById('previewBtn').disabled = true;
                        }
                    });
                }

                // Drag and drop handling
                const dropZone = document.getElementById('dropZone');
                if (dropZone) {
                    dropZone.addEventListener('click', () => {
                        document.getElementById('importFile').click();
                    });
                    
                    dropZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dropZone.classList.add('border-blue-400', 'bg-blue-50');
                    });
                    
                    dropZone.addEventListener('dragleave', (e) => {
                        e.preventDefault();
                        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
                    });
                    
                    dropZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
                        
                        const files = e.dataTransfer.files;
                        if (files.length > 0) {
                            document.getElementById('importFile').files = files;
                            document.getElementById('importFile').dispatchEvent(new Event('change'));
                        }
                    });
                }
            });
            
            function showFileInfo(file) {
                const fileInfo = document.getElementById('fileInfo');
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                
                fileInfo.innerHTML = `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900">${file.name}</p>
                                <p class="text-sm text-gray-500">${fileSize} MB</p>
                            </div>
                        </div>
                        <button type="button" onclick="clearFile()" class="text-red-500 hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                `;
                
                fileInfo.classList.remove('hidden');
            }
            
            function clearFile() {
                document.getElementById('importFile').value = '';
                document.getElementById('fileInfo').classList.add('hidden');
                document.getElementById('previewBtn').disabled = true;
                currentImportFile = null;
            }

            // Import functions
            function previewImport() {
                if (!currentImportFile) return;
                
                const formData = new FormData();
                formData.append('file', currentImportFile);
                formData.append('_token', '{{ csrf_token() }}');
                
                // Show loading
                const previewBtn = document.getElementById('previewBtn');
                previewBtn.disabled = true;
                previewBtn.innerHTML = `
                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    A processar...
                `;
                
                fetch('{{ route("admin.users.import-export.preview") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showPreviewResults(data.data);
                        showImportStep(2);
                    } else {
                        showError('Erro ao processar ficheiro: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Erro ao processar ficheiro');
                })
                .finally(() => {
                    // Reset button
                    previewBtn.disabled = false;
                    previewBtn.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Preview
                    `;
                });
            }

            function showPreviewResults(data) {
                const previewContent = document.getElementById('previewContent');
                
                let html = `
                    <div class="mb-4">
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-blue-600">${data.total_rows}</div>
                                <div class="text-sm text-blue-600">Total de Linhas</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-green-600">${data.valid_rows}</div>
                                <div class="text-sm text-green-600">Válidas</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-red-600">${data.invalid_rows}</div>
                                <div class="text-sm text-red-600">Com Erros</div>
                            </div>
                        </div>
                `;
                
                if (data.has_errors) {
                    html += `
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div class="text-sm text-yellow-700">
                                    <p class="font-medium">Atenção!</p>
                                    <p>Algumas linhas contêm erros e não serão importadas. Verifique os detalhes abaixo.</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                html += `
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Linha</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Erros</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                `;
                
                data.rows.forEach(row => {
                    const statusClass = row.validation.valid ? 'text-green-600' : 'text-red-600';
                    const statusIcon = row.validation.valid ? '✓' : '✗';
                    
                    html += `
                        <tr class="${row.validation.valid ? '' : 'bg-red-50'}">
                            <td class="px-4 py-2 text-sm">${row.row_number}</td>
                            <td class="px-4 py-2 text-sm">${row.data.name || '-'}</td>
                            <td class="px-4 py-2 text-sm">${row.data.email || '-'}</td>
                            <td class="px-4 py-2 text-sm ${statusClass}">${statusIcon}</td>
                            <td class="px-4 py-2 text-sm">
                                ${row.validation.errors.length > 0 ? 
                                    '<ul class="text-red-600 text-xs">' + 
                                    row.validation.errors.map(error => `<li>• ${error}</li>`).join('') + 
                                    '</ul>' : '-'}
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                </div>
                `;
                
                previewContent.innerHTML = html;
            }

            function backToUpload() {
                showImportStep(1);
            }

            function confirmImport() {
                if (!currentImportFile) return;
                
                const formData = new FormData();
                formData.append('file', currentImportFile);
                formData.append('send_welcome_emails', document.querySelector('input[name="send_welcome_emails"]').checked);
                formData.append('_token', '{{ csrf_token() }}');
                
                // Show loading
                const confirmBtn = document.getElementById('confirmBtn');
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = `
                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    A importar...
                `;
                
                fetch('{{ route("admin.users.import-export.import") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showImportResults(data);
                        showImportStep(3);
                        
                        // Refresh the user list if successful
                        if (userSearchManager && data.data && data.data.successful > 0) {
                            userSearchManager.performSearch();
                        }
                    } else {
                        showError('Erro na importação: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Erro na importação');
                })
                .finally(() => {
                    // Reset button
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Confirmar Importação
                    `;
                });
            }

            function showImportResults(data) {
                const importResults = document.getElementById('importResults');
                
                let html = '';
                
                if (data.async) {
                    html = `
                        <div class="text-center">
                            <div class="bg-blue-50 p-6 rounded-lg">
                                <svg class="mx-auto h-12 w-12 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-blue-900 mb-2">Importação em Processamento</h3>
                                <p class="text-blue-700">${data.message}</p>
                            </div>
                        </div>
                    `;
                } else {
                    const successRate = data.data.success_rate;
                    const isSuccess = successRate > 80;
                    const bgColor = isSuccess ? 'bg-green-50' : (successRate > 50 ? 'bg-yellow-50' : 'bg-red-50');
                    const textColor = isSuccess ? 'text-green-900' : (successRate > 50 ? 'text-yellow-900' : 'text-red-900');
                    const iconColor = isSuccess ? 'text-green-400' : (successRate > 50 ? 'text-yellow-400' : 'text-red-400');
                    
                    html = `
                        <div class="text-center">
                            <div class="${bgColor} p-6 rounded-lg">
                                <svg class="mx-auto h-12 w-12 ${iconColor} mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${isSuccess ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z'}"></path>
                                </svg>
                                <h3 class="text-lg font-medium ${textColor} mb-4">Importação Concluída</h3>
                                
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="bg-white p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-green-600">${data.data.successful}</div>
                                        <div class="text-sm text-gray-600">Sucessos</div>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-red-600">${data.data.failed}</div>
                                        <div class="text-sm text-gray-600">Falhas</div>
                                    </div>
                                </div>
                                
                                <div class="bg-white p-4 rounded-lg">
                                    <div class="text-lg font-medium text-gray-900">${successRate.toFixed(1)}%</div>
                                    <div class="text-sm text-gray-600">Taxa de Sucesso</div>
                                </div>
                    `;
                    
                    if (data.data.errors && data.data.errors.length > 0) {
                        html += `
                            <div class="mt-4 text-left">
                                <h4 class="font-medium text-gray-900 mb-2">Erros Encontrados:</h4>
                                <div class="bg-white p-4 rounded-lg max-h-40 overflow-y-auto">
                                    <ul class="text-sm text-red-600 space-y-1">
                        `;
                        
                        data.data.errors.slice(0, 10).forEach(error => {
                            html += `<li>• Linha ${error.row}: ${error.message}</li>`;
                        });
                        
                        if (data.data.errors.length > 10) {
                            html += `<li class="text-gray-500">... e mais ${data.data.errors.length - 10} erros</li>`;
                        }
                        
                        html += `
                                    </ul>
                                </div>
                            </div>
                        `;
                    }
                    
                    html += `
                            </div>
                        </div>
                    `;
                }
                
                importResults.innerHTML = html;
            }

            function showError(message) {
                alert(message);
            }

            // Status Control Functions
            function changeUserStatus(userId, userName, currentStatus) {
                document.getElementById('statusUserName').textContent = userName;
                document.getElementById('statusSelect').value = currentStatus;
                document.getElementById('statusForm').action = `/admin/users/${userId}/status`;
                document.getElementById('statusModal').classList.remove('hidden');
                document.getElementById('statusModal').classList.add('flex');
            }

            function closeStatusModal() {
                document.getElementById('statusModal').classList.add('hidden');
                document.getElementById('statusModal').classList.remove('flex');
                document.getElementById('statusReason').value = '';
                document.getElementById('notifyUser').checked = false;
            }

            function openBulkStatusModal() {
                const selectedCount = selectedUsers.size;
                if (selectedCount === 0) {
                    alert('Selecione pelo menos um usuário para alterar o status.');
                    return;
                }
                
                document.getElementById('bulkStatusCount').textContent = selectedCount;
                document.getElementById('bulkUserIds').value = Array.from(selectedUsers).join(',');
                document.getElementById('bulkStatusModal').classList.remove('hidden');
                document.getElementById('bulkStatusModal').classList.add('flex');
            }

            function closeBulkStatusModal() {
                document.getElementById('bulkStatusModal').classList.add('hidden');
                document.getElementById('bulkStatusModal').classList.remove('flex');
                document.getElementById('bulkStatusReason').value = '';
                document.getElementById('notifyUsers').checked = false;
            }

            // Handle status form submission
            document.addEventListener('DOMContentLoaded', function() {
                const statusForm = document.getElementById('statusForm');
                if (statusForm) {
                    statusForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        const submitBtn = this.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Alterando...';
                        
                        fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                closeStatusModal();
                                // Refresh the user list
                                if (userSearchManager) {
                                    userSearchManager.performSearch();
                                }
                                // Show success message
                                showSuccessMessage(data.message || 'Status alterado com sucesso!');
                            } else {
                                throw new Error(data.message || 'Erro ao alterar status');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erro ao alterar status: ' + error.message);
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                    });
                }

                // Handle bulk status form submission
                const bulkStatusForm = document.getElementById('bulkStatusForm');
                if (bulkStatusForm) {
                    bulkStatusForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        const submitBtn = this.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Alterando...';
                        
                        fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                closeBulkStatusModal();
                                // Clear selected users
                                selectedUsers.clear();
                                updateSelectedUsersInfo();
                                // Refresh the user list
                                if (userSearchManager) {
                                    userSearchManager.performSearch();
                                }
                                // Show success message
                                showSuccessMessage(data.message || 'Status alterado com sucesso!');
                            } else {
                                throw new Error(data.message || 'Erro ao alterar status');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erro ao alterar status: ' + error.message);
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                    });
                }
            });

            function showSuccessMessage(message) {
                // Create a temporary success notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                notification.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        ${message}
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        </script>
    </x-slot>

    <!-- Export Modal -->
    <div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">📊 Exportar Utilizadores</h3>
                <button onclick="closeExportModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="exportForm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Formato</label>
                        <select name="format" class="input-field" required>
                            <option value="csv">CSV</option>
                            <option value="xlsx">Excel (XLSX)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Opções de Exportação</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="export_type" value="all" checked class="mr-2">
                                <span class="text-sm">Todos os utilizadores (com filtros aplicados)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="export_type" value="selected" class="mr-2">
                                <span class="text-sm">Apenas utilizadores selecionados</span>
                            </label>
                        </div>
                    </div>
                    
                    <div id="selectedUsersInfo" class="hidden bg-blue-50 p-3 rounded-md">
                        <p class="text-sm text-blue-700">
                            <span id="selectedCount">0</span> utilizador(es) selecionado(s)
                        </p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeExportModal()" class="users-btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="users-btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exportar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">📥 Importar Utilizadores</h3>
                <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Import Steps -->
            <div class="mb-6">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div id="step1-indicator" class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                        <span class="ml-2 text-sm font-medium text-gray-900">Upload</span>
                    </div>
                    <div class="flex-1 h-px bg-gray-300"></div>
                    <div class="flex items-center">
                        <div id="step2-indicator" class="w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Preview</span>
                    </div>
                    <div class="flex-1 h-px bg-gray-300"></div>
                    <div class="flex items-center">
                        <div id="step3-indicator" class="w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Importar</span>
                    </div>
                </div>
            </div>
            
            <!-- Step 1: File Upload -->
            <div id="importStep1" class="import-step">
                <div class="mb-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium mb-1">Instruções de Importação:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Use o template CSV fornecido para garantir o formato correto</li>
                                    <li>Formatos suportados: CSV, Excel (.xlsx, .xls)</li>
                                    <li>Tamanho máximo: 10MB</li>
                                    <li>Emails duplicados serão ignorados</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mb-4">
                        <a href="{{ route('admin.users.import-export.template') }}" 
                           class="users-btn-secondary text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Template
                        </a>
                    </div>
                </div>
                
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ficheiro CSV/Excel</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" id="importFile" name="file" accept=".csv,.xlsx,.xls" class="hidden" required>
                            <div id="dropZone" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">
                                    <span class="font-medium text-blue-600 hover:text-blue-500">Clique para selecionar</span>
                                    ou arraste o ficheiro aqui
                                </p>
                                <p class="text-xs text-gray-500">CSV, XLSX, XLS até 10MB</p>
                            </div>
                            <div id="fileInfo" class="hidden mt-4 text-sm text-gray-600"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="send_welcome_emails" checked class="mr-2">
                            <span class="text-sm">Enviar emails de boas-vindas aos novos utilizadores</span>
                        </label>
                    </div>
                </form>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeImportModal()" class="users-btn-secondary">
                        Cancelar
                    </button>
                    <button type="button" onclick="previewImport()" id="previewBtn" class="users-btn-primary" disabled>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Preview
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Preview -->
            <div id="importStep2" class="import-step hidden">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="backToUpload()" class="users-btn-secondary">
                        Voltar
                    </button>
                    <button type="button" onclick="confirmImport()" id="confirmBtn" class="users-btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Confirmar Importação
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Import Results -->
            <div id="importStep3" class="import-step hidden">
                <div id="importResults">
                    <!-- Import results will be loaded here -->
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeImportModal()" class="users-btn-primary">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Control Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">🔄 Alterar Status do Usuário</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="statusForm" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-3">
                        Usuário: <span id="statusUserName" class="font-medium"></span>
                    </p>
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">Novo Status</label>
                    <select name="status" id="statusSelect" class="input-field" required>
                        <option value="active">✅ Ativo</option>
                        <option value="inactive">❌ Inativo</option>
                        <option value="pending">⏳ Pendente</option>
                        <option value="blocked">🚫 Bloqueado</option>
                        <option value="suspended">⏸️ Suspenso</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motivo (opcional)</label>
                    <textarea name="status_reason" id="statusReason" rows="3" class="input-field" 
                              placeholder="Descreva o motivo da alteração de status..."></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="notify_user" id="notifyUser" class="mr-2">
                        <span class="text-sm">Notificar usuário por email sobre a alteração</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" class="users-btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="users-btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Alterar Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Status Control Modal -->
    <div id="bulkStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">🔄 Alterar Status em Massa</h3>
                <button onclick="closeBulkStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="bulkStatusForm" method="POST" action="{{ \Illuminate\Support\Facades\Route::has('admin.users.bulk-status') ? route('admin.users.bulk-status') : url('/admin/users/bulk-status') }}">
                @csrf
                @method('PATCH')
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-3">
                        <span id="bulkStatusCount">0</span> usuário(s) selecionado(s)
                    </p>
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">Novo Status</label>
                    <select name="status" id="bulkStatusSelect" class="input-field" required>
                        <option value="active">✅ Ativo</option>
                        <option value="inactive">❌ Inativo</option>
                        <option value="pending">⏳ Pendente</option>
                        <option value="blocked">🚫 Bloqueado</option>
                        <option value="suspended">⏸️ Suspenso</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motivo (opcional)</label>
                    <textarea name="status_reason" id="bulkStatusReason" rows="3" class="input-field" 
                              placeholder="Descreva o motivo da alteração de status..."></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="notify_users" id="notifyUsers" class="mr-2">
                        <span class="text-sm">Notificar usuários por email sobre a alteração</span>
                    </label>
                </div>
                
                <input type="hidden" name="user_ids" id="bulkUserIds">
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeBulkStatusModal()" class="users-btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="users-btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Alterar Status
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>