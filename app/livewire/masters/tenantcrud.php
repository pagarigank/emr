<?php

namespace App\Livewire\Masters;

use App\Livewire\Concerns\ChecksPermissions;
use App\Models\Tenant;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TenantCrud extends Component
{
    use WithPagination, ChecksPermissions;

    // Data State
    public $tenant_id;
    public $tenant_code;
    public $tenant_name;
    public $address;
    public $city;
    public $contact_number;
    public $email;
    public $is_active = true;

    // UI State
    public $search = '';
    public $sortField = 'tenant_name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    
    public $isModalOpen = false;
    public $isArchiveModalOpen = false;
    public $tenantToArchive = null;

    // Context
    public $isSuperAdmin = false;
    public $currentTenantId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'tenant_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        $this->isSuperAdmin = request()->attributes->get('is_superadmin', false);
        $this->currentTenantId = request()->attributes->get('tenant_id');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Tenant::query();

        // Scope data based on role
        if (! $this->isSuperAdmin) {
            $query->where('id', $this->currentTenantId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('tenant_code', 'like', '%' . $this->search . '%')
                  ->orWhere('tenant_name', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.masters.tenant-crud', [
            'tenants' => $query->paginate($this->perPage),
        ]);
    }

    public function create()
    {
        $this->checkPermission('admin.tenants');

        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);
        
        // Tenant Admin can only edit their own tenant
        if (! $this->isSuperAdmin && $tenant->id != $this->currentTenantId) {
            abort(403, 'Unauthorized action.');
        }

        $this->tenant_id = $tenant->id;
        $this->tenant_code = $tenant->tenant_code;
        $this->tenant_name = $tenant->tenant_name;
        $this->address = $tenant->address;
        $this->city = $tenant->city;
        $this->contact_number = $tenant->contact_number;
        $this->email = $tenant->email;
        $this->is_active = $tenant->is_active;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate([
            'tenant_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tenants', 'tenant_code')
                    ->where('history', 1)
                    ->ignore($this->tenant_id)
            ],
            'tenant_name' => 'required|string|max:150',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'is_active' => 'boolean',
        ]);

        $userId = request()->attributes->get('jwt_user')?->id;

        if ($this->tenant_id) {
            // Update
            $tenant = Tenant::findOrFail($this->tenant_id);

            if (! $this->isSuperAdmin && $tenant->id != $this->currentTenantId) {
                abort(403, 'Unauthorized action.');
            }

            $tenant->update([
                'tenant_code' => $this->tenant_code,
                'tenant_name' => $this->tenant_name,
                'address' => $this->address,
                'city' => $this->city,
                'contact_number' => $this->contact_number,
                'email' => $this->email,
                'is_active' => $this->is_active,
                'updated_by' => $userId,
                'updated_date' => now(),
            ]);

            session()->flash('message', 'Company updated successfully.');
        } else {
            // Create
            $this->checkPermission('admin.tenants');

            Tenant::create([
                'tenant_code' => $this->tenant_code,
                'tenant_name' => $this->tenant_name,
                'address' => $this->address,
                'city' => $this->city,
                'contact_number' => $this->contact_number,
                'email' => $this->email,
                'is_active' => $this->is_active,
                'history' => 1,
                'created_by' => $userId,
                'created_date' => now(),
            ]);

        }

        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: $this->tenant_id ? 'Company successfully updated.' : 'Company successfully added.');
    }

    public function confirmArchive($id)
    {
        $this->checkPermission('admin.tenants');

        $this->tenantToArchive = $id;
        $this->isArchiveModalOpen = true;
    }

    public function archive()
    {
        $this->checkPermission('admin.tenants');

        $tenant = Tenant::findOrFail($this->tenantToArchive);

        $tenant->update([
            'history' => 0,
            'updated_by' => request()->attributes->get('jwt_user')->id,
            'updated_date' => now(),
        ]);

        $this->isArchiveModalOpen = false;
        $this->dispatch('toast', type: 'success', message: 'Company successfully archived.');
        $this->tenantToArchive = null;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetFields();
    }

    private function resetFields()
    {
        $this->tenant_id = null;
        $this->tenant_code = '';
        $this->tenant_name = '';
        $this->address = '';
        $this->city = '';
        $this->contact_number = '';
        $this->email = '';
        $this->is_active = true;
        $this->resetValidation();
    }
}
