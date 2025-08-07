<?php

namespace App\Services;

use App\Models\CourseProgress;
use App\Models\Video;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Track video watch progress for a user
     *
     * @param int $userId
     * @param int $videoId
     * @param int $currentTime
     * @param int $totalDuration
     * @return array
     */
    public function trackVideoProgress(int $userId, int $videoId, int $currentTime, int $totalDuration): array
    {
        try {
            // Get or create progress record
            $progress = CourseProgress::firstOrNew([
                'user_id' => $userId,
                'video_id' => $videoId,
            ]);

            // Calculate progress percentage
            $progressPercentage = $totalDuration > 0 
                ? min(100, round(($currentTime / $totalDuration) * 100, 2))
                : 0;

            // Update progress
            $progress->progress_percentage = $progressPercentage;
            $progress->watch_time_seconds = $currentTime;
            $progress->last_watched_at = now();
            
            // Mark as completed if watched more than 95%
            if ($progressPercentage >= 95 && !$progress->completed_at) {
                $progress->completed_at = now();
                
                // Trigger completion event
                $this->handleVideoCompletion($userId, $videoId);
            }
            
            $progress->save();

            // Cache the progress for real-time updates
            $this->cacheProgress($userId, $videoId, $progress);

            return [
                'success' => true,
                'progress' => $progressPercentage,
                'completed' => (bool)$progress->completed_at,
                'last_watched' => $progress->last_watched_at
            ];

        } catch (\Exception $e) {
            \Log::error('Error tracking video progress', [
                'user_id' => $userId,
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to track progress',
                'details' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Calculate course completion percentage for a user
     *
     * @param int $userId
     * @param int $courseId
     * @return array
     */
    public function calculateCourseCompletion(int $userId, int $courseId): array
    {
        try {
            $totalVideos = Video::where('course_id', $courseId)->count();
            
            if ($totalVideos === 0) {
                return [
                    'success' => true,
                    'completion_percentage' => 0,
                    'completed_videos' => 0,
                    'total_videos' => 0
                ];
            }
            
            $completedVideos = CourseProgress::where('user_id', $userId)
                ->whereHas('video', function($q) use ($courseId) {
                    $q->where('course_id', $courseId);
                })
                ->where('progress_percentage', '>=', 95)
                ->count();
            
            $completionPercentage = round(($completedVideos / $totalVideos) * 100, 2);
            
            return [
                'success' => true,
                'completion_percentage' => $completionPercentage,
                'completed_videos' => $completedVideos,
                'total_videos' => $totalVideos
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error calculating course completion', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to calculate course completion',
                'details' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Generate course report for admin
     *
     * @param int $courseId
     * @param array $filters
     * @return array
     */
    public function generateCourseReport(int $courseId, array $filters = []): array
    {
        try {
            $query = CourseProgress::with(['user', 'video'])
                ->whereHas('video', function($q) use ($courseId) {
                    $q->where('course_id', $courseId);
                });
            
            // Apply filters
            if (!empty($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            
            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', Carbon::parse($filters['date_from']));
            }
            
            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', Carbon::parse($filters['date_to']));
            }
            
            $progressData = $query->get();
            
            // Calculate metrics
            $totalUsers = $progressData->groupBy('user_id')->count();
            $totalVideos = $progressData->groupBy('video_id')->count();
            $avgCompletion = $progressData->avg('progress_percentage');
            
            // Group by user for per-user metrics
            $userMetrics = $progressData->groupBy('user_id')->map(function($userProgress) {
                $completed = $userProgress->where('progress_percentage', '>=', 95)->count();
                $total = $userProgress->count();
                
                return [
                    'user_id' => $userProgress->first()->user_id,
                    'user_name' => $userProgress->first()->user->name,
                    'videos_started' => $total,
                    'videos_completed' => $completed,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                    'last_activity' => $userProgress->max('last_watched_at')
                ];
            });
            
            return [
                'success' => true,
                'total_users' => $totalUsers,
                'total_videos' => $totalVideos,
                'average_completion' => round($avgCompletion, 2),
                'user_metrics' => $userMetrics->values(),
                'start_date' => $progressData->min('created_at'),
                'end_date' => $progressData->max('updated_at')
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error generating course report', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to generate course report',
                'details' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Get user engagement metrics
     *
     * @param int $userId
     * @return array
     */
    public function getUserEngagementMetrics(int $userId): array
    {
        try {
            // Get user's progress across all courses
            $userProgress = CourseProgress::with(['video.course'])
                ->where('user_id', $userId)
                ->get();
            
            // Group by course
            $courseMetrics = $userProgress->groupBy('video.course_id')->map(function($courseProgress) {
                $course = $courseProgress->first()->video->course;
                $completed = $courseProgress->where('progress_percentage', '>=', 95)->count();
                $totalVideos = $course->videos()->count();
                
                return [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'videos_started' => $courseProgress->count(),
                    'videos_completed' => $completed,
                    'total_videos' => $totalVideos,
                    'completion_percentage' => $totalVideos > 0 ? round(($completed / $totalVideos) * 100, 2) : 0,
                    'last_activity' => $courseProgress->max('last_watched_at')
                ];
            })->values();
            
            // Calculate total metrics
            $totalVideos = $userProgress->count();
            $totalCompleted = $userProgress->where('progress_percentage', '>=', 95)->count();
            $totalWatchTime = $userProgress->sum('watch_time_seconds');
            $lastActivity = $userProgress->max('last_watched_at');
            
            return [
                'success' => true,
                'user_id' => $userId,
                'total_courses' => $courseMetrics->count(),
                'total_videos_started' => $totalVideos,
                'total_videos_completed' => $totalCompleted,
                'total_watch_time_seconds' => $totalWatchTime,
                'total_watch_time_formatted' => $this->formatSeconds($totalWatchTime),
                'overall_completion' => $totalVideos > 0 ? round(($totalCompleted / $totalVideos) * 100, 2) : 0,
                'last_activity' => $lastActivity,
                'courses' => $courseMetrics
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error getting user engagement metrics', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to get user engagement metrics',
                'details' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Handle video completion events
     *
     * @param int $userId
     * @param int $videoId
     * @return void
     */
    protected function handleVideoCompletion(int $userId, int $videoId): void
    {
        try {
            // Get the video and its course
            $video = Video::with('course')->findOrFail($videoId);
            
            // Trigger event for real-time updates
            event(new \App\Events\VideoCompleted([
                'user_id' => $userId,
                'video_id' => $videoId,
                'course_id' => $video->course_id,
                'completed_at' => now()
            ]));
            
            // Check if course is completed
            $this->checkAndHandleCourseCompletion($userId, $video->course_id);
            
        } catch (\Exception $e) {
            \Log::error('Error handling video completion', [
                'user_id' => $userId,
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check and handle course completion
     *
     * @param int $userId
     * @param int $courseId
     * @return bool
     */
    protected function checkAndHandleCourseCompletion(int $userId, int $courseId): bool
    {
        $completion = $this->calculateCourseCompletion($userId, $courseId);
        
        if ($completion['success'] && $completion['completion_percentage'] >= 100) {
            // Mark course as completed in the database
            DB::table('course_completions')->updateOrInsert(
                ['user_id' => $userId, 'course_id' => $courseId],
                ['completed_at' => now(), 'updated_at' => now()]
            );
            
            // Trigger course completion event
            event(new \App\Events\CourseCompleted([
                'user_id' => $userId,
                'course_id' => $courseId,
                'completed_at' => now()
            ]));
            
            return true;
        }
        
        return false;
    }

    /**
     * Cache progress for real-time updates
     *
     * @param int $userId
     * @param int $videoId
     * @param CourseProgress $progress
     * @return void
     */
    protected function cacheProgress(int $userId, int $videoId, CourseProgress $progress): void
    {
        $cacheKey = "user:{$userId}:video:{$videoId}:progress";
        $cacheData = [
            'progress' => $progress->progress_percentage,
            'watch_time' => $progress->watch_time_seconds,
            'last_updated' => now()->toIso8601String(),
            'completed' => (bool)$progress->completed_at
        ];
        
        Cache::put($cacheKey, $cacheData, now()->addHours(1));
    }

    /**
     * Format seconds into human-readable time
     *
     * @param int $seconds
     * @return string
     */
    protected function formatSeconds(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
