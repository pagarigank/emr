<?php

namespace App\Livewire\Ward;

use App\Models\Department;
use App\Models\Ward\Ward;
use App\Models\Ward\WardRoom;
use App\Models\Ward\WardRoomType;
use Livewire\Component;
use Livewire\WithPagination;

class WardRoomCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '';
    public ?string $ward_id = null, $room_type_id = null, $department_id = null;
    public int $capacity = 1;
    public string $room_rate = '0', $extra_bed_rate = '', $discount_rate = '';
    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:ward_rooms,code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:ward_rooms,code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'code'          => ['required', 'string', 'max:20', $u],
            'name'          => ['required', 'string', 'max:100'],
            'ward_id'       => ['nullable', 'integer', 'exists:wards,id'],
            'room_type_id'  => ['nullable', 'integer', 'exists:ward_room_types,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'capacity'      => ['required', 'integer', 'min:1'],
            'room_rate'     => ['required', 'numeric', 'min:0'],
            'extra_bed_rate'=> ['nullable', 'numeric', 'min:0'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active'     => ['boolean'],
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
        $r = WardRoom::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->ward_id = $r->ward_id ? (string) $r->ward_id : null;
        $this->room_type_id = $r->room_type_id ? (string) $r->room_type_id : null;
        $this->department_id = $r->department_id ? (string) $r->department_id : null;
        $this->capacity = $r->capacity;
        $this->room_rate = (string) ($r->room_rate ?? 0);
        $this->extra_bed_rate = $r->extra_bed_rate !== null ? (string) $r->extra_bed_rate : '';
        $this->discount_rate = $r->discount_rate !== null ? (string) $r->discount_rate : '';
        $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            WardRoom::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Room updated successfully.');
        } else {
            WardRoom::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Room created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        WardRoom::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Room archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'ward_id', 'room_type_id', 'department_id', 'extra_bed_rate', 'discount_rate']);
        $this->capacity = 1; $this->room_rate = '0'; $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = WardRoom::query()
            ->with(['ward', 'roomType', 'department'])
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $wards = Ward::query()->where('tenant_id', $this->currentTenantId())->orderBy('name')->get(['id', 'code', 'name']);
        $roomTypes = WardRoomType::query()->orderBy('name')->get(['id', 'code', 'name']);
        $departments = Department::withoutGlobalScopes()->where('tenant_id', $this->currentTenantId())->orderBy('dept_name')->get(['id', 'dept_code', 'dept_name']);

        return view('livewire.ward.ward-room-crud', compact('items', 'wards', 'roomTypes', 'departments'));
    }
}
