<?php

namespace App\Services;

class NotificationService
{
    public function getRecentNotifications()
    {
        return collect([
            [
                'id' => 1,
                'title' => 'Sistema atualizado',
                'message' => 'O sistema foi atualizado com sucesso',
                'type' => 'info',
                'created_at' => now()->format('d/m/Y H:i')
            ]
        ]);
    }

    public function getTemplates()
    {
        return collect([
            [
                'id' => 1,
                'name' => 'Boas-vindas',
                'subject' => 'Bem-vindo ao sistema',
                'type' => 'welcome'
            ]
        ]);
    }
}