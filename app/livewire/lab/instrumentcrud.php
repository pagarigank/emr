<?php

namespace App\Livewire\Lab;

use App\Models\LabInstrument;
use App\Models\LabSection;
use Livewire\Component;
use Livewire\WithPagination;

class InstrumentCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $model = '', $manufacturer = '', $serial_number = '';
    public ?int $lab_section_id = null;
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
        $tid = $this->currentTenantId() ?? 'NULL';
        $unique = 'unique:lab_instruments,code,NULL,id,tenant_id,' . $tid . ',history,1';
        if ($this->isEditing && $this->editingId)
            $unique = 'unique:lab_instruments,code,' . $this->editingId . ',id,tenant_id,' . $tid . ',history,1';
        return [
            'code'           => ['required', 'string', 'max:20', $unique],
            'name'           => ['required', 'string', 'max:100'],
            'model'          => ['nullable', 'string', 'max:100'],
            'manufacturer'   => ['nullable', 'string', 'max:100'],
            'lab_section_id' => ['nullable', 'integer', 'exists:lab_sections,id'],
            'serial_number'  => ['nullable', 'string', 'max:100'],
            'is_active'      => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Instrument Code', 'name' => 'Instrument Name',
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
        $r = LabInstrument::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->model = $r->model ?? ''; $this->manufacturer = $r->manufacturer ?? '';
        $this->lab_section_id = $r->lab_section_id; $this->serial_number = $r->serial_number ?? '';
        $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            LabInstrument::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Instrument updated successfully.');
        } else {
            LabInstrument::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(), 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Instrument created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        LabInstrument::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Instrument archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'model', 'manufacturer', 'lab_section_id', 'serial_number']);
        $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = LabInstrument::query()
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('model', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $labSections = LabSection::orderBy('name')->get();

        return view('livewire.lab.instrument-crud', compact('items', 'labSections'));
    }
}
