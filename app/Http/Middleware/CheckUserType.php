<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     * Accepts a list of allowed user types like: ->middleware('check.user_type:superadmin,admin')
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles)
    {
        $user = $request->user(); // works with Sanctum

        if (!$user) {
            return ResponseHelper::error([], 'Unauthorized', 401);
        }

        // Load roles if not already loaded
        $userRoles = $user->roles->pluck('name')->toArray(); // assumes relation 'roles'

        // Check if any role matches
        $hasRole = !empty(array_intersect($allowedRoles, $userRoles));

        if (!$hasRole) {
            return ResponseHelper::error([], 'Unauthorized User Role', 403);
        }

        return $next($request);
    }
}
