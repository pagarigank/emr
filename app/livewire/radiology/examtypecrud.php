<?php

namespace App\Livewire\Radiology;

use App\Models\Department;
use App\Models\Radiology\RadiologyBodyPart;
use App\Models\Radiology\RadiologyExamType;
use App\Models\Radiology\RadiologyModality;
use Livewire\Component;
use Livewire\WithPagination;

class ExamTypeCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $description = '';
    public ?int $modality_id = null, $body_part_id = null, $department_id = null;
    public string $standard_charge = '0', $preparation = '', $contrast_type = '';
    public ?int $tat_minutes = null;
    public bool $is_active = true;

    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    private function tenantId(): int { return request()->attributes->get('jwt_user')?->tenant_id ?? 1; }

    protected function rules(): array
    {
        $u = 'unique:radiology_exam_types,code,NULL,id,tenant_id,' . $this->tenantId() . ',history,1';
        if ($this->isEditing && $this->editingId)
            $u = 'unique:radiology_exam_types,code,' . $this->editingId . ',id,tenant_id,' . $this->tenantId() . ',history,1';
        return [
            'code'            => ['required', 'string', 'max:30', $u],
            'name'            => ['required', 'string', 'max:200'],
            'description'     => ['nullable', 'string', 'max:500'],
            'modality_id'     => ['nullable', 'integer', 'exists:radiology_modalities,id'],
            'body_part_id'    => ['nullable', 'integer', 'exists:radiology_body_parts,id'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'standard_charge' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'preparation'     => ['nullable', 'string', 'max:500'],
            'contrast_type'   => ['nullable', 'string', 'max:50'],
            'tat_minutes'     => ['nullable', 'integer', 'min:0'],
            'is_active'       => ['boolean'],
        ];
    }

    protected array $validationAttributes = ['code' => 'Exam Code', 'name' => 'Exam Name'];

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
        $r = RadiologyExamType::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->description = $r->description ?? ''; $this->modality_id = $r->modality_id;
        $this->body_part_id = $r->body_part_id; $this->department_id = $r->department_id;
        $this->standard_charge = (string) ($r->standard_charge ?? '0');
        $this->preparation = $r->preparation ?? ''; $this->contrast_type = $r->contrast_type ?? '';
        $this->tat_minutes = $r->tat_minutes; $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            RadiologyExamType::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Exam type updated successfully.');
        } else {
            RadiologyExamType::create(array_merge($data, [
                'tenant_id' => $this->tenantId(), 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Exam type created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        RadiologyExamType::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Exam type archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'modality_id', 'body_part_id', 'department_id', 'preparation', 'contrast_type', 'tat_minutes']);
        $this->standard_charge = '0'; $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = RadiologyExamType::query()
            ->with(['modality', 'bodyPart', 'department'])
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $modalities = RadiologyModality::query()->where('is_active', true)->orderBy('name')->get();
        $bodyParts  = RadiologyBodyPart::query()->where('is_active', true)->orderBy('name')->get();
        $departments = Department::query()->where('is_active', true)->orderBy('dept_name')->get();

        return view('livewire.radiology.exam-type-crud', compact('items', 'modalities', 'bodyParts', 'departments'));
    }
}
