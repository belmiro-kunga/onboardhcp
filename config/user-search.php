<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Search Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the advanced user search
    | and filtering system.
    |
    */

    'pagination' => [
        'default_per_page' => 25,
        'max_per_page' => 100,
        'available_options' => [10, 25, 50, 100],
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 300, // 5 minutes
        'prefix' => 'user_search_',
        'filter_options_ttl' => 3600, // 1 hour for filter options
    ],

    'search' => [
        'min_search_length' => 2,
        'debounce_delay' => 300, // milliseconds
        'max_suggestions' => 10,
        'highlight_matches' => true,
    ],

    'filters' => [
        'status_options' => [
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'pending' => 'Pendente',
            'blocked' => 'Bloqueado',
            'suspended' => 'Suspenso',
        ],

        'activity_options' => [
            'active' => 'Ativos (últimos 30 dias)',
            'recent' => 'Recentes (últimos 7 dias)',
            'inactive' => 'Inativos',
            'never_logged' => 'Nunca fizeram login',
        ],

        'user_type_options' => [
            'admin' => 'Administradores',
            'employee' => 'Funcionários',
        ],

        'sort_options' => [
            'name' => 'Nome',
            'email' => 'Email',
            'status' => 'Status',
            'department' => 'Departamento',
            'position' => 'Cargo',
            'hire_date' => 'Data de Admissão',
            'last_login' => 'Último Acesso',
            'created_at' => 'Data de Criação',
        ],

        'default_sort' => [
            'field' => 'created_at',
            'direction' => 'desc',
        ],
    ],

    'export' => [
        'max_records' => 10000,
        'formats' => ['csv', 'xlsx', 'pdf'],
        'default_format' => 'csv',
        'chunk_size' => 1000,
        'async_threshold' => 5000, // Records above this will be processed async
        'temp_file_retention_hours' => 24,
    ],

    'import' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB in bytes
        'max_sync_size' => 1 * 1024 * 1024, // 1MB - files larger than this use async processing
        'allowed_mimes' => [
            'text/csv',
            'application/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ],
        'allowed_extensions' => ['csv', 'txt', 'xlsx', 'xls'],
        'batch_size' => 1000,
        'validation_rules' => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'role' => 'nullable|string|exists:roles,name',
            'status' => 'nullable|in:active,inactive,pending,blocked,suspended'
        ],
        'default_values' => [
            'status' => 'active',
            'role' => 'funcionario'
        ],
        'auto_welcome_emails' => true,
        'duplicate_handling' => 'skip', // skip, update, error
        'preview_rows' => 10,
        'error_reporting' => [
            'detailed_errors' => true,
            'export_error_report' => true,
            'max_errors_display' => 100,
            'group_similar_errors' => true
        ],
        'data_transformation' => [
            'trim_whitespace' => true,
            'normalize_phone_numbers' => true,
            'capitalize_names' => true,
            'lowercase_emails' => true
        ],
        'backup' => [
            'create_backup_before_import' => true,
            'backup_retention_days' => 30
        ]
    ],

    'saved_searches' => [
        'enabled' => true,
        'max_per_user' => 10,
        'default_retention_days' => 30,
    ],

    'real_time' => [
        'enabled' => true,
        'websocket_channel' => 'user-updates',
        'broadcast_events' => [
            'user_created',
            'user_updated',
            'user_deleted',
            'bulk_action_performed',
        ],
    ],

    'security' => [
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 100,
            'per_minutes' => 1,
        ],
        'log_searches' => true,
        'mask_sensitive_data' => true,
    ],

    'performance' => [
        'enable_query_optimization' => true,
        'use_database_indexes' => true,
        'enable_result_caching' => true,
        'cache_expensive_queries' => true,
        'max_query_time' => 5, // seconds
    ],
];