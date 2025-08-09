@extends('layouts.admin')

@section('title', 'Detalhes do Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ $role->name }}
                        @if($role->is_system)
                            <span class="badge badge-warning ml-2">Sistema</span>
                        @else
                            <span class="badge badge-primary ml-2">Personalizado</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        @can('edit_roles')
                            @if(!$role->is_system || auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            @endif
                        @endcan
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Informações Básicas -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5>Informações do Role</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Nome:</strong></td>
                                    <td>{{ $role->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Descrição:</strong></td>
                                    <td>{{ $role->description ?? 'Sem descrição' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td>
                                        @if($role->is_system)
                                            <span class="badge badge-warning">Role do Sistema</span>
                                        @else
                                            <span class="badge badge-primary">Role Personalizado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Criado em:</strong></td>
                                    <td>{{ $role->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Atualizado em:</strong></td>
                                    <td>{{ $role->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <!-- Estatísticas -->
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-key"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Permissões</span>
                                    <span class="info-box-number">{{ $role->permissions->count() }}</span>
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Utilizadores</span>
                                    <span class="info-box-number">{{ $role->users->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissões por Módulo -->
                    @if($role->permissions->count() > 0)
                        <div class="row">
                            <div class="col-12">
                                <h5>Permissões por Módulo</h5>
                                @php
                                    $permissionsByModule = $role->permissions->groupBy('module');
                                @endphp
                                
                                <div class="row">
                                    @foreach($permissionsByModule as $module => $modulePermissions)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">
                                                        <i class="fas fa-cube mr-2"></i>
                                                        {{ ucfirst($module) }}
                                                        <span class="badge badge-info ml-2">{{ $modulePermissions->count() }}</span>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    @foreach($modulePermissions as $permission)
                                                        <div class="mb-2">
                                                            <i class="fas fa-check text-success mr-1"></i>
                                                            <strong>{{ $permission->name }}</strong>
                                                            @if($permission->description)
                                                                <small class="text-muted d-block ml-3">{{ $permission->description }}</small>
                                                            @endif
                                                            <small class="text-muted d-block ml-3">
                                                                <code>{{ $permission->slug }}</code>
                                                            </small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Este role não possui permissões atribuídas.
                        </div>
                    @endif

                    <!-- Utilizadores com este Role -->
                    @if($role->users->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Utilizadores com este Role</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Email</th>
                                                <th>Departamento</th>
                                                <th>Status</th>
                                                <th>Atribuído em</th>
                                                <th>Atribuído por</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($role->users as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->department ?? '-' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                                            {{ $user->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $user->pivot->assigned_at ? \Carbon\Carbon::parse($user->pivot->assigned_at)->format('d/m/Y H:i') : '-' }}</td>
                                                    <td>
                                                        @if($user->pivot->assigned_by)
                                                            @php
                                                                $assignedBy = \App\Modules\User\Models\User::find($user->pivot->assigned_by);
                                                            @endphp
                                                            {{ $assignedBy ? $assignedBy->name : 'Sistema' }}
                                                        @else
                                                            Sistema
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @can('manage_user_roles')
                                                            <a href="{{ route('admin.user-roles.permissions.view', $user) }}" class="btn btn-sm btn-info" title="Ver Permissões">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if(!$role->is_system || auth()->user()->isSuperAdmin())
                                                                <form method="POST" action="{{ route('admin.user-roles.destroy', [$user, $role]) }}" class="d-inline" onsubmit="return confirm('Remover este role do utilizador?')">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger" title="Remover Role">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            Nenhum utilizador possui este role atualmente.
                        </div>
                    @endif

                    <!-- Ações -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                @can('edit_roles')
                                    @if(!$role->is_system || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning">
                                            <i class="fas fa-edit mr-1"></i> Editar Role
                                        </a>
                                    @endif
                                @endcan
                                
                                @can('delete_roles')
                                    @if(!$role->is_system)
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Tem certeza que deseja eliminar este role? Esta ação não pode ser desfeita.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash mr-1"></i> Eliminar Role
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                
                                <button type="button" class="btn btn-info" onclick="window.print()">
                                    <i class="fas fa-print mr-1"></i> Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .card-tools, .btn-group, .btn { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endpush
@endsection