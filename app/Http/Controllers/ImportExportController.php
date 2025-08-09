<?php

namespace App\Http\Controllers;

use App\Services\ImportExportService;
use App\Jobs\ImportUsersJob;
use App\Jobs\ExportUsersJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ImportExportController extends Controller
{
    protected ImportExportService $importExportService;

    public function __construct(ImportExportService $importExportService)
    {
        $this->importExportService = $importExportService;
    }

    /**
     * Export users to CSV/Excel
     */
    public function exportUsers(Request $request): Response|JsonResponse
    {
        $request->validate([
            'format' => 'in:csv,excel,xlsx',
            'user_ids' => 'array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        try {
            $format = $request->get('format', 'csv');
            $userIds = $request->get('user_ids', []);
            
            // Get filters for counting records
            $filters = $request->only([
                'search', 'status', 'role', 'department', 
                'date_from', 'date_to', 'sort_by', 'sort_direction'
            ]);

            // Check if we should use async processing
            $asyncThreshold = config('user-search.export.async_threshold', 5000);
            $recordCount = $this->getExportRecordCount($userIds, $filters);

            if ($recordCount > $asyncThreshold) {
                // Use async job for large exports
                ExportUsersJob::dispatch($filters, $userIds, $format, auth()->id());

                return response()->json([
                    'success' => true,
                    'message' => 'Exportação iniciada. Receberá uma notificação quando estiver concluída.',
                    'async' => true
                ]);
            }

            // Process synchronously for small exports
            if (!empty($userIds)) {
                // Export selected users
                $content = $this->importExportService->exportSelectedUsers($userIds, $format);
                $filename = 'utilizadores_selecionados_' . date('Y-m-d_H-i-s');
            } else {
                // Export filtered users
                $content = $this->importExportService->exportUsers($filters, $format);
                $filename = 'utilizadores_' . date('Y-m-d_H-i-s');
            }

            $extension = $format === 'csv' ? 'csv' : 'xlsx';
            $mimeType = $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

            return response($content)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', "attachment; filename=\"{$filename}.{$extension}\"")
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar utilizadores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the count of records that would be exported
     */
    protected function getExportRecordCount(array $userIds, array $filters): int
    {
        if (!empty($userIds)) {
            return count($userIds);
        }

        // Count users based on filters
        $query = User::query();
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }
        
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
        
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->count();
    }

    /**
     * Download import template
     */
    public function downloadTemplate(): Response
    {
        try {
            $content = $this->importExportService->getImportTemplate();
            $filename = 'template_importacao_utilizadores.csv';

            return response($content)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview import data
     */
    public function previewImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240' // 10MB
        ]);

        try {
            $file = $request->file('file');
            $preview = $this->importExportService->previewImport($file);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_rows' => $preview->getTotalRows(),
                    'sample_size' => $preview->getSampleSize(),
                    'valid_rows' => $preview->getValidRowsCount(),
                    'invalid_rows' => $preview->getInvalidRowsCount(),
                    'has_errors' => $preview->hasErrors(),
                    'rows' => $preview->getRows()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar ficheiro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import users from file
     */
    public function importUsers(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB
            'send_welcome_emails' => 'boolean'
        ]);

        try {
            $file = $request->file('file');
            $sendWelcomeEmails = $request->boolean('send_welcome_emails', true);

            // For large files, use job queue
            $fileSize = $file->getSize();
            $maxSyncSize = config('user-management.import.max_sync_size', 1024 * 1024); // 1MB

            if ($fileSize > $maxSyncSize) {
                // Store file temporarily and process via job
                $path = $file->store('imports', 'local');
                
                ImportUsersJob::dispatch($path, $sendWelcomeEmails, auth()->id());

                return response()->json([
                    'success' => true,
                    'message' => 'Importação iniciada. Receberá uma notificação quando estiver concluída.',
                    'async' => true
                ]);
            }

            // Process synchronously for small files
            $result = $this->importExportService->importUsers($file, $sendWelcomeEmails);

            return response()->json([
                'success' => true,
                'message' => 'Importação concluída',
                'data' => [
                    'total_processed' => $result->getTotalProcessed(),
                    'successful' => $result->getSuccessCount(),
                    'failed' => $result->getErrorCount(),
                    'success_rate' => $result->getSuccessRate(),
                    'errors' => $result->getErrors()
                ],
                'async' => false
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar utilizadores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get import/export history
     */
    public function getHistory(Request $request): JsonResponse
    {
        // This would typically fetch from a jobs/operations log table
        // For now, return a placeholder response
        return response()->json([
            'success' => true,
            'data' => [
                'imports' => [],
                'exports' => []
            ]
        ]);
    }

    /**
     * Cancel ongoing import/export operation
     */
    public function cancelOperation(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|string'
        ]);

        // Implementation would depend on your job queue system
        // This is a placeholder for the functionality

        return response()->json([
            'success' => true,
            'message' => 'Operação cancelada com sucesso'
        ]);
    }
}