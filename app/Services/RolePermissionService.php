<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Models\UserGroup;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RolePermissionService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'role_permission:';

    /**
     * Create a new role with permissions
     */
    public function createRole(string $name, string $description = null, array $permissionSlugs = [], bool $isSystem = false): Role
    {
        DB::beginTransaction();
        
        try {
            $role = Role::create([
                'name' => $name,
                'description' => $description,
                'is_system' => $isSystem
            ]);

            if (!empty($permissionSlugs)) {
                $this->assignPermissionsToRole($role, $permissionSlugs);
            }

            DB::commit();
            $this->clearRoleCache($role->id);
            
            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update role information and permissions
     */
    public function updateRole(Role $role, array $data): Role
    {
        DB::beginTransaction();
        
        try {
            // Update basic role information
            $role->update([
                'name' => $data['name'] ?? $role->name,
                'description' => $data['description'] ?? $role->description,
            ]);

            // Update permissions if provided
            if (isset($data['permissions'])) {
                $this->syncRolePermissions($role, $data['permissions']);
            }

            DB::commit();
            $this->clearRoleCache($role->id);
            
            return $role->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a role (only non-system roles)
     */
    public function deleteRole(Role $role): bool
    {
        if ($role->is_system) {
            throw new InvalidArgumentException('Cannot delete system roles');
        }

        DB::beginTransaction();
        
        try {
            // Remove role from all users
            $role->users()->detach();
            
            // Remove all permissions from role
            $role->permissions()->detach();
            
            // Delete the role
            $deleted = $role->delete();

            DB::commit();
            $this->clearRoleCache($role->id);
            
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign permissions to a role
     */
    public function assignPermissionsToRole(Role $role, array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
        
        if ($permissions->count() !== count($permissionSlugs)) {
            $foundSlugs = $permissions->pluck('slug')->toArray();
            $missingSlugs = array_diff($permissionSlugs, $foundSlugs);
            throw new InvalidArgumentException('Invalid permissions: ' . implode(', ', $missingSlugs));
        }

        $role->permissions()->syncWithoutDetaching($permissions->pluck('id'));
        $this->clearRoleCache($role->id);
    }

    /**
     * Remove permissions from a role
     */
    public function removePermissionsFromRole(Role $role, array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
        $role->permissions()->detach($permissions->pluck('id'));
        $this->clearRoleCache($role->id);
    }

    /**
     * Sync role permissions (replace all permissions)
     */
    public function syncRolePermissions(Role $role, array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
        
        if ($permissions->count() !== count($permissionSlugs)) {
            $foundSlugs = $permissions->pluck('slug')->toArray();
            $missingSlugs = array_diff($permissionSlugs, $foundSlugs);
            throw new InvalidArgumentException('Invalid permissions: ' . implode(', ', $missingSlugs));
        }

        $role->permissions()->sync($permissions->pluck('id'));
        $this->clearRoleCache($role->id);
    }

    /**
     * Assign role to user
     */
    public function assignRoleToUser(User $user, string $roleName, int $assignedBy = null): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        
        if (!$user->hasRole($roleName)) {
            $user->roles()->attach($role->id, [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy
            ]);
            
            $this->clearUserPermissionCache($user->id);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRoleFromUser(User $user, string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        
        if ($role) {
            $user->roles()->detach($role->id);
            $this->clearUserPermissionCache($user->id);
        }
    }

    /**
     * Sync user roles (replace all roles)
     */
    public function syncUserRoles(User $user, array $roleNames, int $assignedBy = null): void
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        
        if ($roles->count() !== count($roleNames)) {
            $foundNames = $roles->pluck('name')->toArray();
            $missingNames = array_diff($roleNames, $foundNames);
            throw new InvalidArgumentException('Invalid roles: ' . implode(', ', $missingNames));
        }

        $syncData = [];
        foreach ($roles as $role) {
            $syncData[$role->id] = [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy
            ];
        }

        $user->roles()->sync($syncData);
        $this->clearUserPermissionCache($user->id);
    }

    /**
     * Check if user has permission (considering all roles and groups)
     */
    public function userHasPermission(User $user, string $permissionSlug): bool
    {
        $cacheKey = self::CACHE_PREFIX . "user_permission:{$user->id}:{$permissionSlug}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $permissionSlug) {
            // Check direct role permissions
            $hasRolePermission = $user->roles()
                ->whereHas('permissions', function ($query) use ($permissionSlug) {
                    $query->where('slug', $permissionSlug);
                })
                ->exists();

            if ($hasRolePermission) {
                return true;
            }

            // Check group permissions
            $hasGroupPermission = $user->groups()
                ->whereHas('permissions', function ($query) use ($permissionSlug) {
                    $query->where('slug', $permissionSlug);
                })
                ->exists();

            return $hasGroupPermission;
        });
    }

    /**
     * Check if user has any of the given permissions
     */
    public function userHasAnyPermission(User $user, array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $permission) {
            if ($this->userHasPermission($user, $permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function userHasAllPermissions(User $user, array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $permission) {
            if (!$this->userHasPermission($user, $permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get all permissions for a user (from roles and groups)
     */
    public function getUserPermissions(User $user): Collection
    {
        $cacheKey = self::CACHE_PREFIX . "user_permissions:{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            // Get permissions from roles
            $rolePermissions = Permission::whereHas('roles', function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })->get();

            // Get permissions from groups
            $groupPermissions = Permission::whereHas('groups', function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })->get();

            // Merge and remove duplicates
            return $rolePermissions->merge($groupPermissions)->unique('id');
        });
    }

    /**
     * Get detailed permissions with their sources for a user
     */
    public function getUserPermissionsWithSources(User $user): array
    {
        $cacheKey = self::CACHE_PREFIX . "user_permissions_sources:{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $permissionsData = [];
            
            // Get permissions from roles
            $userRoles = $user->roles()->with('permissions')->get();
            foreach ($userRoles as $role) {
                foreach ($role->permissions as $permission) {
                    $key = $permission->slug;
                    if (!isset($permissionsData[$key])) {
                        $permissionsData[$key] = [
                            'permission' => $permission,
                            'sources' => []
                        ];
                    }
                    $permissionsData[$key]['sources'][] = [
                        'type' => 'role',
                        'name' => $role->name,
                        'id' => $role->id
                    ];
                }
            }
            
            // Get permissions from groups
            $userGroups = $user->groups()->with('permissions')->get();
            foreach ($userGroups as $group) {
                foreach ($group->permissions as $permission) {
                    $key = $permission->slug;
                    if (!isset($permissionsData[$key])) {
                        $permissionsData[$key] = [
                            'permission' => $permission,
                            'sources' => []
                        ];
                    }
                    $permissionsData[$key]['sources'][] = [
                        'type' => 'group',
                        'name' => $group->name,
                        'id' => $group->id
                    ];
                }
            }
            
            return $permissionsData;
        });
    }

    /**
     * Get all roles with their permissions
     */
    public function getAllRolesWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * Get role by name with permissions
     */
    public function getRoleWithPermissions(string $roleName): ?Role
    {
        return Role::with('permissions')->where('name', $roleName)->first();
    }

    /**
     * Create user group
     */
    public function createUserGroup(string $name, string $description = null, string $type = 'custom'): UserGroup
    {
        return UserGroup::create([
            'name' => $name,
            'description' => $description,
            'type' => $type
        ]);
    }

    /**
     * Add users to group
     */
    public function addUsersToGroup(UserGroup $group, array $userIds): void
    {
        $group->users()->syncWithoutDetaching($userIds);
        
        // Clear permission cache for affected users
        foreach ($userIds as $userId) {
            $this->clearUserPermissionCache($userId);
        }
    }

    /**
     * Remove users from group
     */
    public function removeUsersFromGroup(UserGroup $group, array $userIds): void
    {
        $group->users()->detach($userIds);
        
        // Clear permission cache for affected users
        foreach ($userIds as $userId) {
            $this->clearUserPermissionCache($userId);
        }
    }

    /**
     * Assign permissions to group
     */
    public function assignPermissionsToGroup(UserGroup $group, array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
        $group->permissions()->syncWithoutDetaching($permissions->pluck('id'));
        
        // Clear cache for all users in this group
        $this->clearGroupPermissionCache($group->id);
    }

    /**
     * Remove permissions from group
     */
    public function removePermissionsFromGroup(UserGroup $group, array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
        $group->permissions()->detach($permissions->pluck('id'));
        
        // Clear cache for all users in this group
        $this->clearGroupPermissionCache($group->id);
    }

    /**
     * Validate role assignment using advanced validation service
     */
    public function validateRoleAssignment(User $user, string $roleName): array
    {
        $validationService = app(\App\Services\RoleValidationService::class);
        return $validationService->validateRoleAssignment($user, $roleName);
    }

    /**
     * Validate role removal using advanced validation service
     */
    public function validateRoleRemoval(User $user, string $roleName): array
    {
        $validationService = app(\App\Services\RoleValidationService::class);
        return $validationService->validateRoleRemoval($user, $roleName);
    }

    /**
     * Clear role cache
     */
    private function clearRoleCache(int $roleId): void
    {
        $pattern = self::CACHE_PREFIX . "role:{$roleId}:*";
        $this->clearCacheByPattern($pattern);
    }

    /**
     * Clear user permission cache
     */
    public function clearUserPermissionCache(int $userId): void
    {
        $pattern = self::CACHE_PREFIX . "user_*:{$userId}*";
        $this->clearCacheByPattern($pattern);
    }

    /**
     * Clear group permission cache
     */
    private function clearGroupPermissionCache(int $groupId): void
    {
        // Get all users in this group and clear their cache
        $userIds = UserGroup::find($groupId)->users()->pluck('users.id');
        
        foreach ($userIds as $userId) {
            $this->clearUserPermissionCache($userId);
        }
    }

    /**
     * Clear cache by pattern (simplified implementation)
     */
    private function clearCacheByPattern(string $pattern): void
    {
        // This is a simplified implementation
        // In production, you might want to use Redis SCAN or similar
        Cache::flush(); // For now, we'll just flush all cache
    }
}