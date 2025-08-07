<?php

namespace App\Listeners;

use App\Events\PermissionUpdated;
use App\Services\PermissionService;

class ClearPermissionCache
{
    /**
     * The permission service instance.
     *
     * @var \App\Services\PermissionService
     */
    protected $permissionService;

    /**
     * Create the event listener.
     *
     * @param  \App\Services\PermissionService  $permissionService
     * @return void
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PermissionUpdated  $event
     * @return void
     */
    public function handle(PermissionUpdated $event)
    {
        switch ($event->eventType) {
            case 'course_assignment':
                // Clear cache for all affected users
                foreach ($event->affectedIds as $userId) {
                    $this->permissionService->clearUserPermissionCache($userId);
                }
                break;
                
            case 'group_created':
            case 'group_updated':
                // Clear cache for the group
                $this->permissionService->clearGroupCache($event->resourceId);
                break;
                
            case 'user_updated':
                // Clear cache for the user
                $this->permissionService->clearUserPermissionCache($event->resourceId);
                break;
                
            case 'course_access_updated':
                // Clear cache for the course
                $this->permissionService->clearCourseAccessCache($event->resourceId);
                break;
                
            case 'permission_updated':
                // Clear all permission caches (this is a heavy operation, use sparingly)
                // In a production environment, you might want to be more specific
                if (app()->environment('local', 'staging')) {
                    $this->permissionService->clearAllCaches();
                }
                break;
        }
    }
}
