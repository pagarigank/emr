<?php

namespace App\Livewire\Supply;

use App\Models\Supply\Item;
use App\Models\Supply\ItemBatch;
use Livewire\Component;
use Livewire\WithPagination;

class ItemBatchCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'expiry_date', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    public ?string $item_id = null;
    public string $batch_no = '', $expiry_date = '', $location = '';
    public string $quantity_on_hand = '0', $unit_cost = '0', $selling_price = '0';

    public string $itemSearch = '';
    public $itemSearchResults = [];
    public string $selectedItemName = '';
    public bool $showItemDropdown = false;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $itemId = $this->item_id ?? 0;
        $u = "unique:item_batches,batch_no,NULL,id,item_id,{$itemId},tenant_id,{$tid}";
        if ($this->isEditing && $this->editingId)
            $u = "unique:item_batches,batch_no,{$this->editingId},id,item_id,{$itemId},tenant_id,{$tid}";
        return [
            'item_id'         => ['required', 'integer', 'exists:items,id'],
            'batch_no'        => ['required', 'string', 'max:100', $u],
            'expiry_date'     => ['required', 'date'],
            'quantity_on_hand' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'unit_cost'       => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'selling_price'   => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'location'        => ['nullable', 'string', 'max:100'],
        ];
    }

    protected array $validationAttributes = [
        'item_id' => 'Item',
        'batch_no' => 'Batch No.',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function updatedItemSearch(): void
    {
        $this->showItemDropdown = strlen($this->itemSearch) > 0;
        if ($this->showItemDropdown) {
            $this->itemSearchResults = Item::query()
                ->where('is_active', 1)
                ->where(fn ($q) =>
                    $q->where('code', 'like', '%' . $this->itemSearch . '%')
                      ->orWhere('name', 'like', '%' . $this->itemSearch . '%')
                      ->orWhere('description', 'like', '%' . $this->itemSearch . '%')
                )
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'description'])
                ->toArray();
        } else {
            $this->itemSearchResults = [];
        }
    }

    public function selectItem(int $id): void
    {
        $item = Item::find($id, ['id', 'code', 'name', 'description']);
        if ($item) {
            $this->item_id = (string) $item->id;
            $this->selectedItemName = $item->name;
            $this->itemSearch = $item->code . ' — ' . $item->name;
            $this->showItemDropdown = false;
            $this->itemSearchResults = [];
        }
    }

    public function openCreate(): void { $this->resetForm(); $this->isEditing = false; $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $r = ItemBatch::with('item')->findOrFail($id);
        $this->editingId = $r->id;
        $this->item_id = (string) $r->item_id;
        $this->selectedItemName = $r->item?->name ?? '';
        $this->itemSearch = ($r->item?->code ?? '') . ' — ' . $this->selectedItemName;
        $this->batch_no = $r->batch_no;
        $this->expiry_date = $r->expiry_date ? $r->expiry_date->format('Y-m-d') : '';
        $this->quantity_on_hand = (string) ($r->quantity_on_hand ?? '0');
        $this->unit_cost = (string) ($r->unit_cost ?? '0');
        $this->selling_price = (string) ($r->selling_price ?? '0');
        $this->location = $r->location ?? '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        if ($this->isEditing) {
            ItemBatch::findOrFail($this->editingId)->update([
                'item_id' => $data['item_id'],
                'batch_no' => $data['batch_no'],
                'expiry_date' => $data['expiry_date'],
                'quantity_on_hand' => $data['quantity_on_hand'],
                'unit_cost' => $data['unit_cost'] ?? 0,
                'selling_price' => $data['selling_price'] ?? 0,
                'location' => $data['location'] ?? '',
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $this->dispatch('toast', type: 'success', message: 'Item batch updated successfully.');
        } else {
            ItemBatch::create([
                'tenant_id' => $tid,
                'item_id' => $data['item_id'],
                'batch_no' => $data['batch_no'],
                'expiry_date' => $data['expiry_date'],
                'quantity_on_hand' => $data['quantity_on_hand'],
                'unit_cost' => $data['unit_cost'] ?? 0,
                'selling_price' => $data['selling_price'] ?? 0,
                'location' => $data['location'] ?? '',
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $this->dispatch('toast', type: 'success', message: 'Item batch created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        ItemBatch::findOrFail($this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Item batch archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'item_id', 'batch_no', 'expiry_date', 'location', 'itemSearch', 'selectedItemName']);
        $this->quantity_on_hand = '0';
        $this->unit_cost = '0';
        $this->selling_price = '0';
        $this->itemSearchResults = [];
        $this->showItemDropdown = false;
        $this->resetValidation();
    }

    /**
     * FEFO: Get available batches for a item ordered by expiry_date ASC.
     * Used by the dispensing component for batch selection.
     */
    public static function getAvailableBatches(int $itemId): array
    {
        return ItemBatch::withoutGlobalScopes()
            ->where('item_id', $itemId)
            ->where('tenant_id', request()->attributes->get('tenant_id') ?? 1)
            ->where('history', 1)
            ->where('is_active', true)
            ->where('quantity_on_hand', '>', 0)
            ->where('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->get(['id', 'batch_no', 'expiry_date', 'quantity_on_hand', 'unit_cost', 'selling_price'])
            ->toArray();
    }

    public function render()
    {
        $batches = ItemBatch::query()
            ->with('item')
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('batch_no', 'like', '%' . $this->search . '%')
                          ->orWhere('location', 'like', '%' . $this->search . '%')
                          ->orWhereHas('item', fn ($d) =>
                              $d->where('code', 'like', '%' . $this->search . '%')
                                ->orWhere('name', 'like', '%' . $this->search . '%')
                                ->orWhere('description', 'like', '%' . $this->search . '%')
                          )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.supply.item-batch-crud', compact('batches'));
    }
}
