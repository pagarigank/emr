<?php

namespace App\Livewire\Concerns;

trait ChecksPermissions
{
    protected function checkPermission(string $permission): void
    {
        $user = auth('api')->user();

        if (!$user || !$user->hasPermission($permission)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }
}
