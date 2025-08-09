<?php

namespace App\Services;

use App\Models\UserActivity;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ActivityAlertService
{
    protected UserActivityService $activityService;
    protected NotificationService $notificationService;

    public function __construct(UserActivityService $activityService, NotificationService $notificationService)
    {
        $this->activityService = $activityService;
        $this->notificationService = $notificationService;
    }

    /**
     * Check for suspicious login activities
     */
    public function checkSuspiciousLogins(int $userId): array
    {
        $alerts = [];
        $user = User::find($userId);
        
        if (!$user) {
            return $alerts;
        }

        // Check for multiple login attempts from different IPs
        $recentLogins = UserActivity::where('user_id', $userId)
            ->where('activity_type', UserActivity::TYPE_LOGIN)
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->get();

        if ($recentLogins->count() >= 5) {
            $uniqueIPs = $recentLogins->pluck('ip_address')->unique();
            
            if ($uniqueIPs->count() >= 3) {
                $alerts[] = [
                    'type' => 'multiple_ip_logins',
                    'severity' => 'high',
                    'message' => "Usuário {$user->name} fez login de {$uniqueIPs->count()} IPs diferentes nas últimas 24 horas",
                    'data' => [
                        'user_id' => $userId,
                        'login_count' => $recentLogins->count(),
                        'unique_ips' => $uniqueIPs->count(),
                        'ips' => $uniqueIPs->toArray()
                    ]
                ];
            }
        }

        // Check for unusual login times
        $unusualTimeLogins = $recentLogins->filter(function ($login) {
            $hour = $login->created_at->hour;
            return $hour < 6 || $hour > 22; // Outside normal business hours
        });

        if ($unusualTimeLogins->count() >= 2) {
            $alerts[] = [
                'type' => 'unusual_time_login',
                'severity' => 'medium',
                'message' => "Usuário {$user->name} fez {$unusualTimeLogins->count()} logins fora do horário comercial",
                'data' => [
                    'user_id' => $userId,
                    'unusual_logins' => $unusualTimeLogins->count(),
                    'times' => $unusualTimeLogins->pluck('created_at')->toArray()
                ]
            ];
        }

        // Check for rapid successive logins
        $rapidLogins = $this->checkRapidLogins($recentLogins);
        if ($rapidLogins) {
            $alerts[] = $rapidLogins;
        }

        return $alerts;
    }

    /**
     * Check for excessive activity patterns
     */
    public function checkExcessiveActivity(int $userId): array
    {
        $alerts = [];
        $user = User::find($userId);
        
        if (!$user) {
            return $alerts;
        }

        // Check for excessive page views
        $pageViews = UserActivity::where('user_id', $userId)
            ->where('activity_type', UserActivity::TYPE_PAGE_VIEW)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($pageViews > 100) {
            $alerts[] = [
                'type' => 'excessive_page_views',
                'severity' => 'medium',
                'message' => "Usuário {$user->name} visualizou {$pageViews} páginas na última hora",
                'data' => [
                    'user_id' => $userId,
                    'page_views' => $pageViews,
                    'threshold' => 100
                ]
            ];
        }

        // Check for excessive form submissions
        $formSubmissions = UserActivity::where('user_id', $userId)
            ->where('activity_type', UserActivity::TYPE_FORM_SUBMIT)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->count();

        if ($formSubmissions > 20) {
            $alerts[] = [
                'type' => 'excessive_form_submissions',
                'severity' => 'high',
                'message' => "Usuário {$user->name} enviou {$formSubmissions} formulários em 30 minutos",
                'data' => [
                    'user_id' => $userId,
                    'submissions' => $formSubmissions,
                    'threshold' => 20
                ]
            ];
        }

        return $alerts;
    }

    /**
     * Check for inactive users that suddenly become active
     */
    public function checkReactivatedUsers(): array
    {
        $alerts = [];
        
        // Find users who were inactive for 30+ days but logged in today
        $reactivatedUsers = UserActivity::where('activity_type', UserActivity::TYPE_LOGIN)
            ->where('created_at', '>=', now()->startOfDay())
            ->whereHas('user', function ($query) {
                $query->where('last_login_at', '<=', now()->subDays(30));
            })
            ->with('user')
            ->get();

        foreach ($reactivatedUsers as $activity) {
            $daysSinceLastLogin = now()->diffInDays($activity->user->last_login_at);
            
            if ($daysSinceLastLogin >= 30) {
                $alerts[] = [
                    'type' => 'reactivated_user',
                    'severity' => 'low',
                    'message' => "Usuário {$activity->user->name} voltou a fazer login após {$daysSinceLastLogin} dias inativo",
                    'data' => [
                        'user_id' => $activity->user_id,
                        'days_inactive' => $daysSinceLastLogin,
                        'last_login' => $activity->user->last_login_at,
                        'current_login' => $activity->created_at
                    ]
                ];
            }
        }

        return $alerts;
    }

    /**
     * Process all alerts and send notifications
     */
    public function processAlerts(): array
    {
        $allAlerts = [];
        
        // Check recent logins for suspicious activity
        $recentUsers = UserActivity::where('activity_type', UserActivity::TYPE_LOGIN)
            ->where('created_at', '>=', now()->subHours(24))
            ->distinct()
            ->pluck('user_id');

        foreach ($recentUsers as $userId) {
            $loginAlerts = $this->checkSuspiciousLogins($userId);
            $activityAlerts = $this->checkExcessiveActivity($userId);
            
            $allAlerts = array_merge($allAlerts, $loginAlerts, $activityAlerts);
        }

        // Check for reactivated users
        $reactivatedAlerts = $this->checkReactivatedUsers();
        $allAlerts = array_merge($allAlerts, $reactivatedAlerts);

        // Log all alerts
        foreach ($allAlerts as $alert) {
            $this->logAlert($alert);
            
            // Send notification for high severity alerts
            if ($alert['severity'] === 'high') {
                $this->sendAlertNotification($alert);
            }
        }

        return $allAlerts;
    }

    /**
     * Check for rapid successive logins
     */
    private function checkRapidLogins($logins): ?array
    {
        if ($logins->count() < 3) {
            return null;
        }

        $rapidCount = 0;
        $previousLogin = null;

        foreach ($logins as $login) {
            if ($previousLogin) {
                $timeDiff = $previousLogin->created_at->diffInMinutes($login->created_at);
                if ($timeDiff <= 2) {
                    $rapidCount++;
                }
            }
            $previousLogin = $login;
        }

        if ($rapidCount >= 3) {
            return [
                'type' => 'rapid_logins',
                'severity' => 'high',
                'message' => "Usuário fez {$rapidCount} logins rápidos consecutivos",
                'data' => [
                    'user_id' => $logins->first()->user_id,
                    'rapid_count' => $rapidCount,
                    'total_logins' => $logins->count()
                ]
            ];
        }

        return null;
    }

    /**
     * Log alert to system
     */
    private function logAlert(array $alert): void
    {
        Log::warning('Security Alert: ' . $alert['type'], [
            'severity' => $alert['severity'],
            'message' => $alert['message'],
            'data' => $alert['data'],
            'timestamp' => now()
        ]);
    }

    /**
     * Send alert notification to administrators
     */
    private function sendAlertNotification(array $alert): void
    {
        // Cache to prevent spam notifications
        $cacheKey = "alert_sent_{$alert['type']}_{$alert['data']['user_id']}";
        
        if (!Cache::has($cacheKey)) {
            // TODO: Send email/notification to administrators
            Log::info('Alert notification sent', $alert);
            
            // Cache for 1 hour to prevent duplicate notifications
            Cache::put($cacheKey, true, 3600);
        }
    }

    /**
     * Get alert statistics
     */
    public function getAlertStatistics(int $days = 7): array
    {
        // This would typically query a dedicated alerts table
        // For now, we'll return mock statistics
        return [
            'total_alerts' => 15,
            'high_severity' => 3,
            'medium_severity' => 7,
            'low_severity' => 5,
            'most_common_type' => 'unusual_time_login',
            'period_days' => $days
        ];
    }

    /**
     * Get users with recent alerts
     */
    public function getUsersWithAlerts(int $days = 7): array
    {
        // This would typically query alerts data
        // For now, return users with suspicious activity patterns
        $suspiciousUsers = UserActivity::where('created_at', '>=', now()->subDays($days))
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 50') // Users with more than 50 activities per day on average
            ->with('user:id,name,email')
            ->get()
            ->map(function ($activity) {
                return [
                    'user' => $activity->user,
                    'activity_count' => $activity->count(),
                    'risk_level' => $activity->count() > 100 ? 'high' : 'medium'
                ];
            });

        return $suspiciousUsers->toArray();
    }
}
