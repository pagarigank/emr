<?php

namespace App\Livewire\Supply;

use App\Models\Supply\Item;
use App\Models\Supply\ItemCategory;
use App\Models\Supply\Supplier;
use App\Models\Supply\SupplyUnit;
use Livewire\Component;
use Livewire\WithPagination;

class ItemCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $description = '', $sku = '';
    public ?string $item_category_id = null, $supplier_id = null, $supply_unit_id = null;
    public string $unit_cost = '0';
    public int $reorder_level = 0, $reorder_qty = 0;
    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:items,code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:items,code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'code'            => ['required', 'string', 'max:30', $u],
            'name'            => ['required', 'string', 'max:200'],
            'description'     => ['nullable', 'string', 'max:500'],
            'sku'             => ['nullable', 'string', 'max:50'],
            'item_category_id' => ['nullable', 'integer', 'exists:item_categories,id'],
            'supplier_id'     => ['nullable', 'integer', 'exists:suppliers,id'],
            'supply_unit_id'  => ['nullable', 'integer', 'exists:supply_units,id'],
            'unit_cost'       => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'reorder_level'   => ['nullable', 'integer', 'min:0'],
            'reorder_qty'     => ['nullable', 'integer', 'min:0'],
            'is_active'       => ['boolean'],
        ];
    }

    protected array $validationAttributes = ['code' => 'Item Code', 'name' => 'Item Name'];

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
        $r = Item::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->description = $r->description ?? ''; $this->sku = $r->sku ?? '';
        $this->item_category_id = $r->item_category_id ? (string) $r->item_category_id : null;
        $this->supplier_id = $r->supplier_id ? (string) $r->supplier_id : null;
        $this->supply_unit_id = $r->supply_unit_id ? (string) $r->supply_unit_id : null;
        $this->unit_cost = (string) ($r->unit_cost ?? '0');
        $this->reorder_level = $r->reorder_level ?? 0;
        $this->reorder_qty = $r->reorder_qty ?? 0;
        $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            Item::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Item updated successfully.');
        } else {
            Item::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
                'updated_by' => $this->currentUserId(), 'updated_date' => null,
            ]));
            $this->dispatch('toast', type: 'success', message: 'Item created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Item::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Item archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'sku', 'item_category_id', 'supplier_id', 'supply_unit_id']);
        $this->unit_cost = '0'; $this->reorder_level = 0; $this->reorder_qty = 0;
        $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = Item::query()
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('sku', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = ItemCategory::query()->where('tenant_id', $this->currentTenantId())->orderBy('name')->get(['id', 'code', 'name']);
        $suppliers = Supplier::query()->where('tenant_id', $this->currentTenantId())->orderBy('name')->get(['id', 'code', 'name']);
        $units = SupplyUnit::query()->orderBy('name')->get(['id', 'code', 'name']);

        return view('livewire.supply.item-crud', compact('items', 'categories', 'suppliers', 'units'));
    }
}
