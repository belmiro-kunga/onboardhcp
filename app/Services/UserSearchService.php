<?php

namespace App\Services;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserSearchService
{
    protected const CACHE_TTL = 300; // 5 minutes
    protected const DEFAULT_PER_PAGE = 25;
    protected const MAX_PER_PAGE = 100;

    /**
     * Perform advanced search with multiple filters
     */
    public function search(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()->with(['roles']);
        
        // Apply search filters
        $this->applySearchFilter($query, $filters);
        $this->applyStatusFilter($query, $filters);
        $this->applyRoleFilter($query, $filters);
        $this->applyDepartmentFilter($query, $filters);
        $this->applyDateRangeFilter($query, $filters);
        $this->applyActivityFilter($query, $filters);
        
        // Apply sorting
        $this->applySorting($query, $filters);
        
        // Get pagination settings
        $perPage = $this->getPerPage($filters);
        
        // Cache key for this specific search
        $cacheKey = $this->generateCacheKey($filters, $perPage);
        
        // Try to get from cache first for frequently used searches
        if ($this->shouldCache($filters)) {
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $perPage) {
                return $query->paginate($perPage);
            });
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Get search results count without pagination
     */
    public function getSearchCount(array $filters = []): int
    {
        $query = User::query();
        
        $this->applySearchFilter($query, $filters);
        $this->applyStatusFilter($query, $filters);
        $this->applyRoleFilter($query, $filters);
        $this->applyDepartmentFilter($query, $filters);
        $this->applyDateRangeFilter($query, $filters);
        $this->applyActivityFilter($query, $filters);
        
        return $query->count();
    }

    /**
     * Apply text search filter
     */
    protected function applySearchFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhere('department', 'like', "%{$searchTerm}%")
                  ->orWhere('position', 'like', "%{$searchTerm}%");
            });
        }
    }

    /**
     * Apply status filter
     */
    protected function applyStatusFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }
    }

    /**
     * Apply role filter
     */
    protected function applyRoleFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['role'])) {
            if (is_array($filters['role'])) {
                $query->whereHas('roles', function ($q) use ($filters) {
                    $q->whereIn('name', $filters['role']);
                });
            } else {
                $query->whereHas('roles', function ($q) use ($filters) {
                    $q->where('name', $filters['role']);
                });
            }
        }
        
        // Special filter for admin/employee
        if (!empty($filters['user_type'])) {
            if ($filters['user_type'] === 'admin') {
                $query->where('is_admin', true);
            } elseif ($filters['user_type'] === 'employee') {
                $query->where('is_admin', false);
            }
        }
    }

    /**
     * Apply department filter
     */
    protected function applyDepartmentFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['department'])) {
            if (is_array($filters['department'])) {
                $query->whereIn('department', $filters['department']);
            } else {
                $query->where('department', $filters['department']);
            }
        }
    }

    /**
     * Apply date range filter
     */
    protected function applyDateRangeFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }
        
        if (!empty($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }
        
        if (!empty($filters['hire_date_from'])) {
            $query->where('hire_date', '>=', $filters['hire_date_from']);
        }
        
        if (!empty($filters['hire_date_to'])) {
            $query->where('hire_date', '<=', $filters['hire_date_to']);
        }
    }

    /**
     * Apply activity filter
     */
    protected function applyActivityFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['activity'])) {
            switch ($filters['activity']) {
                case 'active':
                    $query->where('last_login_at', '>=', now()->subDays(30));
                    break;
                case 'inactive':
                    $query->where(function ($q) {
                        $q->where('last_login_at', '<', now()->subDays(30))
                          ->orWhereNull('last_login_at');
                    });
                    break;
                case 'recent':
                    $query->where('last_login_at', '>=', now()->subDays(7));
                    break;
                case 'never_logged':
                    $query->whereNull('last_login_at');
                    break;
            }
        }
    }

    /**
     * Apply sorting
     */
    protected function applySorting(Builder $query, array $filters): void
    {
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        // Validate sort direction
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) 
            ? strtolower($sortDirection) 
            : 'desc';
        
        // Apply sorting based on field
        switch ($sortField) {
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'email':
                $query->orderBy('email', $sortDirection);
                break;
            case 'status':
                $query->orderBy('status', $sortDirection);
                break;
            case 'department':
                $query->orderBy('department', $sortDirection);
                break;
            case 'position':
                $query->orderBy('position', $sortDirection);
                break;
            case 'hire_date':
                $query->orderBy('hire_date', $sortDirection);
                break;
            case 'last_login':
                $query->orderBy('last_login_at', $sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }
        
        // Secondary sort by name for consistency
        if ($sortField !== 'name') {
            $query->orderBy('name', 'asc');
        }
    }

    /**
     * Get per page value with validation
     */
    protected function getPerPage(array $filters): int
    {
        $perPage = $filters['per_page'] ?? self::DEFAULT_PER_PAGE;
        
        // Validate and constrain per_page value
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));
        
        return $perPage;
    }

    /**
     * Generate cache key for search
     */
    protected function generateCacheKey(array $filters, int $perPage): string
    {
        $keyData = array_merge($filters, ['per_page' => $perPage]);
        ksort($keyData);
        
        return 'user_search_' . md5(serialize($keyData));
    }

    /**
     * Determine if search should be cached
     */
    protected function shouldCache(array $filters): bool
    {
        // Don't cache if search term is present (too many variations)
        if (!empty($filters['search'])) {
            return false;
        }
        
        // Cache simple filter combinations
        return true;
    }

    /**
     * Get available departments for filtering
     */
    public function getAvailableDepartments(): array
    {
        return Cache::remember('user_departments', 3600, function () {
            return User::whereNotNull('department')
                      ->distinct()
                      ->pluck('department')
                      ->filter()
                      ->sort()
                      ->values()
                      ->toArray();
        });
    }

    /**
     * Get available positions for filtering
     */
    public function getAvailablePositions(): array
    {
        return Cache::remember('user_positions', 3600, function () {
            return User::whereNotNull('position')
                      ->distinct()
                      ->pluck('position')
                      ->filter()
                      ->sort()
                      ->values()
                      ->toArray();
        });
    }

    /**
     * Get available roles for filtering
     */
    public function getAvailableRoles(): array
    {
        return Cache::remember('user_roles', 3600, function () {
            return DB::table('roles')
                     ->orderBy('name')
                     ->pluck('name')
                     ->toArray();
        });
    }

    /**
     * Get search statistics
     */
    public function getSearchStatistics(array $filters = []): array
    {
        $baseQuery = User::query();
        
        return [
            'total_users' => User::count(),
            'filtered_count' => $this->getSearchCount($filters),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'inactive_users' => User::where(function ($q) {
                $q->where('last_login_at', '<', now()->subDays(30))
                  ->orWhereNull('last_login_at');
            })->count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'employee_users' => User::where('is_admin', false)->count(),
        ];
    }

    /**
     * Save search configuration for user
     */
    public function saveSearchConfiguration(int $userId, string $name, array $filters): bool
    {
        // This would typically save to a user_search_configs table
        // For now, we'll use cache with user-specific key
        $cacheKey = "user_search_config_{$userId}_{$name}";
        
        return Cache::put($cacheKey, $filters, now()->addDays(30));
    }

    /**
     * Get saved search configurations for user
     */
    public function getSavedSearchConfigurations(int $userId): array
    {
        // This would typically retrieve from a user_search_configs table
        // For now, we'll return empty array as this requires database table
        return [];
    }

    /**
     * Clear search cache
     */
    public function clearSearchCache(): void
    {
        Cache::forget('user_departments');
        Cache::forget('user_positions');
        Cache::forget('user_roles');
        
        // Clear search result caches (this is a simplified approach)
        // In production, you might want to use cache tags
    }
}