<x-admin-layout title="Novo Simulado - Passo 1" active-menu="simulados" page-title="Criar Simulado - Informações Básicas">
    <x-slot name="styles">
        <style>
            .wizard-step {
                @apply flex items-center;
            }
            .wizard-step.active .step-number {
                @apply bg-blue-600 text-white;
            }
            .wizard-step.completed .step-number {
                @apply bg-green-600 text-white;
            }
            .wizard-step .step-number {
                @apply w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center text-sm font-medium mr-3;
            }
            .wizard-connector {
                @apply flex-1 h-0.5 bg-gray-300 mx-4;
            }
            .wizard-connector.active {
                @apply bg-blue-600;
            }
        </style>
    </x-slot>

    <!-- Wizard Progress -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="wizard-step active">
                <div class="step-number">1</div>
                <div>
                    <div class="text-sm font-medium text-gray-900">Informações Básicas</div>
                    <div class="text-xs text-gray-500">Título, descrição e configurações</div>
                </div>
            </div>
            <div class="wizard-connector"></div>
            <div class="wizard-step">
                <div class="step-number">2</div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Perguntas</div>
                    <div class="text-xs text-gray-500">Adicionar questões</div>
                </div>
            </div>
            <div class="wizard-connector"></div>
            <div class="wizard-step">
                <div class="step-number">3</div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Revisão</div>
                    <div class="text-xs text-gray-500">Confirmar e finalizar</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Criar Novo Simulado</h2>
            <p class="text-sm text-gray-600">Passo 1: Configure as informações básicas do simulado</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.simulados') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Cancelar
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="card">
        <form method="POST" action="{{ route('admin.simulados.wizard.step1') }}">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <p class="mt-1 text-xs text-gray-500">Tempo limite para completar o simulado</p>
                    </div>

                    <!-- Nota de Aprovação -->
                    <div>
                        <label for="nota_aprovacao" class="block text-sm font-medium text-gray-700 mb-2">
                            Nota Mínima de Aprovação (%) *
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
                        <p class="mt-1 text-xs text-gray-500">Percentual mínimo para aprovação</p>
                    </div>
                </div>

                <!-- Dicas -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-blue-900 mb-1">Dicas para criar um bom simulado:</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Escolha um título claro e descritivo</li>
                                <li>• Defina uma duração adequada ao número de perguntas (2-3 min por pergunta)</li>
                                <li>• A nota de aprovação padrão é 70%, mas pode ajustar conforme a dificuldade</li>
                                <li>• Use a descrição para explicar o objetivo e contexto do simulado</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.simulados') }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
                
                <button type="submit" class="btn-primary">
                    Próximo: Adicionar Perguntas
                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>