<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * JwtMiddleware
 *
 * Validates JWT on every protected request.
 *
 * Token sources (in priority order):
 *   1. session('jwt_token')         — web/Livewire routes
 *   2. Authorization: Bearer <token> — API / Postman
 *   3. ?token=<token>               — query-param fallback
 *
 * Multi-tenant context injected into request attributes:
 *   $request->attributes->get('tenant_id') → null for superadmin
 *   $request->attributes->get('role')      → 'superadmin' | 'tenant_admin' | 'staff'
 *   $request->attributes->get('jwt_user')  → User model instance
 *
 * @deprecated 'is_superadmin' attribute (line 75) kept for backward compat.
 *             New code should check `request()->attributes->get('role') === 'superadmin'`
 *             or use proper permission checks via hasPermission().
 */
class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip JWT validation for auth routes, the public booking page, and livewire assets
        if ($request->is('login', 'book-appointment', 'api/auth/login', 'livewire/livewire.js', 'livewire/*.js', 'livewire/*.css')) {
            return $next($request);
        }

        // Allow Livewire updates that originate from the login or public booking pages
        if ($request->is('livewire*/update', 'livewire/update')) {
            $referer = $request->headers->get('referer');
            $path = $referer ? parse_url($referer, PHP_URL_PATH) ?? '' : '';
            if ($path && (str_ends_with($path, '/login') || str_ends_with($path, '/book-appointment'))) {
                return $next($request);
            }
        }

        // ── 1. Resolve token ──────────────────────────────────────────────────
        $token = $this->resolveToken($request);

        if (! $token) {
            return $this->unauthorized($request, 'Token not provided.');
        }

        try {
            // ── 2. Validate token and load user ───────────────────────────────
            $user = JWTAuth::setToken($token)->authenticate();

            if (! $user) {
                return $this->unauthorized($request, 'User not found.');
            }

            // ── 3. Active check ───────────────────────────────────────────────
            if (! $user->is_active) {
                return $this->unauthorized($request, 'Your account has been disabled.');
            }

            // ── 4. Extract claims ─────────────────────────────────────────────
            $claims       = JWTAuth::getPayload($token)->toArray();
            $tenantId     = $claims['tenant_id'] ?? $user->tenant_id; // null = superadmin
            $role         = $claims['role']      ?? $user->role;
            $isSuperAdmin = $role === 'superadmin';

            // ── 5. Bind context to the request ────────────────────────────────
            $request->attributes->set('tenant_id',     $tenantId);
            $request->attributes->set('role',          $role);
            $request->attributes->set('is_superadmin', $isSuperAdmin);
            $request->attributes->set('jwt_user',      $user);

            auth('api')->setUser($user);

        } catch (TokenExpiredException) {
            return $this->unauthorized($request, 'Session expired. Please log in again.');
        } catch (TokenInvalidException $e) {
            return $this->unauthorized($request, 'Invalid token.');
        } catch (JWTException $e) {
            return $this->unauthorized($request, 'Authentication error: ' . $e->getMessage());
        }

        return $next($request);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveToken(Request $request): ?string
    {
        $hasSession = $request->hasSession();
        $hasToken = $hasSession ? $request->session()->has('jwt_token') : false;

        if ($hasSession && $hasToken) {
            return $request->session()->get('jwt_token');
        }
        if ($token = $request->bearerToken()) {
            return $token;
        }
        if ($token = $request->query('token')) {
            return $token;
        }
        return null;
    }

    private function unauthorized(Request $request, string $message): Response
    {
        if ($request->is('api/*')) {
            return response()->json(['error' => 'Unauthorized', 'message' => $message], 401);
        }
        return redirect()->route('login')->with('error', $message);
    }
}
