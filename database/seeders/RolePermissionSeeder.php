<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create basic permissions organized by modules
        $permissions = [
            // User Management Module
            ['name' => 'view_users', 'slug' => 'view_users', 'description' => 'View users list and details', 'module' => 'users'],
            ['name' => 'create_users', 'slug' => 'create_users', 'description' => 'Create new users', 'module' => 'users'],
            ['name' => 'edit_users', 'slug' => 'edit_users', 'description' => 'Edit user information', 'module' => 'users'],
            ['name' => 'delete_users', 'slug' => 'delete_users', 'description' => 'Delete users', 'module' => 'users'],
            ['name' => 'manage_user_roles', 'slug' => 'manage_user_roles', 'description' => 'Assign and remove user roles', 'module' => 'users'],
            ['name' => 'bulk_actions_users', 'slug' => 'bulk_actions_users', 'description' => 'Perform bulk actions on users', 'module' => 'users'],
            ['name' => 'import_export_users', 'slug' => 'import_export_users', 'description' => 'Import and export user data', 'module' => 'users'],
            
            // Role Management Module
            ['name' => 'view_roles', 'slug' => 'view_roles', 'description' => 'View roles and permissions', 'module' => 'roles'],
            ['name' => 'create_roles', 'slug' => 'create_roles', 'description' => 'Create new roles', 'module' => 'roles'],
            ['name' => 'edit_roles', 'slug' => 'edit_roles', 'description' => 'Edit role permissions', 'module' => 'roles'],
            ['name' => 'delete_roles', 'slug' => 'delete_roles', 'description' => 'Delete roles', 'module' => 'roles'],
            
            // Course Management Module
            ['name' => 'view_courses', 'slug' => 'view_courses', 'description' => 'View courses', 'module' => 'courses'],
            ['name' => 'create_courses', 'slug' => 'create_courses', 'description' => 'Create new courses', 'module' => 'courses'],
            ['name' => 'edit_courses', 'slug' => 'edit_courses', 'description' => 'Edit course content', 'module' => 'courses'],
            ['name' => 'delete_courses', 'slug' => 'delete_courses', 'description' => 'Delete courses', 'module' => 'courses'],
            ['name' => 'manage_course_access', 'slug' => 'manage_course_access', 'description' => 'Manage course access levels', 'module' => 'courses'],
            
            // Video Management Module
            ['name' => 'view_videos', 'slug' => 'view_videos', 'description' => 'View videos', 'module' => 'videos'],
            ['name' => 'create_videos', 'slug' => 'create_videos', 'description' => 'Upload new videos', 'module' => 'videos'],
            ['name' => 'edit_videos', 'slug' => 'edit_videos', 'description' => 'Edit video information', 'module' => 'videos'],
            ['name' => 'delete_videos', 'slug' => 'delete_videos', 'description' => 'Delete videos', 'module' => 'videos'],
            
            // Simulado Management Module
            ['name' => 'view_simulados', 'slug' => 'view_simulados', 'description' => 'View simulados', 'module' => 'simulados'],
            ['name' => 'create_simulados', 'slug' => 'create_simulados', 'description' => 'Create new simulados', 'module' => 'simulados'],
            ['name' => 'edit_simulados', 'slug' => 'edit_simulados', 'description' => 'Edit simulado questions', 'module' => 'simulados'],
            ['name' => 'delete_simulados', 'slug' => 'delete_simulados', 'description' => 'Delete simulados', 'module' => 'simulados'],
            ['name' => 'view_simulado_results', 'slug' => 'view_simulado_results', 'description' => 'View simulado results', 'module' => 'simulados'],
            
            // Reports and Analytics Module
            ['name' => 'view_reports', 'slug' => 'view_reports', 'description' => 'View system reports', 'module' => 'reports'],
            ['name' => 'export_reports', 'slug' => 'export_reports', 'description' => 'Export report data', 'module' => 'reports'],
            ['name' => 'view_analytics', 'slug' => 'view_analytics', 'description' => 'View system analytics', 'module' => 'analytics'],
            
            // Audit and Logs Module
            ['name' => 'view_audit_logs', 'slug' => 'view_audit_logs', 'description' => 'View system audit logs', 'module' => 'audit'],
            ['name' => 'export_audit_logs', 'slug' => 'export_audit_logs', 'description' => 'Export audit log data', 'module' => 'audit'],
            
            // System Settings Module
            ['name' => 'manage_system_settings', 'slug' => 'manage_system_settings', 'description' => 'Manage system configuration', 'module' => 'system'],
            ['name' => 'manage_integrations', 'slug' => 'manage_integrations', 'description' => 'Manage external integrations', 'module' => 'system'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Create basic roles
        $roles = [
            [
                'name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'is_system' => true
            ],
            [
                'name' => 'Admin',
                'description' => 'Administrative access to most system features',
                'is_system' => true
            ],
            [
                'name' => 'Manager',
                'description' => 'Management access to users and content',
                'is_system' => false
            ],
            [
                'name' => 'Employee',
                'description' => 'Basic employee access to courses and simulados',
                'is_system' => false
            ],
            [
                'name' => 'Student',
                'description' => 'Student access to assigned courses only',
                'is_system' => false
            ]
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        // Get all permissions and roles
        $permissions = DB::table('permissions')->pluck('id', 'slug');
        $roles = DB::table('roles')->pluck('id', 'name');

        // Super Admin gets all permissions
        $superAdminPermissions = $permissions->values()->toArray();
        $this->assignPermissionsToRole($roles['Super Admin'], $superAdminPermissions);

        // Admin permissions (most permissions except system settings)
        $adminPermissions = $permissions->except([
            'manage_system_settings',
            'manage_integrations'
        ])->values()->toArray();
        $this->assignPermissionsToRole($roles['Admin'], $adminPermissions);

        // Manager permissions
        $managerPermissions = [
            'view_users', 'create_users', 'edit_users', 'manage_user_roles',
            'bulk_actions_users', 'import_export_users',
            'view_roles',
            'view_courses', 'create_courses', 'edit_courses', 'manage_course_access',
            'view_videos', 'create_videos', 'edit_videos',
            'view_simulados', 'create_simulados', 'edit_simulados', 'view_simulado_results',
            'view_reports', 'export_reports', 'view_analytics',
            'view_audit_logs'
        ];
        $this->assignPermissionsToRole($roles['Manager'], 
            $permissions->only($managerPermissions)->values()->toArray());

        // Employee permissions
        $employeePermissions = [
            'view_users',
            'view_courses', 'view_videos',
            'view_simulados', 'view_simulado_results'
        ];
        $this->assignPermissionsToRole($roles['Employee'], 
            $permissions->only($employeePermissions)->values()->toArray());

        // Student permissions (minimal access)
        $studentPermissions = [
            'view_courses', 'view_videos', 'view_simulados'
        ];
        $this->assignPermissionsToRole($roles['Student'], 
            $permissions->only($studentPermissions)->values()->toArray());
    }

    private function assignPermissionsToRole(int $roleId, array $permissionIds): void
    {
        foreach ($permissionIds as $permissionId) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
