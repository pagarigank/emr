<?php

namespace App\Livewire\Supply;

use App\Models\Supply\ItemCategory;
use Livewire\Component;
use Livewire\WithPagination;

class ItemCategoryCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $description = '';
    public ?string $parent_id = null;
    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:item_categories,code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:item_categories,code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'code'        => ['required', 'string', 'max:20', $u],
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'parent_id'   => ['nullable', 'integer', 'exists:item_categories,id'],
            'is_active'   => ['boolean'],
        ];
    }

    protected array $validationAttributes = ['code' => 'Category Code', 'name' => 'Category Name'];

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
        $r = ItemCategory::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->description = $r->description ?? ''; $this->parent_id = $r->parent_id ? (string) $r->parent_id : null;
        $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            ItemCategory::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Category updated successfully.');
        } else {
            ItemCategory::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
                'updated_by' => $this->currentUserId(), 'updated_date' => null,
            ]));
            $this->dispatch('toast', type: 'success', message: 'Category created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        ItemCategory::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Category archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'parent_id']); $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = ItemCategory::query()
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $parentCategories = ItemCategory::query()
            ->where('tenant_id', $this->currentTenantId())
            ->where('id', '!=', $this->editingId)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('livewire.supply.item-category-crud', compact('items', 'parentCategories'));
    }
}
