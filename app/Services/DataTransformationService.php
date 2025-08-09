<?php

namespace App\Services;

class DataTransformationService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('user-search.import.data_transformation', []);
    }

    /**
     * Transform row data according to configuration
     */
    public function transformRow(array $row): array
    {
        $transformed = [];

        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeKey($key);
            $transformedValue = $this->transformValue($normalizedKey, $value);
            $transformed[$normalizedKey] = $transformedValue;
        }

        return $transformed;
    }

    /**
     * Normalize column keys
     */
    protected function normalizeKey(string $key): string
    {
        // Convert Portuguese column names to English
        $keyMap = [
            'nome' => 'name',
            'nome_completo' => 'name',
            'telefone' => 'phone',
            'telemovel' => 'phone',
            'departamento' => 'department',
            'cargo' => 'position',
            'funcao' => 'position',
            'data_de_admissao' => 'hire_date',
            'data_admissao' => 'hire_date',
            'admissao' => 'hire_date',
            'roles' => 'role',
            'perfil' => 'role',
            'estado' => 'status',
            'situacao' => 'status'
        ];

        $normalizedKey = strtolower(trim($key));
        $normalizedKey = str_replace([' ', '-', '_'], '_', $normalizedKey);
        
        return $keyMap[$normalizedKey] ?? $normalizedKey;
    }

    /**
     * Transform individual values
     */
    protected function transformValue(string $key, $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = $this->config['trim_whitespace'] ? trim($value) : $value;

        switch ($key) {
            case 'name':
                return $this->transformName($value);
            
            case 'email':
                return $this->transformEmail($value);
            
            case 'phone':
                return $this->transformPhone($value);
            
            case 'status':
                return $this->transformStatus($value);
            
            case 'role':
                return $this->transformRole($value);
            
            case 'hire_date':
                return $this->transformDate($value);
            
            default:
                return $value;
        }
    }

    /**
     * Transform name field
     */
    protected function transformName(string $name): string
    {
        if ($this->config['capitalize_names'] ?? true) {
            // Capitalize each word properly
            return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
        }
        
        return trim($name);
    }

    /**
     * Transform email field
     */
    protected function transformEmail(string $email): string
    {
        $email = trim($email);
        
        if ($this->config['lowercase_emails'] ?? true) {
            $email = strtolower($email);
        }
        
        return $email;
    }

    /**
     * Transform phone field
     */
    protected function transformPhone(string $phone): string
    {
        if (!($this->config['normalize_phone_numbers'] ?? true)) {
            return trim($phone);
        }

        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Handle Portuguese phone numbers
        if (strlen($phone) === 9 && !str_starts_with($phone, '+')) {
            // Add Portugal country code
            $phone = '+351' . $phone;
        } elseif (strlen($phone) === 12 && str_starts_with($phone, '351')) {
            // Add + to Portugal numbers
            $phone = '+' . $phone;
        }
        
        return $phone;
    }

    /**
     * Transform status field
     */
    protected function transformStatus(string $status): string
    {
        $statusMap = [
            'ativo' => 'active',
            'activo' => 'active',
            'ativa' => 'active',
            'inativo' => 'inactive',
            'inactivo' => 'inactive',
            'inativa' => 'inactive',
            'pendente' => 'pending',
            'bloqueado' => 'blocked',
            'bloqueada' => 'blocked',
            'suspenso' => 'suspended',
            'suspensa' => 'suspended',
            'desativado' => 'inactive',
            'desativada' => 'inactive'
        ];

        $normalizedStatus = strtolower(trim($status));
        return $statusMap[$normalizedStatus] ?? $normalizedStatus;
    }

    /**
     * Transform role field
     */
    protected function transformRole(string $role): string
    {
        $roleMap = [
            'administrador' => 'admin',
            'administrator' => 'admin',
            'funcionario' => 'funcionario',
            'funcionário' => 'funcionario',
            'employee' => 'funcionario',
            'empregado' => 'funcionario',
            'colaborador' => 'funcionario',
            'user' => 'funcionario',
            'utilizador' => 'funcionario'
        ];

        $normalizedRole = strtolower(trim($role));
        return $roleMap[$normalizedRole] ?? $normalizedRole;
    }

    /**
     * Transform date field
     */
    protected function transformDate(string $date): ?string
    {
        $date = trim($date);
        
        if (empty($date)) {
            return null;
        }

        // Try to parse various date formats
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'm/d/Y',
            'm-d-Y',
            'Y/m/d',
            'd/m/y',
            'd-m-y',
            'm/d/y',
            'm-d-y'
        ];

        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if ($parsed && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }

        // If no format matches, return original value
        // The validation will catch invalid dates
        return $date;
    }

    /**
     * Validate transformed data
     */
    public function validateTransformedData(array $data): array
    {
        $issues = [];

        // Check for required fields
        if (empty($data['name'])) {
            $issues[] = 'Nome é obrigatório';
        }

        if (empty($data['email'])) {
            $issues[] = 'Email é obrigatório';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $issues[] = 'Email inválido';
        }

        // Check phone format
        if (!empty($data['phone']) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $data['phone'])) {
            $issues[] = 'Formato de telefone inválido';
        }

        // Check status
        if (!empty($data['status']) && !in_array($data['status'], ['active', 'inactive', 'pending', 'blocked', 'suspended'])) {
            $issues[] = 'Status inválido';
        }

        // Check role
        if (!empty($data['role']) && !in_array($data['role'], ['admin', 'funcionario'])) {
            $issues[] = 'Role inválido';
        }

        return $issues;
    }

    /**
     * Get transformation statistics
     */
    public function getTransformationStats(array $originalData, array $transformedData): array
    {
        $stats = [
            'fields_transformed' => 0,
            'values_changed' => 0,
            'transformations' => []
        ];

        foreach ($originalData as $key => $originalValue) {
            $normalizedKey = $this->normalizeKey($key);
            $transformedValue = $transformedData[$normalizedKey] ?? null;

            if ($key !== $normalizedKey) {
                $stats['fields_transformed']++;
                $stats['transformations'][] = [
                    'type' => 'key_normalization',
                    'from' => $key,
                    'to' => $normalizedKey
                ];
            }

            if ($originalValue !== $transformedValue) {
                $stats['values_changed']++;
                $stats['transformations'][] = [
                    'type' => 'value_transformation',
                    'field' => $normalizedKey,
                    'from' => $originalValue,
                    'to' => $transformedValue
                ];
            }
        }

        return $stats;
    }
}