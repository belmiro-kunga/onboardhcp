<?php

namespace Database\Seeders;

use App\Models\CourseAccessLevel;
use App\Models\Permission;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        DB::table('group_permission')->truncate();
        DB::table('group_user')->truncate();
        
        Permission::truncate();
        UserGroup::truncate();
        CourseAccessLevel::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create default access levels
        $this->createAccessLevels();
        
        // Create default permissions
        $this->createPermissions();
        
        // Create default user groups
        $this->createUserGroups();
    }

    /**
     * Create default access levels
     */
    protected function createAccessLevels(): void
    {
        $levels = [
            [
                'name' => 'Beginner',
                'slug' => 'beginner',
                'level' => 1,
                'description' => 'Basic level access',
            ],
            [
                'name' => 'Intermediate',
                'slug' => 'intermediate',
                'level' => 2,
                'description' => 'Intermediate level access',
            ],
            [
                'name' => 'Advanced',
                'slug' => 'advanced',
                'level' => 3,
                'description' => 'Advanced level access',
            ],
            [
                'name' => 'Expert',
                'slug' => 'expert',
                'level' => 4,
                'description' => 'Expert level access',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'level' => 100,
                'description' => 'Administrative access',
            ],
        ];

        foreach ($levels as $level) {
            CourseAccessLevel::create($level);
        }
    }

    /**
     * Create default permissions
     */
    protected function createPermissions(): void
    {
        $permissions = [
            // Course permissions
            ['name' => 'View any course', 'slug' => 'view-any-course', 'model_type' => 'App\\Models\\Course'],
            ['name' => 'View course', 'slug' => 'view-course', 'model_type' => 'App\\Models\\Course'],
            ['name' => 'Create course', 'slug' => 'create-course', 'model_type' => 'App\\Models\\Course'],
            ['name' => 'Update course', 'slug' => 'update-course', 'model_type' => 'App\\Models\\Course'],
            ['name' => 'Delete course', 'slug' => 'delete-course', 'model_type' => 'App\\Models\\Course'],
            ['name' => 'Publish course', 'slug' => 'publish-course', 'model_type' => 'App\\Models\\Course'],
            
            // Video permissions
            ['name' => 'View any video', 'slug' => 'view-any-video', 'model_type' => 'App\\Models\\Video'],
            ['name' => 'View video', 'slug' => 'view-video', 'model_type' => 'App\\Models\\Video'],
            ['name' => 'Create video', 'slug' => 'create-video', 'model_type' => 'App\\Models\\Video'],
            ['name' => 'Update video', 'slug' => 'update-video', 'model_type' => 'App\\Models\\Video'],
            ['name' => 'Delete video', 'slug' => 'delete-video', 'model_type' => 'App\\Models\\Video'],
            
            // User management permissions
            ['name' => 'View any user', 'slug' => 'view-any-user', 'model_type' => 'App\\Models\\User'],
            ['name' => 'View user', 'slug' => 'view-user', 'model_type' => 'App\\Models\\User'],
            ['name' => 'Create user', 'slug' => 'create-user', 'model_type' => 'App\\Models\\User'],
            ['name' => 'Update user', 'slug' => 'update-user', 'model_type' => 'App\\Models\\User'],
            ['name' => 'Delete user', 'slug' => 'delete-user', 'model_type' => 'App\\Models\\User'],
            
            // Group management permissions
            ['name' => 'Manage groups', 'slug' => 'manage-groups', 'model_type' => 'App\\Models\\UserGroup'],
            
            // System permissions
            ['name' => 'Access admin panel', 'slug' => 'access-admin', 'model_type' => null],
            ['name' => 'Manage settings', 'slug' => 'manage-settings', 'model_type' => null],
            ['name' => 'View reports', 'slug' => 'view-reports', 'model_type' => null],
            ['name' => 'Manage content', 'slug' => 'manage-content', 'model_type' => null],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }

    /**
     * Create default user groups and assign permissions
     */
    protected function createUserGroups(): void
    {
        // Super Admin group (has all permissions)
        $superAdmin = UserGroup::create([
            'name' => 'Super Administrators',
            'description' => 'Full system access',
            'is_system' => true,
        ]);
        
        // Admin group (most permissions)
        $admin = UserGroup::create([
            'name' => 'Administrators',
            'description' => 'System administrators with most permissions',
            'is_system' => true,
        ]);
        
        // Instructor group (can manage their own courses)
        $instructor = UserGroup::create([
            'name' => 'Instructors',
            'description' => 'Course instructors and content creators',
            'is_system' => true,
        ]);
        
        // Moderator group (can moderate content)
        $moderator = UserGroup::create([
            'name' => 'Moderators',
            'description' => 'Content moderators',
            'is_system' => true,
        ]);
        
        // Premium Users group
        $premium = UserGroup::create([
            'name' => 'Premium Users',
            'description' => 'Users with premium subscription',
            'is_system' => true,
        ]);
        
        // Regular Users group (default for new users)
        $user = UserGroup::create([
            'name' => 'Users',
            'description' => 'Regular users with basic access',
            'is_system' => true,
        ]);
        
        // Guest group (not logged in users)
        $guest = UserGroup::create([
            'name' => 'Guests',
            'description' => 'Not logged in users',
            'is_system' => true,
        ]);

        // Assign permissions to groups
        $this->assignGroupPermissions($superAdmin, Permission::all());
        
        $adminPermissions = Permission::whereNotIn('slug', [
            'delete-user',
            'manage-settings',
        ])->get();
        $this->assignGroupPermissions($admin, $adminPermissions);
        
        $instructorPermissions = Permission::whereIn('slug', [
            'view-any-course', 'view-course', 'create-course', 'update-course',
            'view-any-video', 'view-video', 'create-video', 'update-video',
            'access-admin', 'view-reports',
        ])->get();
        $this->assignGroupPermissions($instructor, $instructorPermissions);
        
        $moderatorPermissions = Permission::whereIn('slug', [
            'view-any-course', 'view-course', 'update-course',
            'view-any-video', 'view-video', 'update-video',
            'access-admin', 'view-reports',
        ])->get();
        $this->assignGroupPermissions($moderator, $moderatorPermissions);
        
        $premiumPermissions = Permission::whereIn('slug', [
            'view-any-course', 'view-course',
            'view-any-video', 'view-video',
        ])->get();
        $this->assignGroupPermissions($premium, $premiumPermissions);
        
        $userPermissions = Permission::whereIn('slug', [
            'view-course', 'view-video',
        ])->get();
        $this->assignGroupPermissions($user, $userPermissions);
        
        $guestPermissions = Permission::whereIn('slug', [
            'view-course', 'view-video',
        ])->get();
        $this->assignGroupPermissions($guest, $guestPermissions);
    }
    
    /**
     * Assign permissions to a group
     */
    protected function assignGroupPermissions(UserGroup $group, $permissions): void
    {
        $group->permissions()->syncWithoutDetaching(
            $permissions->pluck('id')->toArray()
        );
    }
}
