<?php

namespace App\Http\Controllers;

use App\Models\UserGroup;
use App\Models\Permission;
use App\Modules\User\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class UserGroupController extends Controller
{
    protected RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
        
        // Apply permission middleware
        $this->middleware('permission:view_users')->only(['index', 'show']);
        $this->middleware('permission:create_users')->only(['create', 'store']);
        $this->middleware('permission:edit_users')->only(['edit', 'update']);
        $this->middleware('permission:delete_users')->only(['destroy']);
    }

    /**
     * Display a listing of user groups
     */
    public function index(Request $request): View|JsonResponse
    {
        $groups = UserGroup::with(['users', 'permissions'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('name')
            ->paginate(25);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $groups
            ]);
        }

        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new group
     */
    public function create(): View
    {
        $permissions = Permission::all()->groupBy('module');
        $users = User::select('id', 'name', 'email', 'department')->get();
        
        return view('admin.groups.create', compact('permissions', 'users'));
    }

    /**
     * Store a newly created group
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:user_groups,name',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:department,team,custom',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,slug',
            'users' => 'array',
            'users.*' => 'exists:users,id'
        ]);

        try {
            $group = $this->rolePermissionService->createUserGroup(
                $validated['name'],
                $validated['description'] ?? null,
                $validated['type']
            );

            // Assign permissions if provided
            if (!empty($validated['permissions'])) {
                $this->rolePermissionService->assignPermissionsToGroup($group, $validated['permissions']);
            }

            // Add users if provided
            if (!empty($validated['users'])) {
                $this->rolePermissionService->addUsersToGroup($group, $validated['users']);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Group created successfully',
                    'data' => $group->load(['permissions', 'users'])
                ], 201);
            }

            return redirect()->route('admin.groups.index')
                ->with('success', 'Group created successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create group: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to create group: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified group
     */
    public function show(UserGroup $group): View|JsonResponse
    {
        $group->load(['permissions', 'users']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $group
            ]);
        }

        return view('admin.groups.show', compact('group'));
    }

    /**
     * Show the form for editing the specified group
     */
    public function edit(UserGroup $group): View
    {
        $group->load(['permissions', 'users']);
        $permissions = Permission::all()->groupBy('module');
        $users = User::select('id', 'name', 'email', 'department')->get();
        
        return view('admin.groups.edit', compact('group', 'permissions', 'users'));
    }

    /**
     * Update the specified group
     */
    public function update(Request $request, UserGroup $group): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('user_groups')->ignore($group->id)],
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:department,team,custom',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,slug',
            'users' => 'array',
            'users.*' => 'exists:users,id'
        ]);

        try {
            $group->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'type' => $validated['type']
            ]);

            // Sync permissions if provided
            if (isset($validated['permissions'])) {
                $permissions = Permission::whereIn('slug', $validated['permissions'])->get();
                $group->permissions()->sync($permissions->pluck('id'));
            }

            // Sync users if provided
            if (isset($validated['users'])) {
                $group->users()->sync($validated['users']);
                
                // Clear permission cache for affected users
                foreach ($validated['users'] as $userId) {
                    $this->rolePermissionService->clearUserPermissionCache($userId);
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Group updated successfully',
                    'data' => $group->fresh()->load(['permissions', 'users'])
                ]);
            }

            return redirect()->route('admin.groups.index')
                ->with('success', 'Group updated successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update group: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to update group: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified group
     */
    public function destroy(UserGroup $group): RedirectResponse|JsonResponse
    {
        try {
            // Clear permission cache for all users in this group
            $userIds = $group->users()->pluck('users.id');
            foreach ($userIds as $userId) {
                $this->rolePermissionService->clearUserPermissionCache($userId);
            }

            $group->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Group deleted successfully'
                ]);
            }

            return redirect()->route('admin.groups.index')
                ->with('success', 'Group deleted successfully');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete group: ' . $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to delete group: ' . $e->getMessage()]);
        }
    }

    /**
     * Add users to group
     */
    public function addUsers(Request $request, UserGroup $group): JsonResponse
    {
        $validated = $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id'
        ]);

        try {
            $this->rolePermissionService->addUsersToGroup($group, $validated['users']);

            return response()->json([
                'success' => true,
                'message' => 'Users added to group successfully',
                'data' => $group->fresh()->load('users')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add users to group: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove users from group
     */
    public function removeUsers(Request $request, UserGroup $group): JsonResponse
    {
        $validated = $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id'
        ]);

        try {
            $this->rolePermissionService->removeUsersFromGroup($group, $validated['users']);

            return response()->json([
                'success' => true,
                'message' => 'Users removed from group successfully',
                'data' => $group->fresh()->load('users')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove users from group: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Assign permissions to group
     */
    public function assignPermissions(Request $request, UserGroup $group): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,slug'
        ]);

        try {
            $this->rolePermissionService->assignPermissionsToGroup($group, $validated['permissions']);

            return response()->json([
                'success' => true,
                'message' => 'Permissions assigned to group successfully',
                'data' => $group->fresh()->load('permissions')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions to group: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove permissions from group
     */
    public function removePermissions(Request $request, UserGroup $group): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,slug'
        ]);

        try {
            $this->rolePermissionService->removePermissionsFromGroup($group, $validated['permissions']);

            return response()->json([
                'success' => true,
                'message' => 'Permissions removed from group successfully',
                'data' => $group->fresh()->load('permissions')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove permissions from group: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get available users for department/team groups
     */
    public function availableUsers(Request $request): JsonResponse
    {
        $query = User::select('id', 'name', 'email', 'department', 'position');

        // Filter by department for department groups
        if ($request->type === 'department' && $request->department) {
            $query->where('department', $request->department);
        }

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get departments for creating department groups
     */
    public function departments(): JsonResponse
    {
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }
}