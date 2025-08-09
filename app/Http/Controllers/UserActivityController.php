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
    }

    /**
     * Process security alerts
     */
    public function processAlerts(): JsonResponse
    {
        try {
            $alerts = $this->alertService->processAlerts();
            
            return response()->json([
                'success' => true,
                'message' => 'Alertas processados com sucesso',
                'processed_count' => count($alerts),
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar alertas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display user activity history page
     */
    public function userActivityHistory(User $user): View
    {
        return view('admin.users.activity', compact('user'));
    }

    /**
     * Get user session information
     */
    public function getUserSessions(User $user): JsonResponse
    {
        try {
            $sessions = $this->activityService->getUserSessions($user->id);
            
            return response()->json([
                'success' => true,
                'sessions' => $sessions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter sessões do usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user activities (alias for getUserActivitiesPaginated for backward compatibility)
     */
    public function getUserActivity(User $user, Request $request): JsonResponse
    {
        return $this->getUserActivitiesPaginated($user, $request);
    }

    /**
     * Get paginated user activities with filters
     */
    public function getUserActivitiesPaginated(User $user, Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 50);
            $filters = $request->only(['activity_type', 'date_from', 'date_to', 'ip_address']);
            
            // First, get the base query
            $query = UserActivity::where('user_id', $user->id);

            // Apply filters
            if (!empty($filters['activity_type'])) {
                $query->where('activity_type', $filters['activity_type']);
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            if (!empty($filters['ip_address'])) {
                $query->where('ip_address', 'like', '%' . $filters['ip_address'] . '%');
            }

            // Execute the count query first
            $total = (clone $query)->count();
            
            // Then get the paginated results
            $activities = $query->orderBy('created_at', 'desc')
                              ->paginate($perPage);

            // Transform activities data
            $transformedActivities = $activities->getCollection()->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'activity_type' => $activity->activity_type,
                    'description' => $activity->activity_description ?? $activity->description,
                    'ip_address' => $activity->ip_address,
                    'user_agent' => $activity->user_agent,
                    'url' => $activity->url,
                    'method' => $activity->method,
                    'session_id' => $activity->session_id,
                    'metadata' => $activity->metadata,
                    'created_at' => $activity->created_at->toISOString(),
                    'created_at_formatted' => $activity->created_at->format('d/m/Y H:i:s')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $transformedActivities,
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                    'from' => $activities->firstItem(),
                    'to' => $activities->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter atividades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export user activity report
     */
    public function exportUserActivityReport(User $user, Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filters = $request->only(['activity_type', 'date_from', 'date_to', 'ip_address']);
        
        $query = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['ip_address'])) {
            $query->where('ip_address', 'like', '%' . $filters['ip_address'] . '%');
        }

        $fileName = 'atividades_' . $user->name . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($query, $user) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'Data/Hora',
                'Tipo de Atividade',
                'Descrição',
                'Endereço IP',
                'Navegador',
                'Dispositivo',
                'URL',
                'Método HTTP',
                'ID da Sessão'
            ]);

            // Process activities in chunks to avoid memory issues
            $query->chunk(1000, function ($activities) use ($handle) {
                foreach ($activities as $activity) {
                    fputcsv($handle, [
                        $activity->created_at->format('d/m/Y H:i:s'),
                        $activity->getActivityLabel(),
                        $activity->description,
                        $activity->ip_address,
                        $activity->browser,
                        $activity->device_type,
                        $activity->url,
                        $activity->method,
                        $activity->session_id
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
