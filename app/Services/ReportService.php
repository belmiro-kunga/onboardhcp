<?php

namespace App\Services;

class ReportService
{
    public function getAvailableReports()
    {
        return collect([
            [
                'id' => 1,
                'name' => 'Relatório de Usuários',
                'description' => 'Relatório completo de usuários do sistema',
                'type' => 'users'
            ],
            [
                'id' => 2,
                'name' => 'Relatório de Cursos',
                'description' => 'Relatório de progresso dos cursos',
                'type' => 'courses'
            ]
        ]);
    }
}