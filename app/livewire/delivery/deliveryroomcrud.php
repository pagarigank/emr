<?php

namespace App\Livewire\Delivery;

use App\Models\Delivery\DeliveryRoom;
use Livewire\Component;
use Livewire\WithPagination;

class DeliveryRoomCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $location = '', $room_type = '', $equipment = '', $special_requirements = '', $status = 'Available';
    public int $capacity = 1;
    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:dr_rooms,code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:dr_rooms,code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'code'                => ['required', 'string', 'max:20', $u],
            'name'                => ['required', 'string', 'max:100'],
            'location'            => ['nullable', 'string', 'max:100'],
            'room_type'           => ['nullable', 'string', 'max:50'],
            'capacity'            => ['required', 'integer', 'min:1'],
            'equipment'           => ['nullable', 'string'],
            'special_requirements' => ['nullable', 'string'],
            'status'              => ['required', 'string', 'max:30'],
            'is_active'           => ['boolean'],
        ];
    }

    protected array $validationAttributes = ['code' => 'Room Code', 'name' => 'Room Name'];

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
        $r = DeliveryRoom::findOrFail($id);
        $this->editingId = $r->id;
        $this->code = $r->code; $this->name = $r->name;
        $this->location = $r->location ?? '';
        $this->room_type = $r->room_type ?? '';
        $this->capacity = $r->capacity;
        $this->equipment = $r->equipment ?? '';
        $this->special_requirements = $r->special_requirements ?? '';
        $this->status = $r->status;
        $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            DeliveryRoom::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'DR room updated successfully.');
        } else {
            DeliveryRoom::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
                'updated_by' => $this->currentUserId(), 'updated_date' => null,
            ]));
            $this->dispatch('toast', type: 'success', message: 'DR room created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        DeliveryRoom::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'DR room archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'location', 'room_type', 'equipment', 'special_requirements']);
        $this->capacity = 1; $this->status = 'Available'; $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = DeliveryRoom::query()
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('location', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.delivery.delivery-room-crud', compact('items'));
    }
}
