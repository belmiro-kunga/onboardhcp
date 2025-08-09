@props(['title' => 'Dashboard'])

<div class="header flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8 bg-white border-b border-gray-200">
    <div class="flex items-center">
        <!-- Mobile Menu Button -->
        <button onclick="toggleMobileMenu()" class="lg:hidden p-2 text-gray-400 hover:text-gray-600 mr-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        
        <h1 class="text-lg sm:text-xl font-semibold text-gray-900">{{ $title }}</h1>
    </div>
    
    <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <button class="p-2 text-gray-400 hover:text-gray-600 relative">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2z"></path>
            </svg>
            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
        </button>
        
        <!-- User Avatar -->
        <div class="flex items-center space-x-3">
            <div class="avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="hidden md:block">
                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500">Administrador</p>
            </div>
        </div>
        
        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Sair
            </button>
        </form>
    </div>
</div>