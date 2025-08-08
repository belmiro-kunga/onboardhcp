@extends('admin.layouts.app')

@section('title', 'Detalhes do Curso')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalhes do Curso</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.courses.edit', $course['id']) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4>{{ $course['title'] }}</h4>
                            
                            @if($course['description'])
                                <div class="mb-3">
                                    <h6>Descrição:</h6>
                                    <p class="text-muted">{{ $course['description'] }}</p>
                                </div>
                            @endif

                            @if(isset($course['videos']) && count($course['videos']) > 0)
                                <div class="mb-3">
                                    <h6>Vídeos do Curso ({{ count($course['videos']) }}):</h6>
                                    <div class="list-group">
                                        @foreach($course['videos'] as $video)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">{{ $video['title'] }}</h6>
                                                    @if($video['description'])
                                                        <p class="mb-1 text-muted small">{{ $video['description'] }}</p>
                                                    @endif
                                                    <small class="text-muted">Duração: {{ $video['duration'] }}</small>
                                                </div>
                                                <div>
                                                    @if($video['is_active'])
                                                        <span class="badge bg-success">Ativo</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inativo</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Este curso ainda não possui vídeos.
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">Informações do Curso</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-6">ID:</dt>
                                        <dd class="col-sm-6">{{ $course['id'] }}</dd>

                                        <dt class="col-sm-6">Categoria:</dt>
                                        <dd class="col-sm-6">
                                            @if($course['category'])
                                                <span class="badge bg-info">{{ $course['category']['name'] }}</span>
                                            @else
                                                <span class="text-muted">Sem categoria</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-6">Nível:</dt>
                                        <dd class="col-sm-6">
                                            <span class="badge bg-{{ $course['difficulty_level'] === 'beginner' ? 'success' : ($course['difficulty_level'] === 'intermediate' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($course['difficulty_level']) }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-6">Duração:</dt>
                                        <dd class="col-sm-6">{{ $course['estimated_duration'] }}</dd>

                                        <dt class="col-sm-6">Status:</dt>
                                        <dd class="col-sm-6">
                                            @if($course['is_active'])
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-secondary">Inativo</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-6">Criado em:</dt>
                                        <dd class="col-sm-6">{{ $course['created_at'] }}</dd>

                                        <dt class="col-sm-6">Atualizado em:</dt>
                                        <dd class="col-sm-6">{{ $course['updated_at'] }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection