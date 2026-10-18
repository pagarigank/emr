<?php

namespace App\Livewire\Er;

use App\Models\Er\ErBed;
use App\Models\Er\ErZone;
use Livewire\Component;
use Livewire\WithPagination;

class ErBedCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;
    
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    
    public string $code = '', $name = '', $status = 'Available';
    public ?int $er_zone_id = null;
    public bool $is_active = true;

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

    protected function rules(): array
    {
        $uniqueRule = 'unique:er_beds,code,NULL,id,tenant_id,' . ($this->currentTenantId() ?? 'NULL') . ',history,1';
        if ($this->isEditing && $this->editingId) {
            $uniqueRule = 'unique:er_beds,code,' . $this->editingId . ',id,tenant_id,' . ($this->currentTenantId() ?? 'NULL') . ',history,1';
        }
        return [
            'code' => ['required', 'string', 'max:20', $uniqueRule],
            'name' => ['required', 'string', 'max:100'],
            'er_zone_id' => ['nullable', 'exists:er_zones,id'],
            'status' => ['required', 'string', 'in:Available,Occupied,Maintenance'],
            'is_active' => ['boolean'],
        ];
    }

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
        $bed = ErBed::findOrFail($id);
        $this->editingId = $bed->id; 
        $this->code = $bed->code; 
        $this->name = $bed->name;
        $this->er_zone_id = $bed->er_zone_id;
        $this->status = $bed->status;
        $this->is_active = $bed->is_active; 
        $this->isEditing = true; 
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['er_zone_id'] = empty($data['er_zone_id']) ? null : $data['er_zone_id'];

        if ($this->isEditing) {
            ErBed::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'ER Bed updated successfully.');
        } else {
            ErBed::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'ER Bed created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        ErBed::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'ER Bed archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'er_zone_id']); 
        $this->status = 'Available';
        $this->is_active = true; 
        $this->resetValidation();
    }

    public function render()
    {
        $beds = ErBed::with('zone')
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $zones = ErZone::when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->orderBy('name')->get();

        return view('livewire.er.er-bed-crud', compact('beds', 'zones'));
    }
}
