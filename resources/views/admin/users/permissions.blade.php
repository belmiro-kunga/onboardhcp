@extends('layouts.admin')

@section('title', 'Permissões do Utilizador')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Permissões de {{ $user->name }}
                        <small class="text-muted">({{ $user->email }})</small>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Resumo de Roles e Grupos -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-user-tag"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Roles Atribuídos</span>
                                    <span class="info-box-number">{{ $user->roles->count() }}</span>
                                    <div class="mt-1">
                                        @foreach($user->roles as $role)
                                            <span class="badge badge-primary mr-1">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Grupos</span>
                                    <span class="info-box-number">{{ $user->groups->count() }}</span>
                                    <div class="mt-1">
                                        @foreach($user->groups as $group)
                                            <span class="badge badge-success mr-1">{{ $group->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissões por Módulo -->
                    @if(!empty($permissionsWithSources))
                        @php
                            $permissionsByModule = collect($permissionsWithSources)->groupBy(function($item) {
                                return $item['permission']->module ?? 'general';
                            });
                        @endphp

                        @foreach($permissionsByModule as $module => $modulePermissions)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-cube mr-2"></i>
                                        {{ ucfirst($module) }}
                                        <span class="badge badge-info ml-2">{{ $modulePermissions->count() }} permissões</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($modulePermissions as $permissionData)
                                            @php
                                                $permission = $permissionData['permission'];
                                                $sources = $permissionData['sources'];
                                            @endphp
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3">
                                                    <h6 class="mb-2">
                                                        <i class="fas fa-key text-warning mr-1"></i>
                                                        {{ $permission->name }}
                                                    </h6>
                                                    @if($permission->description)
                                                        <p class="text-muted small mb-2">{{ $permission->description }}</p>
                                                    @endif
                                                    
                                                    <div class="mb-2">
                                                        <strong>Origem:</strong>
                                                        @foreach($sources as $source)
                                                            @if($source['type'] === 'role')
                                                                <span class="badge badge-primary mr-1" title="Via Role">
                                                                    <i class="fas fa-user-tag mr-1"></i>{{ $source['name'] }}
                                                                </span>
                                                            @else
                                                                <span class="badge badge-success mr-1" title="Via Grupo">
                                                                    <i class="fas fa-users mr-1"></i>{{ $source['name'] }}
                                                                </span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    
                                                    <small class="text-muted">
                                                        <code>{{ $permission->slug }}</code>
                                                    </small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Este utilizador não possui permissões atribuídas através de roles ou grupos.
                        </div>
                    @endif

                    <!-- Ações Rápidas -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Ações Rápidas</h5>
                        </div>
                        <div class="card-body">
                            <div class="btn-group" role="group">
                                @can('manage_user_roles')
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#assignRoleModal">
                                        <i class="fas fa-plus mr-1"></i> Atribuir Role
                                    </button>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addToGroupModal">
                                        <i class="fas fa-users mr-1"></i> Adicionar a Grupo
                                    </button>
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

<!-- Modal para Atribuir Role -->
@can('manage_user_roles')
<div class="modal fade" id="assignRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atribuir Role</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.user-roles.store', $user) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="role">Selecionar Role</label>
                        <select class="form-control" name="role" id="role" required>
                            <option value="">Escolha um role...</option>
                            @foreach($availableRoles as $role)
                                <option value="{{ $role->name }}">
                                    {{ $role->name }}
                                    @if($role->description)
                                        - {{ $role->description }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atribuir Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('styles')
<style>
@media print {
    .card-tools, .btn-group, .modal { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endpush
@endsection