<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\RolePermissionService;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserGroup;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class RolePermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RolePermissionService $rolePermissionService;
    protected User $user;
    protected Role $role;
    protected Permission $permission;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->rolePermissionService = new RolePermissionService();
        
        // Create test data
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'active'
        ]);
        
        $this->role = Role::create([
            'name' => 'Test Role',
            'description' => 'Test role for unit tests',
            'is_system' => false
        ]);
        
        $this->permission = Permission::create([
            'name' => 'Test Permission',
            'slug' => 'test_permission',
            'description' => 'Test permission for unit tests',
            'module' => 'test'
        ]);
    }

    public function test_can_create_role_with_permissions()
    {
        $role = $this->rolePermissionService->createRole(
            'New Test Role',
            'Description for new role',
            ['test_permission']
        );

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('New Test Role', $role->name);
        $this->assertEquals('Description for new role', $role->description);
        $this->assertTrue($role->hasPermission('test_permission'));
    }

    public function test_can_assign_role_to_user()
    {
        $this->rolePermissionService->assignRoleToUser($this->user, $this->role->name);
        
        $this->assertTrue($this->user->hasRole($this->role->name));
    }

    public function test_can_remove_role_from_user()
    {
        // First assign the role
        $this->rolePermissionService->assignRoleToUser($this->user, $this->role->name);
        $this->assertTrue($this->user->hasRole($this->role->name));
        
        // Then remove it
        $this->rolePermissionService->removeRoleFromUser($this->user, $this->role->name);
        $this->assertFalse($this->user->fresh()->hasRole($this->role->name));
    }

    public function test_can_sync_user_roles()
    {
        $role2 = Role::create([
            'name' => 'Second Role',
            'description' => 'Second test role',
            'is_system' => false
        ]);

        $this->rolePermissionService->syncUserRoles($this->user, [$this->role->name, $role2->name]);
        
        $this->assertTrue($this->user->hasRole($this->role->name));
        $this->assertTrue($this->user->hasRole($role2->name));
        $this->assertEquals(2, $this->user->roles()->count());
    }

    public function test_can_check_user_permission_via_role()
    {
        // Assign permission to role
        $this->role->permissions()->attach($this->permission->id);
        
        // Assign role to user
        $this->rolePermissionService->assignRoleToUser($this->user, $this->role->name);
        
        // Check permission
        $this->assertTrue($this->rolePermissionService->userHasPermission($this->user, 'test_permission'));
    }

    public function test_can_check_user_permission_via_group()
    {
        $group = UserGroup::create([
            'name' => 'Test Group',
            'description' => 'Test group',
            'type' => 'custom'
        ]);
        
        // Assign permission to group
        $group->permissions()->attach($this->permission->id);
        
        // Add user to group
        $this->rolePermissionService->addUsersToGroup($group, [$this->user->id]);
        
        // Check permission
        $this->assertTrue($this->rolePermissionService->userHasPermission($this->user, 'test_permission'));
    }

    public function test_can_check_multiple_permissions()
    {
        $permission2 = Permission::create([
            'name' => 'Second Permission',
            'slug' => 'second_permission',
            'description' => 'Second test permission',
            'module' => 'test'
        ]);

        // Assign permissions to role
        $this->role->permissions()->attach([$this->permission->id, $permission2->id]);
        
        // Assign role to user
        $this->rolePermissionService->assignRoleToUser($this->user, $this->role->name);
        
        // Test hasAnyPermission
        $this->assertTrue($this->rolePermissionService->userHasAnyPermission($this->user, ['test_permission', 'nonexistent_permission']));
        
        // Test hasAllPermissions
        $this->assertTrue($this->rolePermissionService->userHasAllPermissions($this->user, ['test_permission', 'second_permission']));
        $this->assertFalse($this->rolePermissionService->userHasAllPermissions($this->user, ['test_permission', 'nonexistent_permission']));
    }

    public function test_cannot_delete_system_role()
    {
        $systemRole = Role::create([
            'name' => 'System Role',
            'description' => 'System role that cannot be deleted',
            'is_system' => true
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot delete system roles');
        
        $this->rolePermissionService->deleteRole($systemRole);
    }

    public function test_can_get_user_permissions_with_sources()
    {
        $group = UserGroup::create([
            'name' => 'Test Group',
            'description' => 'Test group',
            'type' => 'custom'
        ]);
        
        $permission2 = Permission::create([
            'name' => 'Group Permission',
            'slug' => 'group_permission',
            'description' => 'Permission from group',
            'module' => 'test'
        ]);

        // Assign permission to role and group
        $this->role->permissions()->attach($this->permission->id);
        $group->permissions()->attach($permission2->id);
        
        // Assign role to user and add user to group
        $this->rolePermissionService->assignRoleToUser($this->user, $this->role->name);
        $this->rolePermissionService->addUsersToGroup($group, [$this->user->id]);
        
        $permissionsWithSources = $this->rolePermissionService->getUserPermissionsWithSources($this->user);
        
        $this->assertArrayHasKey('test_permission', $permissionsWithSources);
        $this->assertArrayHasKey('group_permission', $permissionsWithSources);
        
        $this->assertEquals('role', $permissionsWithSources['test_permission']['sources'][0]['type']);
        $this->assertEquals('group', $permissionsWithSources['group_permission']['sources'][0]['type']);
    }

    public function test_cache_is_cleared_when_permissions_change()
    {
        // Mock cache to verify it's being cleared
        Cache::shouldReceive('remember')->andReturn(true);
        Cache::shouldReceive('flush')->once();
        
        $this->rolePermissionService->assignRoleToUser($this->user, $this->role->name);
    }

    public function test_role_validation()
    {
        // Test assigning existing role
        $this->rolePermissionService->assignRoleToUser($this->user, $this->role->name);
        $errors = $this->rolePermissionService->validateRoleAssignment($this->user, $this->role->name);
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('already has role', $errors[0]);
    }

    public function test_can_create_user_group()
    {
        $group = $this->rolePermissionService->createUserGroup(
            'Test Department Group',
            'Group for test department',
            'department'
        );

        $this->assertInstanceOf(UserGroup::class, $group);
        $this->assertEquals('Test Department Group', $group->name);
        $this->assertEquals('department', $group->type);
    }

    public function test_can_manage_group_permissions()
    {
        $group = $this->rolePermissionService->createUserGroup('Test Group');
        
        // Assign permission to group
        $this->rolePermissionService->assignPermissionsToGroup($group, ['test_permission']);
        
        $this->assertTrue($group->fresh()->permissions->contains('slug', 'test_permission'));
        
        // Remove permission from group
        $this->rolePermissionService->removePermissionsFromGroup($group, ['test_permission']);
        
        $this->assertFalse($group->fresh()->permissions->contains('slug', 'test_permission'));
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}