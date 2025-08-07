<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\UserGroup;
use App\Models\Permission;
use App\Models\CourseAccessRule;
use App\Models\UserSkillLevel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use App\Events\PermissionUpdated;

class PermissionService
{
    protected $cachePrefix = 'permission_';
    protected $cacheTtl = 3600; // 1 hour

    /**
     * Check if a user has access to a course based on their permissions
     *
     * @param User $user
     * @param Course|int $course
     * @param string $permission
     * @return bool
     */
    public function checkCourseAccess(User $user, $course, string $permission = 'view'): bool
    {
        $courseId = $course instanceof Course ? $course->id : $course;
        $cacheKey = $this->getUserPermissionCacheKey($user->id, $courseId, $permission);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($user, $courseId, $permission) {
            // Check if user is admin (admins have all permissions)
            if ($user->is_admin) {
                return true;
            }

            // Get user's groups and their permissions
            $userGroups = $this->getUserGroups($user->id);
            $groupPermissions = $this->getGroupPermissions($userGroups);

            // Check if any group has the required permission
            if ($this->hasPermission($groupPermissions, $permission, $courseId)) {
                return true;
            }

            // Check course-specific access rules
            $courseAccess = $this->checkCourseAccessRules($user, $courseId);
            if ($courseAccess === false) {
                return false;
            }

            // Check skill-based access if applicable
            if (!$this->checkSkillBasedAccess($user, $courseId)) {
                return false;
            }

            return true;
        });
    }

    /**
     * Assign a course to multiple users with optional permissions
     *
     * @param array|Collection $userIds
     * @param int $courseId
     * @param array $permissions
     * @return array
     */
    public function assignCourseToUsers($userIds, int $courseId, array $permissions = ['view']): array
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($userIds as $userId) {
            try {
                $user = User::findOrFail($userId);
                
                // Add user to course with specified permissions
                $user->courses()->syncWithoutDetaching([$courseId => [
                    'permissions' => json_encode($permissions),
                    'assigned_at' => now()
                ]]);

                $results['success'][] = $userId;
                $this->clearUserPermissionCache($userId, $courseId);
            } catch (\Exception $e) {
                Log::error("Failed to assign course to user: {$e->getMessage()}", [
                    'user_id' => $userId,
                    'course_id' => $courseId
                ]);
                $results['failed'][] = [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Dispatch event for real-time updates
        if (!empty($results['success'])) {
            Event::dispatch(new PermissionUpdated(
                'course_assignment',
                $courseId,
                $results['success']
            ));
        }

        return $results;
    }

    /**
     * Create a new user group with optional permissions
     *
     * @param array $data
     * @param array $permissionIds
     * @return UserGroup
     */
    public function createUserGroup(array $data, array $permissionIds = []): UserGroup
    {
        try {
            // Create the group
            $group = UserGroup::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_system' => $data['is_system'] ?? false
            ]);

            // Attach permissions if any
            if (!empty($permissionIds)) {
                $group->permissions()->attach($permissionIds);
            }

            // Clear relevant caches
            $this->clearGroupCache($group->id);

            // Dispatch event
            Event::dispatch(new PermissionUpdated('group_created', $group->id));

            return $group;
        } catch (\Exception $e) {
            Log::error('Failed to create user group: ' . $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * Restrict course access based on skill level
     *
     * @param int $courseId
     * @param string $skillName
     * @param int $requiredLevel
     * @param array $options
     * @return CourseAccessRule
     */
    public function restrictCourseByLevel(int $courseId, string $skillName, int $requiredLevel, array $options = []): CourseAccessRule
    {
        $rule = CourseAccessRule::updateOrCreate(
            [
                'course_id' => $courseId,
                'access_level_id' => $requiredLevel,
                'skill_name' => $skillName
            ],
            [
                'is_restricted' => $options['is_restricted'] ?? true,
                'restricted_to' => $options['restricted_to'] ?? null,
                'message' => $options['message'] ?? 'Access restricted. Required skill level not met.'
            ]
        );

        // Clear relevant caches
        $this->clearCourseAccessCache($courseId);

        // Dispatch event
        Event::dispatch(new PermissionUpdated('course_access_updated', $courseId));

        return $rule;
    }

    /**
     * Get all groups a user belongs to
     *
     * @param int $userId
     * @return Collection
     */
    protected function getUserGroups(int $userId): Collection
    {
        $cacheKey = $this->cachePrefix . "user_groups_{$userId}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($userId) {
            return UserGroup::whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();
        });
    }

    /**
     * Get all permissions for a collection of groups
     *
     * @param Collection $groups
     * @return Collection
     */
    protected function getGroupPermissions(Collection $groups): Collection
    {
        if ($groups->isEmpty()) {
            return collect();
        }

        $cacheKey = $this->cachePrefix . 'group_permissions_' . md5($groups->pluck('id')->sort()->implode(','));
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($groups) {
            return Permission::whereHas('groups', function ($query) use ($groups) {
                $query->whereIn('group_id', $groups->pluck('id'));
            })->get();
        });
    }

    /**
     * Check if permissions collection has a specific permission
     *
     * @param Collection $permissions
     * @param string $permission
     * @param int|null $courseId
     * @return bool
     */
    protected function hasPermission(Collection $permissions, string $permission, ?int $courseId = null): bool
    {
        return $permissions->contains(function ($p) use ($permission, $courseId) {
            $matches = $p->slug === $permission || $p->slug === '*';
            
            if ($courseId && $p->pivot && $p->pivot->course_id) {
                return $matches && $p->pivot->course_id == $courseId;
            }
            
            return $matches;
        });
    }

    /**
     * Check course access rules
     *
     * @param User $user
     * @param int $courseId
     * @return bool|null Returns null if no specific rules apply
     */
    protected function checkCourseAccessRules(User $user, int $courseId): ?bool
    {
        $rules = $this->getCourseAccessRules($courseId);
        
        foreach ($rules as $rule) {
            if ($rule->is_restricted) {
                // Check if user is in the allowed list
                $allowedUsers = $rule->restricted_to['user_ids'] ?? [];
                $allowedGroups = $rule->restricted_to['group_ids'] ?? [];
                
                $userInAllowedUsers = in_array($user->id, $allowedUsers);
                $userInAllowedGroups = $user->groups()->whereIn('group_id', $allowedGroups)->exists();
                
                if (!$userInAllowedUsers && !$userInAllowedGroups) {
                    return false;
                }
            }
        }
        
        return null; // No specific rules deny access
    }

    /**
     * Check skill-based access
     *
     * @param User $user
     * @param int $courseId
     * @return bool
     */
    protected function checkSkillBasedAccess(User $user, int $courseId): bool
    {
        $rules = $this->getCourseAccessRules($courseId);
        
        foreach ($rules as $rule) {
            if (empty($rule->skill_name)) continue;
            
            $userSkill = UserSkillLevel::where('user_id', $user->id)
                ->where('skill_name', $rule->skill_name)
                ->first();
                
            if (!$userSkill || $userSkill->access_level_id < $rule->access_level_id) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get course access rules with caching
     *
     * @param int $courseId
     * @return Collection
     */
    protected function getCourseAccessRules(int $courseId): Collection
    {
        $cacheKey = $this->cachePrefix . "course_access_rules_{$courseId}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($courseId) {
            return CourseAccessRule::where('course_id', $courseId)->get();
        });
    }

    /**
     * Clear permission cache for a user
     *
     * @param int $userId
     * @param int|null $courseId
     */
    public function clearUserPermissionCache(int $userId, ?int $courseId = null): void
    {
        $pattern = $this->getUserPermissionCacheKey($userId, $courseId, '*');
        $this->clearCacheByPattern($pattern);
        
        // Clear user groups cache
        Cache::forget($this->cachePrefix . "user_groups_{$userId}");
    }

    /**
     * Clear group cache
     *
     * @param int $groupId
     */
    public function clearGroupCache(int $groupId): void
    {
        $pattern = $this->cachePrefix . "group_permissions_*{$groupId}*";
        $this->clearCacheByPattern($pattern);
        
        // Clear user groups cache for all users in this group
        $userIds = DB::table('group_user')
            ->where('group_id', $groupId)
            ->pluck('user_id');
            
        foreach ($userIds as $userId) {
            Cache::forget($this->cachePrefix . "user_groups_{$userId}");
        }
    }

    /**
     * Clear course access cache
     *
     * @param int $courseId
     */
    public function clearCourseAccessCache(int $courseId): void
    {
        Cache::forget($this->cachePrefix . "course_access_rules_{$courseId}");
        
        // Clear all permission caches for this course
        $pattern = $this->cachePrefix . "permission_*_course_{$courseId}_*";
        $this->clearCacheByPattern($pattern);
    }

    /**
     * Clear cache by pattern
     *
     * @param string $pattern
     */
    protected function clearCacheByPattern(string $pattern): void
    {
        if (method_exists(app('cache')->store()->getStore(), 'getPrefix')) {
            $prefix = app('cache')->store()->getPrefix();
            $pattern = $prefix . $pattern;
        }

        $redis = app('redis')->connection();
        $keys = $redis->keys($pattern);
        
        if (!empty($keys)) {
            $redis->del($keys);
        }
    }

    /**
     * Generate cache key for user permissions
     *
     * @param int $userId
     * @param int $courseId
     * @param string $permission
     * @return string
     */
    protected function getUserPermissionCacheKey(int $userId, ?int $courseId, string $permission): string
    {
        $key = $this->cachePrefix . "user_{$userId}_";
        $key .= $courseId ? "course_{$courseId}_" : '';
        $key .= "permission_{$permission}";
        
        return $key;
    }
}
