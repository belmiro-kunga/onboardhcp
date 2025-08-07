<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkUploadCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Number of successfully processed videos.
     *
     * @var int
     */
    protected $processedCount;

    /**
     * Number of failed video uploads.
     *
     * @var int
     */
    protected $failedCount;

    /**
     * List of successfully processed videos.
     *
     * @var array
     */
    protected $processedVideos;

    /**
     * List of failed video uploads with error messages.
     *
     * @var array
     */
    protected $failedVideos;

    /**
     * Create a new notification instance.
     *
     * @param  int  $processedCount
     * @param  int  $failedCount
     * @param  array  $processedVideos
     * @param  array  $failedVideos
     * @return void
     */
    public function __construct(
        int $processedCount,
        int $failedCount,
        array $processedVideos = [],
        array $failedVideos = []
    ) {
        $this->processedCount = $processedCount;
        $this->failedCount = $failedCount;
        $this->processedVideos = $processedVideos;
        $this->failedVideos = $failedVideos;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Bulk Video Upload Completed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your bulk video upload has been processed.')
            ->line('**Processed:** ' . $this->processedCount . ' videos')
            ->line('**Failed:** ' . $this->failedCount . ' videos');

        if ($this->failedCount > 0) {
            $mail->error()
                ->line('\n**Failed Videos:**');

            foreach ($this->failedVideos as $video) {
                $mail->line('- ' . ($video['title'] ?? 'Unknown') . ': ' . ($video['error'] ?? 'Unknown error'));
            }
        }

        if ($this->processedCount > 0) {
            $mail->line('\n**Successfully Processed Videos:**');

            foreach ($this->processedVideos as $video) {
                $mail->line('- ' . $video['title']);
            }
        }

        $mail->action('View Uploads', url('/admin/videos'))
            ->line('Thank you for using our platform!');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'processed_count' => $this->processedCount,
            'failed_count' => $this->failedCount,
            'message' => 'Your bulk upload of ' . ($this->processedCount + $this->failedCount) . ' videos has been processed.',
            'action_url' => '/admin/videos',
            'action_text' => 'View Videos',
        ];
    }
}
