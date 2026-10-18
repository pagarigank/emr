<?php

namespace App\Livewire\Billing;

use App\Models\Billing\HealthInsuranceProvider;
use Livewire\Component;
use Livewire\WithPagination;

class InsuranceProviderCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $contact_person = '', $phone = '', $email = '';
    public string $website = '', $address = '', $provider_type = '', $notes = '';
    public bool $is_active = true;

    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }
    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }

    protected function rules(): array
    {
        $table = 'health_insurance_providers';
        $u = "unique:{$table},code,NULL,id,history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:{$table},code,{$this->editingId},id,history,1";
        return [
            'code'            => ['required', 'string', 'max:30', $u],
            'name'            => ['required', 'string', 'max:200'],
            'contact_person'  => ['nullable', 'string', 'max:200'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'email'           => ['nullable', 'email', 'max:200'],
            'website'         => ['nullable', 'url', 'max:200'],
            'address'         => ['nullable', 'string', 'max:500'],
            'provider_type'   => ['nullable', 'string', 'max:50'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'is_active'       => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Provider Code',
        'name' => 'Provider Name',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openCreate(): void { $this->resetForm(); $this->isEditing = false; $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $r = HealthInsuranceProvider::findOrFail($id);
        $this->editingId = $r->id;
        $this->code = $r->code;
        $this->name = $r->name;
        $this->contact_person = $r->contact_person ?? '';
        $this->phone = $r->phone ?? '';
        $this->email = $r->email ?? '';
        $this->website = $r->website ?? '';
        $this->address = $r->address ?? '';
        $this->provider_type = $r->provider_type ?? '';
        $this->notes = $r->notes ?? '';
        $this->is_active = $r->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        if ($this->isEditing) {
            $record = HealthInsuranceProvider::findOrFail($this->editingId);
            $record->update(array_merge($data, [
                'updated_by' => $uid,
                'updated_date' => $now,
            ]));
            $this->dispatch('toast', type: 'success', message: 'Insurance provider updated successfully.');
        } else {
            HealthInsuranceProvider::create(array_merge($data, [
                'tenant_id' => $tid,
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]));
            $this->dispatch('toast', type: 'success', message: 'Insurance provider created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        HealthInsuranceProvider::findOrFail($this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Insurance provider archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'contact_person', 'phone', 'email', 'website', 'address', 'provider_type', 'notes']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $items = HealthInsuranceProvider::query()
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.billing.insurance-provider-crud', compact('items'));
    }
}
