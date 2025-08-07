<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $difficultyLevels = ['beginner', 'intermediate', 'advanced'];
        $durations = [30, 45, 60, 90, 120, 180]; // em minutos
        
        $courseTitles = [
            'Introdução à Análise Financeira',
            'Gestão de Riscos Avançada',
            'Compliance e Regulamentações',
            'Liderança Estratégica',
            'Marketing Digital Moderno',
            'Tecnologias Emergentes',
            'Sustentabilidade Corporativa',
            'Negociação Eficaz',
            'Gestão de Projetos',
            'Inovação e Criatividade',
            'Comunicação Assertiva',
            'Análise de Dados',
            'Transformação Digital',
            'Ética Empresarial',
            'Desenvolvimento de Equipes'
        ];

        return [
            'title' => $this->faker->randomElement($courseTitles),
            'description' => $this->faker->paragraph(3),
            'category_id' => Category::factory(),
            'difficulty_level' => $this->faker->randomElement($difficultyLevels),
            'estimated_duration' => $this->faker->randomElement($durations),
            'is_active' => $this->faker->boolean(85), // 85% chance de estar ativo
            'order_index' => $this->faker->numberBetween(1, 100),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the course should be for beginners.
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 'beginner',
            'duration_minutes' => $this->faker->randomElement([30, 45, 60]),
        ]);
    }

    /**
     * Indicate that the course should be intermediate.
     */
    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 'intermediate',
            'duration_minutes' => $this->faker->randomElement([60, 90, 120]),
        ]);
    }

    /**
     * Indicate that the course should be advanced.
     */
    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 'advanced',
            'duration_minutes' => $this->faker->randomElement([90, 120, 180]),
        ]);
    }

    /**
     * Indicate that the course should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}