@extends('layouts.admin')

@section('title', 'Gestão de Grupos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Gestão de Grupos de Utilizadores</h3>
                    @can('create_users')
                        <a href="{{ route('admin.groups.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Criar Grupo
                        </a>
                    @endcan
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="search" placeholder="Pesquisar grupos..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="type-filter">
                                <option value="">Todos os tipos</option>
                                <option value="department" {{ request('type') === 'department' ? 'selected' : '' }}>Departamento</option>
                                <option value="team" {{ request('type') === 'team' ? 'selected' : '' }}>Equipa</option>
                                <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Personalizado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-secondary" id="clear-filters">Limpar</button>
                        </div>
                        <div class="col-md-3 text-right">
                            <button type="button" class="btn btn-info" id="sync-departments">
                                <i class="fas fa-sync"></i> Sincronizar Departamentos
                            </button>
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total de Grupos</span>
                                    <span class="info-box-number">{{ $groups->total() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Departamentos</span>
                                    <span class="info-box-number">{{ $groups->where('type', 'department')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-user-friends"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Equipas</span>
                                    <span class="info-box-number">{{ $groups->where('type', 'team')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-cog"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Personalizados</span>
                                    <span class="info-box-number">{{ $groups->where('type', 'custom')->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Grupos -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Membros</th>
                                    <th>Permissões</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $group)
                                    <tr>
                                        <td>
                                            <strong>{{ $group->name }}</strong>
                                            @if($group->is_system)
                                                <span class="badge badge-info ml-1">Sistema</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($group->type)
                                                @case('department')
                                                    <span class="badge badge-primary">Departamento</span>
                                                    @break
                                                @case('team')
                                                    <span class="badge badge-success">Equipa</span>
                                                    @break
                                                @case('custom')
                                                    <span class="badge badge-secondary">Personalizado</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-light">{{ ucfirst($group->type) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ Str::limit($group->description ?? '-', 50) }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $group->users->count() }} membros</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">{{ $group->permissions->count() }} permissões</span>
                                        </td>
                                        <td>{{ $group->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view_users')
                                                    <a href="{{ route('admin.groups.show', $group) }}" class="btn btn-sm btn-info" title="Ver Detalhes">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('edit_users')
                                                    @if(!$group->is_system || auth()->user()->isSuperAdmin())
                                                        <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-sm btn-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                @endcan
                                                
                                                @can('delete_users')
                                                    @if(!$group->is_system)
                                                        <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" class="d-inline" onsubmit="return confirm('Tem certeza que deseja eliminar este grupo?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
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
                                        <td colspan="7" class="text-center">Nenhum grupo encontrado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="d-flex justify-content-center">
                        {{ $groups->links() }}
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
        filterGroups();
    });
    
    // Filtro por tipo
    $('#type-filter').on('change', function() {
        filterGroups();
    });
    
    // Limpar filtros
    $('#clear-filters').on('click', function() {
        $('#search').val('');
        $('#type-filter').val('');
        filterGroups();
    });
    
    // Sincronizar departamentos
    $('#sync-departments').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sincronizando...');
        
        $.post('{{ route("admin.groups.sync-departments") }}', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                location.reload();
            } else {
                toastr.error(response.message);
            }
        })
        .fail(function() {
            toastr.error('Erro ao sincronizar departamentos');
        })
        .always(function() {
            btn.prop('disabled', false).html(originalText);
        });
    });
    
    function filterGroups() {
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