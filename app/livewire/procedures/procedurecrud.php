<?php

namespace App\Livewire\Procedures;

use App\Models\Department;
use App\Models\Procedures\Procedure;
use App\Models\Procedures\ProcedureCategory;
use Livewire\Component;
use Livewire\WithPagination;

class ProcedureCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $description = '', $standard_charge = '0';
    public ?string $category_id = null, $department_id = null;
    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:procedures,code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:procedures,code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'code'            => ['required', 'string', 'max:30', $u],
            'name'            => ['required', 'string', 'max:200'],
            'description'     => ['nullable', 'string', 'max:500'],
            'category_id'     => ['nullable', 'integer', 'exists:procedure_categories,id'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'standard_charge' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'is_active'       => ['boolean'],
        ];
    }

    protected array $validationAttributes = ['code' => 'Procedure Code', 'name' => 'Procedure Name'];

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
        $r = Procedure::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->description = $r->description ?? '';
        $this->category_id = $r->category_id ? (string) $r->category_id : null;
        $this->department_id = $r->department_id ? (string) $r->department_id : null;
        $this->standard_charge = (string) ($r->standard_charge ?? '0');
        $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            Procedure::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Procedure updated successfully.');
        } else {
            Procedure::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Procedure created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Procedure::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Procedure archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'category_id', 'department_id']);
        $this->standard_charge = '0'; $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = Procedure::query()
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = ProcedureCategory::query()->orderBy('name')->get(['id', 'code', 'name']);
        $departments = Department::query()->where('tenant_id', $this->currentTenantId())->orderBy('dept_name')->get(['id', 'dept_code', 'dept_name']);

        return view('livewire.procedures.procedure-crud', compact('items', 'categories', 'departments'));
    }
}
