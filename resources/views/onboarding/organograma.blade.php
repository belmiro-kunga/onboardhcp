<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organograma - Hemera Capital Partners</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('onboarding.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-semibold text-gray-900">Organograma</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-800">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <div class="org-hero">
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-4">Estrutura Organizacional</h1>
                    <p class="text-xl text-indigo-100 max-w-2xl mx-auto">
                        Conheça a estrutura de liderança e as principais pessoas que fazem parte da Hemera Capital Partners.
                    </p>
                </div>
            </div>
        </div>

        <!-- CEO Level -->
        <div class="text-center mb-12">
            <div class="ceo-card">
                <div class="ceo-avatar">
                    <span class="text-2xl font-bold text-white">{{ substr($organograma['ceo']['nome'], 0, 2) }}</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $organograma['ceo']['nome'] }}</h2>
                <p class="text-lg text-blue-600 font-semibold mb-2">{{ $organograma['ceo']['cargo'] }}</p>
                <p class="text-gray-600 max-w-md mx-auto">{{ $organograma['ceo']['bio'] }}</p>
                <div class="mt-4 flex justify-center space-x-4 text-sm text-gray-500">
                    <span>{{ $organograma['ceo']['experiencia'] }}</span>
                    <span>•</span>
                    <span>{{ $organograma['ceo']['formacao'] }}</span>
                </div>
            </div>
        </div>

        <!-- Directors Level -->
        <div class="mb-12">
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-8">Diretoria</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($organograma['diretores'] as $diretor)
                    <div class="director-card">
                        <div class="director-avatar {{ $diretor['cor'] }}">
                            <span class="text-lg font-bold text-white">{{ substr($diretor['nome'], 0, 2) }}</span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-1">{{ $diretor['nome'] }}</h4>
                        <p class="text-blue-600 font-semibold mb-2">{{ $diretor['cargo'] }}</p>
                        <p class="text-gray-600 text-sm mb-3">{{ $diretor['area'] }}</p>
                        <p class="text-gray-500 text-xs">{{ $diretor['experiencia'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Managers Level -->
        <div class="mb-12">
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-8">Gerências</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($organograma['gerentes'] as $gerente)
                    <div class="manager-card">
                        <div class="manager-avatar {{ $gerente['cor'] }}">
                            <span class="font-bold text-white">{{ substr($gerente['nome'], 0, 2) }}</span>
                        </div>
                        <h5 class="font-bold text-gray-900 mb-1">{{ $gerente['nome'] }}</h5>
                        <p class="text-sm text-blue-600 font-medium mb-1">{{ $gerente['cargo'] }}</p>
                        <p class="text-xs text-gray-600 mb-2">{{ $gerente['departamento'] }}</p>
                        <div class="text-xs text-gray-500">
                            <p>Equipe: {{ $gerente['equipe'] }} pessoas</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Organizational Chart Visual -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-12">
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-8">Estrutura Hierárquica</h3>
            <div class="org-chart">
                <!-- CEO -->
                <div class="org-level level-1">
                    <div class="org-node ceo-node">
                        <div class="node-content">
                            <div class="node-avatar bg-blue-600">EH</div>
                            <div class="node-info">
                                <p class="font-bold">Eduardo Hemera</p>
                                <p class="text-sm text-gray-600">CEO</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Directors -->
                <div class="org-level level-2">
                    <div class="org-node">
                        <div class="node-content">
                            <div class="node-avatar bg-green-600">MS</div>
                            <div class="node-info">
                                <p class="font-semibold text-sm">Maria Silva</p>
                                <p class="text-xs text-gray-600">Dir. Operações</p>
                            </div>
                        </div>
                    </div>
                    <div class="org-node">
                        <div class="node-content">
                            <div class="node-avatar bg-purple-600">JS</div>
                            <div class="node-info">
                                <p class="font-semibold text-sm">João Santos</p>
                                <p class="text-xs text-gray-600">Dir. Investimentos</p>
                            </div>
                        </div>
                    </div>
                    <div class="org-node">
                        <div class="node-content">
                            <div class="node-avatar bg-orange-600">AC</div>
                            <div class="node-info">
                                <p class="font-semibold text-sm">Ana Costa</p>
                                <p class="text-xs text-gray-600">Dir. Comercial</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Managers -->
                <div class="org-level level-3">
                    <div class="org-node small">
                        <div class="node-content">
                            <div class="node-avatar bg-blue-500">PO</div>
                            <div class="node-info">
                                <p class="font-medium text-xs">Patricia Oliveira</p>
                                <p class="text-xs text-gray-600">Ger. RH</p>
                            </div>
                        </div>
                    </div>
                    <div class="org-node small">
                        <div class="node-content">
                            <div class="node-avatar bg-green-500">CS</div>
                            <div class="node-info">
                                <p class="font-medium text-xs">Carlos Silva</p>
                                <p class="text-xs text-gray-600">Ger. TI</p>
                            </div>
                        </div>
                    </div>
                    <div class="org-node small">
                        <div class="node-content">
                            <div class="node-avatar bg-purple-500">RF</div>
                            <div class="node-info">
                                <p class="font-medium text-xs">Roberto Ferreira</p>
                                <p class="text-xs text-gray-600">Ger. Análise</p>
                            </div>
                        </div>
                    </div>
                    <div class="org-node small">
                        <div class="node-content">
                            <div class="node-avatar bg-orange-500">LL</div>
                            <div class="node-info">
                                <p class="font-medium text-xs">Lucia Lima</p>
                                <p class="text-xs text-gray-600">Ger. Vendas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Contatos Importantes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-blue-800 font-medium">Recursos Humanos</p>
                            <p class="text-blue-700 text-sm">Patricia Oliveira - patricia@hemeracapital.com</p>
                        </div>
                        <div>
                            <p class="text-blue-800 font-medium">Tecnologia da Informação</p>
                            <p class="text-blue-700 text-sm">Carlos Silva - ti@hemeracapital.com</p>
                        </div>
                        <div>
                            <p class="text-blue-800 font-medium">Administrativo</p>
                            <p class="text-blue-700 text-sm">Maria Silva - admin@hemeracapital.com</p>
                        </div>
                        <div>
                            <p class="text-blue-800 font-medium">Comercial</p>
                            <p class="text-blue-700 text-sm">Ana Costa - comercial@hemeracapital.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between items-center">
            <a href="{{ route('onboarding.cultura-valores') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Anterior: Cultura e Valores
            </a>
            <a href="{{ route('funcionario') }}" class="inline-flex items-center px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                Finalizar Onboarding
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </a>
        </div>
    </main>

    <style>
        .org-hero {
            @apply bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-12 text-center relative overflow-hidden;
        }

        .ceo-card {
            @apply bg-white rounded-lg shadow-lg border border-gray-200 p-8 max-w-md mx-auto;
        }

        .ceo-avatar {
            @apply w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4;
        }

        .director-card {
            @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-md transition-shadow;
        }

        .director-avatar {
            @apply w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4;
        }

        .manager-card {
            @apply bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center hover:shadow-md transition-shadow;
        }

        .manager-avatar {
            @apply w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3;
        }

        .org-chart {
            @apply flex flex-col items-center space-y-8;
        }

        .org-level {
            @apply flex justify-center items-center space-x-8 flex-wrap;
        }

        .org-node {
            @apply bg-gray-50 rounded-lg p-4 border border-gray-200;
        }

        .org-node.small {
            @apply p-3;
        }

        .org-node.ceo-node {
            @apply bg-blue-50 border-blue-200;
        }

        .node-content {
            @apply flex items-center space-x-3;
        }

        .node-avatar {
            @apply w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm;
        }

        .node-info {
            @apply text-left;
        }

        @media (max-width: 768px) {
            .org-level {
                @apply flex-col space-x-0 space-y-4;
            }
        }
    </style>
</body>
</html>
