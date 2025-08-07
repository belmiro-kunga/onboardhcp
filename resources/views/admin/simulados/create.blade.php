<x-admin-layout title="Criar Simulado" active-menu="simulados" page-title="Criação Rápida de Simulado">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Criação Rápida de Simulado</h2>
            <p class="text-sm text-gray-600">Crie um simulado básico rapidamente (você pode adicionar perguntas depois)</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.simulados') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
            <a href="{{ route('admin.simulados.wizard') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Usar Wizard Completo
            </a>
        </div>
    </div>

    <!-- Comparison -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="card border-2 border-blue-200 bg-blue-50">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-blue-900">Criação Rápida</h3>
                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Atual
                </span>
            </div>
            <ul class="text-sm text-blue-800 space-y-2">
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Apenas informações básicas
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Criação em 1 minuto
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Adicionar perguntas depois
                </li>
            </ul>
        </div>

        <div class="card">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Wizard Completo</h3>
            </div>
            <ul class="text-sm text-gray-700 space-y-2">
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Processo guiado passo a passo
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Adicionar perguntas imediatamente
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Simulado completo e pronto
                </li>
            </ul>
        </div>
    </div>

    <!-- Form -->
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Informações Básicas do Simulado</h3>
        
        <form method="POST" action="{{ route('admin.simulados.store') }}">
            @csrf
            
            <div class="space-y-6">
                <!-- Título -->
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                        Título do Simulado *
                    </label>
                    <input type="text" 
                           id="titulo" 
                           name="titulo" 
                           value="{{ old('titulo') }}"
                           class="input-field @error('titulo') border-red-500 @enderror" 
                           placeholder="Ex: Avaliação de Conhecimentos Financeiros"
                           required>
                    @error('titulo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea id="descricao" 
                              name="descricao" 
                              rows="4" 
                              class="input-field @error('descricao') border-red-500 @enderror"
                              placeholder="Descreva o objetivo e conteúdo do simulado...">{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Configurações -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Duração -->
                    <div>
                        <label for="duracao_minutos" class="block text-sm font-medium text-gray-700 mb-2">
                            Duração (minutos) *
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="duracao_minutos" 
                                   name="duracao_minutos" 
                                   value="{{ old('duracao_minutos', 30) }}"
                                   min="1" 
                                   max="300"
                                   class="input-field @error('duracao_minutos') border-red-500 @enderror" 
                                   required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">min</span>
                            </div>
                        </div>
                        @error('duracao_minutos')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nota de Aprovação -->
                    <div>
                        <label for="nota_aprovacao" class="block text-sm font-medium text-gray-700 mb-2">
                            Nota Mínima (%) *
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="nota_aprovacao" 
                                   name="nota_aprovacao" 
                                   value="{{ old('nota_aprovacao', 70) }}"
                                   min="1" 
                                   max="100"
                                   class="input-field @error('nota_aprovacao') border-red-500 @enderror" 
                                   required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">%</span>
                            </div>
                        </div>
                        @error('nota_aprovacao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Inicial</label>
                        <div class="flex items-center space-x-4 mt-3">
                            <label class="flex items-center">
                                <input type="radio" name="ativo" value="1" class="mr-2" {{ old('ativo', '0') === '1' ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">Ativo</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="ativo" value="0" class="mr-2" {{ old('ativo', '0') === '0' ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">Inativo</span>
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recomendado: criar como inativo e ativar após adicionar perguntas</p>
                    </div>
                </div>

                <!-- Próximos Passos -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Após criar o simulado:</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Você será redirecionado para adicionar perguntas
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Adicione quantas perguntas desejar
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Ative o simulado quando estiver pronto
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.simulados') }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Cancelar
                </a>
                
                <div class="flex space-x-3">
                    <button type="submit" name="continue_to_questions" value="1" class="btn-primary">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Criar e Adicionar Perguntas
                    </button>
                    <button type="submit" class="btn-secondary">
                        Criar Simulado Básico
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>