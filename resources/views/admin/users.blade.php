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
            /* Remove duplicate/conflicting styles - handled above */
        </style>
    </x-slot>

    <!-- Enhanced Page Header - Following Simulados Pattern -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-6 mb-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <svg class="w-8 h-8 mr-3 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h2 class="text-3xl font-bold tracking-tight">👥 Gestão de Usuários</h2>
                </div>
                <p class="text-emerald-100 text-lg">Gerencie usuários, roles e permissões com ferramentas avançadas</p>
            </div>
            <div class="flex space-x-4">
                <button class="users-btn-secondary group">
                    <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    📊 Exportar CSV
                </button>
                <button class="users-btn-secondary group">
                    <svg class="w-5 h-5 mr-2 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    📥 Importar CSV
                </button>
                <button onclick="openNewUserModal()" class="users-btn-primary group">
                    <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    ➕ Novo Usuário
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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

    <!-- Filters -->
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros</h3>
        <div class="flex items-center space-x-4">
            <div class="flex-1">
                <input type="text" placeholder="Buscar por nome ou email..." class="input-field">
            </div>
            <select class="input-field w-48">
                <option>Todos os campos</option>
                <option>Nome</option>
                <option>Email</option>
                <option>Role</option>
            </select>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Lista de Utilizadores</h3>
            <p class="text-sm text-gray-500">{{ $users->count() }} utilizador(s) encontrado(s)</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="avatar mr-3">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->is_admin)
                                <span class="status-badge role-admin">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Administrador
                                </span>
                            @else
                                <span class="status-badge role-user">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Funcionário
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="status-badge status-active">Ativo</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at ? $user->created_at->format('d/m/Y') : '07/08/2025' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Nunca
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->birth_date }}', {{ $user->is_admin ? 1 : 0 }})" 
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- New User Modal -->
    <div id="newUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Novo Utilizador</h3>
            <form method="POST" action="{{ route('admin.users.create') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                        <input type="text" name="name" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                        <input type="email" name="email" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Palavra-passe</label>
                        <input type="password" name="password" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
                        <input type="date" name="birth_date" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="is_admin" class="input-field">
                            <option value="0">Funcionário</option>
                            <option value="1">Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeNewUserModal()" class="btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">Criar Utilizador</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Editar Utilizador</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                        <input type="text" id="editName" name="name" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                        <input type="email" id="editEmail" name="email" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
                        <input type="date" id="editBirthDate" name="birth_date" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select id="editIsAdmin" name="is_admin" class="input-field">
                            <option value="0">Funcionário</option>
                            <option value="1">Administrador</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nova Palavra-passe (opcional)</label>
                        <input type="password" name="password" class="input-field">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function openNewUserModal() {
                document.getElementById('newUserModal').classList.remove('hidden');
                document.getElementById('newUserModal').classList.add('flex');
            }

            function closeNewUserModal() {
                document.getElementById('newUserModal').classList.add('hidden');
                document.getElementById('newUserModal').classList.remove('flex');
            }

            function editUser(id, name, email, birthDate, isAdmin) {
                document.getElementById('editForm').action = `/admin/users/${id}`;
                document.getElementById('editName').value = name;
                document.getElementById('editEmail').value = email;
                document.getElementById('editBirthDate').value = birthDate;
                document.getElementById('editIsAdmin').value = isAdmin;
                document.getElementById('editModal').classList.remove('hidden');
                document.getElementById('editModal').classList.add('flex');
            }

            function closeEditModal() {
                document.getElementById('editModal').classList.add('hidden');
                document.getElementById('editModal').classList.remove('flex');
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
        </script>
    </x-slot>
</x-admin-layout>