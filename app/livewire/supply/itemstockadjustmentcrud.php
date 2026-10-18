<?php

namespace App\Livewire\Supply;

use App\Models\Supply\ItemStockAdjustment;
use App\Models\Supply\Item;
use App\Models\Supply\ItemBatch;
use Livewire\Component;
use Livewire\WithPagination;

class ItemStockAdjustmentCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_date';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;

    public string $adjustment_code = '';
    public string $adjustment_type = 'Write-off';
    public ?int $item_id = null;
    public ?int $item_batch_id = null;
    public string $quantity = '0';
    public string $unit_cost = '0';
    public string $reason = '';
    public ?string $reference_type = null;
    public ?string $reference_id = null;

    public string $item_search = '';
    public array $itemResults = [];
    public ?string $item_text = null;

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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

    public function generateCode(): string
    {
        $tid = $this->currentTenantId();
        $last = ItemStockAdjustment::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->adjustment_code, 4)) + 1 : 1;
        return 'ADJ-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->adjustment_code = $this->generateCode();
        $this->showModal = true;
    }

    public function updatedItemSearch(): void
    {
        if (strlen($this->item_search) < 2) {
            $this->itemResults = [];
            return;
        }
        $tid = $this->currentTenantId();
        $this->itemResults = Item::where('tenant_id', $tid)
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->item_search . '%')
                    ->orWhere('description', 'like', '%' . $this->item_search . '%')
                    ->orWhere('code', 'like', '%' . $this->item_search . '%');
            })
            ->limit(10)
            ->get(['id', 'code', 'name', 'description'])
            ->toArray();
    }

    public function selectItem(int $id): void
    {
        $item = Item::find($id);
        if (!$item) {
            return;
        }
        $this->item_id = $item->id;
        $this->item_text = $item->name . ' (' . $item->code . ')';
        $this->item_search = '';
        $this->itemResults = [];
        $this->item_batch_id = null;
    }

    public function removeItem(): void
    {
        $this->item_id = null;
        $this->item_text = null;
        $this->item_batch_id = null;
    }

    public function save(): void
    {
        $this->validate([
            'adjustment_type' => 'required|string|in:Write-off,Damage,Theft,Return,Transfer Out,Transfer In,Correction',
            'item_id' => 'required|integer|exists:items,id',
            'quantity' => 'required|numeric',
            'reason' => 'required|string|min:5',
        ]);

        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();
        $qty = (float) $this->quantity;

        if (in_array($this->adjustment_type, ['Write-off', 'Damage', 'Theft', 'Transfer Out'])) {
            $qty = -abs($qty);
        } else {
            $qty = abs($qty);
        }

        ItemStockAdjustment::create([
            'tenant_id' => $tid,
            'adjustment_code' => $this->adjustment_code,
            'item_id' => $this->item_id,
            'item_batch_id' => $this->item_batch_id,
            'adjustment_type' => $this->adjustment_type,
            'quantity' => $qty,
            'unit_cost' => $this->unit_cost ?: 0,
            'reason' => $this->reason,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        if ($this->item_batch_id) {
            $batch = ItemBatch::find($this->item_batch_id);
            if ($batch) {
                $batch->update([
                    'quantity_on_hand' => max(0, $batch->quantity_on_hand + $qty),
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
        }

        $this->dispatch('toast', type: 'success', message: 'Stock adjustment recorded successfully.');
        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $uid = $this->currentUserId();
        ItemStockAdjustment::findOrFail($this->editingId)->update([
            'history' => 0,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->showDeleteModal = false;
        $this->dispatch('toast', type: 'success', message: 'Adjustment record archived.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'adjustment_code', 'adjustment_type', 'item_id', 'item_batch_id', 'quantity', 'unit_cost', 'reason', 'reference_type', 'reference_id', 'item_search', 'item_text', 'itemResults']);
        $this->quantity = '0';
        $this->unit_cost = '0';
    }

    public function render()
    {
        $adjustments = ItemStockAdjustment::with(['item', 'itemBatch'])
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('adjustment_code', 'like', '%' . $this->search . '%')
                    ->orWhere('adjustment_type', 'like', '%' . $this->search . '%')
                    ->orWhere('reason', 'like', '%' . $this->search . '%');
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $batches = $this->item_id
            ? ItemBatch::where('item_id', $this->item_id)->orderBy('expiry_date')->get(['id', 'batch_no', 'quantity_on_hand', 'expiry_date', 'unit_cost'])
            : collect();

        return view('livewire.supply.item-stock-adjustment-crud', compact('adjustments', 'batches'));
    }
}
