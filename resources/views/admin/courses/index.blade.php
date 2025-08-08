@extends('admin.layouts.app')

@section('title', 'Gestão de Cursos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Gestão de Cursos</h3>
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Curso
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($courses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Nível</th>
                                        <th>Duração</th>
                                        <th>Status</th>
                                        <th>Criado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courses as $course)
                                        <tr>
                                            <td>{{ $course['id'] }}</td>
                                            <td>
                                                <strong>{{ $course['title'] }}</strong>
                                                @if($course['description'])
                                                    <br><small class="text-muted">{{ Str::limit($course['description'], 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($course['category'])
                                                    <span class="badge bg-info">{{ $course['category']['name'] }}</span>
                                                @else
                                                    <span class="text-muted">Sem categoria</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $course['difficulty_level'] === 'beginner' ? 'success' : ($course['difficulty_level'] === 'intermediate' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($course['difficulty_level']) }}
                                                </span>
                                            </td>
                                            <td>{{ $course['estimated_duration'] }}</td>
                                            <td>
                                                @if($course['is_active'])
                                                    <span class="badge bg-success">Ativo</span>
                                                @else
                                                    <span class="badge bg-secondary">Inativo</span>
                                                @endif
                                            </td>
                                            <td>{{ $course['created_at'] }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.courses.show', $course['id']) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.courses.edit', $course['id']) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.courses.destroy', $course['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este curso?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum curso encontrado</h5>
                            <p class="text-muted">Comece criando o seu primeiro curso.</p>
                            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Primeiro Curso
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection