<?php

namespace App\Livewire\Masters;

use App\Livewire\Concerns\ChecksPermissions;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class UserCrud extends Component
{
    use WithPagination, ChecksPermissions;

    // ── Datagrid / Search ─────────────────────────────────────────────────────
    public string $search        = '';
    public string $sortField     = 'username';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    // ── Modal state ───────────────────────────────────────────────────────────
    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public bool $isEditing       = false;

    // ── Form fields ───────────────────────────────────────────────────────────
    public ?int   $editingId    = null;
    public ?int   $tenant_id    = null;
    public string $username     = '';
    public string $full_name    = '';
    public string $email        = '';
    public string $password     = '';
    public string $role         = 'tenant_admin';
    public ?int   $health_care_professional_id = null;
    public bool   $is_active    = true;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function currentTenantId(): ?int
    {
        return request()->attributes->get('tenant_id');
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    private function isSuperAdmin(): bool
    {
        return (bool) request()->attributes->get('is_superadmin', false);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $tenantIdRule = 'required|integer|exists:tenants,id';
        if ($this->role === 'superadmin') {
            $tenantIdRule = 'nullable';
        }

        $emailRule = 'required|email|max:150|unique:users,email';
        if ($this->isEditing && $this->editingId) {
            $emailRule = 'required|email|max:150|unique:users,email,' . $this->editingId;
        }

        $tenantIdVal = $this->isSuperAdmin() ? ($this->role === 'superadmin' ? 'NULL' : ($this->tenant_id ?? 'NULL')) : ($this->currentTenantId() ?? 'NULL');
        $usernameRule = 'unique:users,username,NULL,id,tenant_id,' . $tenantIdVal . ',history,1';
        if ($this->isEditing && $this->editingId) {
            $usernameRule = 'unique:users,username,' . $this->editingId . ',id,tenant_id,' . $tenantIdVal . ',history,1';
        }

        return [
            'tenant_id' => $this->isSuperAdmin() ? $tenantIdRule : 'nullable',
            'username'  => ['required', 'string', 'max:60', $usernameRule],
            'full_name' => ['required', 'string', 'max:150'],
            'email'     => $emailRule,
            'password'  => $this->isEditing ? 'nullable|min:6' : 'required|min:6',
            'role'      => 'required|string|exists:roles,name',
            'health_care_professional_id' => 'nullable|integer|exists:health_care_professionals,id',
            'is_active' => 'boolean',
        ];
    }

    protected array $validationAttributes = [
        'tenant_id' => 'Company',
        'username'  => 'Username',
        'full_name' => 'Full Name',
        'email'     => 'Email',
        'password'  => 'Password',
        'role'      => 'Role',
        'health_care_professional_id' => 'Linked Doctor Profile',
    ];

    // ── Datagrid helpers ──────────────────────────────────────────────────────

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

    // ── CRUD actions ──────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->checkPermission('admin.users');
        $this->resetForm();
        $this->isEditing = false;
        
        if (!$this->isSuperAdmin()) {
            $this->tenant_id = $this->currentTenantId();
        }
        
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->checkPermission('admin.users');
        $user = User::findOrFail($id);

        if (!$this->isSuperAdmin() && (int) $user->tenant_id !== (int) $this->currentTenantId()) {
            abort(403, 'Unauthorized action.');
        }

        $this->editingId = $user->id;
        $this->tenant_id = $user->tenant_id;
        $this->username  = $user->username;
        $this->full_name = $user->full_name;
        $this->email     = $user->email;
        $this->role      = $user->role;
        $this->health_care_professional_id = $user->health_care_professional_id;
        $this->is_active = $user->is_active;
        $this->password  = '';

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->checkPermission('admin.users');
        if (!$this->isSuperAdmin()) {
            if ($this->role === 'superadmin') {
                abort(403, 'Unauthorized action.');
            }
            $this->tenant_id = $this->currentTenantId();
        } else {
            if ($this->role === 'superadmin') {
                $this->tenant_id = null;
            }
        }

        $data = $this->validate();

        $roleModel = Role::where('name', $this->role)->first();

        $payload = [
            'tenant_id' => $this->tenant_id,
            'username'  => $this->username,
            'full_name' => $this->full_name,
            'email'     => $this->email,
            'role'      => $this->role,
            'role_id'   => $roleModel?->id,
            'health_care_professional_id' => $this->health_care_professional_id ?: null,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->password)) {
            $payload['password'] = Hash::make($this->password);
        }

        if ($this->isEditing) {
            $user = User::findOrFail($this->editingId);
            
            if (!$this->isSuperAdmin() && (int) $user->tenant_id !== (int) $this->currentTenantId()) {
                abort(403, 'Unauthorized action.');
            }

            $user->update(array_merge($payload, [
                'updated_by'   => $this->currentUserId(),
                'updated_date' => now(),
            ]));
            $user->role_id = $roleModel?->id;
            $user->health_care_professional_id = $this->health_care_professional_id ?: null;
            $user->save();
            
            $this->dispatch('toast', type: 'success', message: 'User updated successfully.');
        } else {
            $user = User::create(array_merge($payload, [
                'history'      => 1,
                'created_by'   => $this->currentUserId(),
                'created_date' => now(),
            ]));
            $user->tenant_id = $this->tenant_id;
            $user->role_id = $roleModel?->id;
            $user->health_care_professional_id = $this->health_care_professional_id ?: null;
            $user->save();
            
            $this->dispatch('toast', type: 'success', message: 'User created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->checkPermission('admin.users');
        $user = User::findOrFail($id);
        
        if (!$this->isSuperAdmin() && (int) $user->tenant_id !== (int) $this->currentTenantId()) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->id === $this->currentUserId()) {
            $this->addError('delete', 'You cannot archive your own account.');
            return;
        }

        $this->editingId       = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $this->checkPermission('admin.users');
        $user = User::findOrFail($this->editingId);
        
        if (!$this->isSuperAdmin() && (int) $user->tenant_id !== (int) $this->currentTenantId()) {
            abort(403, 'Unauthorized action.');
        }

        $user->update([
            'history'      => 0,
            'updated_by'   => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        
        $this->showDeleteModal = false;
        $this->editingId       = null;
        $this->dispatch('toast', type: 'success', message: 'User archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'tenant_id', 'username', 'full_name', 'email', 'password', 'health_care_professional_id']);
        $this->role = 'tenant_admin';
        $this->is_active = true;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $users = User::query()
            ->with('tenant')
            ->when(!$this->isSuperAdmin(), fn ($q) =>
                $q->where('tenant_id', $this->currentTenantId())
            )
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('username', 'like', '%' . $this->search . '%')
                          ->orWhere('full_name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $tenants = $this->isSuperAdmin() ? Tenant::where('is_active', 1)->orderBy('tenant_name')->get() : collect();
        
        $doctorsQuery = \App\Models\HealthCareProfessional::where('is_active', 1);
        if ($this->isSuperAdmin()) {
            if ($this->tenant_id) {
                $doctorsQuery->where('tenant_id', $this->tenant_id);
            }
        } else {
            $doctorsQuery->where('tenant_id', $this->currentTenantId());
        }
        $doctors = $doctorsQuery->orderBy('full_name')->get();

        $roles = Role::active()->orderBy('display_name')->get();

        return view('livewire.masters.user-crud', [
            'users' => $users,
            'tenants' => $tenants,
            'doctors' => $doctors,
            'roles' => $roles,
            'isSuperAdmin' => $this->isSuperAdmin()
        ]);
    }
}
