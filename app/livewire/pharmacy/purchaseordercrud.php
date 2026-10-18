<?php

namespace App\Livewire\Pharmacy;

use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\DrugBatch;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\PurchaseOrderItem;
use App\Models\Pharmacy\StockAdjustment;
use App\Models\Supply\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showReceiveModal = false, $showDetailModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null, $viewingId = null, $receivingId = null;

    public string $po_code = '', $order_date = '', $expected_date = '', $notes = '';
    public ?string $supplier_id = null;
    public array $lineItems = [];
    public string $total_amount = '0';

    public array $receiveItems = [];

    public string $drugSearch = '';
    public array $drugSearchResults = [];
    public bool $showDrugDropdown = false;
    public ?int $selectedDrugId = null;
    public string $selectedDrugName = '';
    public string $selectedDrugCode = '';

    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        return [
            'supplier_id'               => ['required', 'integer', 'exists:suppliers,id'],
            'order_date'                => ['required', 'date'],
            'expected_date'             => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes'                     => ['nullable', 'string', 'max:2000'],
            'lineItems'                 => ['required', 'array', 'min:1'],
            'lineItems.*.drug_id'       => ['required', 'integer', 'exists:drugs,id'],
            'lineItems.*.drug_name'     => ['required', 'string', 'max:200'],
            'lineItems.*.quantity_ordered' => ['required', 'numeric', 'min:0.01'],
            'lineItems.*.unit_cost'     => ['required', 'numeric', 'min:0'],
        ];
    }

    protected array $validationAttributes = [
        'lineItems' => 'Line Items',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function generatePOCode(): string
    {
        $tid = $this->currentTenantId();
        $last = PurchaseOrder::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->po_code, 3)) + 1 : 1;
        return 'PO-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->po_code = $this->generatePOCode();
        $this->order_date = now()->format('Y-m-d');
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);
        $this->editingId = $po->id;
        $this->po_code = $po->po_code;
        $this->supplier_id = (string) $po->supplier_id;
        $this->order_date = $po->order_date ? $po->order_date->format('Y-m-d') : now()->format('Y-m-d');
        $this->expected_date = $po->expected_date ? $po->expected_date->format('Y-m-d') : '';
        $this->notes = $po->notes ?? '';
        $this->lineItems = $po->items->map(fn ($i) => [
            'drug_id' => $i->drug_id,
            'drug_name' => $i->drug?->generic_name ?? ($i->drug?->drug_name ?? ''),
            'drug_code' => $i->drug?->drug_code ?? '',
            'quantity_ordered' => (string) $i->quantity_ordered,
            'unit_cost' => (string) $i->unit_cost,
            'total_cost' => (string) $i->total_cost,
        ])->toArray();
        $this->recalculateTotal();
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function viewDetail(int $id): void
    {
        $this->viewingId = $id;
        $this->showDetailModal = true;
    }

    public function updatedDrugSearch(): void
    {
        $this->showDrugDropdown = strlen($this->drugSearch) > 0;
        if ($this->showDrugDropdown) {
            $this->drugSearchResults = Drug::query()
                ->where('is_active', 1)
                ->where(fn ($q) =>
                    $q->where('drug_code', 'like', '%' . $this->drugSearch . '%')
                      ->orWhere('drug_name', 'like', '%' . $this->drugSearch . '%')
                      ->orWhere('generic_name', 'like', '%' . $this->drugSearch . '%')
                      ->orWhere('brand_name', 'like', '%' . $this->drugSearch . '%')
                )
                ->orderBy('generic_name')
                ->get(['id', 'drug_code', 'drug_name', 'generic_name', 'brand_name', 'drug_strength'])
                ->toArray();
        } else {
            $this->drugSearchResults = [];
        }
    }

    public function selectDrug(int $id): void
    {
        $drug = Drug::find($id, ['id', 'drug_code', 'drug_name', 'generic_name', 'brand_name', 'drug_strength']);
        if ($drug) {
            $this->selectedDrugId = $drug->id;
            $this->selectedDrugName = $drug->generic_name ?: $drug->drug_name;
            $this->selectedDrugCode = $drug->drug_code;
            $display = $drug->drug_code . ' — ' . $this->selectedDrugName;
            if ($drug->drug_strength) $display .= ' (' . $drug->drug_strength . ')';
            $this->drugSearch = $display;
            $this->showDrugDropdown = false;
            $this->drugSearchResults = [];
        }
    }

    public function addItem(): void
    {
        if (!$this->selectedDrugId) return;
        $drug = Drug::find($this->selectedDrugId);
        if (!$drug) return;
        $this->lineItems[] = [
            'drug_id' => $drug->id,
            'drug_name' => $drug->generic_name ?: $drug->drug_name,
            'drug_code' => $drug->drug_code,
            'quantity_ordered' => '1',
            'unit_cost' => '0',
        ];
        $this->selectedDrugId = null;
        $this->selectedDrugName = '';
        $this->selectedDrugCode = '';
        $this->drugSearch = '';
        $this->showDrugDropdown = false;
        $this->recalculateTotal();
    }

    public function removeItem(int $index): void
    {
        unset($this->lineItems[$index]);
        $this->lineItems = array_values($this->lineItems);
        $this->recalculateTotal();
    }

    public function recalculateTotal(): void
    {
        $total = 0;
        foreach ($this->lineItems as $item) {
            $total += (float) ($item['quantity_ordered'] ?? 0) * (float) ($item['unit_cost'] ?? 0);
        }
        $this->total_amount = number_format($total, 2, '.', '');
    }

    public function updatedLineItems(): void
    {
        $this->recalculateTotal();
    }

    public function save(): void
    {
        $data = $this->validate();
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        if ($this->isEditing) {
            $po = PurchaseOrder::findOrFail($this->editingId);
            $po->update([
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'],
                'notes' => $data['notes'],
                'total_amount' => $this->total_amount,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $po->items()->delete();
            foreach ($data['lineItems'] as $li) {
                $po->items()->create([
                    'drug_id' => $li['drug_id'],
                    'quantity_ordered' => $li['quantity_ordered'],
                    'unit_cost' => $li['unit_cost'],
                    'total_cost' => (float) $li['quantity_ordered'] * (float) $li['unit_cost'],
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Purchase order updated successfully.');
        } else {
            $po = PurchaseOrder::create([
                'tenant_id' => $tid,
                'po_code' => $this->po_code,
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'],
                'status' => 'Draft',
                'total_amount' => $this->total_amount,
                'notes' => $data['notes'],
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            foreach ($data['lineItems'] as $li) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'drug_id' => $li['drug_id'],
                    'quantity_ordered' => $li['quantity_ordered'],
                    'unit_cost' => $li['unit_cost'],
                    'total_cost' => (float) $li['quantity_ordered'] * (float) $li['unit_cost'],
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Purchase order created successfully.');
        }
        $this->closeModal();
    }

    public function submitForApproval(int $id): void
    {
        $uid = $this->currentUserId();
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'Draft') return;
        $po->update([
            'status' => 'Submitted',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: 'Purchase order submitted for approval.');
    }

    public function approve(int $id): void
    {
        $uid = $this->currentUserId();
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'Submitted') return;
        $po->update([
            'status' => 'Approved',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: 'Purchase order approved.');
    }

    public function openReceive(int $id): void
    {
        $po = PurchaseOrder::with('items.drug')->findOrFail($id);
        if (!in_array($po->status, ['Approved', 'Partial'])) return;
        $this->receivingId = $id;
        $this->receiveItems = [];
        foreach ($po->items as $item) {
            $remaining = max(0, $item->quantity_ordered - $item->quantity_received);
            if ($remaining <= 0) continue;
            $this->receiveItems[$item->id] = [
                'drug_name' => $item->drug?->generic_name ?: ($item->drug?->drug_name ?? ''),
                'drug_code' => $item->drug?->drug_code ?? '',
                'quantity_ordered' => (float) $item->quantity_ordered,
                'quantity_received' => (float) $item->quantity_received,
                'remaining' => $remaining,
                'qty_received' => (string) $remaining,
                'batch_no' => '',
                'expiry_date' => '',
                'unit_cost' => (float) $item->unit_cost,
            ];
        }
        $this->showReceiveModal = true;
    }

    public function processReceive(): void
    {
        $this->validate([
            'receiveItems' => ['required', 'array', 'min:1'],
            'receiveItems.*.qty_received' => ['required', 'numeric', 'min:0.01'],
            'receiveItems.*.batch_no' => ['required', 'string', 'max:50'],
            'receiveItems.*.expiry_date' => ['required', 'date'],
        ]);

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();
        $now = now();
        $po = PurchaseOrder::with('items')->findOrFail($this->receivingId);
        $allFullyReceived = true;
        $anyReceived = false;

        foreach ($this->receiveItems as $itemId => $receive) {
            $qty = (float) $receive['qty_received'];
            if ($qty <= 0) continue;
            $anyReceived = true;

            $poItem = $po->items->firstWhere('id', $itemId);
            if (!$poItem) continue;

            $newQtyReceived = $poItem->quantity_received + $qty;

            $poItem->update([
                'quantity_received' => $newQtyReceived,
                'batch_no' => $receive['batch_no'],
                'expiry_date' => $receive['expiry_date'],
                'total_cost' => $newQtyReceived * $poItem->unit_cost,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);

            $batch = DrugBatch::withoutGlobalScopes()
                ->where('drug_id', $poItem->drug_id)
                ->where('batch_no', $receive['batch_no'])
                ->where('tenant_id', $tid)
                ->where('history', 1)
                ->first();

            if ($batch) {
                $batch->increment('quantity_on_hand', $qty);
                $batch->update([
                    'expiry_date' => $receive['expiry_date'],
                    'unit_cost' => $poItem->unit_cost,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            } else {
                $batch = DrugBatch::create([
                    'tenant_id' => $tid,
                    'drug_id' => $poItem->drug_id,
                    'batch_no' => $receive['batch_no'],
                    'expiry_date' => $receive['expiry_date'],
                    'quantity_on_hand' => $qty,
                    'unit_cost' => $poItem->unit_cost,
                    'selling_price' => 0,
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }

            StockAdjustment::create([
                'tenant_id' => $tid,
                'adjustment_code' => 'ADJ-PO-' . $po->po_code . '-' . $itemId,
                'drug_id' => $poItem->drug_id,
                'drug_batch_id' => $batch->id,
                'adjustment_type' => 'Received',
                'quantity' => $qty,
                'unit_cost' => $poItem->unit_cost,
                'reason' => 'Purchase Order receipt — ' . $po->po_code,
                'reference_type' => 'PO',
                'reference_id' => $po->id,
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);

            if ($newQtyReceived < $poItem->quantity_ordered) {
                $allFullyReceived = false;
            }
        }

        if (!$anyReceived) {
            $this->dispatch('toast', type: 'warning', message: 'No quantities entered. Enter at least one item quantity to receive.');
            return;
        }

        $poStatus = $allFullyReceived ? 'Received' : 'Partial';
        $po->update([
            'status' => $poStatus,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->showReceiveModal = false;
        $this->receivingId = null;
        $this->receiveItems = [];
        $this->dispatch('toast', type: 'success', message: 'Purchase order received successfully.');
    }

    public function cancel(int $id): void
    {
        $uid = $this->currentUserId();
        $po = PurchaseOrder::findOrFail($id);
        if (!in_array($po->status, ['Draft', 'Submitted', 'Approved'])) return;
        $po->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $po->items()->update([
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: 'Purchase order cancelled.');
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        $this->cancel($this->editingId);
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showReceiveModal = false;
        $this->showDetailModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'viewingId', 'receivingId', 'po_code', 'supplier_id',
            'order_date', 'expected_date', 'notes', 'lineItems', 'receiveItems',
            'drugSearch', 'selectedDrugId', 'selectedDrugName', 'selectedDrugCode',
            'drugSearchResults', 'showDrugDropdown',
        ]);
        $this->total_amount = '0';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $orders = PurchaseOrder::query()
            ->with(['supplier', 'items'])
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('po_code', 'like', '%' . $this->search . '%')
                          ->orWhere('notes', 'like', '%' . $this->search . '%')
                          ->orWhereHas('supplier', fn ($s) =>
                              $s->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('code', 'like', '%' . $this->search . '%')
                          )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $suppliers = Supplier::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('history', 1)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $detailOrder = null;
        if ($this->showDetailModal && $this->viewingId) {
            $detailOrder = PurchaseOrder::with(['supplier', 'items.drug'])
                ->find($this->viewingId);
        }

        $receiveOrder = null;
        if ($this->showReceiveModal && $this->receivingId) {
            $receiveOrder = PurchaseOrder::with(['supplier', 'items.drug'])
                ->find($this->receivingId);
        }

        return view('livewire.pharmacy.purchase-order-crud', compact(
            'orders', 'suppliers', 'detailOrder', 'receiveOrder'
        ));
    }
}
