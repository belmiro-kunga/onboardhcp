<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\RolePermissionService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CheckPermissions
{
    protected RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $permissions - Comma-separated list of permissions
     * @param  string $type - 'any' (default) or 'all' - whether user needs any or all permissions
     * @param  string|null $guard
     */
    public function handle(Request $request, Closure $next, string $permissions, string $type = 'any', string $guard = null): ResponseAlias
    {
        $user = $request->user($guard);

        if (!$user) {
            return $this->unauthorized($request, 'Authentication required');
        }

        $permissionList = array_map('trim', explode(',', $permissions));
        
        $hasPermission = match($type) {
            'all' => $this->rolePermissionService->userHasAllPermissions($user, $permissionList),
            'any' => $this->rolePermissionService->userHasAnyPermission($user, $permissionList),
            default => $this->rolePermissionService->userHasAnyPermission($user, $permissionList)
        };

        if (!$hasPermission) {
            $message = $type === 'all' 
                ? 'You need all of the following permissions: ' . implode(', ', $permissionList)
                : 'You need at least one of the following permissions: ' . implode(', ', $permissionList);
                
            return $this->unauthorized($request, $message);
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access
     */
    protected function unauthorized(Request $request, string $message): ResponseAlias
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'Unauthorized',
                'code' => 403
            ], 403);
        }

        abort(403, $message);
    }
}