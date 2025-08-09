<?php

namespace App\Services;

use App\Models\User;
use App\Jobs\SendWelcomeEmailJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Imports\UsersPreviewImport;
use App\Services\ImportBackupService;
use App\Services\DataTransformationService;
use App\Services\ImportErrorReport;

class ImportExportService
{
    protected UserSearchService $userSearchService;
    protected RolePermissionService $rolePermissionService;

    public function __construct(
        UserSearchService $userSearchService,
        RolePermissionService $rolePermissionService
    ) {
        $this->userSearchService = $userSearchService;
        $this->rolePermissionService = $rolePermissionService;
    }

    /**
     * Export users to CSV format
     */
    public function exportUsers(array $filters = [], string $format = 'csv'): string
    {
        // Get filtered users
        $users = $this->getUsersForExport($filters);
        
        switch (strtolower($format)) {
            case 'csv':
                return $this->exportToCsv($users);
            case 'excel':
            case 'xlsx':
                return $this->exportToExcel($users);
            default:
                throw new \InvalidArgumentException("Formato não suportado: {$format}");
        }
    }

    /**
     * Export selected users by IDs
     */
    public function exportSelectedUsers(array $userIds, string $format = 'csv'): string
    {
        $users = User::whereIn('id', $userIds)
            ->with(['roles', 'groups'])
            ->get();
            
        switch (strtolower($format)) {
            case 'csv':
                return $this->exportToCsv($users);
            case 'excel':
            case 'xlsx':
                return $this->exportToExcel($users);
            default:
                throw new \InvalidArgumentException("Formato não suportado: {$format}");
        }
    }

    /**
     * Import users from uploaded file
     */
    public function importUsers(UploadedFile $file, bool $sendWelcomeEmails = true): ImportResult
    {
        $this->validateFile($file);
        
        // Create backup before import if enabled
        $backupPath = null;
        if (config('user-search.import.backup.create_backup_before_import', true)) {
            $backupService = new ImportBackupService();
            $backupPath = $backupService->createBackup();
        }
        
        DB::beginTransaction();
        
        try {
            $import = new UsersImport();
            Excel::import($import, $file);
            
            $results = new ImportResult();
            $results->setBackupPath($backupPath);
            
            // Add successful imports
            foreach ($import->getResults() as $user) {
                $results->addSuccess($user);
                
                // Dispatch welcome email job if enabled
                if ($sendWelcomeEmails && !empty($user->email)) {
                    SendWelcomeEmailJob::dispatch($user);
                }
            }
            
            // Add errors
            foreach ($import->getErrors() as $error) {
                $results->addError($error['row'], $error['data'], $error['message']);
            }
            
            if ($results->hasErrors() && $results->getSuccessCount() === 0) {
                DB::rollBack();
            } else {
                DB::commit();
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        return $results;
    }

    /**
     * Preview import data before processing
     */
    public function previewImport(UploadedFile $file): ImportPreview
    {
        $this->validateFile($file);
        
        // Create a preview import that doesn't save to database
        $previewImport = new UsersPreviewImport();
        Excel::import($previewImport, $file);
        
        return $previewImport->getPreview();
    }

    /**
     * Generate CSV template for import
     */
    public function getImportTemplate(): string
    {
        $headers = [
            'Nome Completo',
            'Email',
            'Telefone',
            'Departamento',
            'Cargo',
            'Data de Admissão (YYYY-MM-DD)',
            'Role (admin, funcionario)',
            'Status (active, inactive, pending)'
        ];
        
        $sampleData = [
            'João Silva',
            'joao.silva@empresa.com',
            '+351 912 345 678',
            'Tecnologia',
            'Desenvolvedor',
            '2024-01-15',
            'funcionario',
            'active'
        ];
        
        return $this->generateCsvTemplate($headers, $sampleData);
    }

    /**
     * Get users for export based on filters
     */
    protected function getUsersForExport(array $filters): Collection
    {
        // Remove pagination for export
        $filters['per_page'] = null;
        
        $query = User::query()->with(['roles', 'groups']);
        
        // Apply search filters
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }
        
        // Apply other filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }
        
        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }
        
        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        // Apply sorting
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        return $query->get();
    }

    /**
     * Export users to CSV format
     */
    protected function exportToCsv(Collection $users): string
    {
        $export = new UsersExport($users);
        return Excel::raw($export, \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Export users to Excel format
     */
    protected function exportToExcel(Collection $users): string
    {
        $export = new UsersExport($users);
        return Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        $maxSize = config('user-management.import.max_file_size', 10 * 1024 * 1024); // 10MB
        $allowedMimes = config('user-management.import.allowed_mimes', [
            'text/csv',
            'application/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
        
        if ($file->getSize() > $maxSize) {
            throw new ValidationException(
                validator([], []),
                ['file' => ['O ficheiro é muito grande. Tamanho máximo: ' . ($maxSize / 1024 / 1024) . 'MB']]
            );
        }
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new ValidationException(
                validator([], []),
                ['file' => ['Formato de ficheiro não suportado. Use CSV ou Excel.']]
            );
        }
    }



    /**
     * Generate CSV template
     */
    protected function generateCsvTemplate(array $headers, array $sampleData = []): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");
        
        // Add headers
        fputcsv($output, $headers);
        
        // Add sample data row if provided
        if (!empty($sampleData)) {
            fputcsv($output, $sampleData);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}

/**
 * Import Result Class
 */
class ImportResult
{
    protected array $successes = [];
    protected array $errors = [];
    protected ?string $backupPath = null;
    protected array $metadata = [];

    public function addSuccess(User $user): void
    {
        $this->successes[] = $user;
    }

    public function addError(int $row, array $data, string $message): void
    {
        $this->errors[] = [
            'row' => $row,
            'data' => $data,
            'message' => $message
        ];
    }

    public function setBackupPath(?string $backupPath): void
    {
        $this->backupPath = $backupPath;
    }

    public function getBackupPath(): ?string
    {
        return $this->backupPath;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getSuccesses(): array
    {
        return $this->successes;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return count($this->successes);
    }

    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasSuccesses(): bool
    {
        return !empty($this->successes);
    }

    public function getTotalProcessed(): int
    {
        return $this->getSuccessCount() + $this->getErrorCount();
    }

    public function getSuccessRate(): float
    {
        $total = $this->getTotalProcessed();
        return $total > 0 ? ($this->getSuccessCount() / $total) * 100 : 0;
    }

    public function getDetailedReport(): array
    {
        return [
            'summary' => [
                'total_processed' => $this->getTotalProcessed(),
                'successful' => $this->getSuccessCount(),
                'failed' => $this->getErrorCount(),
                'success_rate' => $this->getSuccessRate(),
                'backup_created' => !empty($this->backupPath),
                'backup_path' => $this->backupPath
            ],
            'successes' => array_map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ], $this->successes),
            'errors' => $this->errors,
            'metadata' => $this->metadata
        ];
    }
}

/**
 * Import Preview Class
 */
class ImportPreview
{
    protected array $rows = [];
    protected int $totalRows = 0;
    protected int $sampleSize = 0;

    public function addRow(int $rowNumber, array $data, array $validation): void
    {
        $this->rows[] = [
            'row_number' => $rowNumber,
            'data' => $data,
            'validation' => $validation
        ];
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function setTotalRows(int $total): void
    {
        $this->totalRows = $total;
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    public function setSampleSize(int $size): void
    {
        $this->sampleSize = $size;
    }

    public function getSampleSize(): int
    {
        return $this->sampleSize;
    }

    public function getValidRowsCount(): int
    {
        return count(array_filter($this->rows, fn($row) => $row['validation']['valid']));
    }

    public function getInvalidRowsCount(): int
    {
        return count(array_filter($this->rows, fn($row) => !$row['validation']['valid']));
    }

    public function hasErrors(): bool
    {
        return $this->getInvalidRowsCount() > 0;
    }
}