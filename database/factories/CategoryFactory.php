<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Finanças' => 'Treinamentos sobre gestão financeira, investimentos e análise de mercado',
            'Compliance' => 'Normas regulamentares, ética empresarial e conformidade legal',
            'Tecnologia' => 'Ferramentas digitais, sistemas e inovação tecnológica',
            'Recursos Humanos' => 'Gestão de pessoas, desenvolvimento de talentos e cultura organizacional',
            'Vendas' => 'Técnicas de vendas, relacionamento com clientes e negociação',
            'Marketing' => 'Estratégias de marketing, branding e comunicação',
            'Liderança' => 'Desenvolvimento de liderança, gestão de equipes e tomada de decisão',
            'Operações' => 'Processos operacionais, qualidade e eficiência',
            'Jurídico' => 'Aspectos legais, contratos e regulamentações',
            'Sustentabilidade' => 'Práticas ESG, responsabilidade social e meio ambiente'
        ];

        $category = $this->faker->randomElement(array_keys($categories));
        
        return [
            'name' => $category,
            'description' => $categories[$category],
            'is_active' => $this->faker->boolean(90), // 90% chance de estar ativo
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the category should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the category should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}