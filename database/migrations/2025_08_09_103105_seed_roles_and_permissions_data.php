<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create basic permissions for advanced user management
        $permissions = [
            // User Management
            ['name' => 'view_users', 'slug' => 'view_users', 'description' => 'View users list and details'],
            ['name' => 'create_users', 'slug' => 'create_users', 'description' => 'Create new users'],
            ['name' => 'edit_users', 'slug' => 'edit_users', 'description' => 'Edit user information'],
            ['name' => 'delete_users', 'slug' => 'delete_users', 'description' => 'Delete users'],
            ['name' => 'manage_user_roles', 'slug' => 'manage_user_roles', 'description' => 'Assign and remove user roles'],
            ['name' => 'bulk_actions_users', 'slug' => 'bulk_actions_users', 'description' => 'Perform bulk actions on users'],
            ['name' => 'import_export_users', 'slug' => 'import_export_users', 'description' => 'Import and export user data'],
            
            // Role Management
            ['name' => 'view_roles', 'slug' => 'view_roles', 'description' => 'View roles and permissions'],
            ['name' => 'create_roles', 'slug' => 'create_roles', 'description' => 'Create new roles'],
            ['name' => 'edit_roles', 'slug' => 'edit_roles', 'description' => 'Edit role permissions'],
            ['name' => 'delete_roles', 'slug' => 'delete_roles', 'description' => 'Delete roles'],
            
            // Audit and Logs
            ['name' => 'view_audit_logs', 'slug' => 'view_audit_logs', 'description' => 'View system audit logs'],
            ['name' => 'export_audit_logs', 'slug' => 'export_audit_logs', 'description' => 'Export audit log data'],
            
            // Reports and Analytics
            ['name' => 'view_reports', 'slug' => 'view_reports', 'description' => 'View system reports'],
            ['name' => 'export_reports', 'slug' => 'export_reports', 'description' => 'Export report data'],
            ['name' => 'view_analytics', 'slug' => 'view_analytics', 'description' => 'View system analytics'],
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

        // Create basic roles for advanced user management
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove role-permission assignments
        DB::table('role_permissions')->truncate();
        
        // Remove roles created by this migration
        DB::table('roles')->whereIn('name', [
            'Super Admin', 'Admin', 'Manager', 'Employee'
        ])->delete();
        
        // Remove permissions created by this migration
        $permissionSlugs = [
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'manage_user_roles', 'bulk_actions_users', 'import_export_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'view_audit_logs', 'export_audit_logs',
            'view_reports', 'export_reports', 'view_analytics'
        ];
        
        DB::table('permissions')->whereIn('slug', $permissionSlugs)->delete();
    }

    private function assignPermissionsToRoles(): void
    {
        // Get all permissions and roles
        $permissions = DB::table('permissions')->pluck('id', 'slug');
        $roles = DB::table('roles')->pluck('id', 'name');

        // Super Admin gets all permissions
        $superAdminPermissions = $permissions->values()->toArray();
        $this->assignPermissionsToRole($roles['Super Admin'], $superAdminPermissions);

        // Admin permissions (most permissions)
        $adminPermissions = $permissions->values()->toArray();
        $this->assignPermissionsToRole($roles['Admin'], $adminPermissions);

        // Manager permissions
        $managerPermissions = [
            'view_users', 'create_users', 'edit_users', 'manage_user_roles',
            'bulk_actions_users', 'import_export_users',
            'view_roles',
            'view_reports', 'export_reports', 'view_analytics',
            'view_audit_logs'
        ];
        $this->assignPermissionsToRole($roles['Manager'], 
            $permissions->only($managerPermissions)->values()->toArray());

        // Employee permissions
        $employeePermissions = [
            'view_users'
        ];
        $this->assignPermissionsToRole($roles['Employee'], 
            $permissions->only($employeePermissions)->values()->toArray());
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
};
