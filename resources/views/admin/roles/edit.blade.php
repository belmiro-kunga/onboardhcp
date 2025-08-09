@extends('layouts.admin')

@section('title', 'Editar Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Editar Role: {{ $role->name }}
                        @if($role->is_system)
                            <span class="badge badge-warning ml-2">Sistema</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                
                @if($role->is_system && !auth()->user()->isSuperAdmin())
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Este é um role do sistema e não pode ser editado.
                        </div>
                        
                        <!-- Mostrar apenas informações do role -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nome do Role</label>
                                    <input type="text" class="form-control" value="{{ $role->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Descrição</label>
                                    <input type="text" class="form-control" value="{{ $role->description }}" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Permissões ({{ $role->permissions->count() }})</label>
                            <div class="row">
                                @foreach($permissions as $module => $modulePermissions)
                                    @php
                                        $rolePermissionSlugs = $role->permissions->pluck('slug')->toArray();
                                        $moduleRolePermissions = $modulePermissions->whereIn('slug', $rolePermissionSlugs);
                                    @endphp
                                    
                                    @if($moduleRolePermissions->count() > 0)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">
                                                        {{ ucfirst($module) }}
                                                        <span class="badge badge-info ml-1">{{ $moduleRolePermissions->count() }}</span>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    @foreach($moduleRolePermissions as $permission)
                                                        <div class="mb-2">
                                                            <i class="fas fa-check text-success mr-1"></i>
                                                            <strong>{{ $permission->name }}</strong>
                                                            @if($permission->description)
                                                                <small class="text-muted d-block ml-3">{{ $permission->description }}</small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Nome do Role <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $role->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="description">Descrição</label>
                                        <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                               id="description" name="description" value="{{ old('description', $role->description) }}">
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Permissões</label>
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">
                                        <i class="fas fa-check-square"></i> Selecionar Todas
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all">
                                        <i class="fas fa-square"></i> Deselecionar Todas
                                    </button>
                                </div>
                                
                                @php
                                    $rolePermissionSlugs = old('permissions', $role->permissions->pluck('slug')->toArray());
                                @endphp
                                
                                <div class="row">
                                    @foreach($permissions as $module => $modulePermissions)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">
                                                        <input type="checkbox" class="module-checkbox" data-module="{{ $module }}">
                                                        {{ ucfirst($module) }}
                                                        <span class="badge badge-info ml-1">{{ $modulePermissions->count() }}</span>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    @foreach($modulePermissions as $permission)
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input permission-checkbox" 
                                                                   data-module="{{ $module }}"
                                                                   name="permissions[]" 
                                                                   value="{{ $permission->slug }}" 
                                                                   id="permission_{{ $permission->id }}"
                                                                   {{ in_array($permission->slug, $rolePermissionSlugs) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                {{ $permission->name }}
                                                                @if($permission->description)
                                                                    <small class="text-muted d-block">{{ $permission->description }}</small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Selecionar todas as permissões
    $('#select-all').on('click', function() {
        $('.permission-checkbox').prop('checked', true);
        $('.module-checkbox').prop('checked', true).prop('indeterminate', false);
    });
    
    // Deselecionar todas as permissões
    $('#deselect-all').on('click', function() {
        $('.permission-checkbox').prop('checked', false);
        $('.module-checkbox').prop('checked', false).prop('indeterminate', false);
    });
    
    // Selecionar/deselecionar todas as permissões de um módulo
    $('.module-checkbox').on('change', function() {
        const module = $(this).data('module');
        const isChecked = $(this).is(':checked');
        
        $(`.permission-checkbox[data-module="${module}"]`).prop('checked', isChecked);
    });
    
    // Atualizar checkbox do módulo baseado nas permissões selecionadas
    $('.permission-checkbox').on('change', function() {
        const module = $(this).data('module');
        const totalPermissions = $(`.permission-checkbox[data-module="${module}"]`).length;
        const checkedPermissions = $(`.permission-checkbox[data-module="${module}"]:checked`).length;
        
        const moduleCheckbox = $(`.module-checkbox[data-module="${module}"]`);
        
        if (checkedPermissions === 0) {
            moduleCheckbox.prop('checked', false).prop('indeterminate', false);
        } else if (checkedPermissions === totalPermissions) {
            moduleCheckbox.prop('checked', true).prop('indeterminate', false);
        } else {
            moduleCheckbox.prop('checked', false).prop('indeterminate', true);
        }
    });
    
    // Inicializar estado dos checkboxes de módulo
    $('.module-checkbox').each(function() {
        const module = $(this).data('module');
        const totalPermissions = $(`.permission-checkbox[data-module="${module}"]`).length;
        const checkedPermissions = $(`.permission-checkbox[data-module="${module}"]:checked`).length;
        
        if (checkedPermissions === 0) {
            $(this).prop('checked', false).prop('indeterminate', false);
        } else if (checkedPermissions === totalPermissions) {
            $(this).prop('checked', true).prop('indeterminate', false);
        } else {
            $(this).prop('checked', false).prop('indeterminate', true);
        }
    });
});
</script>
@endpush
@endsection