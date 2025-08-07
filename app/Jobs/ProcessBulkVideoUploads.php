<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Video;
use App\Notifications\BulkUploadCompleted;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessBulkVideoUploads implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The uploaded video data.
     *
     * @var array
     */
    protected $uploads;

    /**
     * The user who initiated the bulk upload.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * The email address to send the completion notification to.
     *
     * @var string|null
     */
    protected $notificationEmail;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 86400; // 24 hours

    /**
     * Create a new job instance.
     *
     * @param  array  $uploads
     * @param  \App\Models\User  $user
     * @param  string|null  $notificationEmail
     * @return void
     */
    public function __construct(array $uploads, User $user, ?string $notificationEmail = null)
    {
        $this->uploads = $uploads;
        $this->user = $user->withoutRelations();
        $this->notificationEmail = $notificationEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $processed = [];
        $failed = [];

        foreach ($this->uploads as $index => $upload) {
            try {
                // Check if the batch has been cancelled
                if ($this->batch() && $this->batch()->cancelled()) {
                    Log::info('Batch processing was cancelled', ['batch_id' => $this->batch()->id]);
                    return;
                }

                // Move the temp file to permanent storage
                $tempPath = $upload['file'];
                $newPath = 'videos/' . now()->format('Y/m') . '/' . basename($tempPath);
                
                if (!Storage::exists($tempPath)) {
                    throw new \Exception("Temporary file not found: {$tempPath}");
                }
                
                Storage::move($tempPath, $newPath);
                
                // Create video record
                $video = Video::create([
                    'title' => $upload['title'],
                    'description' => $upload['description'],
                    'course_id' => $upload['course_id'],
                    'user_id' => $upload['user_id'],
                    'source_type' => 'upload',
                    'file_path' => $newPath,
                    'is_published' => $upload['is_published'] ?? false,
                    'is_free' => $upload['is_free'] ?? false,
                    'position' => $upload['position'] ?? 0,
                    'metadata' => $upload['metadata'] ?? [],
                    'processing_status' => 'queued',
                ]);
                
                // Dispatch job to process the video
                ProcessVideoUpload::dispatch($video);
                
                $processed[] = [
                    'title' => $upload['title'],
                    'video_id' => $video->id,
                ];
                
            } catch (\Exception $e) {
                Log::error('Failed to process video upload', [
                    'index' => $index,
                    'title' => $upload['title'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Clean up any temporary files
                if (isset($tempPath) && Storage::exists($tempPath)) {
                    Storage::delete($tempPath);
                }
                
                $failed[] = [
                    'title' => $upload['title'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
            }
            
            // Update batch progress
            if ($this->batch()) {
                $this->batch()->incrementProcessedItems(1, $this->job->getJobId());
            }
        }
        
        // Send notification if email was provided
        if ($this->notificationEmail) {
            $this->user->notify(new BulkUploadCompleted(
                count($processed),
                count($failed),
                $processed,
                $failed
            ));
        }
        
        Log::info('Bulk video upload processing completed', [
            'processed' => count($processed),
            'failed' => count($failed),
            'user_id' => $this->user->id,
        ]);
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Bulk video upload job failed', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
        
        // Send failure notification if email was provided
        if ($this->notificationEmail) {
            $this->user->notify(new BulkUploadCompleted(
                0,
                count($this->uploads),
                [],
                array_map(function($upload) use ($exception) {
                    return [
                        'title' => $upload['title'] ?? 'Unknown',
                        'error' => $exception->getMessage(),
                    ];
                }, $this->uploads)
            ));
        }
    }
}
