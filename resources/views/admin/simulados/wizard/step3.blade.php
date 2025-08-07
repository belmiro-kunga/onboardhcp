<x-admin-layout title="Novo Simulado - Passo 3" active-menu="simulados" page-title="Criar Simulado - Revisão Final">
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
            .summary-item {
                @apply flex justify-between items-center py-3 border-b border-gray-200 last:border-b-0;
            }
            .pergunta-preview {
                @apply border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50;
            }
        </style>
    </x-slot>

    <!-- Wizard Progress -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="wizard-step completed">
                <div class="step-number">✓</div>
                <div>
                    <div class="text-sm font-medium text-gray-900">Informações Básicas</div>
                    <div class="text-xs text-gray-500">Concluído</div>
                </div>
            </div>
            <div class="wizard-connector active"></div>
            <div class="wizard-step completed">
                <div class="step-number">✓</div>
                <div>
                    <div class="text-sm font-medium text-gray-900">Perguntas</div>
                    <div class="text-xs text-gray-500">{{ count($perguntas) }} pergunta(s)</div>
                </div>
            </div>
            <div class="wizard-connector active"></div>
            <div class="wizard-step active">
                <div class="step-number">3</div>
                <div>
                    <div class="text-sm font-medium text-gray-900">Revisão</div>
                    <div class="text-xs text-gray-500">Confirmar e finalizar</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Revisar e Finalizar</h2>
            <p class="text-sm text-gray-600">Passo 3: Confirme todas as informações antes de criar o simulado</p>
        </div>
        <div class="flex space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                Pronto para criar
            </span>
        </div>
    </div>

    <!-- Resumo do Simulado -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Informações Básicas -->
        <div class="lg:col-span-2">
            <div class="card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Simulado</h3>
                
                <div class="space-y-0">
                    <div class="summary-item">
                        <span class="text-sm font-medium text-gray-600">Título:</span>
                        <span class="text-sm text-gray-900 font-medium">{{ $simuladoData['titulo'] }}</span>
                    </div>
                    
                    @if($simuladoData['descricao'])
                    <div class="summary-item">
                        <span class="text-sm font-medium text-gray-600">Descrição:</span>
                        <span class="text-sm text-gray-900 max-w-md text-right">{{ Str::limit($simuladoData['descricao'], 100) }}</span>
                    </div>
                    @endif
                    
                    <div class="summary-item">
                        <span class="text-sm font-medium text-gray-600">Duração:</span>
                        <span class="text-sm text-gray-900 font-medium">{{ $simuladoData['duracao_minutos'] }} minutos</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="text-sm font-medium text-gray-600">Nota de Aprovação:</span>
                        <span class="text-sm text-gray-900 font-medium">{{ $simuladoData['nota_aprovacao'] }}%</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="text-sm font-medium text-gray-600">Total de Perguntas:</span>
                        <span class="text-sm text-gray-900 font-medium">{{ count($perguntas) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="lg:col-span-1">
            <div class="card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas</h3>
                
                <div class="space-y-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ count($perguntas) }}</div>
                        <div class="text-sm text-blue-800">Perguntas</div>
                    </div>
                    
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">~{{ count($perguntas) * 2 }}</div>
                        <div class="text-sm text-green-800">Minutos estimados</div>
                    </div>
                    
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ $simuladoData['nota_aprovacao'] }}%</div>
                        <div class="text-sm text-purple-800">Para aprovação</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview das Perguntas -->
    <div class="card mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Preview das Perguntas</h3>
            <a href="{{ route('admin.simulados.wizard.step2') }}" class="text-sm text-blue-600 hover:text-blue-800">
                Editar perguntas
            </a>
        </div>
        
        <div class="space-y-4 max-h-96 overflow-y-auto">
            @foreach($perguntas as $index => $pergunta)
            <div class="pergunta-preview">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 text-sm font-medium mr-3">
                            {{ $index + 1 }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pergunta['tipo'] === 'multipla_escolha' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                            {{ $pergunta['tipo'] === 'multipla_escolha' ? 'Múltipla Escolha' : 'Escolha Única' }}
                        </span>
                    </div>
                </div>
                
                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ $pergunta['pergunta'] }}</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($pergunta['opcoes'] as $opcaoIndex => $opcao)
                    @if($opcao) <!-- Só mostrar opções preenchidas -->
                    <div class="flex items-center text-sm">
                        <span class="w-5 h-5 rounded-full border-2 {{ in_array($opcaoIndex, $pergunta['respostas_corretas']) ? 'bg-green-100 border-green-500 text-green-700' : 'border-gray-300 text-gray-500' }} flex items-center justify-center text-xs font-medium mr-2">
                            {{ chr(65 + $opcaoIndex) }}
                        </span>
                        <span class="{{ in_array($opcaoIndex, $pergunta['respostas_corretas']) ? 'text-green-700 font-medium' : 'text-gray-700' }}">
                            {{ $opcao }}
                        </span>
                        @if(in_array($opcaoIndex, $pergunta['respostas_corretas']))
                            <svg class="w-3 h-3 text-green-500 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
                
                @if($pergunta['explicacao'])
                <div class="mt-3 p-2 bg-white rounded border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600"><strong>Explicação:</strong> {{ Str::limit($pergunta['explicacao'], 100) }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Finalização -->
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Finalizar Simulado</h3>
        
        <form method="POST" action="{{ route('admin.simulados.wizard.finalize') }}">
            @csrf
            
            <div class="space-y-4">
                <!-- Status Inicial -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="ativo" value="1" checked class="mr-3">
                        <div>
                            <span class="text-sm font-medium text-gray-900">Ativar simulado imediatamente</span>
                            <p class="text-xs text-gray-500">Se desmarcado, o simulado será criado como inativo</p>
                        </div>
                    </label>
                </div>

                <!-- Confirmação -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-900 mb-1">Confirme antes de criar</h4>
                            <p class="text-sm text-yellow-800">
                                Você está prestes a criar o simulado "<strong>{{ $simuladoData['titulo'] }}</strong>" 
                                com {{ count($perguntas) }} pergunta(s). Esta ação não pode ser desfeita, mas você poderá editar o simulado posteriormente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.simulados.wizard.step2') }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
                
                <button type="submit" class="btn-primary bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Criar Simulado
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>