<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Video;
use App\Models\CourseProgress;
use App\Models\User;
use Illuminate\Database\Seeder;

class VideoTrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar categorias se não existirem
        if (Category::count() === 0) {
            $this->call(CategorySeeder::class);
        }

        $categories = Category::active()->get();
        
        // Criar cursos para cada categoria
        foreach ($categories as $category) {
            $coursesCount = rand(2, 5);
            
            for ($i = 0; $i < $coursesCount; $i++) {
                $course = Course::factory()
                    ->for($category)
                    ->active()
                    ->create();
                
                // Criar vídeos para cada curso
                $videosCount = rand(3, 8);
                
                for ($j = 1; $j <= $videosCount; $j++) {
                    Video::factory()
                        ->for($course)
                        ->state([
                            'order_index' => $j,
                            'title' => "Módulo {$j} - " . fake()->sentence(3),
                        ])
                        ->create();
                }
            }
        }

        // Criar progresso para usuários existentes
        $users = User::all();
        $courses = Course::with('videos')->get();
        
        foreach ($users as $user) {
            // Cada usuário terá progresso em alguns cursos aleatórios
            $userCourses = $courses->random(rand(1, min(5, $courses->count())));
            
            foreach ($userCourses as $course) {
                foreach ($course->videos as $video) {
                    // 70% chance de ter progresso em cada vídeo
                    if (rand(1, 100) <= 70) {
                        CourseProgress::factory()
                            ->for($user)
                            ->for($course)
                            ->for($video)
                            ->create();
                    }
                }
            }
        }
    }
}