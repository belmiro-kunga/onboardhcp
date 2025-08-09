<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use App\Modules\User\Models\User;
use App\Services\UserActivityService;
use App\Services\ActivityAlertService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class UserActivityController extends Controller
{
    protected UserActivityService $activityService;
    protected ActivityAlertService $alertService;

    public function __construct(UserActivityService $activityService, ActivityAlertService $alertService)
    {
        $this->activityService = $activityService;
        $this->alertService = $alertService;
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display activity dashboard
     */
    public function index()
    {
        $systemStats = $this->activityService->getSystemActivityStats(30);
        $recentLogins = $this->activityService->getRecentLogins(10);
        $inactiveUsers = $this->activityService->getInactiveUsers(30);

        return view('admin.activity.index', compact('systemStats', 'recentLogins', 'inactiveUsers'));
    }

    /**
     * Get user activities for a specific user
     */
    public function userActivities(Request $request, User $user): JsonResponse
    {
        $filters = [
            'activity_type' => $request->get('activity_type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search')
        ];

        $activities = $this->activityService->getUserActivities(
            $user->id,
            $request->get('per_page', 15),
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activities->items(),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                    'from' => $activities->firstItem(),
                    'to' => $activities->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Get user activity statistics
     */
    public function userStats(Request $request, User $user): JsonResponse
    {
        $days = $request->get('days', 30);
        $stats = $this->activityService->getUserActivityStats($user->id, $days);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get system activity statistics
     */
    public function systemStats(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $stats = $this->activityService->getSystemActivityStats($days);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get activity timeline for dashboard
     */
    public function timeline(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 50);
        $activityType = $request->get('activity_type');
        $userId = $request->get('user_id');

        $query = UserActivity::with('user:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($activityType) {
            $query->where('activity_type', $activityType);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $activities = $query->get();

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Get real-time activity data for charts
     */
    public function chartData(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $startDate = now()->subDays($days);

        // Daily activity counts
        $dailyData = UserActivity::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Activity types distribution
        $typeData = UserActivity::where('created_at', '>=', $startDate)
            ->selectRaw('activity_type, COUNT(*) as count')
            ->groupBy('activity_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                $activity = new UserActivity(['activity_type' => $item->activity_type]);
                return [
                    'type' => $item->activity_type,
                    'label' => $activity->getActivityTypeLabel(),
                    'count' => $item->count,
                    'icon' => $activity->getActivityIcon()
                ];
            });

        // Hourly distribution
        $hourlyData = UserActivity::where('created_at', '>=', $startDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour');

        return response()->json([
            'success' => true,
            'data' => [
                'daily' => $dailyData,
                'types' => $typeData,
                'hourly' => $hourlyData,
                'period_days' => $days
            ]
        ]);
    }

    /**
     * Export activity report
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'user_id' => 'nullable|exists:users,id',
            'activity_type' => 'nullable|string'
        ]);

        // Log the export activity
        $this->activityService->logExport(
            'activity_report',
            $request->format,
            0 // Will be updated with actual count
        );

        // TODO: Implement actual export functionality
        // For now, return a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Exportação de relatório de atividades iniciada. Você receberá um email quando estiver pronta.',
            'export_id' => uniqid('activity_export_'),
            'format' => $request->format
        ]);
    }

    /**
     * Clean old activities (maintenance endpoint)
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'keep_days' => 'required|integer|min:30|max:365'
        ]);

        $deletedCount = $this->activityService->cleanOldActivities($request->keep_days);

        return response()->json([
            'success' => true,
            'message' => "Limpeza concluída. {$deletedCount} registros antigos foram removidos.",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Get online users (users with recent activity)
     */
    public function onlineUsers(Request $request): JsonResponse
    {
        $minutes = $request->get('minutes', 15); // Consider users online if active in last 15 minutes
        $cutoffTime = now()->subMinutes($minutes);

        $onlineUsers = UserActivity::where('created_at', '>=', $cutoffTime)
            ->with('user:id,name,email,avatar')
            ->select('user_id')
            ->distinct()
            ->get()
            ->pluck('user')
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'online_users' => $onlineUsers,
                'count' => $onlineUsers->count(),
                'cutoff_minutes' => $minutes
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }
}
