<?php

namespace App\Notifications;

use App\Services\ImportResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?ImportResult $result;
    protected ?string $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(?ImportResult $result = null, ?string $errorMessage = null)
    {
        $this->result = $result;
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
                ->subject('Importação de Utilizadores - Erro')
                ->error()
                ->line('A importação de utilizadores falhou.')
                ->line('Erro: ' . $this->errorMessage)
                ->line('Por favor, verifique o ficheiro e tente novamente.');
        }

        $message = (new MailMessage)
            ->subject('Importação de Utilizadores - Concluída')
            ->line('A importação de utilizadores foi concluída.');

        if ($this->result) {
            $message->line("Total processado: {$this->result->getTotalProcessed()}")
                   ->line("Sucessos: {$this->result->getSuccessCount()}")
                   ->line("Erros: {$this->result->getErrorCount()}")
                   ->line("Taxa de sucesso: " . number_format($this->result->getSuccessRate(), 1) . "%");

            if ($this->result->hasErrors()) {
                $message->line('Alguns utilizadores não puderam ser importados devido a erros de validação.');
            }

            if ($this->result->hasSuccesses()) {
                $message->success();
            } else {
                $message->error();
            }
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
                'type' => 'import_failed',
                'title' => 'Importação Falhou',
                'message' => $this->errorMessage,
                'icon' => 'error'
            ];
        }

        if ($this->result) {
            return [
                'type' => 'import_completed',
                'title' => 'Importação Concluída',
                'message' => "Processados: {$this->result->getTotalProcessed()}, Sucessos: {$this->result->getSuccessCount()}, Erros: {$this->result->getErrorCount()}",
                'icon' => $this->result->hasErrors() ? 'warning' : 'success',
                'data' => [
                    'total_processed' => $this->result->getTotalProcessed(),
                    'successful' => $this->result->getSuccessCount(),
                    'failed' => $this->result->getErrorCount(),
                    'success_rate' => $this->result->getSuccessRate(),
                    'has_errors' => $this->result->hasErrors()
                ]
            ];
        }

        return [
            'type' => 'import_completed',
            'title' => 'Importação Concluída',
            'message' => 'A importação foi processada.',
            'icon' => 'info'
        ];
    }
}