<?php

namespace App\Livewire\Icu;

use App\Models\Icu\IcuBed;
use App\Models\Icu\IcuWard;
use Livewire\Component;
use Livewire\WithPagination;

class IcuBedCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '';
    public ?string $ward_id = null;
    public string $room_rate = '0.00';
    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:icu_beds,code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:icu_beds,code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'code'      => ['required', 'string', 'max:20', $u],
            'name'      => ['required', 'string', 'max:100'],
            'ward_id'   => ['nullable', 'integer', 'exists:icu_wards,id'],
            'room_rate' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    protected array $validationAttributes = ['code' => 'Bed Code', 'name' => 'Bed Name'];

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
        $r = IcuBed::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->ward_id = $r->ward_id ? (string) $r->ward_id : null;
        $this->room_rate = (string) $r->room_rate;
        $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            IcuBed::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'ICU bed updated successfully.');
        } else {
            IcuBed::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'ICU bed created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        IcuBed::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'ICU bed archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'ward_id']); 
        $this->room_rate = '0.00';
        $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = IcuBed::query()
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $wards = IcuWard::query()->where('tenant_id', $this->currentTenantId())->orderBy('name')->get(['id', 'code', 'name']);

        return view('livewire.icu.icu-bed-crud', compact('items', 'wards'));
    }
}
