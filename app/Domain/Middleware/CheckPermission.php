<?php

namespace App\Domain\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle($request, Closure $next, ...$permissions)
    {
        // Check if the user has at least one of the required permissions
        if (!Auth::user()->hasPermissionTo($permissions)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
