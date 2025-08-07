<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Header com Resultado -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="text-center">
                    <div class="mb-4">
                        @if($tentativa->aprovado)
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-3xl text-green-600"></i>
                        </div>
                        <h1 class="text-3xl font-bold text-green-600">Parabéns!</h1>
                        <p class="text-lg text-gray-600">Você foi aprovado no simulado</p>
                        @else
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-times text-3xl text-red-600"></i>
                        </div>
                        <h1 class="text-3xl font-bold text-red-600">Não Aprovado</h1>
                        <p class="text-lg text-gray-600">Continue estudando e tente novamente</p>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $tentativa->pontuacao }}</div>
                            <div class="text-sm text-gray-500">Acertos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $tentativa->simulado->total_perguntas }}</div>
                            <div class="text-sm text-gray-500">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold {{ $tentativa->aprovado ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($tentativa->percentual, 1) }}%
                            </div>
                            <div class="text-sm text-gray-500">Percentual</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $tentativa->tempo_gasto_formatado }}</div>
                            <div class="text-sm text-gray-500">Tempo Gasto</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Correção Detalhada -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Correção Detalhada</h2>
                
                @foreach($detalhes as $index => $detalhe)
                <div class="border-b border-gray-200 pb-6 mb-6 last:border-b-0 last:pb-0 last:mb-0">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full mr-3">
                                Pergunta {{ $index + 1 }}
                            </span>
                            @if($detalhe['correta'])
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">
                                <i class="fas fa-check mr-1"></i>Correta
                            </span>
                            @else
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded">
                                <i class="fas fa-times mr-1"></i>Incorreta
                            </span>
                            @endif
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $detalhe['pergunta']->pergunta }}</h3>
                    
                    <!-- Opções com indicação de resposta -->
                    <div class="space-y-2 mb-4">
                        @foreach($detalhe['pergunta']->opcoes as $opcaoIndex => $opcao)
                        <div class="p-3 rounded-lg border 
                            @if(in_array($opcaoIndex, $detalhe['resposta_correta']))
                                border-green-200 bg-green-50
                            @elseif(in_array($opcaoIndex, $detalhe['resposta_usuario']))
                                border-red-200 bg-red-50
                            @else
                                border-gray-200 bg-gray-50
                            @endif
                        ">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-900">{{ $opcao }}</span>
                                <div class="flex space-x-2">
                                    @if(in_array($opcaoIndex, $detalhe['resposta_correta']))
                                    <span class="text-green-600 text-sm font-medium">
                                        <i class="fas fa-check mr-1"></i>Correta
                                    </span>
                                    @endif
                                    @if(in_array($opcaoIndex, $detalhe['resposta_usuario']) && !in_array($opcaoIndex, $detalhe['resposta_correta']))
                                    <span class="text-red-600 text-sm font-medium">
                                        <i class="fas fa-times mr-1"></i>Sua resposta
                                    </span>
                                    @elseif(in_array($opcaoIndex, $detalhe['resposta_usuario']) && in_array($opcaoIndex, $detalhe['resposta_correta']))
                                    <span class="text-green-600 text-sm font-medium">
                                        <i class="fas fa-check mr-1"></i>Sua resposta
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Explicação -->
                    @if($detalhe['explicacao'])
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="font-medium text-blue-900 mb-2">
                            <i class="fas fa-info-circle mr-2"></i>Explicação
                        </h4>
                        <p class="text-blue-800">{{ $detalhe['explicacao'] }}</p>
                    </div>
                    @endif
                    
                    <!-- Vídeo Explicativo -->
                    @if($detalhe['video_url'])
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h4 class="font-medium text-purple-900 mb-2">
                            <i class="fas fa-play-circle mr-2"></i>Vídeo Explicativo
                        </h4>
                        <a href="{{ $detalhe['video_url'] }}" target="_blank" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            <i class="fas fa-external-link-alt mr-2"></i>Assistir Vídeo
                        </a>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            
            <!-- Ações -->
            <div class="mt-6 text-center">
                <a href="{{ route('simulados.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 mr-4">
                    <i class="fas fa-list mr-2"></i>Ver Todos os Simulados
                </a>
                
                @if(!$tentativa->aprovado)
                <a href="{{ route('simulados.show', $tentativa->simulado->id) }}" 
                   class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <i class="fas fa-redo mr-2"></i>Tentar Novamente
                </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>