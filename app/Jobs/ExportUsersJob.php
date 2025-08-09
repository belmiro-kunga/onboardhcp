<?php

namespace App\Jobs;

use App\Services\ImportExportService;
use App\Models\User;
use App\Notifications\ExportCompletedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $filters;
    protected array $userIds;
    protected string $format;
    protected int $userId;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(array $filters, array $userIds, string $format, int $userId)
    {
        $this->filters = $filters;
        $this->userIds = $userIds;
        $this->format = $format;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(ImportExportService $importExportService): void
    {
        try {
            Log::info('Starting user export job', [
                'filters' => $this->filters,
                'user_ids_count' => count($this->userIds),
                'format' => $this->format,
                'user_id' => $this->userId
            ]);

            // Generate export content
            if (!empty($this->userIds)) {
                $content = $importExportService->exportSelectedUsers($this->userIds, $this->format);
                $filename = 'utilizadores_selecionados_' . date('Y-m-d_H-i-s');
            } else {
                $content = $importExportService->exportUsers($this->filters, $this->format);
                $filename = 'utilizadores_' . date('Y-m-d_H-i-s');
            }

            // Store the export file
            $extension = $this->format === 'csv' ? 'csv' : 'xlsx';
            $filePath = "exports/{$filename}.{$extension}";
            
            Storage::put($filePath, $content);

            // Generate download URL (valid for 24 hours)
            $downloadUrl = Storage::temporaryUrl($filePath, now()->addHours(24));

            // Notify the user who initiated the export
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new ExportCompletedNotification($downloadUrl, $filename, $extension));
            }

            Log::info('User export job completed successfully', [
                'file_path' => $filePath,
                'user_id' => $this->userId,
                'file_size' => strlen($content)
            ]);

        } catch (\Exception $e) {
            Log::error('User export job failed', [
                'filters' => $this->filters,
                'user_ids_count' => count($this->userIds),
                'format' => $this->format,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Notify user of failure
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new ExportCompletedNotification(null, null, null, $e->getMessage()));
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('User export job failed permanently', [
            'filters' => $this->filters,
            'user_ids_count' => count($this->userIds),
            'format' => $this->format,
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);

        // Notify user of permanent failure
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ExportCompletedNotification(null, null, null, 'Exportação falhou permanentemente: ' . $exception->getMessage()));
        }
    }
}