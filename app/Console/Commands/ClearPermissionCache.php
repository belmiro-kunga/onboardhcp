<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Course;
use App\Models\UserGroup;
use Illuminate\Console\Command;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Cache;

class ClearPermissionCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:clear 
                            {--user= : Clear cache for a specific user}
                            {--group= : Clear cache for a specific group}
                            {--course= : Clear cache for a specific course}
                            {--all : Clear all permission caches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear permission cache for users, groups, or courses';

    /**
     * The permission service instance.
     *
     * @var \App\Services\PermissionService
     */
    protected $permissionService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PermissionService $permissionService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = $this->option('user');
        $groupId = $this->option('group');
        $courseId = $this->option('course');
        $all = $this->option('all');

        if ($all) {
            return $this->clearAllCaches();
        }

        if ($user) {
            return $this->clearUserCache($user);
        }

        if ($groupId) {
            return $this->clearGroupCache($groupId);
        }

        if ($courseId) {
            return $this->clearCourseCache($courseId);
        }

        $this->info('No specific target provided. Use --help to see available options.');
        return 0;
    }

    /**
     * Clear cache for a specific user
     *
     * @param  string  $user
     * @return int
     */
    protected function clearUserCache($user): int
    {
        $user = User::where('id', $user)
            ->orWhere('email', $user)
            ->first();

        if (!$user) {
            $this->error('User not found.');
            return 1;
        }

        $this->permissionService->clearUserPermissionCache($user->id);
        $this->info("Permission cache cleared for user: {$user->name} ({$user->email})");
        
        return 0;
    }

    /**
     * Clear cache for a specific group
     *
     * @param  string  $groupId
     * @return int
     */
    protected function clearGroupCache($groupId): int
    {
        $group = UserGroup::find($groupId);

        if (!$group) {
            $this->error('Group not found.');
            return 1;
        }

        $this->permissionService->clearGroupCache($group->id);
        $this->info("Permission cache cleared for group: {$group->name}");
        
        return 0;
    }

    /**
     * Clear cache for a specific course
     *
     * @param  string  $courseId
     * @return int
     */
    protected function clearCourseCache($courseId): int
    {
        $course = Course::find($courseId);

        if (!$course) {
            $this->error('Course not found.');
            return 1;
        }

        $this->permissionService->clearCourseAccessCache($course->id);
        $this->info("Permission cache cleared for course: {$course->name}");
        
        return 0;
    }

    /**
     * Clear all permission caches
     *
     * @return int
     */
    protected function clearAllCaches(): int
    {
        if (!$this->confirm('This will clear all permission caches. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Clearing all permission caches...');
        
        // Clear user caches
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                $this->permissionService->clearUserPermissionCache($user->id);
            }
            $this->info("Processed users: {$users->count()}");
        });
        
        // Clear group caches
        $groups = UserGroup::all();
        foreach ($groups as $group) {
            $this->permissionService->clearGroupCache($group->id);
        }
        
        // Clear course caches
        $courses = Course::all();
        foreach ($courses as $course) {
            $this->permissionService->clearCourseAccessCache($course->id);
        }
        
        // Clear any remaining permission-related caches
        Cache::tags(['permissions'])->flush();
        
        $this->info('All permission caches have been cleared.');
        return 0;
    }
}
