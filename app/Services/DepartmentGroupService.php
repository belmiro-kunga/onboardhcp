<?php

namespace App\Services;

use App\Models\UserGroup;
use App\Modules\User\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DepartmentGroupService
{
    protected RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    /**
     * Create or get department group
     */
    public function createOrGetDepartmentGroup(string $department): UserGroup
    {
        $groupName = "Departamento: " . ucfirst($department);
        
        return UserGroup::firstOrCreate(
            [
                'name' => $groupName,
                'type' => 'department'
            ],
            [
                'description' => "Grupo automático para o departamento {$department}",
                'is_system' => true
            ]
        );
    }

    /**
     * Sync user to department group when department changes
     */
    public function syncUserToDepartmentGroup(User $user, ?string $oldDepartment = null): void
    {
        DB::beginTransaction();
        
        try {
            // Remove from old department group if exists
            if ($oldDepartment) {
                $oldGroup = UserGroup::where('name', "Departamento: " . ucfirst($oldDepartment))
                    ->where('type', 'department')
                    ->first();
                
                if ($oldGroup) {
                    $this->rolePermissionService->removeUsersFromGroup($oldGroup, [$user->id]);
                }
            }

            // Add to new department group if user has department
            if ($user->department) {
                $newGroup = $this->createOrGetDepartmentGroup($user->department);
                $this->rolePermissionService->addUsersToGroup($newGroup, [$user->id]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all department groups
     */
    public function getAllDepartmentGroups(): Collection
    {
        return UserGroup::where('type', 'department')->get();
    }

    /**
     * Get users by department
     */
    public function getUsersByDepartment(string $department): Collection
    {
        return User::where('department', $department)->get();
    }

    /**
     * Get department statistics
     */
    public function getDepartmentStatistics(): array
    {
        $stats = User::whereNotNull('department')
            ->select('department', DB::raw('count(*) as total'))
            ->groupBy('department')
            ->get()
            ->keyBy('department')
            ->toArray();

        return $stats;
    }

    /**
     * Sync all users to their department groups
     */
    public function syncAllUsersToDepartmentGroups(): array
    {
        $results = [
            'synced' => 0,
            'errors' => []
        ];

        $users = User::whereNotNull('department')->get();

        foreach ($users as $user) {
            try {
                $this->syncUserToDepartmentGroup($user);
                $results['synced']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Assign default permissions to department group
     */
    public function assignDefaultPermissionsToDepartment(string $department, array $permissionSlugs): void
    {
        $group = $this->createOrGetDepartmentGroup($department);
        $this->rolePermissionService->assignPermissionsToGroup($group, $permissionSlugs);
    }

    /**
     * Get suggested permissions for department
     */
    public function getSuggestedPermissionsForDepartment(string $department): array
    {
        $suggestions = [
            'recursos humanos' => [
                'view_users', 'create_users', 'edit_users', 'manage_user_roles',
                'view_reports', 'export_reports'
            ],
            'ti' => [
                'view_users', 'edit_users', 'manage_user_roles', 'view_roles',
                'manage_system_settings', 'view_audit_logs'
            ],
            'formacao' => [
                'view_courses', 'create_courses', 'edit_courses', 'manage_course_access',
                'view_videos', 'create_videos', 'edit_videos',
                'view_simulados', 'create_simulados', 'edit_simulados'
            ],
            'comercial' => [
                'view_users', 'view_courses', 'view_videos', 'view_simulados',
                'view_reports', 'view_analytics'
            ],
            'financeiro' => [
                'view_users', 'view_reports', 'export_reports', 'view_analytics'
            ]
        ];

        $departmentLower = strtolower($department);
        
        return $suggestions[$departmentLower] ?? [
            'view_courses', 'view_videos', 'view_simulados'
        ];
    }

    /**
     * Auto-assign permissions based on department
     */
    public function autoAssignDepartmentPermissions(string $department): void
    {
        $suggestedPermissions = $this->getSuggestedPermissionsForDepartment($department);
        $this->assignDefaultPermissionsToDepartment($department, $suggestedPermissions);
    }

    /**
     * Clean up empty department groups
     */
    public function cleanupEmptyDepartmentGroups(): int
    {
        $emptyGroups = UserGroup::where('type', 'department')
            ->whereDoesntHave('users')
            ->get();

        $count = $emptyGroups->count();
        
        foreach ($emptyGroups as $group) {
            $group->delete();
        }

        return $count;
    }
}