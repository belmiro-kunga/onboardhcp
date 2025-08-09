<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserSearchService;
use App\Modules\User\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class UserSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserSearchService $userSearchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userSearchService = new UserSearchService();
    }

    /** @test */
    public function it_can_search_users_by_name()
    {
        // Create test users
        User::factory()->create(['name' => 'João Silva', 'email' => 'joao@test.com']);
        User::factory()->create(['name' => 'Maria Santos', 'email' => 'maria@test.com']);
        User::factory()->create(['name' => 'Pedro Costa', 'email' => 'pedro@test.com']);

        $results = $this->userSearchService->search(['search' => 'João']);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('João Silva', $results->first()->name);
    }

    /** @test */
    public function it_can_search_users_by_email()
    {
        User::factory()->create(['name' => 'João Silva', 'email' => 'joao@test.com']);
        User::factory()->create(['name' => 'Maria Santos', 'email' => 'maria@test.com']);

        $results = $this->userSearchService->search(['search' => 'maria@test.com']);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('Maria Santos', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_users_by_status()
    {
        User::factory()->create(['name' => 'Active User', 'status' => 'active']);
        User::factory()->create(['name' => 'Inactive User', 'status' => 'inactive']);

        $results = $this->userSearchService->search(['status' => ['active']]);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('Active User', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_users_by_user_type()
    {
        User::factory()->create(['name' => 'Admin User', 'is_admin' => true]);
        User::factory()->create(['name' => 'Regular User', 'is_admin' => false]);

        $results = $this->userSearchService->search(['user_type' => 'admin']);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('Admin User', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_users_by_department()
    {
        User::factory()->create(['name' => 'IT User', 'department' => 'IT']);
        User::factory()->create(['name' => 'HR User', 'department' => 'HR']);

        $results = $this->userSearchService->search(['department' => ['IT']]);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('IT User', $results->first()->name);
    }

    /** @test */
    public function it_can_sort_users_by_name()
    {
        User::factory()->create(['name' => 'Zé Silva']);
        User::factory()->create(['name' => 'Ana Costa']);
        User::factory()->create(['name' => 'Bruno Santos']);

        $results = $this->userSearchService->search([
            'sort_by' => 'name',
            'sort_direction' => 'asc'
        ]);

        $names = $results->pluck('name')->toArray();
        $this->assertEquals(['Ana Costa', 'Bruno Santos', 'Zé Silva'], $names);
    }

    /** @test */
    public function it_can_sort_users_by_created_date()
    {
        $user1 = User::factory()->create(['name' => 'First User']);
        $user2 = User::factory()->create(['name' => 'Second User']);
        $user3 = User::factory()->create(['name' => 'Third User']);

        // Update created_at to ensure order
        $user1->update(['created_at' => now()->subDays(3)]);
        $user2->update(['created_at' => now()->subDays(2)]);
        $user3->update(['created_at' => now()->subDays(1)]);

        $results = $this->userSearchService->search([
            'sort_by' => 'created_at',
            'sort_direction' => 'desc'
        ]);

        $names = $results->pluck('name')->toArray();
        $this->assertEquals(['Third User', 'Second User', 'First User'], $names);
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $user1 = User::factory()->create(['name' => 'Old User']);
        $user2 = User::factory()->create(['name' => 'Recent User']);

        $user1->update(['created_at' => now()->subDays(10)]);
        $user2->update(['created_at' => now()->subDays(2)]);

        $results = $this->userSearchService->search([
            'created_from' => now()->subDays(5)->format('Y-m-d')
        ]);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('Recent User', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_by_activity()
    {
        $activeUser = User::factory()->create([
            'name' => 'Active User',
            'last_login_at' => now()->subDays(5)
        ]);
        
        $inactiveUser = User::factory()->create([
            'name' => 'Inactive User',
            'last_login_at' => now()->subDays(40)
        ]);

        $results = $this->userSearchService->search(['activity' => 'active']);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('Active User', $results->first()->name);
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

        $results = $this->userSearchService->search([
            'search' => 'João',
            'department' => ['IT'],
            'user_type' => 'admin',
            'status' => ['active']
        ]);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('João IT Admin', $results->first()->name);
    }

    /** @test */
    public function it_returns_correct_search_count()
    {
        User::factory()->count(5)->create(['status' => 'active']);
        User::factory()->count(3)->create(['status' => 'inactive']);

        $count = $this->userSearchService->getSearchCount(['status' => ['active']]);

        $this->assertEquals(5, $count);
    }

    /** @test */
    public function it_can_get_available_departments()
    {
        User::factory()->create(['department' => 'IT']);
        User::factory()->create(['department' => 'HR']);
        User::factory()->create(['department' => 'Finance']);
        User::factory()->create(['department' => null]); // Should be excluded

        $departments = $this->userSearchService->getAvailableDepartments();

        $this->assertCount(3, $departments);
        $this->assertContains('IT', $departments);
        $this->assertContains('HR', $departments);
        $this->assertContains('Finance', $departments);
    }

    /** @test */
    public function it_can_get_available_positions()
    {
        User::factory()->create(['position' => 'Developer']);
        User::factory()->create(['position' => 'Manager']);
        User::factory()->create(['position' => 'Analyst']);
        User::factory()->create(['position' => null]); // Should be excluded

        $positions = $this->userSearchService->getAvailablePositions();

        $this->assertCount(3, $positions);
        $this->assertContains('Developer', $positions);
        $this->assertContains('Manager', $positions);
        $this->assertContains('Analyst', $positions);
    }

    /** @test */
    public function it_can_get_search_statistics()
    {
        User::factory()->count(10)->create(['status' => 'active']);
        User::factory()->count(5)->create(['status' => 'inactive']);
        User::factory()->count(3)->create(['is_admin' => true]);

        $statistics = $this->userSearchService->getSearchStatistics();

        $this->assertEquals(15, $statistics['total_users']);
        $this->assertEquals(10, $statistics['filtered_count']); // No filters applied, so should match total
        $this->assertEquals(3, $statistics['admin_users']);
        $this->assertEquals(12, $statistics['employee_users']);
    }

    /** @test */
    public function it_respects_pagination_limits()
    {
        User::factory()->count(50)->create();

        $results = $this->userSearchService->search(['per_page' => 10]);

        $this->assertEquals(10, $results->count());
        $this->assertEquals(50, $results->total());
        $this->assertEquals(5, $results->lastPage());
    }

    /** @test */
    public function it_can_save_search_configuration()
    {
        $userId = 1;
        $configName = 'My Search';
        $filters = ['status' => ['active'], 'department' => ['IT']];

        $result = $this->userSearchService->saveSearchConfiguration($userId, $configName, $filters);

        $this->assertTrue($result);
        
        // Verify it was cached
        $cacheKey = "user_search_config_{$userId}_{$configName}";
        $this->assertEquals($filters, Cache::get($cacheKey));
    }

    /** @test */
    public function it_can_clear_search_cache()
    {
        // Set some cache values
        Cache::put('user_departments', ['IT', 'HR'], 3600);
        Cache::put('user_positions', ['Developer', 'Manager'], 3600);
        Cache::put('user_roles', ['Admin', 'User'], 3600);

        $this->userSearchService->clearSearchCache();

        // Verify cache was cleared
        $this->assertNull(Cache::get('user_departments'));
        $this->assertNull(Cache::get('user_positions'));
        $this->assertNull(Cache::get('user_roles'));
    }
}