@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">Gerenciamento de Vídeos</h1>
                <a href="{{ route('admin.videos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Vídeo
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(isset($error) && $error)
        <div class="alert alert-warning">
            {{ $error }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="videosTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Curso</th>
                            <th>Duração</th>
                            <th>Status</th>
                            <th>Data de Criação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($videos as $video)
                            <tr>
                                <td>{{ $video['title'] }}</td>
                                <td>{{ $video['course']['title'] ?? 'N/A' }}</td>
                                <td>{{ $video['duration'] ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $video['is_active'] ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $video['is_active'] ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ $video['created_at'] ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.videos.edit', $video['id']) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-video" 
                                                data-id="{{ $video['id'] }}"
                                                title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Nenhum vídeo encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteVideoModal" tabindex="-1" role="dialog" aria-labelledby="deleteVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteVideoModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir este vídeo? Esta ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteVideoForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#videosTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
            },
            "order": [[4, "desc"]] // Sort by creation date by default
        });

        // Handle delete button click
        $('.delete-video').on('click', function() {
            const videoId = $(this).data('id');
            const form = $('#deleteVideoForm');
            form.attr('action', `/admin/videos/${videoId}`);
            $('#deleteVideoModal').modal('show');
        });
    });
</script>
@endpush
