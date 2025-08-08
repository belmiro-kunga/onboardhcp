<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GamificationService
{
    /**
     * Get gamification settings
     *
     * @return array
     */
    public function getSettings(): array
    {
        return [
            'points_per_video' => 10,
            'points_per_course_completion' => 50,
            'points_per_simulation' => 25,
            'badges_enabled' => true,
            'leaderboard_enabled' => true,
            'monthly_reset' => false,
        ];
    }

    /**
     * Get leaderboard data
     *
     * @param int $limit
     * @return Collection
     */
    public function getLeaderboard(int $limit = 10): Collection
    {
        return Cache::remember('gamification_leaderboard', 3600, function () use ($limit) {
            return User::select('users.*')
                ->selectRaw('COALESCE(SUM(user_points.points), 0) as total_points')
                ->leftJoin('user_points', 'users.id', '=', 'user_points.user_id')
                ->where('users.is_admin', false)
                ->groupBy('users.id')
                ->orderByDesc('total_points')
                ->limit($limit)
                ->get()
                ->map(function ($user, $index) {
                    $user->position = $index + 1;
                    $user->total_points = $user->total_points ?? 0;
                    return $user;
                });
        });
    }

    /**
     * Get user points
     *
     * @param int $userId
     * @return int
     */
    public function getUserPoints(int $userId): int
    {
        return Cache::remember("user_points_{$userId}", 1800, function () use ($userId) {
            return DB::table('user_points')
                ->where('user_id', $userId)
                ->sum('points') ?? 0;
        });
    }

    /**
     * Add points to user
     *
     * @param int $userId
     * @param int $points
     * @param string $reason
     * @return bool
     */
    public function addPoints(int $userId, int $points, string $reason = 'General activity'): bool
    {
        try {
            DB::table('user_points')->insert([
                'user_id' => $userId,
                'points' => $points,
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Clear cache
            Cache::forget("user_points_{$userId}");
            Cache::forget('gamification_leaderboard');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get user badges
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserBadges(int $userId): Collection
    {
        return Cache::remember("user_badges_{$userId}", 1800, function () use ($userId) {
            return collect([
                [
                    'id' => 1,
                    'name' => 'Primeiro Vídeo',
                    'description' => 'Assistiu ao primeiro vídeo',
                    'icon' => '🎬',
                    'earned' => true,
                    'earned_at' => now()->subDays(5),
                ],
                [
                    'id' => 2,
                    'name' => 'Estudante Dedicado',
                    'description' => 'Completou 5 vídeos',
                    'icon' => '📚',
                    'earned' => true,
                    'earned_at' => now()->subDays(3),
                ],
                [
                    'id' => 3,
                    'name' => 'Mestre do Conhecimento',
                    'description' => 'Completou 10 vídeos',
                    'icon' => '🎓',
                    'earned' => false,
                    'earned_at' => null,
                ],
            ]);
        });
    }

    /**
     * Get gamification statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return Cache::remember('gamification_statistics', 1800, function () {
            $totalUsers = User::where('is_admin', false)->count();
            $activeUsers = User::where('is_admin', false)
                ->where('last_login_at', '>=', now()->subDays(30))
                ->count();
            
            $topUser = $this->getLeaderboard(1)->first();
            
            return [
                'total_points_distributed' => DB::table('user_points')->sum('points') ?? 0,
                'total_badges_earned' => 18, // Placeholder
                'active_participants' => $activeUsers,
                'total_users' => $totalUsers,
                'participation_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0,
                'top_user' => $topUser ? $topUser->name : 'Nenhum usuário',
                'top_user_points' => $topUser ? $topUser->total_points : 0,
            ];
        });
    }

    /**
     * Calculate user level based on points
     *
     * @param int $points
     * @return array
     */
    public function calculateLevel(int $points): array
    {
        $levels = [
            1 => 0,
            2 => 100,
            3 => 250,
            4 => 500,
            5 => 1000,
            6 => 2000,
            7 => 3500,
            8 => 5000,
            9 => 7500,
            10 => 10000,
        ];

        $currentLevel = 1;
        $nextLevel = 2;
        $pointsForNext = 100;
        $progress = 0;

        foreach ($levels as $level => $requiredPoints) {
            if ($points >= $requiredPoints) {
                $currentLevel = $level;
            } else {
                $nextLevel = $level;
                $pointsForNext = $requiredPoints - $points;
                $previousLevelPoints = $levels[$currentLevel] ?? 0;
                $currentLevelRange = $requiredPoints - $previousLevelPoints;
                $currentLevelProgress = $points - $previousLevelPoints;
                $progress = $currentLevelRange > 0 ? round(($currentLevelProgress / $currentLevelRange) * 100, 1) : 0;
                break;
            }
        }

        return [
            'current_level' => $currentLevel,
            'next_level' => $nextLevel <= 10 ? $nextLevel : null,
            'points_for_next' => $nextLevel <= 10 ? $pointsForNext : 0,
            'progress_percentage' => $progress,
            'total_points' => $points,
        ];
    }
}