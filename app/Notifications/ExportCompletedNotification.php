<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?string $downloadUrl;
    protected ?string $filename;
    protected ?string $extension;
    protected ?string $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(?string $downloadUrl = null, ?string $filename = null, ?string $extension = null, ?string $errorMessage = null)
    {
        $this->downloadUrl = $downloadUrl;
        $this->filename = $filename;
        $this->extension = $extension;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->errorMessage) {
            return (new MailMessage)
                ->subject('Exportação de Utilizadores - Erro')
                ->error()
                ->line('A exportação de utilizadores falhou.')
                ->line('Erro: ' . $this->errorMessage)
                ->line('Por favor, tente novamente.');
        }

        $message = (new MailMessage)
            ->subject('Exportação de Utilizadores - Concluída')
            ->success()
            ->line('A exportação de utilizadores foi concluída com sucesso.');

        if ($this->downloadUrl && $this->filename) {
            $message->line("Ficheiro: {$this->filename}.{$this->extension}")
                   ->action('Descarregar Ficheiro', $this->downloadUrl)
                   ->line('O link de download é válido por 24 horas.');
        }

        return $message->line('Obrigado por usar o sistema de gestão de utilizadores!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        if ($this->errorMessage) {
            return [
                'type' => 'export_failed',
                'title' => 'Exportação Falhou',
                'message' => $this->errorMessage,
                'icon' => 'error'
            ];
        }

        return [
            'type' => 'export_completed',
            'title' => 'Exportação Concluída',
            'message' => $this->filename ? "Ficheiro {$this->filename}.{$this->extension} pronto para download" : 'A exportação foi concluída',
            'icon' => 'success',
            'data' => [
                'download_url' => $this->downloadUrl,
                'filename' => $this->filename,
                'extension' => $this->extension
            ]
        ];
    }
}