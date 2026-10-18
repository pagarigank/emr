<?php

namespace App\Livewire\Pharmacy;

use App\Models\Pharmacy\StockAdjustment;
use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\DrugBatch;
use Livewire\Component;
use Livewire\WithPagination;

class StockAdjustmentCrud extends Component
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
    public ?int $drug_id = null;
    public ?int $drug_batch_id = null;
    public string $quantity = '0';
    public string $unit_cost = '0';
    public string $reason = '';
    public ?string $reference_type = null;
    public ?string $reference_id = null;

    public string $drug_search = '';
    public array $drugResults = [];
    public ?string $drug_text = null;

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
        $last = StockAdjustment::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->adjustment_code, 4)) + 1 : 1;
        return 'ADJ-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->adjustment_code = $this->generateCode();
        $this->showModal = true;
    }

    public function updatedDrugSearch(): void
    {
        if (strlen($this->drug_search) < 2) {
            $this->drugResults = [];
            return;
        }
        $tid = $this->currentTenantId();
        $this->drugResults = Drug::where('tenant_id', $tid)
            ->where(function ($q) {
                $q->where('generic_name', 'like', '%' . $this->drug_search . '%')
                    ->orWhere('brand_name', 'like', '%' . $this->drug_search . '%')
                    ->orWhere('drug_code', 'like', '%' . $this->drug_search . '%');
            })
            ->limit(10)
            ->get(['id', 'drug_code', 'generic_name', 'brand_name', 'drug_strength'])
            ->toArray();
    }

    public function selectDrug(int $id): void
    {
        $drug = Drug::find($id);
        if (!$drug) {
            return;
        }
        $this->drug_id = $drug->id;
        $this->drug_text = $drug->generic_name . ' (' . $drug->drug_code . ')' . ($drug->drug_strength ? ' - ' . $drug->drug_strength : '');
        $this->drug_search = '';
        $this->drugResults = [];
        $this->drug_batch_id = null;
    }

    public function removeDrug(): void
    {
        $this->drug_id = null;
        $this->drug_text = null;
        $this->drug_batch_id = null;
    }

    public function save(): void
    {
        $this->validate([
            'adjustment_type' => 'required|string|in:Write-off,Damage,Theft,Return,Transfer Out,Transfer In,Correction',
            'drug_id' => 'required|integer|exists:drugs,id',
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

        StockAdjustment::create([
            'tenant_id' => $tid,
            'adjustment_code' => $this->adjustment_code,
            'drug_id' => $this->drug_id,
            'drug_batch_id' => $this->drug_batch_id,
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

        if ($this->drug_batch_id) {
            $batch = DrugBatch::find($this->drug_batch_id);
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
        StockAdjustment::findOrFail($this->editingId)->update([
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
        $this->reset(['editingId', 'adjustment_code', 'adjustment_type', 'drug_id', 'drug_batch_id', 'quantity', 'unit_cost', 'reason', 'reference_type', 'reference_id', 'drug_search', 'drug_text', 'drugResults']);
        $this->quantity = '0';
        $this->unit_cost = '0';
    }

    public function render()
    {
        $adjustments = StockAdjustment::with(['drug', 'drugBatch'])
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('adjustment_code', 'like', '%' . $this->search . '%')
                    ->orWhere('adjustment_type', 'like', '%' . $this->search . '%')
                    ->orWhere('reason', 'like', '%' . $this->search . '%');
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $batches = $this->drug_id
            ? DrugBatch::where('drug_id', $this->drug_id)->orderBy('expiry_date')->get(['id', 'batch_no', 'quantity_on_hand', 'expiry_date', 'unit_cost'])
            : collect();

        return view('livewire.pharmacy.stock-adjustment-crud', compact('adjustments', 'batches'));
    }
}
