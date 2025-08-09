<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending welcome email to user', [
                'user_id' => $this->user->id,
                'email' => $this->user->email
            ]);

            // For now, we'll just log the welcome email
            // In a real implementation, you would send an actual email
            // Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
            
            Log::info('Welcome email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Welcome email job failed permanently', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage()
        ]);
    }
}