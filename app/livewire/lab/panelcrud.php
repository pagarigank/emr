<?php

namespace App\Livewire\Lab;

use App\Models\LabPanel;
use App\Models\LabSection;
use App\Models\LabTest;
use Livewire\Component;
use Livewire\WithPagination;

class PanelCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $description = '';
    public ?int $lab_section_id = null, $tat_minutes = null;
    public string $standard_charge = '0';
    public bool $is_active = true;
    public array $selectedTests = [];

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
        $unique = 'unique:lab_panels,code,NULL,id,tenant_id,' . $tid . ',history,1';
        if ($this->isEditing && $this->editingId)
            $unique = 'unique:lab_panels,code,' . $this->editingId . ',id,tenant_id,' . $tid . ',history,1';
        return [
            'code'           => ['required', 'string', 'max:20', $unique],
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:255'],
            'lab_section_id' => ['nullable', 'integer', 'exists:lab_sections,id'],
            'tat_minutes'    => ['nullable', 'integer', 'min:0'],
            'standard_charge' => ['nullable', 'numeric', 'min:0'],
            'is_active'      => ['boolean'],
            'selectedTests'  => ['array'],
            'selectedTests.*' => ['integer', 'exists:lab_tests,id'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Panel Code', 'name' => 'Panel Name',
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
        $r = LabPanel::with('tests')->findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->description = $r->description ?? ''; $this->lab_section_id = $r->lab_section_id;
        $this->tat_minutes = $r->tat_minutes;
        $this->standard_charge = (string) ($r->standard_charge ?? 0);
        $this->is_active = $r->is_active;
        $this->selectedTests = $r->tests->pluck('id')->toArray();
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            $panel = LabPanel::findOrFail($this->editingId);
            $panel->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $panel->tests()->sync($this->selectedTests);
            $this->dispatch('toast', type: 'success', message: 'Panel updated successfully.');
        } else {
            $panel = LabPanel::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(), 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $panel->tests()->sync($this->selectedTests);
            $this->dispatch('toast', type: 'success', message: 'Panel created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        LabPanel::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Panel archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'lab_section_id', 'tat_minutes', 'standard_charge', 'selectedTests']);
        $this->is_active = true; $this->standard_charge = '0'; $this->resetValidation();
    }

    public function render()
    {
        $items = LabPanel::query()
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $labSections = LabSection::orderBy('name')->get();
        $labTests    = LabTest::orderBy('name')->get();

        return view('livewire.lab.panel-crud', compact('items', 'labSections', 'labTests'));
    }
}
