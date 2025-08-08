<?php

namespace App\Services;

class SupportService
{
    public function getRecentTickets()
    {
        return collect([
            [
                'id' => 1,
                'title' => 'Problema com login',
                'user_name' => 'João Silva',
                'status' => 'open',
                'priority' => 'medium',
                'created_at' => now()->format('d/m/Y H:i')
            ]
        ]);
    }

    public function getFaqs()
    {
        return collect([
            [
                'id' => 1,
                'question' => 'Como fazer login no sistema?',
                'answer' => 'Use seu email e senha fornecidos pelo administrador.',
                'category' => 'Login'
            ],
            [
                'id' => 2,
                'question' => 'Como alterar minha senha?',
                'answer' => 'Entre em contato com o administrador do sistema.',
                'category' => 'Conta'
            ]
        ]);
    }
}