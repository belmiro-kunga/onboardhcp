<?php

namespace Database\Factories;

use App\Models\CourseProgress;
use App\Models\User;
use App\Models\Course;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseProgress>
 */
class CourseProgressFactory extends Factory
{
    protected $model = CourseProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $progressPercentage = $this->faker->numberBetween(0, 100);
        $isCompleted = $progressPercentage >= 100;
        
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'video_id' => Video::factory(),
            'progress_percentage' => $progressPercentage,
            'completed_at' => $isCompleted ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'last_watched_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'watch_time_seconds' => $this->faker->numberBetween(60, 1800),
            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the progress should be completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'progress_percentage' => 100,
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the progress should be in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'progress_percentage' => $this->faker->numberBetween(1, 99),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the progress should be just started.
     */
    public function started(): static
    {
        return $this->state(fn (array $attributes) => [
            'progress_percentage' => $this->faker->numberBetween(1, 25),
            'completed_at' => null,
            'watch_time_seconds' => $this->faker->numberBetween(60, 300),
        ]);
    }
}