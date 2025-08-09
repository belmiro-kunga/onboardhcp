<?php

namespace App\Traits;

use App\Services\RolePermissionService;
use Illuminate\Support\Collection;

trait HasPermissions
{
    /**
     * Check if user has a specific permission
     */
    public function can(string $permission): bool
    {
        $rolePermissionService = app(RolePermissionService::class);
        return $rolePermissionService->userHasPermission($this, $permission);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function canAny(array $permissions): bool
    {
        $rolePermissionService = app(RolePermissionService::class);
        return $rolePermissionService->userHasAnyPermission($this, $permissions);
    }

    /**
     * Check if user has all of the given permissions
     */
    public function canAll(array $permissions): bool
    {
        $rolePermissionService = app(RolePermissionService::class);
        return $rolePermissionService->userHasAllPermissions($this, $permissions);
    }

    /**
     * Get all user permissions
     */
    public function getAllPermissions(): Collection
    {
        $rolePermissionService = app(RolePermissionService::class);
        return $rolePermissionService->getUserPermissions($this);
    }

    /**
     * Get permissions grouped by module
     */
    public function getPermissionsByModule(): Collection
    {
        return $this->getAllPermissions()->groupBy('module');
    }

    /**
     * Check if user has permission for a specific module
     */
    public function canAccessModule(string $module): bool
    {
        return $this->getAllPermissions()
            ->where('module', $module)
            ->isNotEmpty();
    }

    /**
     * Get user's role names
     */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Get user's group names
     */
    public function getGroupNames(): array
    {
        return $this->groups->pluck('name')->toArray();
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Check if user is admin (Super Admin or Admin)
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Admin']);
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('Manager');
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return $this->canAny([
            'create_users',
            'edit_users',
            'delete_users',
            'manage_user_roles'
        ]);
    }

    /**
     * Check if user can manage roles
     */
    public function canManageRoles(): bool
    {
        return $this->canAny([
            'create_roles',
            'edit_roles',
            'delete_roles'
        ]);
    }

    /**
     * Check if user can view audit logs
     */
    public function canViewAuditLogs(): bool
    {
        return $this->can('view_audit_logs');
    }

    /**
     * Check if user can access admin panel
     */
    public function canAccessAdmin(): bool
    {
        return $this->isAdmin() || $this->canManageUsers() || $this->canManageRoles();
    }
}