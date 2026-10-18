<?php

namespace App\Livewire\Masters;

use App\Models\Hospital;
use Livewire\Component;
use Livewire\WithPagination;

class HospitalCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'code';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $address = '', $contact_number = '', $hospital_type = '';
    public bool $is_active = true;

    private function currentUserId(): int
    {
        return auth()->user()?->id ?? 1;
    }

    private function getTenantId(): ?int
    {
        return request()->attributes->get('tenant_id');
    }

    protected function rules(): array
    {
        $unique = 'unique:hospitals,code,NULL,id,history,1';
        if ($this->isEditing && $this->editingId)
            $unique = 'unique:hospitals,code,' . $this->editingId . ',id,history,1';
        return [
            'code' => ['required', 'string', 'max:20', $unique],
            'name' => ['required', 'string', 'max:200'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'hospital_type' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Hospital Code', 'name' => 'Hospital Name',
        'address' => 'Address', 'contact_number' => 'Contact Number',
        'hospital_type' => 'Type',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field)
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openCreate(): void { $this->resetForm(); $this->isEditing = false; $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $h = Hospital::findOrFail($id);
        $this->editingId = $h->id; $this->code = $h->code; $this->name = $h->name;
        $this->address = $h->address ?? ''; $this->contact_number = $h->contact_number ?? '';
        $this->hospital_type = $h->hospital_type ?? '';
        $this->is_active = $h->is_active; $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $tenantId = $this->getTenantId();
        if ($this->isEditing) {
            Hospital::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Hospital updated successfully.');
        } else {
            Hospital::create(array_merge($data, [
                'tenant_id' => $tenantId,
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Hospital created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Hospital::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Hospital archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'address', 'contact_number', 'hospital_type']);
        $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $tenantId = $this->getTenantId();
        $hospitals = Hospital::query()
            ->where('tenant_id', $tenantId)
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.masters.hospital-crud', compact('hospitals'));
    }
}
