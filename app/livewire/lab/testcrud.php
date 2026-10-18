<?php

namespace App\Livewire\Lab;

use App\Models\LabTest;
use App\Models\LabSection;
use App\Models\SpecimenType;
use App\Models\LabUnit;
use Livewire\Component;
use Livewire\WithPagination;

class TestCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $short_name = '', $test_type = 'Numeric', $description = '';
    public ?int $lab_section_id = null, $specimen_type_id = null, $lab_unit_id = null, $tat_minutes = null;
    public string $standard_charge = '0';
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
        $unique = 'unique:lab_tests,code,NULL,id,tenant_id,' . $tid . ',history,1';
        if ($this->isEditing && $this->editingId)
            $unique = 'unique:lab_tests,code,' . $this->editingId . ',id,tenant_id,' . $tid . ',history,1';
        return [
            'code'             => ['required', 'string', 'max:20', $unique],
            'name'             => ['required', 'string', 'max:100'],
            'short_name'       => ['nullable', 'string', 'max:50'],
            'lab_section_id'   => ['nullable', 'integer', 'exists:lab_sections,id'],
            'specimen_type_id' => ['nullable', 'integer', 'exists:specimen_types,id'],
            'lab_unit_id'      => ['nullable', 'integer', 'exists:lab_units,id'],
            'test_type'        => ['required', 'string', 'in:Numeric,Text,Categorical'],
            'tat_minutes'      => ['nullable', 'integer', 'min:0'],
            'standard_charge'  => ['nullable', 'numeric', 'min:0'],
            'description'      => ['nullable', 'string', 'max:255'],
            'is_active'        => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Test Code', 'name' => 'Test Name',
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
        $r = LabTest::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->short_name = $r->short_name ?? ''; $this->lab_section_id = $r->lab_section_id;
        $this->specimen_type_id = $r->specimen_type_id; $this->lab_unit_id = $r->lab_unit_id;
        $this->test_type = $r->test_type; $this->tat_minutes = $r->tat_minutes;
        $this->standard_charge = (string) ($r->standard_charge ?? 0);
        $this->description = $r->description ?? ''; $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            LabTest::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Test updated successfully.');
        } else {
            LabTest::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(), 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Test created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        LabTest::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Test archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'short_name', 'lab_section_id', 'specimen_type_id', 'lab_unit_id', 'test_type', 'tat_minutes', 'standard_charge', 'description']);
        $this->is_active = true; $this->test_type = 'Numeric'; $this->standard_charge = '0'; $this->resetValidation();
    }

    public function render()
    {
        $items = LabTest::query()
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('short_name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $labSections   = LabSection::orderBy('name')->get();
        $specimenTypes = SpecimenType::orderBy('name')->get();
        $labUnits      = LabUnit::orderBy('name')->get();

        return view('livewire.lab.test-crud', compact('items', 'labSections', 'specimenTypes', 'labUnits'));
    }
}
