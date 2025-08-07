<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\PermissionService;

class UserGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_system'
    ];

    protected $casts = [
        'is_system' => 'boolean'
    ];

    /**
     * The users that belong to the group.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withTimestamps();
    }

    /**
     * The permissions that belong to the group.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'group_permission')
            ->withPivot('course_id')
            ->withTimestamps();
    }

    /**
     * Add users to the group
     *
     * @param array|int $userIds
     * @return array
     */
    public function addUsers($userIds): array
    {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        
        $this->users()->syncWithoutDetaching($userIds);
        
        // Clear permission cache for affected users
        $permissionService = app(PermissionService::class);
        foreach ($userIds as $userId) {
            $permissionService->clearUserPermissionCache($userId);
        }
        
        return [
            'attached' => $userIds,
            'total' => $this->users()->count()
        ];
    }

    /**
     * Remove users from the group
     *
     * @param array|int $userIds
     * @return array
     */
    public function removeUsers($userIds): array
    {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        
        // Get users before detaching to clear their cache
        $users = $this->users()->whereIn('user_id', $userIds)->get();
        
        $this->users()->detach($userIds);
        
        // Clear permission cache for affected users
        $permissionService = app(PermissionService::class);
        foreach ($users as $user) {
            $permissionService->clearUserPermissionCache($user->id);
        }
        
        return [
            'detached' => $userIds,
            'total' => $this->users()->count()
        ];
    }

    /**
     * Assign permissions to the group
     *
     * @param array $permissionIds
     * @param int|null $courseId
     * @return array
     */
    public function assignPermissions(array $permissionIds, ?int $courseId = null): array
    {
        $permissions = [];
        foreach ($permissionIds as $permissionId) {
            $permissions[$permissionId] = $courseId ? ['course_id' => $courseId] : [];
        }
        
        $this->permissions()->syncWithoutDetach($permissions);
        
        // Clear permission cache for all users in this group
        $this->clearGroupPermissionCache();
        
        return [
            'attached' => $permissionIds,
            'total' => $this->permissions()->count()
        ];
    }

    /**
     * Revoke permissions from the group
     *
     * @param array $permissionIds
     * @return array
     */
    public function revokePermissions(array $permissionIds): array
    {
        $this->permissions()->detach($permissionIds);
        
        // Clear permission cache for all users in this group
        $this->clearGroupPermissionCache();
        
        return [
            'detached' => $permissionIds,
            'total' => $this->permissions()->count()
        ];
    }

    /**
     * Clear permission cache for all users in this group
     */
    protected function clearGroupPermissionCache(): void
    {
        $permissionService = app(PermissionService::class);
        $permissionService->clearGroupCache($this->id);
    }
}
