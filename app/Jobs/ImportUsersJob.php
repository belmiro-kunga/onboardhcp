<?php

namespace App\Jobs;

use App\Services\ImportExportService;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class ImportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected bool $sendWelcomeEmails;
    protected int $userId;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, bool $sendWelcomeEmails, int $userId)
    {
        $this->filePath = $filePath;
        $this->sendWelcomeEmails = $sendWelcomeEmails;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(ImportExportService $importExportService): void
    {
        try {
            Log::info('Starting user import job', [
                'file_path' => $this->filePath,
                'user_id' => $this->userId
            ]);

            // Create UploadedFile instance from stored file
            $fullPath = Storage::path($this->filePath);
            $originalName = basename($this->filePath);
            $mimeType = Storage::mimeType($this->filePath);
            
            $file = new UploadedFile(
                $fullPath,
                $originalName,
                $mimeType,
                null,
                true // test mode - don't validate file existence
            );

            // Process the import
            $result = $importExportService->importUsers($file, $this->sendWelcomeEmails);

            // Notify the user who initiated the import
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new ImportCompletedNotification($result));
            }

            Log::info('User import job completed successfully', [
                'file_path' => $this->filePath,
                'user_id' => $this->userId,
                'total_processed' => $result->getTotalProcessed(),
                'successful' => $result->getSuccessCount(),
                'failed' => $result->getErrorCount()
            ]);

        } catch (\Exception $e) {
            Log::error('User import job failed', [
                'file_path' => $this->filePath,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Notify user of failure
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new ImportCompletedNotification(null, $e->getMessage()));
            }

            throw $e;
        } finally {
            // Clean up the temporary file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('User import job failed permanently', [
            'file_path' => $this->filePath,
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);

        // Clean up the temporary file
        if (Storage::exists($this->filePath)) {
            Storage::delete($this->filePath);
        }

        // Notify user of permanent failure
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ImportCompletedNotification(null, 'Importação falhou permanentemente: ' . $exception->getMessage()));
        }
    }
}