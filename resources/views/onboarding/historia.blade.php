<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossa História - Hemera Capital Partners</title>
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
                    <h1 class="text-xl font-semibold text-gray-900">Nossa História</h1>
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
            <div class="history-hero">
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-4">Nossa Jornada</h1>
                    <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                        Conheça a trajetória da Hemera Capital Partners desde sua fundação até se tornar uma referência no mercado financeiro.
                    </p>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="relative">
            <!-- Timeline Line -->
            <div class="absolute left-1/2 transform -translate-x-px h-full w-0.5 bg-gray-300"></div>

            <!-- Timeline Items -->
            @foreach($timeline as $index => $evento)
                <div class="relative flex items-center mb-12 {{ $index % 2 == 0 ? 'justify-start' : 'justify-end' }}">
                    <!-- Timeline Node -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-4 h-4 bg-blue-600 rounded-full border-4 border-white shadow-lg z-10"></div>
                    
                    <!-- Content Card -->
                    <div class="w-5/12 {{ $index % 2 == 0 ? 'pr-8 text-right' : 'pl-8 text-left' }}">
                        <div class="timeline-card {{ $index % 2 == 0 ? 'timeline-left' : 'timeline-right' }}">
                            <div class="flex items-center mb-3 {{ $index % 2 == 0 ? 'justify-end' : 'justify-start' }}">
                                <div class="timeline-year">{{ $evento['ano'] }}</div>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $evento['titulo'] }}</h3>
                            <p class="text-gray-600 leading-relaxed">{{ $evento['descricao'] }}</p>
                            @if(isset($evento['conquistas']) && count($evento['conquistas']) > 0)
                                <div class="mt-4">
                                    <h4 class="font-semibold text-gray-800 mb-2">Principais Conquistas:</h4>
                                    <ul class="space-y-1">
                                        @foreach($evento['conquistas'] as $conquista)
                                            <li class="flex items-center text-sm text-gray-600 {{ $index % 2 == 0 ? 'justify-end' : 'justify-start' }}">
                                                <svg class="w-3 h-3 text-green-500 {{ $index % 2 == 0 ? 'ml-2' : 'mr-2' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $conquista }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Key Milestones -->
        <div class="mt-16 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">Marcos Importantes</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="milestone-card">
                    <div class="milestone-icon bg-blue-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Fundação</h3>
                    <p class="text-gray-600 text-sm">Estabelecimento da empresa com foco em gestão de patrimônio</p>
                </div>

                <div class="milestone-card">
                    <div class="milestone-icon bg-green-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Crescimento</h3>
                    <p class="text-gray-600 text-sm">Expansão da carteira de clientes e diversificação de produtos</p>
                </div>

                <div class="milestone-card">
                    <div class="milestone-icon bg-purple-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Inovação</h3>
                    <p class="text-gray-600 text-sm">Implementação de tecnologias avançadas e processos digitais</p>
                </div>

                <div class="milestone-card">
                    <div class="milestone-icon bg-orange-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Liderança</h3>
                    <p class="text-gray-600 text-sm">Reconhecimento como referência no mercado de gestão de patrimônio</p>
                </div>
            </div>
        </div>

        <!-- Vision for Future -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 text-center text-white mb-12">
            <h2 class="text-2xl font-bold mb-4">Nosso Futuro</h2>
            <p class="text-lg text-blue-100 max-w-3xl mx-auto leading-relaxed">
                Continuamos comprometidos em ser pioneiros na gestão de patrimônio, sempre buscando inovação, 
                excelência e os melhores resultados para nossos clientes. O futuro nos reserva ainda mais 
                conquistas e oportunidades de crescimento.
            </p>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between items-center">
            <a href="{{ route('onboarding.sobre-empresa') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Anterior: Sobre a Empresa
            </a>
            <a href="{{ route('onboarding.departamentos') }}" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Próximo: Departamentos
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </main>

    <style>
        .history-hero {
            @apply bg-gradient-to-br from-purple-600 to-pink-700 rounded-2xl p-12 text-center relative overflow-hidden;
        }

        .timeline-card {
            @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6 relative;
        }

        .timeline-left::after {
            content: '';
            @apply absolute top-6 right-0 transform translate-x-full w-0 h-0;
            border-left: 10px solid white;
            border-top: 10px solid transparent;
            border-bottom: 10px solid transparent;
        }

        .timeline-right::after {
            content: '';
            @apply absolute top-6 left-0 transform -translate-x-full w-0 h-0;
            border-right: 10px solid white;
            border-top: 10px solid transparent;
            border-bottom: 10px solid transparent;
        }

        .timeline-year {
            @apply bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-bold;
        }

        .milestone-card {
            @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-md transition-shadow;
        }

        .milestone-icon {
            @apply w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-4;
        }
    </style>
</body>
</html>
