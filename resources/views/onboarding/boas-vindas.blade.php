<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boas-vindas - Hemera Capital Partners</title>
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
                    <h1 class="text-xl font-semibold text-gray-900">Boas-vindas</h1>
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
    <main class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Welcome Hero -->
        <div class="text-center mb-12">
            <div class="welcome-hero">
                <div class="floating-elements">
                    <div class="floating-circle circle-1"></div>
                    <div class="floating-circle circle-2"></div>
                    <div class="floating-circle circle-3"></div>
                </div>
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full mb-6 welcome-icon">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">
                        Seja muito bem-vindo(a), {{ $user->name }}! 🎉
                    </h1>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                        É com grande alegria que recebemos você na família <strong>Hemera Capital Partners</strong>. 
                        Estamos ansiosos para compartilhar esta jornada de crescimento e sucesso junto com você.
                    </p>
                </div>
            </div>
        </div>

        <!-- Welcome Message Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <!-- Message from CEO -->
            <div class="welcome-card">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">EH</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Mensagem do CEO</h3>
                        <p class="text-gray-600 mb-3">
                            "Bem-vindo à nossa equipe! Na Hemera Capital, acreditamos que nosso maior ativo são as pessoas. 
                            Você foi escolhido não apenas pelas suas competências técnicas, mas também por se alinhar com nossos valores e cultura."
                        </p>
                        <p class="text-sm text-gray-500 font-medium">Eduardo Hemera, CEO & Fundador</p>
                    </div>
                </div>
            </div>

            <!-- Message from HR -->
            <div class="welcome-card">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">PO</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Mensagem do RH</h3>
                        <p class="text-gray-600 mb-3">
                            "Estamos aqui para apoiá-lo em cada passo da sua jornada. Este processo de onboarding foi 
                            cuidadosamente planejado para que você se sinta acolhido e preparado para contribuir com nossos objetivos."
                        </p>
                        <p class="text-sm text-gray-500 font-medium">Patricia Oliveira, Gerente de RH</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- First Steps -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Seus Primeiros Passos</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Conheça a Empresa</h3>
                    <p class="text-gray-600 text-sm">Explore nossa história, missão e valores para entender melhor quem somos.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Explore os Departamentos</h3>
                    <p class="text-gray-600 text-sm">Conheça as diferentes áreas e como elas trabalham juntas.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Complete os Treinamentos</h3>
                    <p class="text-gray-600 text-sm">Participe dos cursos e simulados para se preparar para suas atividades.</p>
                </div>
            </div>
        </div>

        <!-- Important Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Informações Importantes</h3>
                    <ul class="text-blue-800 space-y-2">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Seu primeiro dia oficial será na próxima segunda-feira
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Complete este onboarding até o final da semana
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Sua gerente Patricia estará disponível para dúvidas
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Seus equipamentos serão entregues no primeiro dia
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between items-center">
            <a href="{{ route('onboarding.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar ao Menu
            </a>
            <a href="{{ route('onboarding.sobre-empresa') }}" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Próximo: Sobre a Empresa
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </main>

    <style>
        .welcome-hero {
            @apply relative bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-12 overflow-hidden;
        }

        .floating-elements {
            @apply absolute inset-0;
        }

        .floating-circle {
            @apply absolute rounded-full opacity-20;
            animation: float 6s ease-in-out infinite;
        }

        .circle-1 {
            @apply w-20 h-20 bg-blue-300 top-4 right-8;
            animation-delay: 0s;
        }

        .circle-2 {
            @apply w-16 h-16 bg-indigo-300 bottom-8 left-12;
            animation-delay: 2s;
        }

        .circle-3 {
            @apply w-12 h-12 bg-purple-300 top-1/2 right-1/4;
            animation-delay: 4s;
        }

        .welcome-icon {
            animation: pulse 2s infinite;
        }

        .welcome-card {
            @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow;
        }

        .step-card {
            @apply text-center;
        }

        .step-number {
            @apply w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>
</body>
</html>
