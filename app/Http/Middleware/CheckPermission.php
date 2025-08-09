<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\RolePermissionService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CheckPermission
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
     */
    public function handle(Request $request, Closure $next, string $permission, string $guard = null): ResponseAlias
    {
        $user = $request->user($guard);

        if (!$user) {
            return $this->unauthorized($request, 'Authentication required');
        }

        if (!$this->rolePermissionService->userHasPermission($user, $permission)) {
            return $this->unauthorized($request, 'Insufficient permissions');
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
                'error' => 'Unauthorized'
            ], 403);
        }

        abort(403, $message);
    }
}