<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->denied($request);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $permissions = $user->permissions_list;

        if (!in_array($permission, $permissions, true)) {
            return $this->denied($request);
        }

        return $next($request);
    }

    private function denied(Request $request): Response
    {
        if ($request->is('api/*')) {
            return response()->json(['error' => 'Forbidden', 'message' => 'Insufficient permissions.'], 403);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => 'Forbidden', 'message' => 'Insufficient permissions.'], 403);
        }

        return redirect()->route('dashboard')->with('error', 'You do not have permission to access this page.');
    }
}
