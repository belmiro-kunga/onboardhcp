<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    protected RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
        
        // Apply permission middleware
        $this->middleware('permission:view_roles')->only(['index', 'show']);
        $this->middleware('permission:create_roles')->only(['create', 'store']);
        $this->middleware('permission:edit_roles')->only(['edit', 'update']);
        $this->middleware('permission:delete_roles')->only(['destroy']);
    }

    /**
     * Display a listing of roles
     */
    public function index(Request $request): View|JsonResponse
    {
        $roles = Role::with('permissions')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                if ($type === 'system') {
                    $query->where('is_system', true);
                } elseif ($type === 'custom') {
                    $query->where('is_system', false);
                }
            })
            ->orderBy('name')
            ->paginate(25);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $roles,
                'permissions' => Permission::all()->groupBy('module')
            ]);
        }

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create(): View
    {
        $permissions = Permission::all()->groupBy('module');
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,slug'
        ]);

        try {
            $role = $this->rolePermissionService->createRole(
                $validated['name'],
                $validated['description'] ?? null,
                $validated['permissions'] ?? [],
                false // Custom roles are not system roles
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role created successfully',
                    'data' => $role->load('permissions')
                ], 201);
            }

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role created successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create role: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to create role: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified role
     */
    public function show(Role $role): View|JsonResponse
    {
        $role->load(['permissions', 'users']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $role
            ]);
        }

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');
        $permissions = Permission::all()->groupBy('module');
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,slug'
        ]);

        // Prevent editing system roles
        if ($role->is_system && !auth()->user()->hasRole('Super Admin')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit system roles'
                ], 403);
            }

            return back()->withErrors(['error' => 'Cannot edit system roles']);
        }

        try {
            $updatedRole = $this->rolePermissionService->updateRole($role, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role updated successfully',
                    'data' => $updatedRole->load('permissions')
                ]);
            }

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role updated successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update role: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to update role: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role): RedirectResponse|JsonResponse
    {
        try {
            $this->rolePermissionService->deleteRole($role);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully'
                ]);
            }

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role deleted successfully');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete role: ' . $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to delete role: ' . $e->getMessage()]);
        }
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissions(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,slug'
        ]);

        try {
            $this->rolePermissionService->assignPermissionsToRole($role, $validated['permissions']);

            return response()->json([
                'success' => true,
                'message' => 'Permissions assigned successfully',
                'data' => $role->fresh()->load('permissions')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove permissions from role
     */
    public function removePermissions(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,slug'
        ]);

        try {
            $this->rolePermissionService->removePermissionsFromRole($role, $validated['permissions']);

            return response()->json([
                'success' => true,
                'message' => 'Permissions removed successfully',
                'data' => $role->fresh()->load('permissions')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove permissions: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get role permissions
     */
    public function permissions(Role $role): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $role->permissions
        ]);
    }

    /**
     * Get users with this role
     */
    public function users(Role $role): JsonResponse
    {
        $users = $role->users()->with('roles')->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}