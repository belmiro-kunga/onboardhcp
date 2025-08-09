<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use App\Services\DataTransformationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class UsersImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected array $results = [];
    protected array $errors = [];
    protected DataTransformationService $transformationService;

    public function __construct()
    {
        $this->transformationService = new DataTransformationService();
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $index => $row) {
            try {
                $rowNumber = $index + 2; // +2 because of header row and 0-based index
                $this->validateRow($row->toArray(), $rowNumber);
                $user = $this->createUserFromRow($row->toArray());
                $this->results[] = $user;
            } catch (ValidationException $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'data' => $row->toArray(),
                    'message' => implode(', ', $e->validator->errors()->all())
                ];
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'data' => $row->toArray(),
                    'message' => 'Erro interno: ' . $e->getMessage()
                ];
            }
        }
    }

    /**
     * Validate row data
     */
    protected function validateRow(array $row, int $rowNumber): void
    {
        // Transform data first
        $transformedRow = $this->transformationService->transformRow($row);
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'role' => 'nullable|string|exists:roles,name',
            'status' => 'nullable|in:active,inactive,pending,blocked,suspended'
        ];

        $messages = [
            'name.required' => "Nome é obrigatório (linha {$rowNumber})",
            'email.required' => "Email é obrigatório (linha {$rowNumber})",
            'email.email' => "Email inválido (linha {$rowNumber})",
            'email.unique' => "Email já existe no sistema (linha {$rowNumber})",
            'hire_date.date' => "Data de admissão inválida (linha {$rowNumber})",
            'role.exists' => "Role não existe (linha {$rowNumber})",
            'status.in' => "Status inválido (linha {$rowNumber})"
        ];

        $validator = Validator::make($transformedRow, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Create user from row data
     */
    protected function createUserFromRow(array $row): User
    {
        // Transform data using the transformation service
        $transformedRow = $this->transformationService->transformRow($row);
        
        $userData = [
            'name' => $transformedRow['name'] ?? '',
            'email' => $transformedRow['email'],
            'phone' => $transformedRow['phone'] ?? null,
            'department' => $transformedRow['department'] ?? null,
            'position' => $transformedRow['position'] ?? null,
            'hire_date' => $transformedRow['hire_date'] ?? null,
            'status' => $transformedRow['status'] ?? 'active',
            'password' => Hash::make($this->generateTemporaryPassword()),
            'email_verified_at' => now()
        ];

        $user = User::create($userData);

        // Assign role if specified
        $roleName = $transformedRow['role'] ?? null;
        if (!empty($roleName)) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }
        }

        return $user;
    }

    /**
     * Normalize status values
     */
    protected function normalizeStatus(string $status): string
    {
        $statusMap = [
            'ativo' => 'active',
            'inativo' => 'inactive',
            'pendente' => 'pending',
            'bloqueado' => 'blocked',
            'suspenso' => 'suspended'
        ];

        $normalizedStatus = strtolower(trim($status));
        return $statusMap[$normalizedStatus] ?? $normalizedStatus;
    }

    /**
     * Generate temporary password for imported users
     */
    protected function generateTemporaryPassword(): string
    {
        return 'Temp' . rand(1000, 9999) . '!';
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get import errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get success count
     */
    public function getSuccessCount(): int
    {
        return count($this->results);
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    /**
     * Check if there are errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get total processed count
     */
    public function getTotalProcessed(): int
    {
        return $this->getSuccessCount() + $this->getErrorCount();
    }

    /**
     * Get success rate
     */
    public function getSuccessRate(): float
    {
        $total = $this->getTotalProcessed();
        return $total > 0 ? ($this->getSuccessCount() / $total) * 100 : 0;
    }
}