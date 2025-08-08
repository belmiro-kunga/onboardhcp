<?php

namespace App\Services;

class CertificateService
{
    public function getAllCertificates()
    {
        return collect([
            [
                'id' => 1,
                'user_name' => 'João Silva',
                'course_name' => 'Curso de Integração',
                'issued_at' => now()->format('d/m/Y'),
                'status' => 'issued'
            ]
        ]);
    }

    public function getTemplates()
    {
        return collect([
            [
                'id' => 1,
                'name' => 'Template Padrão',
                'description' => 'Template padrão para certificados',
                'is_active' => true
            ]
        ]);
    }
}