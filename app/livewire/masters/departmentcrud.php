<?php

namespace App\Livewire\Masters;

use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentCrud extends Component
{
    use WithPagination;

    // ── Datagrid / Search ─────────────────────────────────────────────────────
    public string $search        = '';
    public string $sortField     = 'dept_code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    // ── Modal state ───────────────────────────────────────────────────────────
    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public bool $isEditing       = false;

    // ── Form fields ───────────────────────────────────────────────────────────
    public ?int   $editingId    = null;
    public string $dept_code    = '';
    public string $dept_name    = '';
    public string $description  = '';
    public bool   $is_active    = true;

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Resolve the current tenant from the JWT-injected request attribute.
     * Superadmin has tenant_id = null; all others have a concrete ID.
     */
    private function currentTenantId(): ?int
    {
        return request()->attributes->get('tenant_id'); // null for superadmin
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    private function isSuperAdmin(): bool
    {
        return (bool) request()->attributes->get('is_superadmin', false);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        // Unique check: dept_code must be unique within the tenant,
        // excluding the currently-edited record by its id.
        $uniqueRule = 'unique:departments,dept_code,NULL,id,tenant_id,' . ($this->currentTenantId() ?? 'NULL') . ',history,1';
        if ($this->isEditing && $this->editingId) {
            $uniqueRule = 'unique:departments,dept_code,' . $this->editingId . ',id,tenant_id,' . ($this->currentTenantId() ?? 'NULL') . ',history,1';
        }
        return [
            'dept_code'   => ['required', 'string', 'max:20', $uniqueRule],
            'dept_name'   => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'dept_code' => 'Department Code',
        'dept_name' => 'Department Name',
    ];

    // ── Datagrid helpers ──────────────────────────────────────────────────────

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // ── CRUD actions ──────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $dept              = Department::findOrFail($id);
        $this->editingId   = $dept->id;
        $this->dept_code   = $dept->dept_code;
        $this->dept_name   = $dept->dept_name;
        $this->description = $dept->description ?? '';
        $this->is_active   = $dept->is_active;
        $this->isEditing   = true;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->isEditing) {
            Department::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by'   => $this->currentUserId(),
                'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Department updated successfully.');
        } else {
            $dept = Department::create(array_merge($data, [
                'history'      => 1,
                'created_by'   => $this->currentUserId(),
                'created_date' => now(),
            ]));
            $dept->tenant_id = $this->currentTenantId();
            $dept->save();
            $this->dispatch('toast', type: 'success', message: 'Department created successfully.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId       = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Soft-delete: set history = 0 instead of physically removing the row.
     * Also records who archived it and when.
     */
    public function delete(): void
    {
        Department::findOrFail($this->editingId)->update([
            'history'      => 0,
            'updated_by'   => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Department archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'dept_code', 'dept_name', 'description']);
        $this->is_active = true;
        $this->resetValidation();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $departments = Department::query()
            ->when(! $this->isSuperAdmin(), fn ($q) =>
                $q->where('tenant_id', $this->currentTenantId())
            )
            // history=1 enforced by global scope
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('dept_code', 'like', '%' . $this->search . '%')
                          ->orWhere('dept_name', 'like', '%' . $this->search . '%')
                          ->orWhere('description', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.masters.department-crud', compact('departments'));
    }
}
