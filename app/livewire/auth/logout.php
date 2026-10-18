<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Logout component — call this to invalidate the JWT and clear the session.
 */
class Logout extends Component
{
    public function logout(): void
    {
        // Invalidate the token stored in session
        if (session()->has('jwt_token')) {
            try {
                JWTAuth::setToken(session('jwt_token'))->invalidate();
            } catch (JWTException) {
                // Already expired / invalid — still clean up
            }
        }

        session()->forget(['jwt_token', 'jwt_user', 'tenant_id']);
        session()->regenerate(true);

        $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return <<<'BLADE'
        <div>
            <button wire:click="logout"
                    class="block w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors text-left">
                Sign Out
            </button>
        </div>
        BLADE;
    }
}
