@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Editar Simulado</h1>
    
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.simulados.update', $simulado->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="titulo" class="block text-gray-700 text-sm font-bold mb-2">Título</label>
                <input type="text" name="titulo" id="titulo" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       value="{{ old('titulo', $simulado->titulo) }}" required>
            </div>

            <div class="mb-4">
                <label for="descricao" class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                <textarea name="descricao" id="descricao" rows="3"
                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('descricao', $simulado->descricao) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="duracao_minutos" class="block text-gray-700 text-sm font-bold mb-2">Duração (minutos)</label>
                    <input type="number" name="duracao_minutos" id="duracao_minutos" min="1"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           value="{{ old('duracao_minutos', $simulado->duracao_minutos) }}" required>
                </div>
                <div>
                    <label for="nota_aprovacao" class="block text-gray-700 text-sm font-bold mb-2">Nota para Aprovação (%)</label>
                    <input type="number" name="nota_aprovacao" id="nota_aprovacao" min="1" max="100"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           value="{{ old('nota_aprovacao', $simulado->nota_aprovacao) }}" required>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="{{ route('admin.simulados.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Voltar
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Atualizar Simulado
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
