<?php

namespace Database\Factories;

use App\Models\Video;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sourceTypes = ['local', 'youtube', 'r2'];
        $sourceType = $this->faker->randomElement($sourceTypes);
        
        $videoTitles = [
            'Introdução ao Módulo',
            'Conceitos Fundamentais',
            'Aplicação Prática',
            'Estudos de Caso',
            'Exercícios e Atividades',
            'Revisão e Conclusão',
            'Dicas e Melhores Práticas',
            'Ferramentas Recomendadas',
            'Próximos Passos',
            'Perguntas Frequentes'
        ];

        return [
            'course_id' => Course::factory(),
            'title' => $this->faker->randomElement($videoTitles),
            'description' => $this->faker->sentence(10),
            'source_type' => $sourceType,
            'source_url' => $this->generateSourceUrl($sourceType),
            'duration' => $this->faker->numberBetween(300, 1800), // 5 a 30 minutos
            'order_index' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(90),
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Generate source URL based on type.
     */
    private function generateSourceUrl(string $sourceType): string
    {
        return match($sourceType) {
            'youtube' => 'https://www.youtube.com/watch?v=' . $this->faker->regexify('[A-Za-z0-9_-]{11}'),
            'r2' => 'https://r2.cloudflare.com/videos/' . $this->faker->uuid . '.mp4',
            'local' => '/storage/videos/' . $this->faker->uuid . '.mp4',
            default => '/storage/videos/' . $this->faker->uuid . '.mp4',
        };
    }

    /**
     * Indicate that the video should be from YouTube.
     */
    public function youtube(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=' . $this->faker->regexify('[A-Za-z0-9_-]{11}'),
        ]);
    }

    /**
     * Indicate that the video should be from R2.
     */
    public function r2(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'r2',
            'source_url' => 'https://r2.cloudflare.com/videos/' . $this->faker->uuid . '.mp4',
        ]);
    }

    /**
     * Indicate that the video should be local.
     */
    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'local',
            'source_url' => '/storage/videos/' . $this->faker->uuid . '.mp4',
        ]);
    }
}