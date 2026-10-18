<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Livewire\Concerns\LogsPermissionAudit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RoleCrud extends Component
{
    use WithPagination, LogsPermissionAudit;

    public string $search = '';
    public string $sortField = 'display_name';
    public string $sortDirection = 'asc';
    public int $perPage = 25;

    public bool $showModal = false;
    public bool $isEditing = false;
    public bool $editingSystemRole = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $display_name = '';
    public string $description = '';
    public bool $is_active = true;

    public array $permissionCheckboxes = [];

    public string $activeTab = 'details';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->editingSystemRole = false;
        $this->activeTab = 'details';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $role = Role::findOrFail($id);

        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->display_name = $role->display_name;
        $this->description = $role->description ?? '';
        $this->is_active = $role->is_active;
        $this->editingSystemRole = (bool) $role->is_system;
        $this->isEditing = true;
        $this->activeTab = 'details';

        $this->loadPermissionsForRole($role->id);

        $this->showModal = true;
    }

    private function loadPermissionsForRole(int $roleId): void
    {
        $existing = DB::table('role_permissions')
            ->where('role_id', $roleId)
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
        $nameRule = $this->isEditing
            ? 'required|string|max:40|unique:roles,name,' . $this->editingId
            : 'required|string|max:40|unique:roles,name';

        $this->validate([
            'name'         => $this->editingSystemRole ? 'nullable' : $nameRule,
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:500',
            'is_active'    => 'boolean',
        ]);

        $now = now();
        $userId = request()->attributes->get('jwt_user')?->id ?? 1;

        if ($this->isEditing) {
            $role = Role::findOrFail($this->editingId);
            $payload = [
                'display_name' => $this->display_name,
                'description'  => $this->description ?: null,
                'is_active'    => $this->is_active,
                'updated_by'   => $userId,
                'updated_date' => $now,
            ];
            if (!$this->editingSystemRole) {
                $payload['name'] = $this->name;
            }
            $role->update($payload);

            $this->savePermissions($role);

            $this->dispatch('toast', type: 'success', message: 'Role updated successfully.');
        } else {
            $role = Role::create([
                'name'         => $this->name,
                'display_name' => $this->display_name,
                'description'  => $this->description ?: null,
                'is_system'    => false,
                'is_active'    => $this->is_active,
                'history'      => 1,
                'created_by'   => $userId,
                'created_date' => $now,
                'updated_by'   => $userId,
                'updated_date' => $now,
            ]);

            $this->savePermissions($role);

            $this->dispatch('toast', type: 'success', message: 'Role created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function savePermissions(Role $role): void
    {
        $selected = array_keys(array_filter($this->permissionCheckboxes));
        $now = now();

        $oldPermIds = DB::table('role_permissions')
            ->where('role_id', $role->id)
            ->pluck('permission_id')
            ->toArray();

        $added = array_diff($selected, $oldPermIds);
        $removed = array_diff($oldPermIds, $selected);

        DB::table('role_permissions')
            ->where('role_id', $role->id)
            ->delete();

        $inserts = [];
        foreach ($selected as $permId) {
            $inserts[] = [
                'role'          => $role->name,
                'role_id'       => $role->id,
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
                role: $role->name,
                permissionId: $permId,
                permissionName: $permNames[$permId] ?? null,
            );
        }

        foreach ($removed as $permId) {
            $this->logAudit(
                action: 'revoked',
                entityType: 'role_permission',
                role: $role->name,
                permissionId: $permId,
                permissionName: $permNames[$permId] ?? null,
            );
        }

        User::withoutGlobalScopes()
            ->where('role_id', $role->id)
            ->where('history', 1)
            ->pluck('id')
            ->each(fn ($id) => Cache::forget("user_permissions_{$id}"));
    }

    public function confirmDelete(int $id): void
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            $this->dispatch('toast', type: 'error', message: 'System roles cannot be deleted.');
            return;
        }

        $role->update([
            'history'      => 0,
            'updated_by'   => request()->attributes->get('jwt_user')?->id ?? 1,
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Role archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'display_name', 'description', 'editingSystemRole', 'permissionCheckboxes']);
        $this->is_active = true;
        $this->isEditing = false;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function render()
    {
        $roles = Role::where('history', 1)
            ->when($this->search, fn ($q) =>
                $q->where(function ($inner) {
                    $inner->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('display_name', 'like', '%' . $this->search . '%');
                })
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $groupedPermissions = Permission::where('is_active', true)
            ->where('history', 1)
            ->orderBy('module')
            ->orderBy('display_name')
            ->get()
            ->groupBy(fn ($p) => $p->module ?? 'Uncategorized');

        return view('livewire.admin.role-crud', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }
}
