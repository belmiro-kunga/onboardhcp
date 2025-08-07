<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Course;
use App\Models\Video;
use App\Models\CourseProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;

class VideoService
{
    /**
     * Criar um novo curso com validação e atribuição de categoria
     */
    public function createCourse(array $data): Course
    {
        try {
            // Validação dos dados
            $validator = Validator::make($data, [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'difficulty_level' => 'required|in:beginner,intermediate,advanced',
                'estimated_duration' => 'nullable|integer|min:1',
                'order_index' => 'nullable|integer|min:0',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Verificar se a categoria está ativa
            $category = Category::findOrFail($data['category_id']);
            if (!$category->is_active) {
                throw new Exception('Não é possível criar curso em categoria inativa.');
            }

            // Definir order_index se não fornecido
            if (!isset($data['order_index'])) {
                $data['order_index'] = Course::where('category_id', $data['category_id'])->max('order_index') + 1;
            }

            // Criar curso em transação
            $course = DB::transaction(function () use ($data) {
                return Course::create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'category_id' => $data['category_id'],
                    'difficulty_level' => $data['difficulty_level'],
                    'estimated_duration' => $data['estimated_duration'] ?? 0,
                    'order_index' => $data['order_index'],
                    'is_active' => $data['is_active'] ?? true
                ]);
            });

            Log::info('Curso criado com sucesso', [
                'course_id' => $course->id,
                'title' => $course->title,
                'category_id' => $course->category_id
            ]);

            return $course;

        } catch (ValidationException $e) {
            Log::warning('Falha na validação ao criar curso', [
                'errors' => $e->errors(),
                'data' => $data
            ]);
            throw $e;
        } catch (Exception $e) {
            Log::error('Erro ao criar curso', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw new Exception('Erro interno ao criar curso: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar curso com verificação de permissões
     */
    public function updateCourse(Course $course, array $data, User $user = null): Course
    {
        try {
            // Verificação de permissões (se usuário fornecido)
            if ($user && !$this->canUserEditCourse($user, $course)) {
                throw new Exception('Usuário não tem permissão para editar este curso.');
            }

            // Validação dos dados
            $validator = Validator::make($data, [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category_id' => 'sometimes|exists:categories,id',
                'difficulty_level' => 'sometimes|in:beginner,intermediate,advanced',
                'estimated_duration' => 'sometimes|integer|min:1',
                'order_index' => 'sometimes|integer|min:0',
                'is_active' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Verificar categoria se alterada
            if (isset($data['category_id']) && $data['category_id'] !== $course->category_id) {
                $category = Category::findOrFail($data['category_id']);
                if (!$category->is_active) {
                    throw new Exception('Não é possível mover curso para categoria inativa.');
                }
            }

            // Atualizar em transação
            $updatedCourse = DB::transaction(function () use ($course, $data) {
                $course->update($data);
                return $course->fresh();
            });

            Log::info('Curso atualizado com sucesso', [
                'course_id' => $course->id,
                'updated_fields' => array_keys($data),
                'user_id' => $user?->id
            ]);

            return $updatedCourse;

        } catch (ValidationException $e) {
            Log::warning('Falha na validação ao atualizar curso', [
                'course_id' => $course->id,
                'errors' => $e->errors(),
                'data' => $data
            ]);
            throw $e;
        } catch (Exception $e) {
            Log::error('Erro ao atualizar curso', [
                'course_id' => $course->id,
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw new Exception('Erro interno ao atualizar curso: ' . $e->getMessage());
        }
    }

    /**
     * Deletar curso com tratamento em cascata
     */
    public function deleteCourse(Course $course, User $user = null): bool
    {
        try {
            // Verificação de permissões
            if ($user && !$this->canUserDeleteCourse($user, $course)) {
                throw new Exception('Usuário não tem permissão para deletar este curso.');
            }

            // Verificar se há progresso de usuários
            $progressCount = CourseProgress::where('course_id', $course->id)->count();
            if ($progressCount > 0) {
                Log::warning('Tentativa de deletar curso com progresso de usuários', [
                    'course_id' => $course->id,
                    'progress_count' => $progressCount
                ]);
                throw new Exception('Não é possível deletar curso que possui progresso de usuários. Desative o curso em vez de deletá-lo.');
            }

            // Deletar em transação com cascata
            $deleted = DB::transaction(function () use ($course) {
                // Deletar vídeos associados
                $course->videos()->delete();
                
                // Deletar progresso (se houver)
                CourseProgress::where('course_id', $course->id)->delete();
                
                // Deletar o curso
                return $course->delete();
            });

            Log::info('Curso deletado com sucesso', [
                'course_id' => $course->id,
                'title' => $course->title,
                'user_id' => $user?->id
            ]);

            return $deleted;

        } catch (Exception $e) {
            Log::error('Erro ao deletar curso', [
                'course_id' => $course->id,
                'message' => $e->getMessage(),
                'user_id' => $user?->id
            ]);
            throw new Exception('Erro interno ao deletar curso: ' . $e->getMessage());
        }
    }

    /**
     * Obter análises de progresso do curso
     */
    public function getCourseAnalytics(Course $course): array
    {
        try {
            $analytics = [
                'course_info' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'category' => $course->category->name,
                    'difficulty_level' => $course->difficulty_level,
                    'total_videos' => $course->videos()->count(),
                    'active_videos' => $course->videos()->active()->count(),
                    'estimated_duration' => $course->estimated_duration,
                    'is_active' => $course->is_active
                ],
                'enrollment_stats' => [
                    'total_enrolled' => CourseProgress::where('course_id', $course->id)
                        ->distinct('user_id')->count(),
                    'completed' => CourseProgress::where('course_id', $course->id)
                        ->where('progress_percentage', 100)->distinct('user_id')->count(),
                    'in_progress' => CourseProgress::where('course_id', $course->id)
                        ->where('progress_percentage', '>', 0)
                        ->where('progress_percentage', '<', 100)
                        ->distinct('user_id')->count(),
                    'not_started' => CourseProgress::where('course_id', $course->id)
                        ->where('progress_percentage', 0)->distinct('user_id')->count()
                ],
                'completion_rate' => $this->calculateCompletionRate($course),
                'average_progress' => $this->calculateAverageProgress($course),
                'video_analytics' => $this->getVideoAnalytics($course),
                'recent_activity' => $this->getRecentActivity($course),
                'time_analytics' => $this->getTimeAnalytics($course)
            ];

            Log::info('Análises do curso geradas', [
                'course_id' => $course->id,
                'total_enrolled' => $analytics['enrollment_stats']['total_enrolled']
            ]);

            return $analytics;

        } catch (Exception $e) {
            Log::error('Erro ao gerar análises do curso', [
                'course_id' => $course->id,
                'message' => $e->getMessage()
            ]);
            throw new Exception('Erro interno ao gerar análises: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se usuário pode editar curso
     */
    private function canUserEditCourse(User $user, Course $course): bool
    {
        // Implementar lógica de permissões conforme necessário
        // Por exemplo: admin pode editar todos, usuário comum apenas seus próprios
        return $user->is_admin || $user->id === $course->created_by ?? true;
    }

    /**
     * Verificar se usuário pode deletar curso
     */
    private function canUserDeleteCourse(User $user, Course $course): bool
    {
        // Apenas admins podem deletar cursos
        return $user->is_admin ?? true;
    }

    /**
     * Calcular taxa de conclusão do curso
     */
    private function calculateCompletionRate(Course $course): float
    {
        $totalEnrolled = CourseProgress::where('course_id', $course->id)
            ->distinct('user_id')->count();
        
        if ($totalEnrolled === 0) {
            return 0;
        }

        $completed = CourseProgress::where('course_id', $course->id)
            ->where('progress_percentage', 100)
            ->distinct('user_id')->count();

        return round(($completed / $totalEnrolled) * 100, 2);
    }

    /**
     * Calcular progresso médio do curso
     */
    private function calculateAverageProgress(Course $course): float
    {
        $averageProgress = CourseProgress::where('course_id', $course->id)
            ->avg('progress_percentage');

        return round($averageProgress ?? 0, 2);
    }

    /**
     * Obter análises por vídeo
     */
    private function getVideoAnalytics(Course $course): array
    {
        $videos = $course->videos()->with('progress')->get();
        $analytics = [];

        foreach ($videos as $video) {
            $totalViews = $video->progress()->count();
            $completedViews = $video->progress()->where('progress_percentage', 100)->count();
            
            $analytics[] = [
                'video_id' => $video->id,
                'title' => $video->title,
                'total_views' => $totalViews,
                'completed_views' => $completedViews,
                'completion_rate' => $totalViews > 0 ? round(($completedViews / $totalViews) * 100, 2) : 0,
                'average_watch_time' => $video->progress()->avg('watch_time_seconds') ?? 0
            ];
        }

        return $analytics;
    }

    /**
     * Obter atividade recente do curso
     */
    private function getRecentActivity(Course $course): array
    {
        return CourseProgress::where('course_id', $course->id)
            ->with('user:id,name', 'video:id,title')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($progress) {
                return [
                    'user_name' => $progress->user->name,
                    'video_title' => $progress->video->title ?? 'Curso Geral',
                    'progress_percentage' => $progress->progress_percentage,
                    'updated_at' => $progress->updated_at->format('d/m/Y H:i')
                ];
            })
            ->toArray();
    }

    /**
     * Obter análises de tempo
     */
    private function getTimeAnalytics(Course $course): array
    {
        $totalWatchTime = CourseProgress::where('course_id', $course->id)
            ->sum('watch_time_seconds');

        $averageSessionTime = CourseProgress::where('course_id', $course->id)
            ->where('watch_time_seconds', '>', 0)
            ->avg('watch_time_seconds');

        return [
            'total_watch_time_hours' => round($totalWatchTime / 3600, 2),
            'average_session_time_minutes' => round(($averageSessionTime ?? 0) / 60, 2),
            'estimated_total_duration_hours' => round($course->estimated_duration / 60, 2)
        ];
    }

    /**
     * Obter estatísticas gerais do sistema de vídeos
     */
    public function getSystemStatistics(): array
    {
        try {
            return [
                'courses' => [
                    'total' => Course::count(),
                    'active' => Course::active()->count(),
                    'by_difficulty' => [
                        'beginner' => Course::byDifficulty('beginner')->count(),
                        'intermediate' => Course::byDifficulty('intermediate')->count(),
                        'advanced' => Course::byDifficulty('advanced')->count()
                    ]
                ],
                'videos' => [
                    'total' => Video::count(),
                    'active' => Video::active()->count()
                ],
                'categories' => [
                    'total' => Category::count(),
                    'active' => Category::where('is_active', true)->count()
                ],
                'engagement' => [
                    'total_enrollments' => CourseProgress::distinct('user_id', 'course_id')->count(),
                    'total_completions' => CourseProgress::where('progress_percentage', 100)->count(),
                    'overall_completion_rate' => $this->calculateOverallCompletionRate()
                ]
            ];
        } catch (Exception $e) {
            Log::error('Erro ao gerar estatísticas do sistema', [
                'message' => $e->getMessage()
            ]);
            throw new Exception('Erro interno ao gerar estatísticas: ' . $e->getMessage());
        }
    }

    /**
     * Calcular taxa de conclusão geral do sistema
     */
    private function calculateOverallCompletionRate(): float
    {
        $totalEnrollments = CourseProgress::distinct('user_id', 'course_id')->count();
        
        if ($totalEnrollments === 0) {
            return 0;
        }

        $totalCompletions = CourseProgress::where('progress_percentage', 100)
            ->distinct('user_id', 'course_id')->count();

        return round(($totalCompletions / $totalEnrollments) * 100, 2);
    }

    /**
     * Get all videos with their related course information
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllVideos()
    {
        try {
            return Video::with(['course' => function($query) {
                $query->select('id', 'title', 'is_active');
            }])
            ->select(['id', 'title', 'description', 'course_id', 'duration_seconds', 'is_active', 'order_index', 'created_at'])
            ->orderBy('course_id')
            ->orderBy('order_index')
            ->get()
            ->map(function($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'duration' => $this->formatDuration($video->duration_seconds),
                    'is_active' => $video->is_active,
                    'order_index' => $video->order_index,
                    'created_at' => $video->created_at->format('d/m/Y H:i'),
                    'course' => $video->course ? [
                        'id' => $video->course->id,
                        'title' => $video->course->title,
                        'is_active' => $video->course->is_active
                    ] : null
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching all videos', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty collection on error
            return collect([]);
        }
    }
    
    /**
     * Format duration in seconds to HH:MM:SS format
     *
     * @param int $seconds
     * @return string
     */
    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}