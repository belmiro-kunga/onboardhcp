<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserGroup;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class RolePermissionIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'status' => 'active'
        ]);
        
        // Create regular user
        $this->user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'status' => 'active'
        ]);
        
        // Create basic permissions
        Permission::create(['name' => 'View Roles', 'slug' => 'view_roles', 'module' => 'roles']);
        Permission::create(['name' => 'Create Roles', 'slug' => 'create_roles', 'module' => 'roles']);
        Permission::create(['name' => 'Edit Roles', 'slug' => 'edit_roles', 'module' => 'roles']);
        Permission::create(['name' => 'Delete Roles', 'slug' => 'delete_roles', 'module' => 'roles']);
        
        // Create admin role with all permissions
        $adminRole = Role::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
            'is_system' => true
        ]);
        
        $adminRole->permissions()->attach(Permission::all()->pluck('id'));
        $this->admin->roles()->attach($adminRole->id);
    }

    public function test_admin_can_access_roles_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.roles.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.roles.index');
        $response->assertViewHas('roles');
    }

    public function test_regular_user_cannot_access_roles_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_role()
    {
        $roleData = [
            'name' => 'Test Role',
            'description' => 'Test role description',
            'permissions' => ['view_roles', 'create_roles']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.roles.store'), $roleData);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'name' => 'Test Role',
            'description' => 'Test role description'
        ]);

        $role = Role::where('name', 'Test Role')->first();
        $this->assertTrue($role->hasPermission('view_roles'));
        $this->assertTrue($role->hasPermission('create_roles'));
    }

    public function test_admin_can_edit_role()
    {
        $role = Role::create([
            'name' => 'Editable Role',
            'description' => 'Role that can be edited',
            'is_system' => false
        ]);

        $updateData = [
            'name' => 'Updated Role',
            'description' => 'Updated description',
            'permissions' => ['view_roles']
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $role), $updateData);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertEquals('Updated Role', $role->name);
        $this->assertEquals('Updated description', $role->description);
    }

    public function test_cannot_edit_system_role_without_super_admin()
    {
        $systemRole = Role::create([
            'name' => 'System Role',
            'description' => 'System role',
            'is_system' => true
        ]);

        $updateData = [
            'name' => 'Hacked Role',
            'description' => 'Trying to hack system role'
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $systemRole), $updateData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        $systemRole->refresh();
        $this->assertEquals('System Role', $systemRole->name);
    }

    public function test_admin_can_delete_custom_role()
    {
        $role = Role::create([
            'name' => 'Deletable Role',
            'description' => 'Role that can be deleted',
            'is_system' => false
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id
        ]);
    }

    public function test_cannot_delete_system_role()
    {
        $systemRole = Role::create([
            'name' => 'System Role',
            'description' => 'System role',
            'is_system' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.roles.destroy', $systemRole));

        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('roles', [
            'id' => $systemRole->id
        ]);
    }

    public function test_admin_can_assign_role_to_user()
    {
        $role = Role::create([
            'name' => 'Assignable Role',
            'description' => 'Role that can be assigned',
            'is_system' => false
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.user-roles.store', $this->user), [
                'role' => $role->name
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertTrue($this->user->fresh()->hasRole($role->name));
    }

    public function test_admin_can_remove_role_from_user()
    {
        $role = Role::create([
            'name' => 'Removable Role',
            'description' => 'Role that can be removed',
            'is_system' => false
        ]);

        // First assign the role
        $this->user->roles()->attach($role->id);
        $this->assertTrue($this->user->hasRole($role->name));

        // Then remove it
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.user-roles.destroy', [$this->user, $role]));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertFalse($this->user->fresh()->hasRole($role->name));
    }

    public function test_admin_can_view_user_permissions()
    {
        $role = Role::create([
            'name' => 'Test Role',
            'description' => 'Test role',
            'is_system' => false
        ]);

        $permission = Permission::first();
        $role->permissions()->attach($permission->id);
        $this->user->roles()->attach($role->id);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.user-roles.permissions.view', $this->user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.permissions');
        $response->assertViewHas('user');
        $response->assertViewHas('permissionsWithSources');
    }

    public function test_admin_can_manage_groups()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.groups.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.groups.index');
    }

    public function test_admin_can_create_group()
    {
        $groupData = [
            'name' => 'Test Group',
            'description' => 'Test group description',
            'type' => 'custom',
            'permissions' => ['view_roles'],
            'users' => [$this->user->id]
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.groups.store'), $groupData);

        $response->assertRedirect(route('admin.groups.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_groups', [
            'name' => 'Test Group',
            'type' => 'custom'
        ]);

        $group = UserGroup::where('name', 'Test Group')->first();
        $this->assertTrue($group->users->contains($this->user));
        $this->assertTrue($group->permissions->contains('slug', 'view_roles'));
    }

    public function test_bulk_role_assignment()
    {
        $role = Role::create([
            'name' => 'Bulk Role',
            'description' => 'Role for bulk assignment',
            'is_system' => false
        ]);

        $user2 = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.bulk-roles.assign'), [
                'users' => [$this->user->id, $user2->id],
                'roles' => [$role->name]
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertTrue($this->user->fresh()->hasRole($role->name));
        $this->assertTrue($user2->fresh()->hasRole($role->name));
    }

    public function test_middleware_protects_routes()
    {
        // Test that routes are protected by permission middleware
        $protectedRoutes = [
            ['GET', route('admin.roles.create')],
            ['POST', route('admin.roles.store')],
            ['DELETE', route('admin.roles.destroy', 1)],
        ];

        foreach ($protectedRoutes as [$method, $route]) {
            $response = $this->actingAs($this->user)
                ->call($method, $route);

            $this->assertContains($response->getStatusCode(), [403, 404, 405]);
        }
    }

    public function test_role_permission_inheritance()
    {
        // Create role with permission
        $role = Role::create(['name' => 'Test Role', 'is_system' => false]);
        $permission = Permission::first();
        $role->permissions()->attach($permission->id);

        // Create group with different permission
        $group = UserGroup::create(['name' => 'Test Group', 'type' => 'custom']);
        $permission2 = Permission::create(['name' => 'Group Permission', 'slug' => 'group_permission', 'module' => 'test']);
        $group->permissions()->attach($permission2->id);

        // Assign role and add to group
        $this->user->roles()->attach($role->id);
        $this->user->groups()->attach($group->id);

        // User should have both permissions
        $this->assertTrue($this->user->can($permission->slug));
        $this->assertTrue($this->user->can($permission2->slug));
    }
}