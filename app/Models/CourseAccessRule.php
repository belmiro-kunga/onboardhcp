<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\PermissionService;

class CourseAccessRule extends Model
{
    protected $fillable = [
        'course_id',
        'access_level_id',
        'skill_name',
        'is_restricted',
        'restricted_to',
        'message'
    ];

    protected $casts = [
        'is_restricted' => 'boolean',
        'restricted_to' => 'array'
    ];

    /**
     * The course this rule applies to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * The access level required
     */
    public function accessLevel(): BelongsTo
    {
        return $this->belongsTo(CourseAccessLevel::class, 'access_level_id');
    }

    /**
     * Check if a user is in the restricted users list
     */
    public function isUserRestricted(int $userId): bool
    {
        if (!$this->is_restricted || empty($this->restricted_to['user_ids'])) {
            return false;
        }

        return in_array($userId, $this->restricted_to['user_ids']);
    }

    /**
     * Check if a user is in the restricted groups list
     */
    public function isUserInRestrictedGroups(User $user): bool
    {
        if (!$this->is_restricted || empty($this->restricted_to['group_ids'])) {
            return false;
        }

        return $user->groups()
            ->whereIn('group_id', $this->restricted_to['group_ids'])
            ->exists();
    }

    /**
     * Add users to the restricted list
     */
    public function restrictToUsers(array $userIds): void
    {
        $restrictedTo = $this->restricted_to ?? [];
        $restrictedTo['user_ids'] = array_unique(
            array_merge($restrictedTo['user_ids'] ?? [], $userIds)
        );

        $this->update([
            'is_restricted' => true,
            'restricted_to' => $restrictedTo
        ]);

        $this->clearCache();
    }

    /**
     * Add groups to the restricted list
     */
    public function restrictToGroups(array $groupIds): void
    {
        $restrictedTo = $this->restricted_to ?? [];
        $restrictedTo['group_ids'] = array_unique(
            array_merge($restrictedTo['group_ids'] ?? [], $groupIds)
        );

        $this->update([
            'is_restricted' => true,
            'restricted_to' => $restrictedTo
        ]);

        $this->clearCache();
    }

    /**
     * Remove users from the restricted list
     */
    public function allowUsers(array $userIds): void
    {
        if (empty($this->restricted_to['user_ids'])) {
            return;
        }

        $restrictedTo = $this->restricted_to;
        $restrictedTo['user_ids'] = array_diff(
            $restrictedTo['user_ids'],
            $userIds
        );

        $this->update(['restricted_to' => $restrictedTo]);
        $this->clearCache();
    }

    /**
     * Remove groups from the restricted list
     */
    public function allowGroups(array $groupIds): void
    {
        if (empty($this->restricted_to['group_ids'])) {
            return;
        }

        $restrictedTo = $this->restricted_to;
        $restrictedTo['group_ids'] = array_diff(
            $restrictedTo['group_ids'],
            $groupIds
        );

        $this->update(['restricted_to' => $restrictedTo]);
        $this->clearCache();
    }

    /**
     * Clear the cache for this rule
     */
    protected function clearCache(): void
    {
        $permissionService = app(PermissionService::class);
        $permissionService->clearCourseAccessCache($this->course_id);
    }

    /**
     * Get the access rule for a specific course and skill
     */
    public static function forCourseAndSkill(int $courseId, string $skillName): ?self
    {
        return static::where('course_id', $courseId)
            ->where('skill_name', $skillName)
            ->first();
    }

    /**
     * Get the access rule or create it if it doesn't exist
     */
    public static function findOrCreateForCourse(
        int $courseId,
        string $skillName,
        int $accessLevelId,
        bool $isRestricted = false,
        array $restrictedTo = null,
        string $message = null
    ): self {
        return static::firstOrCreate(
            [
                'course_id' => $courseId,
                'skill_name' => $skillName
            ],
            [
                'access_level_id' => $accessLevelId,
                'is_restricted' => $isRestricted,
                'restricted_to' => $restrictedTo,
                'message' => $message
            ]
        );
    }
}
