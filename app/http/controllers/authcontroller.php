<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * AuthController
 *
 * JWT token issuance, refresh, and revocation.
 * Supports both superadmin and tenant_admin login via email + password.
 */
class AuthController extends Controller
{
    /**
     * Issue a JWT token after validating credentials.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        $user = JWTAuth::user();

        if (! $user->is_active) {
            JWTAuth::invalidate($token);
            return response()->json([
                'success' => false,
                'message' => 'Your account has been disabled. Contact your administrator.',
            ], 403);
        }

        return $this->respondWithToken($token, $user);
    }

    /**
     * Return the currently authenticated user profile.
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'user'    => $this->formatUser($user),
        ]);
    }

    /**
     * Refresh the current token (extend session without re-login).
     */
    public function refresh(): JsonResponse
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return $this->respondWithToken($newToken, auth('api')->user());
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed: ' . $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Invalidate (logout) the current token.
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException) {
            // Already invalid — still respond OK
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function respondWithToken(string $token, $user): JsonResponse
    {
        return response()->json([
            'success'      => true,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60, // seconds
            'user'         => $this->formatUser($user),
        ]);
    }

    private function formatUser($user): array
    {
        return [
            'id'          => $user->id,
            'full_name'   => $user->full_name,
            'email'       => $user->email,
            'role'        => $user->role,
            'tenant_id'   => $user->tenant_id,
            'tenant_name' => $user->tenant?->tenant_name,
        ];
    }
}
