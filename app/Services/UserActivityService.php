<?php

namespace App\Services;

use App\Models\UserActivity;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserActivityService
{
    /**
     * Log user activity
     */
    public function logActivity(
        int $userId,
        string $activityType,
        string $description = null,
        array $metadata = [],
        Request $request = null
    ): UserActivity {
        $request = $request ?: request();
        
        return UserActivity::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'activity_description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'metadata' => $metadata,
            'created_at' => now()
        ]);
    }

    /**
     * Log login activity
     */
    public function logLogin(User $user, Request $request = null): UserActivity
    {
        // Update user's last login timestamp
        $user->update(['last_login_at' => now()]);

        return $this->logActivity(
            $user->id,
            UserActivity::TYPE_LOGIN,
            'Usuário fez login no sistema',
            [
                'login_method' => 'web',
                'remember_me' => $request ? $request->boolean('remember') : false
            ],
            $request
        );
    }

    /**
     * Log logout activity
     */
    public function logLogout(User $user, Request $request = null): UserActivity
    {
        return $this->logActivity(
            $user->id,
            UserActivity::TYPE_LOGOUT,
            'Usuário fez logout do sistema',
            ['logout_type' => 'manual'],
            $request
        );
    }

    /**
     * Log page view activity
     */
    public function logPageView(string $pageName, array $metadata = []): ?UserActivity
    {
        if (!Auth::check()) {
            return null;
        }

        return $this->logActivity(
            Auth::id(),
            UserActivity::TYPE_PAGE_VIEW,
            "Visualizou a página: {$pageName}",
            array_merge(['page_name' => $pageName], $metadata)
        );
    }

    /**
     * Log form submission
     */
    public function logFormSubmit(string $formName, array $metadata = []): ?UserActivity
    {
        if (!Auth::check()) {
            return null;
        }

        return $this->logActivity(
            Auth::id(),
            UserActivity::TYPE_FORM_SUBMIT,
            "Enviou formulário: {$formName}",
            array_merge(['form_name' => $formName], $metadata)
        );
    }

    /**
     * Log search activity
     */
    public function logSearch(string $query, array $filters = [], int $resultsCount = 0): ?UserActivity
    {
        if (!Auth::check()) {
            return null;
        }

        return $this->logActivity(
            Auth::id(),
            UserActivity::TYPE_SEARCH,
            "Realizou pesquisa: {$query}",
            [
                'search_query' => $query,
                'filters' => $filters,
                'results_count' => $resultsCount
            ]
        );
    }

    /**
     * Log export activity
     */
    public function logExport(string $exportType, string $format, int $recordsCount = 0): ?UserActivity
    {
        if (!Auth::check()) {
            return null;
        }

        return $this->logActivity(
            Auth::id(),
            UserActivity::TYPE_EXPORT,
            "Exportou dados: {$exportType} ({$format})",
            [
                'export_type' => $exportType,
                'format' => $format,
                'records_count' => $recordsCount
            ]
        );
    }

    /**
     * Log status change activity
     */
    public function logStatusChange(int $targetUserId, string $oldStatus, string $newStatus, string $reason = null): ?UserActivity
    {
        if (!Auth::check()) {
            return null;
        }

        $targetUser = User::find($targetUserId);
        $description = "Alterou status do usuário {$targetUser->name} de {$oldStatus} para {$newStatus}";

        return $this->logActivity(
            Auth::id(),
            UserActivity::TYPE_STATUS_CHANGE,
            $description,
            [
                'target_user_id' => $targetUserId,
                'target_user_name' => $targetUser->name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason
            ]
        );
    }

    /**
     * Get user activities with pagination
     */
    public function getUserActivities(int $userId, int $perPage = 15, array $filters = [])
    {
        $query = UserActivity::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (!empty($filters['search'])) {
            $query->where('activity_description', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get activity statistics for a user
     */
    public function getUserActivityStats(int $userId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $stats = UserActivity::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select('activity_type', DB::raw('count(*) as count'))
            ->groupBy('activity_type')
            ->pluck('count', 'activity_type')
            ->toArray();

        $totalActivities = array_sum($stats);

        // Get daily activity counts
        $dailyStats = UserActivity::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Get most active hours
        $hourlyStats = UserActivity::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        return [
            'total_activities' => $totalActivities,
            'activity_types' => $stats,
            'daily_stats' => $dailyStats,
            'hourly_stats' => $hourlyStats,
            'period_days' => $days
        ];
    }

    /**
     * Get system-wide activity statistics
     * 
     * @param int $days
     * @return array
     */
    public function getSystemActivityStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // Total activities
        $totalActivities = UserActivity::where('created_at', '>=', $startDate)->count();

        // Active users count
        $activeUsers = UserActivity::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count('user_id');

        // Most active users
        $mostActiveUsers = UserActivity::where('created_at', '>=', $startDate)
            ->select('user_id', DB::raw('count(*) as activity_count'))
            ->with('user:id,name,email')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(10)
            ->get();

        // Activity types distribution - ensure we always return a Collection
        $activityTypes = UserActivity::where('created_at', '>=', $startDate)
            ->select('activity_type', DB::raw('count(*) as count'))
            ->groupBy('activity_type')
            ->orderBy('count', 'desc')
            ->get();

        // Daily activity trend
        $dailyTrend = UserActivity::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total_activities' => $totalActivities,
            'active_users' => $activeUsers,
            'most_active_users' => $mostActiveUsers,
            'activity_types' => $activityTypes, // Now always a Collection
            'daily_trend' => $dailyTrend,
            'period_days' => $days
        ];
    }

    /**
     * Clean old activities (for maintenance)
     */
    public function cleanOldActivities(int $keepDays = 90): int
    {
        $cutoffDate = now()->subDays($keepDays);
        
        return UserActivity::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get recent logins (last 24 hours)
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentLogins(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return UserActivity::where('activity_type', UserActivity::TYPE_LOGIN)
            ->where('created_at', '>=', now()->subDay())
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return (object)[
                    'user' => $activity->user,
                    'login_time' => $activity->created_at,
                    'ip_address' => $activity->ip_address,
                    'browser' => $activity->browser,
                    'device_type' => $activity->device_type,
                    'created_at' => $activity->created_at,
                    'activity_type' => $activity->activity_type
                ];
            });
    }

    /**
     * Get count of old activities that would be deleted
     */
    public function getOldActivitiesCount(int $days): int
    {
        return UserActivity::where('created_at', '<', now()->subDays($days))->count();
    }

    /**
     * Get breakdown of old activities by type
     */
    public function getOldActivitiesBreakdown(int $days): array
    {
        $activities = UserActivity::where('created_at', '<', now()->subDays($days))
            ->select('activity_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('activity_type')
            ->get();

        $breakdown = [];
        foreach ($activities as $activity) {
            $breakdown[$activity->getActivityLabel()] = $activity->count;
        }

        return $breakdown;
    }

    /**
     * Get recent activity summary
     */
    public function getRecentActivitySummary(int $days): array
    {
        $startDate = now()->subDays($days);
        
        $summary = UserActivity::where('created_at', '>=', $startDate)
            ->select('activity_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('activity_type')
            ->get()
            ->pluck('count', 'activity_type')
            ->toArray();

        return [
            'logins' => $summary[UserActivity::TYPE_LOGIN] ?? 0,
            'page_views' => $summary[UserActivity::TYPE_PAGE_VIEW] ?? 0,
            'form_submits' => $summary[UserActivity::TYPE_FORM_SUBMIT] ?? 0,
            'searches' => $summary[UserActivity::TYPE_SEARCH] ?? 0,
            'exports' => $summary[UserActivity::TYPE_EXPORT] ?? 0,
            'total' => array_sum($summary)
        ];
    }



    /**
     * Get users who haven't logged in recently
     */
    public function getInactiveUsers(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $cutoffDate = now()->subDays($days);
        
        $activeUserIds = UserActivity::where('activity_type', UserActivity::TYPE_LOGIN)
            ->where('created_at', '>=', $cutoffDate)
            ->distinct()
            ->pluck('user_id');

        return User::whereNotIn('id', $activeUserIds)
            ->where('status', 'active')
            ->orderBy('last_login_at', 'desc')
            ->get();
    }
}
