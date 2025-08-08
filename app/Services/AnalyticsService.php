<?php

namespace App\Services;

use App\Models\CourseProgress;
use App\Models\Video;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\VideoView;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

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
     * Track video watch progress for a user
     *
     * @param int $userId
     * @param int $videoId
     * @param int $currentTime
     * @param int $totalDuration
     * @return array
     */
    /**
     * Get analytics for a specific course
     *
     * @param Course $course
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getCourseAnalytics(Course $course, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Get enrollment stats
        $enrollments = $this->getEnrollmentStats($course, $start, $end);
        
        // Get completion stats
        $completions = $this->getCompletionStats($course, $start, $end);
        
        // Get engagement metrics
        $engagement = $this->getEngagementMetrics($course, $start, $end);
        
        // Get video analytics
        $videoAnalytics = $this->getVideoAnalytics($course, $start, $end);
        
        return [
            'enrollments' => $enrollments,
            'completions' => $completions,
            'engagement' => $engagement,
            'video_analytics' => $videoAnalytics,
        ];
    }
    
    /**
     * Get user progress in a course
     *
     * @param User $user
     * @param Course $course
     * @return array
     */
    public function getUserProgress(User $user, Course $course): array
    {
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();
            
        $videos = $course->videos()
            ->with(['progress' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->orderBy('position')
            ->get();
            
        $totalVideos = $videos->count();
        $completedVideos = $videos->filter(fn($video) => $video->progress?->completed_at)->count();
        $completionPercentage = $totalVideos > 0 ? round(($completedVideos / $totalVideos) * 100) : 0;
        
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'enrolled_at' => $enrollment->created_at->toIso8601String(),
            ],
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'total_videos' => $totalVideos,
                'total_duration' => $videos->sum('duration'),
            ],
            'progress' => [
                'completed_videos' => $completedVideos,
                'completion_percentage' => $completionPercentage,
                'total_watch_time' => $videos->sum(fn($video) => $video->progress?->watch_time_seconds ?? 0),
                'last_watched_at' => $videos->max('progress.last_watched_at')?->toIso8601String(),
                'started_at' => $videos->min('progress.created_at')?->toIso8601String(),
            ],
            'videos' => $videos->map(function($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'duration' => $video->duration,
                    'position' => $video->position,
                    'progress' => $video->progress ? [
                        'progress_percentage' => $video->progress->progress_percentage,
                        'watch_time_seconds' => $video->progress->watch_time_seconds,
                        'completed_at' => $video->progress->completed_at?->toIso8601String(),
                        'last_watched_at' => $video->progress->last_watched_at?->toIso8601String(),
                    ] : null,
                ];
            })->toArray(),
        ];
    }
    
    /**
     * Prepare progress report data for export
     *
     * @param Course $course
     * @param bool $includeInactive
     * @param string|null $statusFilter
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function prepareProgressReportData(Course $course, bool $includeInactive = false, ?string $statusFilter = null)
    {
        $query = $course->enrollments()
            ->with(['user', 'progress'])
            ->withCount([
                'videos as completed_videos_count' => function($query) {
                    $query->whereNotNull('completed_at');
                },
                'videos as total_videos_count',
            ]);
            
        if (!$includeInactive) {
            $query->where('status', 'active');
        }
        
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        
        return $query->get()->map(function($enrollment) {
            $enrollment->completion_percentage = $enrollment->total_videos_count > 0 
                ? round(($enrollment->completed_videos_count / $enrollment->total_videos_count) * 100, 2)
                : 0;
                
            $enrollment->videos_watched_count = $enrollment->progress->count();
            $enrollment->last_activity_at = $enrollment->progress->max('last_watched_at');
            
            return $enrollment;
        });
    }
    
    /**
     * Get dashboard statistics
     *
     * @param Carbon|null $startDate
     * @return array
     */
    public function getDashboardStats(?Carbon $startDate = null): array
    {
        $startDate = $startDate ?: now()->subDays(30);
        
        $stats = [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_enrollments' => $this->getEnrollmentStats(null, $startDate, now()),
            'completion_rates' => $this->getCompletionRates($startDate),
            'recent_activity' => $this->getRecentActivity($startDate),
            'popular_courses' => $this->getPopularCourses(5, $startDate),
            'user_engagement' => $this->getUserEngagement($startDate),
        ];
        
        return $stats;
    }
    
    /**
     * Get real-time updates for the dashboard
     *
     * @param array $channels
     * @param string|null $lastUpdate
     * @return array
     */
    public function getRealtimeUpdates(array $channels, ?string $lastUpdate = null): array
    {
        $updates = [];
        $lastUpdateTime = $lastUpdate ? Carbon::parse($lastUpdate) : now()->subMinute();
        
        if (in_array('enrollments', $channels)) {
            $updates['enrollments'] = $this->getRecentEnrollments($lastUpdateTime);
        }
        
        if (in_array('completions', $channels)) {
            $updates['completions'] = $this->getRecentCompletions($lastUpdateTime);
        }
        
        if (in_array('activity', $channels)) {
            $updates['activity'] = $this->getRecentActivity($lastUpdateTime, 10);
        }
        
        return $updates;
    }
    
    /**
     * Get enrollment statistics
     *
     * @param Course|null $course
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    protected function getEnrollmentStats(?Course $course, Carbon $start, Carbon $end): array
    {
        $query = Enrollment::query();
        
        if ($course) {
            $query->where('course_id', $course->id);
        }
        
        $total = (clone $query)->count();
        
        $recent = (clone $query)
            ->whereBetween('created_at', [$start, $end])
            ->count();
            
        $period = CarbonPeriod::create($start, $end);
        $enrollmentsByDay = [];
        
        foreach ($period as $date) {
            $count = (clone $query)
                ->whereDate('created_at', $date)
                ->count();
                
            $enrollmentsByDay[$date->format('Y-m-d')] = $count;
        }
        
        return [
            'total' => $total,
            'recent' => $recent,
            'by_day' => $enrollmentsByDay,
        ];
    }
    
    /**
     * Get completion statistics
     *
     * @param Course $course
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    protected function getCompletionStats(Course $course, Carbon $start, Carbon $end): array
    {
        $totalEnrollments = $course->enrollments()->count();
        $completedEnrollments = $course->enrollments()
            ->where('status', 'completed')
            ->count();
            
        $completionRate = $totalEnrollments > 0 
            ? round(($completedEnrollments / $totalEnrollments) * 100, 2)
            : 0;
            
        $completionsByDay = [];
        $period = CarbonPeriod::create($start, $end);
        
        foreach ($period as $date) {
            $count = $course->enrollments()
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->count();
                
            $completionsByDay[$date->format('Y-m-d')] = $count;
        }
        
        return [
            'total_completions' => $completedEnrollments,
            'completion_rate' => $completionRate,
            'by_day' => $completionsByDay,
        ];
    }
    
    /**
     * Get engagement metrics
     *
     * @param Course $course
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    protected function getEngagementMetrics(Course $course, Carbon $start, Carbon $end): array
    {
        // Average watch time per video
        $avgWatchTime = (int) $course->videos()
            ->join('course_progress', 'videos.id', '=', 'course_progress.video_id')
            ->whereBetween('course_progress.updated_at', [$start, $end])
            ->avg('course_progress.watch_time_seconds');
            
        // Drop-off rate (users who started but didn't complete)
        $startedCount = $course->enrollments()
            ->where('status', '!=', 'completed')
            ->whereHas('progress')
            ->count();
            
        $dropOffRate = $course->enrollments_count > 0
            ? round(($startedCount / $course->enrollments_count) * 100, 2)
            : 0;
            
        // Time to complete (for completed enrollments)
        $avgTimeToComplete = (int) $course->enrollments()
            ->where('status', 'completed')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, completed_at)) as avg_seconds')
            ->value('avg_seconds');
            
        return [
            'avg_watch_time_seconds' => $avgWatchTime,
            'drop_off_rate' => $dropOffRate,
            'avg_time_to_complete_seconds' => $avgTimeToComplete,
        ];
    }
    
    /**
     * Get video analytics
     *
     * @param Course $course
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    protected function getVideoAnalytics(Course $course, Carbon $start, Carbon $end): array
    {
        $videos = $course->videos()
            ->withCount([
                'views as view_count',
                'completions as completion_count',
            ])
            ->withAvg('progress', 'watch_time_seconds')
            ->orderBy('position')
            ->get();
            
        return $videos->map(function($video) use ($start, $end) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'duration' => $video->duration,
                'view_count' => $video->view_count,
                'completion_count' => $video->completion_count,
                'avg_watch_time' => (int) $video->progress_avg_watch_time_seconds,
                'completion_rate' => $video->view_count > 0
                    ? round(($video->completion_count / $video->view_count) * 100, 2)
                    : 0,
            ];
        })->toArray();
    }
    
    /**
     * Get completion rates over time
     *
     * @param Carbon $start
     * @param Carbon|null $end
     * @return array
     */
    protected function getCompletionRates(Carbon $start, ?Carbon $end = null): array
    {
        $end = $end ?: now();
        
        $completions = CourseProgress::query()
            ->select([
                DB::raw('DATE(completed_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as count'),
            ])
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
        $enrollments = Enrollment::query()
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
            ])
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
        $dates = array_unique(array_merge(
            array_keys($completions),
            array_keys($enrollments)
        ));
        
        sort($dates);
        
        $rates = [];
        $cumulativeEnrollments = 0;
        $cumulativeCompletions = 0;
        
        foreach ($dates as $date) {
            $cumulativeEnrollments += $enrollments[$date] ?? 0;
            $cumulativeCompletions += $completions[$date] ?? 0;
            
            $rates[$date] = [
                'enrollments' => $enrollments[$date] ?? 0,
                'completions' => $completions[$date] ?? 0,
                'cumulative_enrollments' => $cumulativeEnrollments,
                'cumulative_completions' => $cumulativeCompletions,
                'completion_rate' => $cumulativeEnrollments > 0
                    ? round(($cumulativeCompletions / $cumulativeEnrollments) * 100, 2)
                    : 0,
            ];
        }
        
        return $rates;
    }
    
    /**
     * Get recent activity
     *
     * @param Carbon $since
     * @param int $limit
     * @return array
     */
    protected function getRecentActivity(Carbon $since, int $limit = 20): array
    {
        $videoViews = VideoView::with(['user', 'video.course'])
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($view) {
                return [
                    'type' => 'video_view',
                    'user' => $view->user->only(['id', 'name', 'email']),
                    'video' => $view->video->only(['id', 'title']),
                    'course' => $view->video->course->only(['id', 'title']),
                    'created_at' => $view->created_at->toIso8601String(),
                    'watch_time' => $view->watch_time_seconds,
                ];
            });
            
        $completions = CourseProgress::with(['user', 'video.course'])
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $since)
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($progress) {
                return [
                    'type' => 'video_completion',
                    'user' => $progress->user->only(['id', 'name', 'email']),
                    'video' => $progress->video->only(['id', 'title']),
                    'course' => $progress->video->course->only(['id', 'title']),
                    'created_at' => $progress->completed_at->toIso8601String(),
                ];
            });
            
        return $videoViews->concat($completions)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values()
            ->toArray();
    }
    
    /**
     * Get popular courses
     *
     * @param int $limit
     * @param Carbon|null $since
     * @return array
     */
    protected function getPopularCourses(int $limit = 5, ?Carbon $since = null): array
    {
        $query = Course::query()
            ->withCount(['enrollments', 'completions'])
            ->withAvg('reviews', 'rating')
            ->orderBy('enrollments_count', 'desc')
            ->limit($limit);
            
        if ($since) {
            $query->whereHas('enrollments', function($q) use ($since) {
                $q->where('created_at', '>=', $since);
            });
        }
        
        return $query->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'enrollments_count' => $course->enrollments_count,
                    'completions_count' => $course->completions_count,
                    'completion_rate' => $course->enrollments_count > 0
                        ? round(($course->completions_count / $course->enrollments_count) * 100, 2)
                        : 0,
                    'average_rating' => (float) $course->reviews_avg_rating,
                ];
            })
            ->toArray();
    }
    
    /**
     * Get user engagement metrics
     *
     * @param Carbon $since
     * @return array
     */
    protected function getUserEngagement(Carbon $since): array
    {
        $activeUsers = User::whereHas('videoViews', function($q) use ($since) {
            $q->where('created_at', '>=', $since);
        })->count();
        
        $totalUsers = User::count();
        $engagementRate = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0;
        
        $avgSessionsPerUser = VideoView::where('created_at', '>=', $since)
            ->select(DB::raw('COUNT(DISTINCT session_id) / COUNT(DISTINCT user_id) as avg_sessions'))
            ->value('avg_sessions') ?: 0;
            
        $avgWatchTime = (int) VideoView::where('created_at', '>=', $since)
            ->avg('watch_time_seconds');
            
        return [
            'active_users' => $activeUsers,
            'total_users' => $totalUsers,
            'engagement_rate' => $engagementRate,
            'avg_sessions_per_user' => round($avgSessionsPerUser, 2),
            'avg_watch_time_seconds' => $avgWatchTime,
        ];
    }
    
    /**
     * Get recent enrollments
     *
     * @param Carbon $since
     * @param int $limit
     * @return array
     */
    protected function getRecentEnrollments(Carbon $since, int $limit = 10): array
    {
        return Enrollment::with(['user', 'course'])
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($enrollment) {
                return [
                    'user' => $enrollment->user->only(['id', 'name', 'email']),
                    'course' => $enrollment->course->only(['id', 'title']),
                    'enrolled_at' => $enrollment->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }
    
    /**
     * Get recent course completions
     *
     * @param Carbon $since
     * @param int $limit
     * @return array
     */
    protected function getRecentCompletions(Carbon $since, int $limit = 10): array
    {
        return Enrollment::with(['user', 'course'])
            ->where('status', 'completed')
            ->where('completed_at', '>=', $since)
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($enrollment) {
                return [
                    'user' => $enrollment->user->only(['id', 'name', 'email']),
                    'course' => $enrollment->course->only(['id', 'title']),
                    'completed_at' => $enrollment->completed_at->toIso8601String(),
                    'days_to_complete' => $enrollment->created_at->diffInDays($enrollment->completed_at),
                ];
            })
            ->toArray();
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
    /**
     * Get course completion stats for admin dashboard
     *
     * @return array
     */
    public function getCourseCompletionStats(): array
    {
        try {
            $totalCourses = Course::count();
            $totalEnrollments = DB::table('course_progress')->distinct('user_id')->count();
            $totalCompletions = DB::table('course_progress')
                ->where('progress_percentage', '>=', 95)
                ->distinct('user_id')
                ->count();
            
            $completionRate = $totalEnrollments > 0 
                ? round(($totalCompletions / $totalEnrollments) * 100, 2) 
                : 0;
            
            return [
                'total_courses' => $totalCourses,
                'total_enrollments' => $totalEnrollments,
                'total_completions' => $totalCompletions,
                'completion_rate' => $completionRate
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error getting course completion stats', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_courses' => 0,
                'total_enrollments' => 0,
                'total_completions' => 0,
                'completion_rate' => 0
            ];
        }
    }

    /**
     * Get engagement metrics for admin dashboard
     *
     * @return array
     */
    public function getEngagementMetrics(): array
    {
        try {
            $totalUsers = User::count();
            $activeUsers = DB::table('course_progress')
                ->where('last_watched_at', '>=', now()->subDays(30))
                ->distinct('user_id')
                ->count();
            
            $engagementRate = $totalUsers > 0 
                ? round(($activeUsers / $totalUsers) * 100, 2) 
                : 0;
            
            $avgWatchTime = DB::table('course_progress')
                ->where('last_watched_at', '>=', now()->subDays(30))
                ->avg('watch_time_seconds') ?? 0;
            
            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'engagement_rate' => $engagementRate,
                'avg_watch_time_seconds' => round($avgWatchTime),
                'avg_watch_time_formatted' => $this->formatSeconds($avgWatchTime)
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error getting engagement metrics', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_users' => 0,
                'active_users' => 0,
                'engagement_rate' => 0,
                'avg_watch_time_seconds' => 0,
                'avg_watch_time_formatted' => '00:00:00'
            ];
        }
    }

    /**
     * Format seconds to HH:MM:SS
     *
     * @param int $seconds
     * @return string
     */
    private function formatSeconds(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}