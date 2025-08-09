@props(['title' => 'OnboardHCP'])

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Hemera Capital Partners</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #2F54EB;
            --secondary: #F5F7FA;
            --success: #52C41A;
            --warning: #FAAD14;
            --error: #FF4D4F;
            --info: #1890FF;
            --background: #FFFFFF;
            --text-primary: #1F1F1F;
            --text-secondary: #8C8C8C;
            --border: #E5E7EB;
            --menu-background: #F9FAFB;
            --menu-highlight: #E6F7FF;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--menu-background);
        }
        
        .header {
            height: 64px;
            background-color: var(--background);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
        }
        
        .content {
            padding: 24px;
            background-color: var(--menu-background);
            min-height: calc(100vh - 64px);
        }
        
        .card {
            background-color: var(--background);
            border-radius: 8px;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--background);
            border-radius: 6px;
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .btn-secondary {
            background-color: #F0F5FF;
            color: var(--primary);
            border-radius: 6px;
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary);
            color: var(--background);
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .input-field {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px;
            width: 100%;
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(47, 84, 235, 0.1);
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #E6F7FF;
            color: var(--info);
        }
    </style>
    {{ $styles ?? '' }}
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="header flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8 bg-white border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                <span class="text-white font-bold text-sm">HC</span>
            </div>
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
            @auth
            <div class="flex items-center space-x-3">
                <div class="avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="hidden md:block">
                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">Funcionário</p>
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
            @endauth
        </div>
    </div>

    <!-- Main Content -->
    <div class="content p-4 sm:p-6 lg:p-8">
        {{ $slot }}
    </div>

    {{ $scripts ?? '' }}
</body>
</html>
