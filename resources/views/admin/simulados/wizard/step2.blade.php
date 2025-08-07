<x-admin-layout title="Novo Simulado - Passo 2" active-menu="simulados" page-title="Criar Simulado - Adicionar Perguntas">
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
            .pergunta-card {
                @apply border border-gray-200 rounded-lg p-4 mb-4 bg-white;
            }
            .opcao-input {
                @apply flex items-center space-x-3 mb-2;
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
            <div class="wizard-step active">
                <div class="step-number">2</div>
                <div>
                    <div class="text-sm font-medium text-gray-900">Perguntas</div>
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
            <h2 class="text-2xl font-bold text-gray-900">{{ $simuladoData['titulo'] }}</h2>
            <p class="text-sm text-gray-600">Passo 2: Adicione as perguntas do simulado</p>
        </div>
        <div class="flex space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                {{ count($perguntas) }} pergunta(s) adicionada(s)
            </span>
        </div>
    </div>

    <!-- Perguntas Existentes -->
    @if(count($perguntas) > 0)
    <div class="card mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Perguntas Adicionadas</h3>
        
        @foreach($perguntas as $index => $pergunta)
        <div class="pergunta-card">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 text-sm font-medium mr-3">
                            {{ $index + 1 }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pergunta['tipo'] === 'multipla_escolha' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                            {{ $pergunta['tipo'] === 'multipla_escolha' ? 'Múltipla Escolha' : 'Escolha Única' }}
                        </span>
                    </div>
                    
                    <h4 class="text-sm font-medium text-gray-900 mb-3">{{ $pergunta['pergunta'] }}</h4>
                    
                    <div class="space-y-1">
                        @foreach($pergunta['opcoes'] as $opcaoIndex => $opcao)
                        <div class="flex items-center text-sm">
                            <span class="w-6 h-6 rounded-full border-2 {{ in_array($opcaoIndex, $pergunta['respostas_corretas']) ? 'bg-green-100 border-green-500 text-green-700' : 'border-gray-300 text-gray-500' }} flex items-center justify-center text-xs font-medium mr-2">
                                {{ chr(65 + $opcaoIndex) }}
                            </span>
                            <span class="{{ in_array($opcaoIndex, $pergunta['respostas_corretas']) ? 'text-green-700 font-medium' : 'text-gray-700' }}">
                                {{ $opcao }}
                            </span>
                            @if(in_array($opcaoIndex, $pergunta['respostas_corretas']))
                                <svg class="w-4 h-4 text-green-500 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    
                    @if($pergunta['explicacao'])
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700"><strong>Explicação:</strong> {{ $pergunta['explicacao'] }}</p>
                    </div>
                    @endif
                </div>
                
                <form method="POST" action="{{ route('admin.simulados.wizard.remove-pergunta', $index) }}" class="ml-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900" title="Remover pergunta" onclick="return confirm('Tem certeza que deseja remover esta pergunta?')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Adicionar Nova Pergunta -->
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Adicionar Nova Pergunta</h3>
        
        <form method="POST" action="{{ route('admin.simulados.wizard.add-pergunta') }}" id="perguntaForm">
            @csrf
            
            <div class="space-y-6">
                <!-- Pergunta -->
                <div>
                    <label for="pergunta" class="block text-sm font-medium text-gray-700 mb-2">
                        Pergunta *
                    </label>
                    <textarea id="pergunta" 
                              name="pergunta" 
                              rows="3" 
                              class="input-field @error('pergunta') border-red-500 @enderror"
                              placeholder="Digite a pergunta aqui..."
                              required>{{ old('pergunta') }}</textarea>
                    @error('pergunta')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Pergunta -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Pergunta *</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="tipo" value="escolha_unica" class="mr-2" {{ old('tipo', 'escolha_unica') === 'escolha_unica' ? 'checked' : '' }} onchange="updateTipoPergunta()">
                            <span class="text-sm text-gray-700">Escolha Única (apenas uma resposta correta)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="tipo" value="multipla_escolha" class="mr-2" {{ old('tipo') === 'multipla_escolha' ? 'checked' : '' }} onchange="updateTipoPergunta()">
                            <span class="text-sm text-gray-700">Múltipla Escolha (várias respostas corretas)</span>
                        </label>
                    </div>
                </div>

                <!-- Opções de Resposta -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opções de Resposta *</label>
                    <div id="opcoes-container">
                        @for($i = 0; $i < 4; $i++)
                        <div class="opcao-input">
                            <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-medium text-gray-600">
                                {{ chr(65 + $i) }}
                            </span>
                            <input type="text" 
                                   name="opcoes[]" 
                                   value="{{ old('opcoes.' . $i) }}"
                                   class="flex-1 input-field" 
                                   placeholder="Digite a opção {{ chr(65 + $i) }}"
                                   {{ $i < 2 ? 'required' : '' }}>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="respostas_corretas[]" 
                                       value="{{ $i }}"
                                       class="resposta-correta"
                                       {{ old('respostas_corretas') && in_array($i, old('respostas_corretas', [])) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600">Correta</span>
                            </label>
                        </div>
                        @endfor
                    </div>
                    <button type="button" onclick="addOpcao()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                        + Adicionar mais uma opção
                    </button>
                    @error('opcoes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('respostas_corretas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Explicação -->
                <div>
                    <label for="explicacao" class="block text-sm font-medium text-gray-700 mb-2">
                        Explicação da Resposta (opcional)
                    </label>
                    <textarea id="explicacao" 
                              name="explicacao" 
                              rows="3" 
                              class="input-field"
                              placeholder="Explique por que esta é a resposta correta...">{{ old('explicacao') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Esta explicação será mostrada após o funcionário responder</p>
                </div>

                <!-- Vídeo URL -->
                <div>
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">
                        URL do Vídeo Explicativo (opcional)
                    </label>
                    <input type="url" 
                           id="video_url" 
                           name="video_url" 
                           value="{{ old('video_url') }}"
                           class="input-field" 
                           placeholder="https://youtube.com/watch?v=...">
                    <p class="mt-1 text-xs text-gray-500">Link para vídeo que explica a resposta</p>
                </div>
            </div>

            <!-- Add Question Button -->
            <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Adicionar Pergunta
                </button>
            </div>
        </form>
    </div>

    <!-- Navigation -->
    <div class="flex justify-between items-center mt-8">
        <a href="{{ route('admin.simulados.wizard') }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar
        </a>
        
        @if(count($perguntas) > 0)
        <a href="{{ route('admin.simulados.wizard.step3') }}" class="btn-primary">
            Próximo: Revisar e Finalizar
            <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
        @else
        <div class="text-sm text-gray-500">Adicione pelo menos uma pergunta para continuar</div>
        @endif
    </div>

    <x-slot name="scripts">
        <script>
            let opcaoCount = 4;

            function addOpcao() {
                if (opcaoCount >= 8) return; // Máximo 8 opções
                
                const container = document.getElementById('opcoes-container');
                const letra = String.fromCharCode(65 + opcaoCount);
                
                const div = document.createElement('div');
                div.className = 'opcao-input';
                div.innerHTML = `
                    <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-medium text-gray-600">
                        ${letra}
                    </span>
                    <input type="text" 
                           name="opcoes[]" 
                           class="flex-1 input-field" 
                           placeholder="Digite a opção ${letra}">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="respostas_corretas[]" 
                               value="${opcaoCount}"
                               class="resposta-correta">
                        <span class="ml-2 text-sm text-gray-600">Correta</span>
                    </label>
                    <button type="button" onclick="removeOpcao(this)" class="ml-2 text-red-600 hover:text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                
                container.appendChild(div);
                opcaoCount++;
                updateTipoPergunta();
            }

            function removeOpcao(button) {
                button.parentElement.remove();
                opcaoCount--;
            }

            function updateTipoPergunta() {
                const tipo = document.querySelector('input[name="tipo"]:checked').value;
                const checkboxes = document.querySelectorAll('.resposta-correta');
                
                if (tipo === 'escolha_unica') {
                    // Converter para radio buttons
                    checkboxes.forEach((checkbox, index) => {
                        checkbox.type = 'radio';
                        checkbox.name = 'respostas_corretas[]';
                        checkbox.value = index;
                    });
                } else {
                    // Manter como checkboxes
                    checkboxes.forEach((checkbox, index) => {
                        checkbox.type = 'checkbox';
                        checkbox.name = 'respostas_corretas[]';
                        checkbox.value = index;
                    });
                }
            }

            // Inicializar tipo de pergunta
            document.addEventListener('DOMContentLoaded', function() {
                updateTipoPergunta();
            });
        </script>
    </x-slot>
</x-admin-layout>