<x-admin-layout title="Simulados" active-menu="simulados" page-title="Gestão de Simulados">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Gestão de Simulados</h2>
            <p class="text-sm text-gray-600">Crie e gerencie simulados para os funcionários</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.simulados.create') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Criação Rápida
            </a>
            <button onclick="openWizardModal()" class="btn-primary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Wizard Completo
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="widget">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total de Simulados</p>
                    <p class="text-sm text-gray-500">Simulados criados</p>
                </div>
            </div>
            <div class="flex items-center mt-4">
                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="text-2xl font-bold text-gray-900">{{ $estatisticas['total_simulados'] }}</span>
            </div>
        </div>

        <div class="widget">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Simulados Ativos</p>
                    <p class="text-sm text-gray-500">Disponíveis para funcionários</p>
                </div>
            </div>
            <div class="flex items-center mt-4">
                <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-2xl font-bold text-gray-900">{{ $estatisticas['simulados_ativos'] }}</span>
            </div>
        </div>

        <div class="widget">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Participações</p>
                    <p class="text-sm text-gray-500">Total de tentativas</p>
                </div>
            </div>
            <div class="flex items-center mt-4">
                <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <span class="text-2xl font-bold text-gray-900">{{ $estatisticas['total_tentativas'] }}</span>
            </div>
        </div>

        <div class="widget">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Taxa de Aprovação</p>
                    <p class="text-sm text-gray-500">Média geral</p>
                </div>
            </div>
            <div class="flex items-center mt-4">
                <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($estatisticas['taxa_aprovacao'], 1) }}%</span>
            </div>
        </div>
    </div>

    <!-- Simulados List -->
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Lista de Simulados</h3>
            <p class="text-sm text-gray-500">{{ $simulados->count() }} simulado(s) encontrado(s)</p>
        </div>

        @if($simulados->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perguntas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duração</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nota Mín.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($simulados as $simulado)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $simulado->titulo }}</div>
                                    @if($simulado->descricao)
                                        <div class="text-sm text-gray-500">{{ Str::limit($simulado->descricao, 50) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $simulado->total_perguntas }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $simulado->duracao_minutos }} min
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $simulado->nota_aprovacao }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($simulado->ativo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        Inativo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $simulado->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.simulados.show', $simulado->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Ver Detalhes">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.simulados.edit', $simulado->id) }}" 
                                       class="text-green-600 hover:text-green-900" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.simulados.destroy', $simulado->id) }}" 
                                          class="inline" onsubmit="return confirm('Tem certeza que deseja eliminar este simulado?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
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
            <div class="text-center py-12">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum simulado encontrado</h3>
                <p class="text-gray-500 mb-4">Comece criando o seu primeiro simulado.</p>
                <div class="flex justify-center space-x-3">
                    <a href="{{ route('admin.simulados.create') }}" class="btn-secondary">
                        Criação Rápida
                    </a>
                    <button onclick="openWizardModal()" class="btn-primary">
                        Wizard Completo
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Wizard Modal -->
    <div id="wizardModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Criar Novo Simulado - Wizard Completo</h3>
                    <p class="text-sm text-gray-600">Processo guiado em 3 passos</p>
                </div>
                <button onclick="closeWizardModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Wizard Progress -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="wizard-step active" id="step1-indicator">
                        <div class="step-number">1</div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">Informações Básicas</div>
                            <div class="text-xs text-gray-500">Título, descrição e configurações</div>
                        </div>
                    </div>
                    <div class="wizard-connector" id="connector1"></div>
                    <div class="wizard-step" id="step2-indicator">
                        <div class="step-number">2</div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-500">Perguntas</div>
                            <div class="text-xs text-gray-500">Adicionar questões</div>
                        </div>
                    </div>
                    <div class="wizard-connector" id="connector2"></div>
                    <div class="wizard-step" id="step3-indicator">
                        <div class="step-number">3</div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-500">Revisão</div>
                            <div class="text-xs text-gray-500">Confirmar e finalizar</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <!-- Step 1: Informações Básicas -->
                <div id="wizard-step-1" class="wizard-content">
                    <form id="step1Form">
                        <div class="space-y-6">
                            <div>
                                <label for="modal_titulo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Título do Simulado *
                                </label>
                                <input type="text" 
                                       id="modal_titulo" 
                                       name="titulo" 
                                       class="input-field" 
                                       placeholder="Ex: Avaliação de Conhecimentos Financeiros"
                                       required>
                                <div class="error-message text-red-600 text-sm mt-1 hidden"></div>
                            </div>

                            <div>
                                <label for="modal_descricao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrição
                                </label>
                                <textarea id="modal_descricao" 
                                          name="descricao" 
                                          rows="4" 
                                          class="input-field"
                                          placeholder="Descreva o objetivo e conteúdo do simulado..."></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="modal_duracao" class="block text-sm font-medium text-gray-700 mb-2">
                                        Duração (minutos) *
                                    </label>
                                    <input type="number" 
                                           id="modal_duracao" 
                                           name="duracao_minutos" 
                                           value="30"
                                           min="1" 
                                           max="300"
                                           class="input-field" 
                                           required>
                                </div>

                                <div>
                                    <label for="modal_nota" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nota Mínima (%) *
                                    </label>
                                    <input type="number" 
                                           id="modal_nota" 
                                           name="nota_aprovacao" 
                                           value="70"
                                           min="1" 
                                           max="100"
                                           class="input-field" 
                                           required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Perguntas -->
                <div id="wizard-step-2" class="wizard-content hidden">
                    <div class="space-y-6">
                        <!-- Lista de Perguntas Adicionadas -->
                        <div id="perguntas-list" class="space-y-4">
                            <!-- Perguntas serão adicionadas aqui dinamicamente -->
                        </div>

                        <!-- Formulário para Nova Pergunta -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Adicionar Nova Pergunta</h4>
                            <form id="perguntaForm">
                                <div class="space-y-4">
                                    <div>
                                        <label for="modal_pergunta" class="block text-sm font-medium text-gray-700 mb-2">
                                            Pergunta *
                                        </label>
                                        <textarea id="modal_pergunta" 
                                                  name="pergunta" 
                                                  rows="3" 
                                                  class="input-field"
                                                  placeholder="Digite a pergunta aqui..."
                                                  required></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Pergunta *</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="radio" name="tipo" value="escolha_unica" class="mr-2" checked>
                                                <span class="text-sm text-gray-700">Escolha Única</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="tipo" value="multipla_escolha" class="mr-2">
                                                <span class="text-sm text-gray-700">Múltipla Escolha</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Opções de Resposta *</label>
                                        <div id="modal-opcoes-container">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-medium text-gray-600">A</span>
                                                <input type="text" name="opcoes[]" class="flex-1 input-field" placeholder="Digite a opção A" required>
                                                <label class="flex items-center">
                                                    <input type="radio" name="respostas_corretas" value="0" class="resposta-correta">
                                                    <span class="ml-2 text-sm text-gray-600">Correta</span>
                                                </label>
                                            </div>
                                            <div class="flex items-center space-x-3 mb-2">
                                                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-medium text-gray-600">B</span>
                                                <input type="text" name="opcoes[]" class="flex-1 input-field" placeholder="Digite a opção B" required>
                                                <label class="flex items-center">
                                                    <input type="radio" name="respostas_corretas" value="1" class="resposta-correta">
                                                    <span class="ml-2 text-sm text-gray-600">Correta</span>
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" onclick="addModalOpcao()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                                            + Adicionar mais uma opção
                                        </button>
                                    </div>

                                    <div>
                                        <label for="modal_explicacao" class="block text-sm font-medium text-gray-700 mb-2">
                                            Explicação (opcional)
                                        </label>
                                        <textarea id="modal_explicacao" 
                                                  name="explicacao" 
                                                  rows="2" 
                                                  class="input-field"
                                                  placeholder="Explique por que esta é a resposta correta..."></textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-4">
                                    <button type="button" onclick="addPergunta()" class="btn-primary">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Adicionar Pergunta
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Revisão -->
                <div id="wizard-step-3" class="wizard-content hidden">
                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Resumo do Simulado</h4>
                            <div id="simulado-summary" class="space-y-2 text-sm">
                                <!-- Resumo será preenchido dinamicamente -->
                            </div>
                        </div>

                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-3">Perguntas Adicionadas</h4>
                            <div id="perguntas-review" class="space-y-3">
                                <!-- Review das perguntas será preenchido dinamicamente -->
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-900 mb-1">Confirme antes de criar</h4>
                                    <p class="text-sm text-yellow-800">
                                        Você está prestes a criar o simulado. Esta ação criará o simulado com todas as perguntas configuradas.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" id="modal_ativo" name="ativo" checked class="mr-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Ativar simulado imediatamente</span>
                                    <p class="text-xs text-gray-500">Se desmarcado, o simulado será criado como inativo</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-between items-center p-6 border-t border-gray-200">
                <button onclick="closeWizardModal()" class="btn-secondary">
                    Cancelar
                </button>
                
                <div class="flex space-x-3">
                    <button id="prevBtn" onclick="previousStep()" class="btn-secondary hidden">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Anterior
                    </button>
                    <button id="nextBtn" onclick="nextStep()" class="btn-primary">
                        Próximo
                        <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                    <button id="finishBtn" onclick="finishWizard()" class="btn-primary bg-green-600 hover:bg-green-700 hidden">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Criar Simulado
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                @apply w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center text-sm font-medium;
            }
            .wizard-connector {
                @apply flex-1 h-0.5 bg-gray-300 mx-4;
            }
            .wizard-connector.active {
                @apply bg-blue-600;
            }
            .wizard-content {
                @apply transition-all duration-300;
            }
            .pergunta-card {
                @apply border border-gray-200 rounded-lg p-4 bg-gray-50;
            }
        </style>
    </x-slot>

    <x-slot name="scripts">
        <script>
            let currentStep = 1;
            let perguntas = [];
            let opcaoCount = 2;

            function openWizardModal() {
                document.getElementById('wizardModal').classList.remove('hidden');
                document.getElementById('wizardModal').classList.add('flex');
                resetWizard();
            }

            function closeWizardModal() {
                document.getElementById('wizardModal').classList.add('hidden');
                document.getElementById('wizardModal').classList.remove('flex');
                resetWizard();
            }

            function resetWizard() {
                currentStep = 1;
                perguntas = [];
                opcaoCount = 2;
                showStep(1);
                document.getElementById('step1Form').reset();
                document.getElementById('perguntaForm').reset();
                document.getElementById('perguntas-list').innerHTML = '';
                updatePerguntasCount();
            }

            function showStep(step) {
                // Hide all steps
                document.querySelectorAll('.wizard-content').forEach(content => {
                    content.classList.add('hidden');
                });

                // Show current step
                document.getElementById(`wizard-step-${step}`).classList.remove('hidden');

                // Update indicators
                updateStepIndicators(step);

                // Update buttons
                updateButtons(step);

                currentStep = step;
            }

            function updateStepIndicators(step) {
                for (let i = 1; i <= 3; i++) {
                    const indicator = document.getElementById(`step${i}-indicator`);
                    const connector = document.getElementById(`connector${i}`);
                    
                    if (i < step) {
                        indicator.classList.add('completed');
                        indicator.classList.remove('active');
                        indicator.querySelector('.step-number').textContent = '✓';
                        if (connector) connector.classList.add('active');
                    } else if (i === step) {
                        indicator.classList.add('active');
                        indicator.classList.remove('completed');
                        indicator.querySelector('.step-number').textContent = i;
                        if (connector) connector.classList.remove('active');
                    } else {
                        indicator.classList.remove('active', 'completed');
                        indicator.querySelector('.step-number').textContent = i;
                        if (connector) connector.classList.remove('active');
                    }
                }
            }

            function updateButtons(step) {
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const finishBtn = document.getElementById('finishBtn');

                if (step === 1) {
                    prevBtn.classList.add('hidden');
                    nextBtn.classList.remove('hidden');
                    finishBtn.classList.add('hidden');
                } else if (step === 2) {
                    prevBtn.classList.remove('hidden');
                    nextBtn.classList.remove('hidden');
                    finishBtn.classList.add('hidden');
                } else if (step === 3) {
                    prevBtn.classList.remove('hidden');
                    nextBtn.classList.add('hidden');
                    finishBtn.classList.remove('hidden');
                }
            }

            function nextStep() {
                if (currentStep === 1) {
                    if (validateStep1()) {
                        showStep(2);
                        updatePerguntasCount();
                    }
                } else if (currentStep === 2) {
                    if (perguntas.length > 0) {
                        showStep(3);
                        updateSummary();
                    } else {
                        alert('Adicione pelo menos uma pergunta para continuar.');
                    }
                }
            }

            function previousStep() {
                if (currentStep > 1) {
                    showStep(currentStep - 1);
                }
            }

            function validateStep1() {
                const titulo = document.getElementById('modal_titulo').value.trim();
                const duracao = document.getElementById('modal_duracao').value;
                const nota = document.getElementById('modal_nota').value;

                if (!titulo) {
                    alert('Por favor, preencha o título do simulado.');
                    return false;
                }

                if (!duracao || duracao < 1) {
                    alert('Por favor, defina uma duração válida.');
                    return false;
                }

                if (!nota || nota < 1 || nota > 100) {
                    alert('Por favor, defina uma nota de aprovação válida (1-100%).');
                    return false;
                }

                return true;
            }

            function addModalOpcao() {
                if (opcaoCount >= 6) return;

                const container = document.getElementById('modal-opcoes-container');
                const letra = String.fromCharCode(65 + opcaoCount);
                
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-3 mb-2';
                div.innerHTML = `
                    <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-medium text-gray-600">${letra}</span>
                    <input type="text" name="opcoes[]" class="flex-1 input-field" placeholder="Digite a opção ${letra}">
                    <label class="flex items-center">
                        <input type="radio" name="respostas_corretas" value="${opcaoCount}" class="resposta-correta">
                        <span class="ml-2 text-sm text-gray-600">Correta</span>
                    </label>
                    <button type="button" onclick="removeModalOpcao(this)" class="text-red-600 hover:text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                
                container.appendChild(div);
                opcaoCount++;
            }

            function removeModalOpcao(button) {
                button.parentElement.remove();
                opcaoCount--;
            }

            function addPergunta() {
                const pergunta = document.getElementById('modal_pergunta').value.trim();
                const tipo = document.querySelector('input[name="tipo"]:checked').value;
                const opcoes = Array.from(document.querySelectorAll('input[name="opcoes[]"]')).map(input => input.value.trim()).filter(value => value);
                const respostaCorreta = document.querySelector('input[name="respostas_corretas"]:checked');
                const explicacao = document.getElementById('modal_explicacao').value.trim();

                if (!pergunta) {
                    alert('Por favor, digite a pergunta.');
                    return;
                }

                if (opcoes.length < 2) {
                    alert('Por favor, adicione pelo menos 2 opções.');
                    return;
                }

                if (!respostaCorreta) {
                    alert('Por favor, marque a resposta correta.');
                    return;
                }

                const novaPergunta = {
                    pergunta: pergunta,
                    tipo: tipo,
                    opcoes: opcoes,
                    resposta_correta: parseInt(respostaCorreta.value),
                    explicacao: explicacao
                };

                perguntas.push(novaPergunta);
                renderPerguntas();
                clearPerguntaForm();
                updatePerguntasCount();
            }

            function renderPerguntas() {
                const container = document.getElementById('perguntas-list');
                container.innerHTML = '';

                perguntas.forEach((pergunta, index) => {
                    const div = document.createElement('div');
                    div.className = 'pergunta-card';
                    div.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 text-sm font-medium mr-3">${index + 1}</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${pergunta.tipo === 'multipla_escolha' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'}">
                                        ${pergunta.tipo === 'multipla_escolha' ? 'Múltipla Escolha' : 'Escolha Única'}
                                    </span>
                                </div>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">${pergunta.pergunta}</h4>
                                <div class="space-y-1">
                                    ${pergunta.opcoes.map((opcao, opcaoIndex) => `
                                        <div class="flex items-center text-sm">
                                            <span class="w-5 h-5 rounded-full border-2 ${opcaoIndex === pergunta.resposta_correta ? 'bg-green-100 border-green-500 text-green-700' : 'border-gray-300 text-gray-500'} flex items-center justify-center text-xs font-medium mr-2">
                                                ${String.fromCharCode(65 + opcaoIndex)}
                                            </span>
                                            <span class="${opcaoIndex === pergunta.resposta_correta ? 'text-green-700 font-medium' : 'text-gray-700'}">${opcao}</span>
                                            ${opcaoIndex === pergunta.resposta_correta ? '<svg class="w-3 h-3 text-green-500 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' : ''}
                                        </div>
                                    `).join('')}
                                </div>
                                ${pergunta.explicacao ? `<div class="mt-2 p-2 bg-white rounded border-l-4 border-blue-500"><p class="text-xs text-gray-600"><strong>Explicação:</strong> ${pergunta.explicacao}</p></div>` : ''}
                            </div>
                            <button onclick="removePergunta(${index})" class="ml-4 text-red-600 hover:text-red-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                    container.appendChild(div);
                });
            }

            function removePergunta(index) {
                if (confirm('Tem certeza que deseja remover esta pergunta?')) {
                    perguntas.splice(index, 1);
                    renderPerguntas();
                    updatePerguntasCount();
                }
            }

            function clearPerguntaForm() {
                document.getElementById('perguntaForm').reset();
                // Reset opcoes container
                const container = document.getElementById('modal-opcoes-container');
                container.innerHTML = `
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-medium text-gray-600">A</span>
                        <input type="text" name="opcoes[]" class="flex-1 input-field" placeholder="Digite a opção A" required>
                        <label class="flex items-center">
                            <input type="radio" name="respostas_corretas" value="0" class="resposta-correta">
                            <span class="ml-2 text-sm text-gray-600">Correta</span>
                        </label>
                    </div>
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-medium text-gray-600">B</span>
                        <input type="text" name="opcoes[]" class="flex-1 input-field" placeholder="Digite a opção B" required>
                        <label class="flex items-center">
                            <input type="radio" name="respostas_corretas" value="1" class="resposta-correta">
                            <span class="ml-2 text-sm text-gray-600">Correta</span>
                        </label>
                    </div>
                `;
                opcaoCount = 2;
            }

            function updatePerguntasCount() {
                const step2Indicator = document.getElementById('step2-indicator');
                const countElement = step2Indicator.querySelector('.text-xs');
                countElement.textContent = `${perguntas.length} pergunta(s)`;
            }

            function updateSummary() {
                const titulo = document.getElementById('modal_titulo').value;
                const descricao = document.getElementById('modal_descricao').value;
                const duracao = document.getElementById('modal_duracao').value;
                const nota = document.getElementById('modal_nota').value;

                const summaryContainer = document.getElementById('simulado-summary');
                summaryContainer.innerHTML = `
                    <div><strong>Título:</strong> ${titulo}</div>
                    ${descricao ? `<div><strong>Descrição:</strong> ${descricao}</div>` : ''}
                    <div><strong>Duração:</strong> ${duracao} minutos</div>
                    <div><strong>Nota de Aprovação:</strong> ${nota}%</div>
                    <div><strong>Total de Perguntas:</strong> ${perguntas.length}</div>
                `;

                const reviewContainer = document.getElementById('perguntas-review');
                reviewContainer.innerHTML = perguntas.map((pergunta, index) => `
                    <div class="border border-gray-200 rounded p-3">
                        <div class="font-medium text-sm">${index + 1}. ${pergunta.pergunta}</div>
                        <div class="text-xs text-gray-500 mt-1">${pergunta.opcoes.length} opções • Resposta: ${String.fromCharCode(65 + pergunta.resposta_correta)}</div>
                    </div>
                `).join('');
            }

            function finishWizard() {
                const titulo = document.getElementById('modal_titulo').value;
                const descricao = document.getElementById('modal_descricao').value;
                const duracao = document.getElementById('modal_duracao').value;
                const nota = document.getElementById('modal_nota').value;
                const ativo = document.getElementById('modal_ativo').checked;

                const data = {
                    titulo: titulo,
                    descricao: descricao,
                    duracao_minutos: parseInt(duracao),
                    nota_aprovacao: parseInt(nota),
                    ativo: ativo,
                    perguntas: perguntas,
                    _token: '{{ csrf_token() }}'
                };

                // Enviar dados via AJAX
                fetch('{{ route("admin.simulados.wizard.finalize") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeWizardModal();
                        location.reload(); // Recarregar a página para mostrar o novo simulado
                    } else {
                        alert('Erro ao criar simulado: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao criar simulado. Tente novamente.');
                });
            }

            // Fechar modal ao clicar fora
            document.getElementById('wizardModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeWizardModal();
                }
            });

            // Abrir modal automaticamente se parâmetro wizard=true estiver presente
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('wizard') === 'true') {
                    openWizardModal();
                    // Limpar o parâmetro da URL sem recarregar a página
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }
            });
        </script>
    </x-slot>
</x-admin-layout>