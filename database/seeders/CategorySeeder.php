<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Finanças',
                'description' => 'Treinamentos sobre gestão financeira, investimentos, análise de mercado e planejamento financeiro corporativo.',
                'is_active' => true,
            ],
            [
                'name' => 'Compliance',
                'description' => 'Normas regulamentares, ética empresarial, conformidade legal e gestão de riscos regulatórios.',
                'is_active' => true,
            ],
            [
                'name' => 'Tecnologia',
                'description' => 'Ferramentas digitais, sistemas de informação, inovação tecnológica e transformação digital.',
                'is_active' => true,
            ],
            [
                'name' => 'Recursos Humanos',
                'description' => 'Gestão de pessoas, desenvolvimento de talentos, cultura organizacional e políticas de RH.',
                'is_active' => true,
            ],
            [
                'name' => 'Vendas',
                'description' => 'Técnicas de vendas, relacionamento com clientes, negociação e gestão comercial.',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'description' => 'Estratégias de marketing, branding, comunicação e marketing digital.',
                'is_active' => true,
            ],
            [
                'name' => 'Liderança',
                'description' => 'Desenvolvimento de liderança, gestão de equipes, tomada de decisão e coaching.',
                'is_active' => true,
            ],
            [
                'name' => 'Operações',
                'description' => 'Processos operacionais, gestão da qualidade, eficiência e melhoria contínua.',
                'is_active' => true,
            ],
            [
                'name' => 'Jurídico',
                'description' => 'Aspectos legais, elaboração de contratos, regulamentações e consultoria jurídica.',
                'is_active' => true,
            ],
            [
                'name' => 'Sustentabilidade',
                'description' => 'Práticas ESG, responsabilidade social, meio ambiente e sustentabilidade corporativa.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}