<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use App\Livewire\Concerns\LogsPermissionAudit;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserPermissionCrud extends Component
{
    use LogsPermissionAudit;
    public int $userId;

    public User $user;

    public array $permissionOverrides = [];

    public function mount(int $userId): void
    {
        $this->userId = $userId;
        $this->user = User::withoutGlobalScopes()->findOrFail($userId);
        $this->loadOverrides();
    }

    private function loadOverrides(): void
    {
        $existing = UserPermission::where('user_id', $this->userId)
            ->pluck('type', 'permission_id')
            ->toArray();

        $permissions = Permission::where('is_active', true)
            ->where('history', 1)
            ->pluck('id');

        $this->permissionOverrides = [];
        foreach ($permissions as $id) {
            $this->permissionOverrides[$id] = $existing[$id] ?? 'neutral';
        }
    }

    public function save(): void
    {
        $uid = request()->attributes->get('jwt_user')?->id ?? 1;
        $now = now();

        $oldOverrides = UserPermission::where('user_id', $this->userId)
            ->pluck('type', 'permission_id')
            ->toArray();

        DB::transaction(function () use ($uid, $now) {
            UserPermission::where('user_id', $this->userId)->delete();

            $inserts = [];
            foreach ($this->permissionOverrides as $permId => $type) {
                if ($type === 'neutral') continue;
                $inserts[] = [
                    'user_id'       => $this->userId,
                    'permission_id' => $permId,
                    'type'          => $type,
                    'tenant_id'     => null,
                    'created_by'    => $uid,
                    'created_date'  => $now,
                ];
            }

            if (!empty($inserts)) {
                UserPermission::insert($inserts);
            }
        });

        $newOverrides = [];
        foreach ($this->permissionOverrides as $permId => $type) {
            if ($type !== 'neutral') {
                $newOverrides[$permId] = $type;
            }
        }

        $allPermIds = array_unique(array_merge(array_keys($oldOverrides), array_keys($newOverrides)));
        $permNames = Permission::whereIn('id', $allPermIds)->pluck('name', 'id');

        foreach ($allPermIds as $permId) {
            $oldType = $oldOverrides[$permId] ?? null;
            $newType = $newOverrides[$permId] ?? null;

            if ($oldType === $newType) continue;

            if ($newType === null) {
                $action = 'override_removed';
            } elseif ($oldType === null) {
                $action = 'override_' . $newType;
            } else {
                $action = 'override_' . $newType;
            }

            $this->logAudit(
                action: $action,
                entityType: 'user_permission',
                entityId: $this->userId,
                userId: $this->userId,
                permissionId: $permId,
                permissionName: $permNames[$permId] ?? null,
                oldValue: $oldType,
                newValue: $newType,
            );
        }

        $this->user->flushPermissionCache();
        $this->dispatch('toast', type: 'success', message: 'User permission overrides saved successfully.');
    }

    public function render()
    {
        $groupedPermissions = Permission::where('is_active', true)
            ->where('history', 1)
            ->orderBy('module')
            ->orderBy('display_name')
            ->get()
            ->groupBy(fn ($p) => $p->module ?? 'Uncategorized');

        return view('livewire.admin.user-permission-crud', [
            'groupedPermissions' => $groupedPermissions,
        ]);
    }
}
