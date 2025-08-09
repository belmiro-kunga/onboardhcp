<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Modules\User\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class UserSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for authentication
        $this->adminUser = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@test.com'
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_search()
    {
        $response = $this->getJson('/admin/users/search');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_admin_privileges_for_search()
    {
        $regularUser = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($regularUser)
                        ->getJson('/admin/users/search');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_perform_basic_search()
    {
        User::factory()->create(['name' => 'João Silva', 'email' => 'joao@test.com']);
        User::factory()->create(['name' => 'Maria Santos', 'email' => 'maria@test.com']);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?search=João');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'pagination' => [
                            'total' => 1
                        ]
                    ]
                ]);

        $users = $response->json('data.users');
        $this->assertCount(1, $users);
        $this->assertEquals('João Silva', $users[0]['name']);
    }

    /** @test */
    public function it_can_filter_by_status()
    {
        User::factory()->create(['name' => 'Active User', 'status' => 'active']);
        User::factory()->create(['name' => 'Inactive User', 'status' => 'inactive']);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?status[]=active');

        $response->assertStatus(200);
        
        $users = $response->json('data.users');
        $this->assertCount(1, $users);
        $this->assertEquals('Active User', $users[0]['name']);
    }

    /** @test */
    public function it_can_filter_by_user_type()
    {
        User::factory()->create(['name' => 'Another Admin', 'is_admin' => true]);
        User::factory()->create(['name' => 'Regular User', 'is_admin' => false]);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?user_type=admin');

        $response->assertStatus(200);
        
        $users = $response->json('data.users');
        // Should include the test admin user and the created admin user
        $this->assertGreaterThanOrEqual(2, count($users));
        
        foreach ($users as $user) {
            $this->assertTrue($user['is_admin']);
        }
    }

    /** @test */
    public function it_can_sort_results()
    {
        User::factory()->create(['name' => 'Zé Silva']);
        User::factory()->create(['name' => 'Ana Costa']);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        
        $users = $response->json('data.users');
        $names = array_column($users, 'name');
        
        // Should be sorted alphabetically
        $this->assertEquals('Ana Costa', $names[1]); // First after admin user
    }

    /** @test */
    public function it_validates_search_parameters()
    {
        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?search=a'); // Too short

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['search']);
    }

    /** @test */
    public function it_validates_status_filter()
    {
        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?status[]=invalid_status');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status.0']);
    }

    /** @test */
    public function it_validates_sort_parameters()
    {
        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?sort_by=invalid_field');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['sort_by']);
    }

    /** @test */
    public function it_validates_pagination_parameters()
    {
        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?per_page=1000'); // Too high

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function it_can_get_filter_options()
    {
        // Create users with different departments and positions
        User::factory()->create(['department' => 'IT', 'position' => 'Developer']);
        User::factory()->create(['department' => 'HR', 'position' => 'Manager']);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search/filter-options');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'departments',
                    'positions',
                    'roles',
                    'status_options',
                    'activity_options',
                    'sort_options'
                ]);

        $data = $response->json();
        $this->assertContains('IT', $data['departments']);
        $this->assertContains('HR', $data['departments']);
        $this->assertContains('Developer', $data['positions']);
        $this->assertContains('Manager', $data['positions']);
    }

    /** @test */
    public function it_can_get_search_suggestions()
    {
        User::factory()->create(['name' => 'João Silva', 'email' => 'joao@test.com']);
        User::factory()->create(['name' => 'João Santos', 'email' => 'joao.santos@test.com']);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search/suggestions?q=João');

        $response->assertStatus(200);
        
        $suggestions = $response->json();
        $this->assertGreaterThan(0, count($suggestions));
    }

    /** @test */
    public function it_requires_minimum_characters_for_suggestions()
    {
        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search/suggestions?q=a');

        $response->assertStatus(200);
        $suggestions = $response->json();
        $this->assertEmpty($suggestions);
    }

    /** @test */
    public function it_can_save_search_configuration()
    {
        $searchConfig = [
            'name' => 'My Active IT Users',
            'filters' => [
                'status' => ['active'],
                'department' => ['IT']
            ]
        ];

        $response = $this->actingAs($this->adminUser)
                        ->postJson('/admin/users/search/save-config', $searchConfig);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Configuração de pesquisa guardada com sucesso!'
                ]);
    }

    /** @test */
    public function it_validates_save_search_configuration()
    {
        $response = $this->actingAs($this->adminUser)
                        ->postJson('/admin/users/search/save-config', [
                            // Missing required fields
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'filters']);
    }

    /** @test */
    public function it_can_get_saved_configurations()
    {
        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search/saved-configs');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => []
                ]);
    }

    /** @test */
    public function it_can_export_search_results()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search/export?export=true&export_format=csv');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'format' => 'csv'
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'export_id',
                    'format',
                    'filters_applied'
                ]);
    }

    /** @test */
    public function it_returns_statistics_with_search_results()
    {
        User::factory()->count(5)->create(['status' => 'active']);
        User::factory()->count(3)->create(['status' => 'inactive']);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'users',
                        'pagination',
                        'statistics' => [
                            'total_users',
                            'filtered_count',
                            'active_users',
                            'inactive_users',
                            'admin_users',
                            'employee_users'
                        ],
                        'filters_applied'
                    ]
                ]);

        $statistics = $response->json('data.statistics');
        $this->assertGreaterThan(0, $statistics['total_users']);
    }

    /** @test */
    public function it_handles_search_errors_gracefully()
    {
        // Mock a service error by using invalid database connection
        config(['database.default' => 'invalid']);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search');

        $response->assertStatus(500)
                ->assertJson([
                    'success' => false
                ])
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);
    }

    /** @test */
    public function it_can_combine_multiple_filters()
    {
        User::factory()->create([
            'name' => 'João IT Admin',
            'department' => 'IT',
            'is_admin' => true,
            'status' => 'active'
        ]);
        
        User::factory()->create([
            'name' => 'Maria HR User',
            'department' => 'HR',
            'is_admin' => false,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->adminUser)
                        ->getJson('/admin/users/search?' . http_build_query([
                            'search' => 'João',
                            'department' => ['IT'],
                            'user_type' => 'admin',
                            'status' => ['active']
                        ]));

        $response->assertStatus(200);
        
        $users = $response->json('data.users');
        $this->assertCount(1, $users);
        $this->assertEquals('João IT Admin', $users[0]['name']);
    }
}