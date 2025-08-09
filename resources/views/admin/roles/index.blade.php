@extends('layouts.admin')

@section('title', 'Gestão de Roles')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Gestão de Roles</h3>
                    @can('create_roles')
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Criar Role
                        </a>
                    @endcan
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="search" placeholder="Pesquisar roles..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="type-filter">
                                <option value="">Todos os tipos</option>
                                <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>Sistema</option>
                                <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Personalizados</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-secondary" id="clear-filters">Limpar Filtros</button>
                        </div>
                    </div>

                    <!-- Tabela de Roles -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Tipo</th>
                                    <th>Permissões</th>
                                    <th>Utilizadores</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>
                                            <strong>{{ $role->name }}</strong>
                                            @if($role->is_system)
                                                <span class="badge badge-info ml-1">Sistema</span>
                                            @endif
                                        </td>
                                        <td>{{ $role->description ?? '-' }}</td>
                                        <td>
                                            @if($role->is_system)
                                                <span class="badge badge-secondary">Sistema</span>
                                            @else
                                                <span class="badge badge-primary">Personalizado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $role->permissions->count() }} permissões</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">{{ $role->users->count() }} utilizadores</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view_roles')
                                                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('edit_roles')
                                                    @if(!$role->is_system || auth()->user()->isSuperAdmin())
                                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                @endcan
                                                
                                                @can('delete_roles')
                                                    @if(!$role->is_system)
                                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Tem certeza que deseja eliminar este role?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhum role encontrado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="d-flex justify-content-center">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Pesquisa em tempo real
    $('#search').on('keyup', function() {
        filterRoles();
    });
    
    // Filtro por tipo
    $('#type-filter').on('change', function() {
        filterRoles();
    });
    
    // Limpar filtros
    $('#clear-filters').on('click', function() {
        $('#search').val('');
        $('#type-filter').val('');
        filterRoles();
    });
    
    function filterRoles() {
        const search = $('#search').val();
        const type = $('#type-filter').val();
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (type) params.append('type', type);
        
        window.location.search = params.toString();
    }
});
</script>
@endpush
@endsection