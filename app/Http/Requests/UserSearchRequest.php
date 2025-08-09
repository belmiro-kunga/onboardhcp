<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $maxPerPage = config('user-search.pagination.max_per_page', 100);
        $availableStatuses = array_keys(config('user-search.filters.status_options', []));
        $availableActivities = array_keys(config('user-search.filters.activity_options', []));
        $availableSortFields = array_keys(config('user-search.filters.sort_options', []));

        return [
            // Search term
            'search' => 'nullable|string|max:255|min:2',
            
            // Status filters
            'status' => 'nullable|array',
            'status.*' => 'string|in:' . implode(',', $availableStatuses),
            
            // Role filters
            'role' => 'nullable|array',
            'role.*' => 'string|max:100',
            
            // Department filters
            'department' => 'nullable|array',
            'department.*' => 'string|max:100',
            
            // User type filter
            'user_type' => 'nullable|string|in:admin,employee',
            
            // Activity filter
            'activity' => 'nullable|string|in:' . implode(',', $availableActivities),
            
            // Date range filters
            'created_from' => 'nullable|date|before_or_equal:today',
            'created_to' => 'nullable|date|after_or_equal:created_from|before_or_equal:today',
            'hire_date_from' => 'nullable|date',
            'hire_date_to' => 'nullable|date|after_or_equal:hire_date_from',
            
            // Sorting
            'sort_by' => 'nullable|string|in:' . implode(',', $availableSortFields),
            'sort_direction' => 'nullable|string|in:asc,desc',
            
            // Pagination
            'per_page' => "nullable|integer|min:1|max:{$maxPerPage}",
            'page' => 'nullable|integer|min:1',
            
            // Export
            'export' => 'nullable|boolean',
            'export_format' => 'nullable|string|in:csv,xlsx,pdf',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'search.min' => 'O termo de pesquisa deve ter pelo menos 2 caracteres.',
            'search.max' => 'O termo de pesquisa não pode exceder 255 caracteres.',
            'status.*.in' => 'Status inválido selecionado.',
            'user_type.in' => 'Tipo de utilizador inválido.',
            'activity.in' => 'Filtro de atividade inválido.',
            'created_from.before_or_equal' => 'A data de criação inicial não pode ser futura.',
            'created_to.after_or_equal' => 'A data de criação final deve ser posterior à inicial.',
            'created_to.before_or_equal' => 'A data de criação final não pode ser futura.',
            'hire_date_to.after_or_equal' => 'A data de admissão final deve ser posterior à inicial.',
            'sort_by.in' => 'Campo de ordenação inválido.',
            'sort_direction.in' => 'Direção de ordenação deve ser "asc" ou "desc".',
            'per_page.max' => 'Máximo de :max registos por página.',
            'per_page.min' => 'Mínimo de 1 registo por página.',
            'page.min' => 'Número da página deve ser maior que 0.',
            'export_format.in' => 'Formato de exportação inválido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'termo de pesquisa',
            'status' => 'status',
            'role' => 'role',
            'department' => 'departamento',
            'user_type' => 'tipo de utilizador',
            'activity' => 'atividade',
            'created_from' => 'data de criação inicial',
            'created_to' => 'data de criação final',
            'hire_date_from' => 'data de admissão inicial',
            'hire_date_to' => 'data de admissão final',
            'sort_by' => 'campo de ordenação',
            'sort_direction' => 'direção de ordenação',
            'per_page' => 'registos por página',
            'page' => 'página',
            'export_format' => 'formato de exportação',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string arrays to actual arrays for multi-select fields
        $multiSelectFields = ['status', 'role', 'department'];
        
        foreach ($multiSelectFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([
                    $field => explode(',', $this->input($field))
                ]);
            }
        }

        // Set default values
        $this->merge([
            'sort_by' => $this->input('sort_by', config('user-search.filters.default_sort.field')),
            'sort_direction' => $this->input('sort_direction', config('user-search.filters.default_sort.direction')),
            'per_page' => $this->input('per_page', config('user-search.pagination.default_per_page')),
            'page' => $this->input('page', 1),
        ]);
    }

    /**
     * Get the validated data with defaults applied.
     */
    public function getSearchFilters(): array
    {
        $validated = $this->validated();
        
        // Remove empty values
        return array_filter($validated, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return $value !== null && $value !== '';
        });
    }

    /**
     * Check if this is an export request.
     */
    public function isExportRequest(): bool
    {
        return $this->boolean('export', false);
    }

    /**
     * Get the export format.
     */
    public function getExportFormat(): string
    {
        return $this->input('export_format', config('user-search.export.default_format'));
    }
}