<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $roles - Comma-separated list of roles
     * @param  string $type - 'any' (default) or 'all' - whether user needs any or all roles
     * @param  string|null $guard
     */
    public function handle(Request $request, Closure $next, string $roles, string $type = 'any', string $guard = null): ResponseAlias
    {
        $user = $request->user($guard);

        if (!$user) {
            return $this->unauthorized($request, 'Authentication required');
        }

        $roleList = array_map('trim', explode(',', $roles));
        
        $hasRole = match($type) {
            'all' => $this->userHasAllRoles($user, $roleList),
            'any' => $user->hasAnyRole($roleList),
            default => $user->hasAnyRole($roleList)
        };

        if (!$hasRole) {
            $message = $type === 'all' 
                ? 'You need all of the following roles: ' . implode(', ', $roleList)
                : 'You need at least one of the following roles: ' . implode(', ', $roleList);
                
            return $this->unauthorized($request, $message);
        }

        return $next($request);
    }

    /**
     * Check if user has all specified roles
     */
    protected function userHasAllRoles($user, array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$user->hasRole($role)) {
                return false;
            }
        }
        return true;
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