<?php

namespace App\Observers;

use App\Modules\User\Models\User;
use App\Services\DepartmentGroupService;

class UserObserver
{
    protected DepartmentGroupService $departmentGroupService;

    public function __construct(DepartmentGroupService $departmentGroupService)
    {
        $this->departmentGroupService = $departmentGroupService;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Sync user to department group when created
        if ($user->department) {
            $this->departmentGroupService->syncUserToDepartmentGroup($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if department was changed
        if ($user->isDirty('department')) {
            $oldDepartment = $user->getOriginal('department');
            $this->departmentGroupService->syncUserToDepartmentGroup($user, $oldDepartment);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Remove user from department group when deleted
        if ($user->department) {
            $this->departmentGroupService->syncUserToDepartmentGroup($user, $user->department);
        }
    }
}