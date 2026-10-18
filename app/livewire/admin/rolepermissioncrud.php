<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Livewire\Concerns\LogsPermissionAudit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RolePermissionCrud extends Component
{
    use LogsPermissionAudit;
    public string $selectedRole = 'staff';

    public array $permissionCheckboxes = [];

    public function mount(): void
    {
        $first = Role::active()->first();
        if ($first) {
            $this->selectedRole = $first->name;
        }
        $this->loadPermissions();
    }

    public function updatedSelectedRole(): void
    {
        $this->loadPermissions();
    }

    private function loadPermissions(): void
    {
        $existing = DB::table('role_permissions')
            ->where('role', $this->selectedRole)
            ->pluck('permission_id')
            ->toArray();

        $permissions = Permission::where('is_active', true)
            ->where('history', 1)
            ->pluck('id');

        $this->permissionCheckboxes = [];
        foreach ($permissions as $id) {
            $this->permissionCheckboxes[$id] = in_array($id, $existing);
        }
    }

    public function save(): void
    {
        $selected = array_keys(array_filter($this->permissionCheckboxes));
        $now = now();

        $oldPermIds = DB::table('role_permissions')
            ->where('role', $this->selectedRole)
            ->pluck('permission_id')
            ->toArray();

        $added = array_diff($selected, $oldPermIds);
        $removed = array_diff($oldPermIds, $selected);

        DB::table('role_permissions')
            ->where('role', $this->selectedRole)
            ->delete();

        $inserts = [];
        $role = Role::where('name', $this->selectedRole)->first();

        foreach ($selected as $permId) {
            $inserts[] = [
                'role'          => $this->selectedRole,
                'role_id'       => $role?->id,
                'permission_id' => $permId,
                'tenant_id'     => null,
                'created_date'  => $now,
            ];
        }

        if (!empty($inserts)) {
            DB::table('role_permissions')->insert($inserts);
        }

        $permNames = Permission::whereIn('id', array_merge($added, $removed))->pluck('name', 'id');

        foreach ($added as $permId) {
            $this->logAudit(
                action: 'assigned',
                entityType: 'role_permission',
                role: $this->selectedRole,
                permissionId: $permId,
                permissionName: $permNames[$permId] ?? null,
            );
        }

        foreach ($removed as $permId) {
            $this->logAudit(
                action: 'revoked',
                entityType: 'role_permission',
                role: $this->selectedRole,
                permissionId: $permId,
                permissionName: $permNames[$permId] ?? null,
            );
        }

        User::withoutGlobalScopes()
            ->where('role', $this->selectedRole)
            ->where('history', 1)
            ->pluck('id')
            ->each(fn ($id) => Cache::forget("user_permissions_{$id}"));

        $this->dispatch('toast', type: 'success', message: 'Permissions updated successfully.');
    }

    public function render()
    {
        $roles = Role::active()
            ->orderBy('display_name')
            ->pluck('display_name', 'name');

        $groupedPermissions = Permission::where('is_active', true)
            ->where('history', 1)
            ->orderBy('module')
            ->orderBy('display_name')
            ->get()
            ->groupBy(fn ($p) => $p->module ?? 'Uncategorized');

        return view('livewire.admin.role-permission-crud', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }
}
