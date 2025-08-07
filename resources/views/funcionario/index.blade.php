@extends('layouts.app')

@section('title', 'Integração - Hemera Capital Partners')

@section('content')
<div class="w-full max-w-4xl mx-auto">
    <div class="card">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 mr-4 rounded-full gradient-bg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Hemera Capital Partners</h1>
                        <p class="text-lg text-gray-600">Sistema de Integração</p>
                    </div>
                </div>
                <p class="text-gray-600 mt-2">Bem-vindo ao processo de integração, {{ Auth::user()->name ?? Auth::user()->email }}!</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.users') }}" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    👥 Utilizadores
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Sair
                    </button>
                </form>
            </div>
        </div>

        <!-- Progresso da Integração -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Progresso da Integração</h3>
                <span class="text-sm text-gray-600">0% Concluído</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-blue-400 to-purple-500 h-2 rounded-full" style="width: 0%"></div>
            </div>
        </div>

        <!-- Etapas de Integração -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Step 1: Documentação -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800">Documentação</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Complete o seu registo e envie os documentos necessários</p>
                <button class="w-full btn-primary text-sm">Iniciar</button>
            </div>

            <!-- Step 2: Treinamento -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow opacity-50">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800">Formação</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Complete os módulos de formação obrigatórios</p>
                <button class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-lg text-sm cursor-not-allowed">Bloqueado</button>
            </div>

            <!-- Step 3: Configuração -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow opacity-50">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800">Configuração</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Configure as suas preferências e acesso aos sistemas</p>
                <button class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-lg text-sm cursor-not-allowed">Bloqueado</button>
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 text-center">
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Bem-vindo à Hemera Capital Partners!</h3>
            <p class="text-gray-600 mb-4">
                Estamos entusiasmados por tê-lo na nossa equipa. Este sistema irá guiá-lo através do processo de integração, 
                garantindo que tenha todas as informações e recursos necessários para começar.
            </p>
            <div class="text-sm text-gray-500">
                Login realizado em {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection