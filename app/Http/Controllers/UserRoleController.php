<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Modules\User\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class UserRoleController extends Controller
{
    protected RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
        
        // Apply permission middleware
        $this->middleware('permission:manage_user_roles');
    }

    /**
     * Get user roles
     */
    public function index(User $user): JsonResponse
    {
        $user->load('roles');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'roles' => $user->roles,
                'available_roles' => Role::all()
            ]
        ]);
    }

    /**
     * Assign role to user
     */
    public function store(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        // Validate role assignment
        $errors = $this->rolePermissionService->validateRoleAssignment($user, $validated['role']);
        if (!empty($errors)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 422);
            }

            return back()->withErrors($errors);
        }

        try {
            $this->rolePermissionService->assignRoleToUser(
                $user, 
                $validated['role'], 
                auth()->id()
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role assigned successfully',
                    'data' => $user->fresh()->load('roles')
                ]);
            }

            return back()->with('success', 'Role assigned successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign role: ' . $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to assign role: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove role from user
     */
    public function destroy(Request $request, User $user, Role $role): JsonResponse|RedirectResponse
    {
        // Validate role removal
        $errors = $this->rolePermissionService->validateRoleRemoval($user, $role->name);
        if (!empty($errors)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 422);
            }

            return back()->withErrors($errors);
        }

        try {
            $this->rolePermissionService->removeRoleFromUser($user, $role->name);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role removed successfully',
                    'data' => $user->fresh()->load('roles')
                ]);
            }

            return back()->with('success', 'Role removed successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove role: ' . $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to remove role: ' . $e->getMessage()]);
        }
    }

    /**
     * Sync user roles (replace all roles)
     */
    public function sync(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);

        try {
            $this->rolePermissionService->syncUserRoles(
                $user, 
                $validated['roles'], 
                auth()->id()
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User roles updated successfully',
                    'data' => $user->fresh()->load('roles')
                ]);
            }

            return back()->with('success', 'User roles updated successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user roles: ' . $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to update user roles: ' . $e->getMessage()]);
        }
    }

    /**
     * Get user permissions (from roles and groups)
     */
    public function permissions(User $user): JsonResponse
    {
        $permissions = $this->rolePermissionService->getUserPermissions($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'permissions' => $permissions->groupBy('module')
            ]
        ]);
    }

    /**
     * Show user permissions with sources (web interface)
     */
    public function showPermissions(User $user)
    {
        $user->load(['roles', 'groups']);
        $permissionsWithSources = $this->rolePermissionService->getUserPermissionsWithSources($user);
        $availableRoles = Role::whereNotIn('id', $user->roles->pluck('id'))->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'permissions_with_sources' => $permissionsWithSources
                ]
            ]);
        }

        return view('admin.users.permissions', compact('user', 'permissionsWithSources', 'availableRoles'));
    }

    /**
     * Check if user has specific permission
     */
    public function checkPermission(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'permission' => 'required|exists:permissions,slug'
        ]);

        $hasPermission = $this->rolePermissionService->userHasPermission(
            $user, 
            $validated['permission']
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'permission' => $validated['permission'],
                'has_permission' => $hasPermission
            ]
        ]);
    }

    /**
     * Bulk assign roles to multiple users
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);

        $results = [
            'success' => [],
            'errors' => []
        ];

        foreach ($validated['users'] as $userId) {
            $user = User::find($userId);
            
            try {
                foreach ($validated['roles'] as $roleName) {
                    // Validate each role assignment
                    $errors = $this->rolePermissionService->validateRoleAssignment($user, $roleName);
                    if (empty($errors)) {
                        $this->rolePermissionService->assignRoleToUser($user, $roleName, auth()->id());
                    }
                }
                
                $results['success'][] = [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'roles' => $validated['roles']
                ];

            } catch (\Exception $e) {
                $results['errors'][] = [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk role assignment completed',
            'data' => $results
        ]);
    }

    /**
     * Bulk remove roles from multiple users
     */
    public function bulkRemove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);

        $results = [
            'success' => [],
            'errors' => []
        ];

        foreach ($validated['users'] as $userId) {
            $user = User::find($userId);
            
            try {
                foreach ($validated['roles'] as $roleName) {
                    // Validate each role removal
                    $errors = $this->rolePermissionService->validateRoleRemoval($user, $roleName);
                    if (empty($errors)) {
                        $this->rolePermissionService->removeRoleFromUser($user, $roleName);
                    }
                }
                
                $results['success'][] = [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'roles' => $validated['roles']
                ];

            } catch (\Exception $e) {
                $results['errors'][] = [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk role removal completed',
            'data' => $results
        ]);
    }
}