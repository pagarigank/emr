<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

/**
 * Login Livewire component.
 *
 * Submits credentials to the internal /api/auth/login endpoint,
 * receives a JWT token, stores it in the session, then navigates
 * to the dashboard — making all subsequent page loads token-protected.
 */
class Login extends Component
{
    public string  $email    = '';
    public string  $password = '';
    public bool    $remember = false;
    public ?string $error    = null;
    public bool    $loading  = false;

    protected $rules = [
        'email'    => ['required', 'email'],
        'password' => ['required', 'string', 'min:6'],
    ];

    public function login(): void
    {
        $this->error   = null;
        $this->loading = true;
        $this->validate();

        try {
            $credentials = [
                'email'    => $this->email,
                'password' => $this->password,
            ];

            // Direct JWT attempt — avoids loopback HTTP request issues
            if (! $token = \Tymon\JWTAuth\Facades\JWTAuth::attempt($credentials)) {
                $this->error = 'Invalid email or password.';
                $this->loading = false;
                return;
            }

            $user = \Tymon\JWTAuth\Facades\JWTAuth::user();

            if (! $user->is_active) {
                \Tymon\JWTAuth\Facades\JWTAuth::invalidate($token);
                $this->error = 'Your account has been disabled. Contact your administrator.';
                $this->loading = false;
                return;
            }

            // Flush permission cache to ensure fresh menus on every login
            $user->flushPermissionCache();

            // Store token and full user context in the server-side session
            session([
                'jwt_token'            => $token,
                'jwt_user'             => [
                    'id'          => $user->id,
                    'full_name'   => $user->full_name,
                    'email'       => $user->email,
                    'role'        => $user->role,
                    'tenant_id'   => $user->tenant_id,
                    'tenant_name' => $user->tenant?->tenant_name,
                ],
                'jwt_user.full_name'   => $user->full_name,
                'jwt_user.email'       => $user->email,
                'jwt_user.role'        => $user->role,
                'jwt_user.tenant_id'   => $user->tenant_id,
                'jwt_user.tenant_name' => $user->tenant?->tenant_name,
                'tenant_id'            => $user->tenant_id,
            ]);

            session()->save(); // Force writing the session to disk immediately

            $this->redirect(route('dashboard'), navigate: true);

        } catch (\Exception $e) {
            $this->error = 'Authentication error. Please try again.';
        }

        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.guest');
    }
}
