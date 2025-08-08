<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Category;

class ExampleCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar categoria se não existir
        $category = Category::firstOrCreate([
            'name' => 'Onboarding'
        ], [
            'description' => 'Cursos de integração para novos funcionários',
            'is_active' => true
        ]);

        // Criar curso de exemplo
        $course = Course::create([
            'title' => 'Introdução à Hemera Capital Partners',
            'description' => 'Curso completo de onboarding para novos funcionários da Hemera Capital Partners. Aprenda sobre nossa cultura, valores, processos e ferramentas essenciais para começar sua jornada conosco com sucesso.',
            'category_id' => $category->id,
            'difficulty_level' => 'beginner',
            'estimated_duration' => 120, // 2 horas
            'is_active' => true,
            'order_index' => 1,
            'metadata' => [
                'objectives' => [
                    'Conhecer a história e valores da empresa',
                    'Entender os processos internos básicos',
                    'Familiarizar-se com as ferramentas de trabalho',
                    'Compreender as políticas de compliance',
                    'Integrar-se à cultura organizacional'
                ],
                'prerequisites' => [],
                'certification_available' => true,
                'target_audience' => 'Novos funcionários',
                'language' => 'pt-BR'
            ]
        ]);

        echo "Curso criado com sucesso: {$course->title} (ID: {$course->id})\n";
        
        // Criar mais alguns cursos de exemplo
        $courses = [
            [
                'title' => 'Compliance e Ética Empresarial',
                'description' => 'Curso essencial sobre compliance, ética empresarial e regulamentações do setor financeiro.',
                'difficulty_level' => 'intermediate',
                'estimated_duration' => 90,
                'metadata' => [
                    'objectives' => [
                        'Compreender as regulamentações do setor',
                        'Aplicar princípios éticos no trabalho',
                        'Identificar situações de risco',
                        'Seguir procedimentos de compliance'
                    ],
                    'certification_available' => true,
                    'mandatory' => true
                ]
            ],
            [
                'title' => 'Ferramentas e Sistemas Internos',
                'description' => 'Treinamento prático sobre as principais ferramentas e sistemas utilizados na empresa.',
                'difficulty_level' => 'beginner',
                'estimated_duration' => 60,
                'metadata' => [
                    'objectives' => [
                        'Navegar pelos sistemas internos',
                        'Utilizar ferramentas de comunicação',
                        'Acessar recursos e documentação',
                        'Gerenciar tarefas e projetos'
                    ],
                    'hands_on' => true
                ]
            ],
            [
                'title' => 'Gestão de Investimentos - Fundamentos',
                'description' => 'Curso introdutório sobre gestão de investimentos e análise de mercado financeiro.',
                'difficulty_level' => 'intermediate',
                'estimated_duration' => 180,
                'metadata' => [
                    'objectives' => [
                        'Entender conceitos básicos de investimentos',
                        'Analisar diferentes classes de ativos',
                        'Compreender estratégias de gestão',
                        'Avaliar riscos e retornos'
                    ],
                    'certification_available' => true,
                    'advanced_track' => true
                ]
            ]
        ];

        foreach ($courses as $courseData) {
            $courseData['category_id'] = $category->id;
            $courseData['is_active'] = true;
            $courseData['order_index'] = Course::max('order_index') + 1;
            
            $newCourse = Course::create($courseData);
            echo "Curso criado: {$newCourse->title} (ID: {$newCourse->id})\n";
        }

        echo "\nTotal de cursos criados: " . Course::count() . "\n";
    }
}
