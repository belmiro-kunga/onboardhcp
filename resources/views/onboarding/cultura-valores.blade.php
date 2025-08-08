<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cultura e Valores - Hemera Capital Partners</title>
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
                    <h1 class="text-xl font-semibold text-gray-900">Cultura e Valores</h1>
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
    <main class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <div class="culture-hero">
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-4">Nossa Cultura</h1>
                    <p class="text-xl text-yellow-100 max-w-2xl mx-auto">
                        Conheça os valores que nos guiam e a cultura que faz da Hemera Capital Partners um lugar especial para trabalhar.
                    </p>
                </div>
            </div>
        </div>

        <!-- Core Values -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">Nossos Valores Fundamentais</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($cultura['valores'] as $valor)
                    <div class="value-card">
                        <div class="value-icon {{ $valor['cor'] }}">
                            {!! $valor['icone'] !!}
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $valor['nome'] }}</h3>
                        <p class="text-gray-600 leading-relaxed">{{ $valor['descricao'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Culture Principles -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Nossos Princípios Culturais</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach($cultura['principios'] as $principio)
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $principio['titulo'] }}</h3>
                            <p class="text-gray-600">{{ $principio['descricao'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Work Environment -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Nosso Ambiente de Trabalho</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="environment-card">
                    <div class="environment-icon bg-green-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Inovação Constante</h3>
                    <p class="text-gray-600">Encorajamos novas ideias e abordagens criativas para resolver desafios.</p>
                </div>

                <div class="environment-card">
                    <div class="environment-icon bg-blue-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Colaboração</h3>
                    <p class="text-gray-600">Trabalhamos em equipe, compartilhando conhecimento e apoiando uns aos outros.</p>
                </div>

                <div class="environment-card">
                    <div class="environment-icon bg-purple-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Aprendizado Contínuo</h3>
                    <p class="text-gray-600">Investimos no desenvolvimento pessoal e profissional de cada colaborador.</p>
                </div>

                <div class="environment-card">
                    <div class="environment-icon bg-orange-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Integridade</h3>
                    <p class="text-gray-600">Mantemos os mais altos padrões éticos em todas as nossas ações.</p>
                </div>
            </div>
        </div>

        <!-- Benefits -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 text-white mb-12">
            <h2 class="text-2xl font-bold text-center mb-8">Benefícios e Vantagens</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($cultura['beneficios'] as $beneficio)
                    <div class="benefit-item">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                {!! $beneficio['icone'] !!}
                            </div>
                            <div>
                                <h3 class="font-semibold">{{ $beneficio['nome'] }}</h3>
                                <p class="text-blue-100 text-sm">{{ $beneficio['descricao'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Code of Conduct -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Código de Conduta</h2>
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-1">
                        <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700">Tratamos todos os colegas com respeito e dignidade, independentemente de cargo ou função.</p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-1">
                        <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700">Mantemos confidencialidade absoluta sobre informações de clientes e da empresa.</p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-1">
                        <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700">Agimos sempre com transparência e honestidade em todas as nossas relações.</p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-1">
                        <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700">Buscamos sempre a excelência em nosso trabalho e nos comprometemos com resultados de qualidade.</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between items-center">
            <a href="{{ route('onboarding.departamentos') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Anterior: Departamentos
            </a>
            <a href="{{ route('onboarding.organograma') }}" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Próximo: Organograma
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </main>

    <style>
        .culture-hero {
            @apply bg-gradient-to-br from-yellow-600 to-orange-700 rounded-2xl p-12 text-center relative overflow-hidden;
        }

        .value-card {
            @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-md transition-shadow;
        }

        .value-icon {
            @apply w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4;
        }

        .value-icon svg {
            @apply w-8 h-8 text-white;
        }

        .environment-card {
            @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow;
        }

        .environment-icon {
            @apply w-12 h-12 rounded-lg flex items-center justify-center mb-4;
        }

        .benefit-item {
            @apply bg-white bg-opacity-10 rounded-lg p-4;
        }
    </style>
</body>
</html>
