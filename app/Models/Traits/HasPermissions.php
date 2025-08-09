<?php

namespace App\Models\Traits;

use App\Models\UserGroup;
use App\Models\Permission;
use App\Models\UserSkillLevel;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Trait for user permission functionality
 */
trait HasPermissions
{
    /**
     * The groups the user belongs to.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'group_user', 'user_id', 'group_id')
            ->withTimestamps();
    }

    /**
     * Check if the user is in a specific group
     */
    public function inGroup($group): bool
    {
        if (is_string($group)) {
            return $this->groups->contains('name', $group);
        }

        if ($group instanceof UserGroup) {
            return $this->groups->contains('id', $group->id);
        }

        if (is_int($group)) {
            return $this->groups->contains('id', $group);
        }

        return false;
    }

    /**
     * Add the user to a group
     */
    public function addToGroup($group): array
    {
        if (is_string($group)) {
            $group = UserGroup::where('name', $group)->firstOrFail();
        } elseif (is_int($group)) {
            $group = UserGroup::findOrFail($group);
        }

        if (!$this->inGroup($group)) {
            $this->groups()->attach($group->id);
            $this->clearPermissionCache();
        }

        return [
            'added' => true,
            'group' => $group
        ];
    }

    /**
     * Remove the user from a group
     */
    public function removeFromGroup($group): array
    {
        if (is_string($group)) {
            $group = UserGroup::where('name', $group)->firstOrFail();
        } elseif (is_int($group)) {
            $group = UserGroup::findOrFail($group);
        }

        if ($this->inGroup($group)) {
            $this->groups()->detach($group->id);
            $this->clearPermissionCache();
        }

        return [
            'removed' => true,
            'group' => $group
        ];
    }

    /**
     * Get all permissions for the user
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        $permissions = collect();

        // Get permissions from groups
        $this->groups->each(function ($group) use (&$permissions) {
            $permissions = $permissions->merge($group->permissions);
        });

        // Add direct permissions if any
        if (method_exists($this, 'permissions')) {
            $permissions = $permissions->merge($this->permissions);
        }

        return $permissions->unique('id');
    }

    /**
     * Check if the user has a specific permission
     */
    public function hasPermission($permission, $model = null): bool
    {
        // Admins have all permissions
        if ($this->is_admin ?? false) {
            return true;
        }

        // Check if the permission exists in any of the user's groups
        return $this->getAllPermissions()
            ->where('slug', $permission)
            ->when($model, function ($query) use ($model) {
                return $query->where('model_type', get_class($model));
            })
            ->isNotEmpty();
    }

    /**
     * Check if the user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions, $model = null): bool
    {
        if ($this->is_admin ?? false) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission, $model)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions, $model = null): bool
    {
        if ($this->is_admin ?? false) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission, $model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the user's skill level for a specific skill
     */
    public function getSkillLevel(string $skillName): ?UserSkillLevel
    {
        return $this->skillLevels()
            ->where('skill_name', $skillName)
            ->first();
    }

    /**
     * Get all skill levels for the user
     */
    public function skillLevels()
    {
        return $this->hasMany(UserSkillLevel::class);
    }

    /**
     * Check if the user has the required skill level
     */
    public function hasSkillLevel(string $skillName, int $requiredLevel): bool
    {
        $skill = $this->getSkillLevel($skillName);
        return $skill && $skill->access_level_id >= $requiredLevel;
    }

    /**
     * Clear the user's permission cache
     */
    public function clearPermissionCache(): void
    {
        $permissionService = app(PermissionService::class);
        $permissionService->clearUserPermissionCache($this->id);
    }

    /**
     * Check if the user can access a course
     */
    public function canAccessCourse($course): bool
    {
        $permissionService = app(PermissionService::class);
        $courseId = is_object($course) ? $course->id : $course;
        
        return $permissionService->checkCourseAccess($this, $courseId);
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return (bool) ($this->is_admin ?? false);
    }

    /**
     * Make the user an admin
     */
    public function makeAdmin(): self
    {
        $this->is_admin = true;
        $this->save();
        $this->clearPermissionCache();
        
        return $this;
    }

    /**
     * Remove admin privileges
     */
    public function removeAdmin(): self
    {
        $this->is_admin = false;
        $this->save();
        $this->clearPermissionCache();
        
        return $this;
    }
}
