<?php

namespace App\Livewire\RenalCare;

use App\Models\RenalCare\DialysisMachine;
use Livewire\Component;
use Livewire\WithPagination;

class DialysisMachineCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $categoryFilter = '';
    public string $sortField = 'station_code';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    // Form fields
    public string $station_code = '';
    public string $station_name = '';
    public ?string $serial_number = '';
    public ?string $brand = '';
    public ?string $model_name = '';
    public string $station_category = 'Regular';
    public string $status = 'Available';
    public ?string $notes = '';
    public bool $is_active = true;

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        return [
            'station_code'     => ['required', 'string', 'max:30'],
            'station_name'     => ['required', 'string', 'max:100'],
            'serial_number'    => ['nullable', 'string', 'max:100'],
            'brand'            => ['nullable', 'string', 'max:100'],
            'model_name'       => ['nullable', 'string', 'max:100'],
            'station_category' => ['required', 'string', 'in:Regular,HepB_Isolation,HepC_Isolation,ICU_Mobile'],
            'status'           => ['required', 'string', 'in:Available,Occupied,Disinfecting,Maintenance,Offline'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'is_active'        => ['boolean'],
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }

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
        $this->station_code = $this->generateStationCode();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $m = DialysisMachine::findOrFail($id);
        $this->editingId = $m->id;
        $this->station_code = $m->station_code;
        $this->station_name = $m->station_name;
        $this->serial_number = $m->serial_number ?? '';
        $this->brand = $m->brand ?? '';
        $this->model_name = $m->model_name ?? '';
        $this->station_category = $m->station_category;
        $this->status = $m->status;
        $this->notes = $m->notes ?? '';
        $this->is_active = (bool) $m->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();

        if ($this->isEditing) {
            DialysisMachine::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $uid,
                'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Dialysis station updated successfully.');
        } else {
            DialysisMachine::create(array_merge($data, [
                'tenant_id' => $tid,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => now(),
                'updated_by' => $uid,
                'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Dialysis station created successfully.');
        }

        $this->closeModal();
    }

    public function updateStatus(int $id, string $status): void
    {
        $m = DialysisMachine::findOrFail($id);
        $m->update([
            'status' => $status,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: "Station {$m->station_code} status set to {$status}.");
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        DialysisMachine::findOrFail($this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Dialysis station archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'station_code', 'station_name', 'serial_number',
            'brand', 'model_name', 'notes',
        ]);
        $this->station_category = 'Regular';
        $this->status = 'Available';
        $this->is_active = true;
        $this->resetValidation();
    }

    private function generateStationCode(): string
    {
        $last = DialysisMachine::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) preg_replace('/\D/', '', $last->station_code)) + 1 : 1;
        return 'ST-' . str_pad((string) $num, 2, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $tid = $this->currentTenantId();

        $query = DialysisMachine::query()
            ->where('tenant_id', $tid)
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($inner) use ($s) {
                    $inner->where('station_code', 'like', $s)
                        ->orWhere('station_name', 'like', $s)
                        ->orWhere('brand', 'like', $s)
                        ->orWhere('model_name', 'like', $s)
                        ->orWhere('serial_number', 'like', $s);
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('station_category', $this->categoryFilter))
            ->orderBy($this->sortField, $this->sortDirection);

        $items = $query->paginate($this->perPage);

        // Summary KPI Counts
        $allMachines = DialysisMachine::where('tenant_id', $tid)->get();
        $stats = [
            'total'        => $allMachines->count(),
            'available'    => $allMachines->where('status', 'Available')->count(),
            'occupied'     => $allMachines->where('status', 'Occupied')->count(),
            'disinfecting' => $allMachines->where('status', 'Disinfecting')->count(),
            'maintenance'  => $allMachines->whereIn('status', ['Maintenance', 'Offline'])->count(),
            'isolation'    => $allMachines->whereIn('station_category', ['HepB_Isolation', 'HepC_Isolation'])->count(),
        ];

        return view('livewire.renal-care.dialysis-machine-crud', compact('items', 'stats'));
    }
}
