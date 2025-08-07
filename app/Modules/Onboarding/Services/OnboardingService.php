<?php

namespace App\Modules\Onboarding\Services;

use App\Modules\User\Models\User;

class OnboardingService
{
    protected $steps = [
        'documentation' => [
            'name' => 'Documentação',
            'description' => 'Complete o seu registo e envie os documentos necessários',
            'icon' => 'document',
            'order' => 1,
        ],
        'training' => [
            'name' => 'Formação',
            'description' => 'Complete os módulos de formação obrigatórios',
            'icon' => 'book',
            'order' => 2,
        ],
        'configuration' => [
            'name' => 'Configuração',
            'description' => 'Configure as suas preferências e acesso aos sistemas',
            'icon' => 'settings',
            'order' => 3,
        ],
    ];

    public function getOnboardingSteps(): array
    {
        return $this->steps;
    }

    public function getUserProgress(User $user): array
    {
        // Por enquanto, retorna progresso fixo
        // Futuramente pode ser armazenado no banco de dados
        return [
            'completed_steps' => [],
            'current_step' => 'documentation',
            'progress_percentage' => 0,
        ];
    }

    public function completeStep(User $user, string $step): bool
    {
        // Lógica para marcar etapa como concluída
        // Futuramente implementar com banco de dados
        return true;
    }

    public function getNextStep(User $user): ?string
    {
        $progress = $this->getUserProgress($user);
        $completedSteps = $progress['completed_steps'];

        foreach ($this->steps as $stepKey => $step) {
            if (!in_array($stepKey, $completedSteps)) {
                return $stepKey;
            }
        }

        return null; // Todas as etapas concluídas
    }

    public function calculateProgress(User $user): int
    {
        $progress = $this->getUserProgress($user);
        $totalSteps = count($this->steps);
        $completedSteps = count($progress['completed_steps']);

        return $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
    }
}