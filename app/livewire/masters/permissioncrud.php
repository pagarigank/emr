<?php

namespace App\Livewire\Masters;

use App\Models\Permission;
use App\Livewire\Concerns\LogsPermissionAudit;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionCrud extends Component
{
    use WithPagination, LogsPermissionAudit;

    public string $search        = '';
    public string $sortField     = 'module';
    public string $sortDirection = 'asc';
    public int    $perPage       = 25;

    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public bool $isEditing       = false;

    public ?int   $editingId    = null;
    public string $name         = '';
    public string $display_name = '';
    public string $module       = '';
    public string $description  = '';
    public bool   $is_active    = true;

    private function currentTenantId(): ?int
    {
        return request()->attributes->get('tenant_id');
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $uniqueRule = 'unique:permissions,name';
        if ($this->isEditing && $this->editingId) {
            $uniqueRule = 'unique:permissions,name,' . $this->editingId;
        }
        return [
            'name'         => ['required', 'string', 'max:100', 'alpha_dash', $uniqueRule],
            'display_name' => ['required', 'string', 'max:200'],
            'module'       => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:255'],
            'is_active'    => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'name'         => 'Permission Key',
        'display_name' => 'Display Name',
        'module'       => 'Module',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $perm              = Permission::findOrFail($id);
        $this->editingId   = $perm->id;
        $this->name         = $perm->name;
        $this->display_name = $perm->display_name;
        $this->module       = $perm->module ?? '';
        $this->description  = $perm->description ?? '';
        $this->is_active    = $perm->is_active;
        $this->isEditing    = true;
        $this->showModal    = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $now  = now();
        $uid  = $this->currentUserId();

        if ($this->isEditing) {
            $old = Permission::findOrFail($this->editingId);
            $oldData = $old->toArray();
            $old->update([
                'name'         => $data['name'],
                'display_name' => $data['display_name'],
                'module'       => $data['module'],
                'description'  => $data['description'],
                'is_active'    => $data['is_active'],
                'updated_by'   => $uid,
                'updated_date' => $now,
            ]);
            $this->logAudit(
                action: 'updated',
                entityType: 'permission',
                entityId: $old->id,
                permissionId: $old->id,
                permissionName: $data['name'],
                oldValue: $oldData,
                newValue: $data,
            );
            $this->dispatch('toast', type: 'success', message: 'Permission updated successfully.');
        } else {
            $perm = Permission::create([
                'name'         => $data['name'],
                'display_name' => $data['display_name'],
                'module'       => $data['module'],
                'description'  => $data['description'],
                'guard_name'   => 'web',
                'is_active'    => $data['is_active'],
                'history'      => 1,
                'created_by'   => $uid,
                'created_date' => $now,
            ]);
            $this->logAudit(
                action: 'created',
                entityType: 'permission',
                entityId: $perm->id,
                permissionId: $perm->id,
                permissionName: $perm->name,
                newValue: $perm->toArray(),
            );
            $this->dispatch('toast', type: 'success', message: 'Permission created successfully.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId       = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $perm = Permission::findOrFail($this->editingId);
        $perm->update([
            'history'      => 0,
            'updated_by'   => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->logAudit(
            action: 'archived',
            entityType: 'permission',
            entityId: $perm->id,
            permissionId: $perm->id,
            permissionName: $perm->name,
            oldValue: $perm->toArray(),
        );
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Permission archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'display_name', 'module', 'description']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $modules = Permission::where('is_active', true)
            ->whereNotNull('module')
            ->distinct()
            ->pluck('module')
            ->sort()
            ->values()
            ->toArray();

        $permissions = Permission::query()
            ->where('history', 1)
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('display_name', 'like', '%' . $this->search . '%')
                          ->orWhere('module', 'like', '%' . $this->search . '%')
                          ->orWhere('description', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.masters.permission-crud', compact('permissions', 'modules'));
    }
}
