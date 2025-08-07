<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\PermissionService;

class UserSkillLevel extends Model
{
    protected $fillable = [
        'user_id',
        'skill_name',
        'access_level_id',
        'experience_points',
        'last_updated'
    ];

    protected $casts = [
        'experience_points' => 'integer',
        'last_updated' => 'datetime'
    ];

    /**
     * The user this skill level belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The access level for this skill
     */
    public function accessLevel(): BelongsTo
    {
        return $this->belongsTo(CourseAccessLevel::class, 'access_level_id');
    }

    /**
     * Get or create a skill level for a user
     */
    public static function forUser(int $userId, string $skillName): ?self
    {
        return static::where('user_id', $userId)
            ->where('skill_name', $skillName)
            ->first();
    }

    /**
     * Get or create a skill level for a user
     */
    public static function firstOrCreateForUser(
        int $userId,
        string $skillName,
        int $accessLevelId = 1,
        int $experiencePoints = 0
    ): self {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'skill_name' => $skillName
            ],
            [
                'access_level_id' => $accessLevelId,
                'experience_points' => $experiencePoints,
                'last_updated' => now()
            ]
        );
    }

    /**
     * Add experience points to this skill
     */
    public function addExperience(int $points): self
    {
        $this->experience_points += $points;
        $this->last_updated = now();
        
        // Check for level up
        $this->checkForLevelUp();
        
        $this->save();
        $this->clearUserCache();
        
        return $this;
    }

    /**
     * Check if the user should level up based on experience
     */
    protected function checkForLevelUp(): void
    {
        $requiredXp = $this->calculateXpForNextLevel();
        
        if ($this->experience_points >= $requiredXp) {
            $this->access_level_id++;
            $this->experience_points -= $requiredXp;
            
            // If there's still enough XP for another level, recurse
            if ($this->experience_points >= $this->calculateXpForNextLevel()) {
                $this->checkForLevelUp();
            }
        }
    }

    /**
     * Calculate XP required for the next level
     */
    protected function calculateXpForNextLevel(): int
    {
        // Base XP formula - can be customized
        return (int) (100 * pow(1.5, $this->access_level_id - 1));
    }

    /**
     * Get the progress to the next level (0-100)
     */
    public function getProgressToNextLevel(): int
    {
        $currentLevelXp = $this->calculateXpForLevel($this->access_level_id);
        $nextLevelXp = $this->calculateXpForLevel($this->access_level_id + 1);
        $xpInCurrentLevel = $this->experience_points - $currentLevelXp;
        $xpNeededForNextLevel = $nextLevelXp - $currentLevelXp;
        
        return min(100, (int) (($xpInCurrentLevel / $xpNeededForNextLevel) * 100));
    }

    /**
     * Calculate total XP needed for a specific level
     */
    protected function calculateXpForLevel(int $level): int
    {
        if ($level <= 1) return 0;
        return (int) (100 * (1 - pow(1.5, $level - 1)) / (1 - 1.5));
    }

    /**
     * Clear the user's permission cache
     */
    protected function clearUserCache(): void
    {
        $permissionService = app(PermissionService::class);
        $permissionService->clearUserPermissionCache($this->user_id);
    }

    /**
     * Get the user's skill level for a specific course
     */
    public static function getForCourse(int $userId, int $courseId): ?self
    {
        $course = Course::find($courseId);
        if (!$course || empty($course->required_skill)) {
            return null;
        }

        return static::where('user_id', $userId)
            ->where('skill_name', $course->required_skill)
            ->first();
    }

    /**
     * Check if the user meets the required skill level for a course
     */
    public static function meetsCourseRequirement(int $userId, int $courseId): bool
    {
        $course = Course::find($courseId);
        if (!$course || empty($course->required_skill)) {
            return true;
        }

        $userSkill = static::where('user_id', $userId)
            ->where('skill_name', $course->required_skill)
            ->first();

        if (!$userSkill) {
            return false;
        }

        $requiredLevel = $course->required_skill_level ?? 1;
        return $userSkill->access_level_id >= $requiredLevel;
    }
}
