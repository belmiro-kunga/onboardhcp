@extends('layouts.admin')

@section('title', 'Criar Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Criar Novo Role</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
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
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Descrição</label>
                                    <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                           id="description" name="description" value="{{ old('description') }}">
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Permissões</label>
                            <div class="row">
                                @foreach($permissions as $module => $modulePermissions)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <input type="checkbox" class="module-checkbox" data-module="{{ $module }}">
                                                    {{ ucfirst($module) }}
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
                                                               {{ in_array($permission->slug, old('permissions', [])) ? 'checked' : '' }}>
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
                            <i class="fas fa-save"></i> Criar Role
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
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
        $(this).trigger('change');
    });
});
</script>
@endpush
@endsection