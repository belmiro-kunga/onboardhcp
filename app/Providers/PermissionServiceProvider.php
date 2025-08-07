<?php

namespace App\Providers;

use App\Services\PermissionService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\PermissionUpdated;
use App\Listeners\ClearPermissionCache;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PermissionService::class, function ($app) {
            return new PermissionService();
        });

        // Register the command to clear permission cache
        $this->commands([
            \App\Console\Commands\ClearPermissionCache::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(
            PermissionUpdated::class,
            ClearPermissionCache::class
        );

        // Register model events to clear cache
        $this->registerModelEvents();
    }

    /**
     * Register model events to clear permission cache
     */
    protected function registerModelEvents(): void
    {
        // When a user is updated, clear their permission cache
        $permissionService = $this->app->make(PermissionService::class);
        
        // User events
        $userModel = config('auth.providers.users.model');
        
        if (class_exists($userModel)) {
            $userModel::saved(function ($user) use ($permissionService) {
                $permissionService->clearUserPermissionCache($user->id);
            });
            
            $userModel::deleted(function ($user) use ($permissionService) {
                $permissionService->clearUserPermissionCache($user->id);
            });
        }
        
        // Group events
        if (class_exists(\App\Models\UserGroup::class)) {
            \App\Models\UserGroup::saved(function ($group) use ($permissionService) {
                $permissionService->clearGroupCache($group->id);
            });
            
            \App\Models\UserGroup::deleted(function ($group) use ($permissionService) {
                $permissionService->clearGroupCache($group->id);
            });
        }
        
        // Permission events
        if (class_exists(\App\Models\Permission::class)) {
            \App\Models\Permission::saved(function ($permission) use ($permissionService) {
                $permission->clearPermissionCache();
            });
        }
        
        // Course access rule events
        if (class_exists(\App\Models\CourseAccessRule::class)) {
            \App\Models\CourseAccessRule::saved(function ($rule) use ($permissionService) {
                $permissionService->clearCourseAccessCache($rule->course_id);
            });
            
            \App\Models\CourseAccessRule::deleted(function ($rule) use ($permissionService) {
                $permissionService->clearCourseAccessCache($rule->course_id);
            });
        }
    }
}
