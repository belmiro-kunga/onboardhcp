<?php

namespace App\Services;

class CmsService
{
    public function getAllPages()
    {
        return collect([
            [
                'id' => 1,
                'title' => 'Página Inicial',
                'slug' => 'home',
                'status' => 'published',
                'created_at' => now()->format('d/m/Y H:i')
            ]
        ]);
    }
}