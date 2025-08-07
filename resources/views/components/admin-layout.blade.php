<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Panel' }} - Hemera Capital Partners</title>
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
        
        .sidebar {
            width: 240px;
            background-color: var(--background);
            border-right: 1px solid var(--border);
        }
        
        .menu-item {
            height: 48px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            color: var(--text-secondary);
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .menu-item:hover {
            background-color: var(--menu-highlight);
            color: var(--primary);
        }
        
        .menu-item.active {
            background-color: var(--menu-highlight);
            color: var(--primary);
            border-right: 3px solid var(--primary);
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
        }
        
        .card {
            background-color: var(--background);
            border-radius: 8px;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }
        
        .widget {
            background-color: var(--background);
            border-radius: 8px;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
        
        .metric-value {
            font-size: 32px;
            color: var(--primary);
            font-weight: 700;
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
        
        .role-admin {
            background-color: #F6FFED;
            color: var(--success);
        }
        
        .role-user {
            background-color: #FFF7E6;
            color: var(--warning);
        }
    </style>
    {{ $styles ?? '' }}
</head>
<body class="min-h-screen flex">
    <!-- Sidebar -->
    <x-admin-sidebar :active="$activeMenu ?? 'dashboard'" />

    <!-- Main Content -->
    <div class="flex-1 ml-60">
        <!-- Header -->
        <x-admin-header :title="$pageTitle ?? 'Dashboard'" />

        <!-- Content -->
        <div class="content">
            {{ $slot }}
        </div>
    </div>

    {{ $scripts ?? '' }}
</body>
</html>