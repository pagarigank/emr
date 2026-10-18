<?php

namespace App\Livewire\Pharmacy;

use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\DrugBatch;
use Livewire\Component;
use Livewire\WithPagination;

class DrugInventoryCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'generic_name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

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

    public function render()
    {
        $tid = $this->currentTenantId();

        $drugs = Drug::query()
            ->where('tenant_id', $tid)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('generic_name', 'like', '%' . $this->search . '%')
                    ->orWhere('brand_name', 'like', '%' . $this->search . '%')
                    ->orWhere('drug_code', 'like', '%' . $this->search . '%');
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $drugs->getCollection()->transform(function ($drug) {
            $batches = DrugBatch::withoutGlobalScopes()
                ->where('drug_id', $drug->id)
                ->where('history', 1)
                ->get();

            $drug->total_stock = $batches->sum('quantity_on_hand');
            $drug->batch_count = $batches->count();
            $drug->near_expiry_count = $batches->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now())
                ->where('quantity_on_hand', '>', 0)
                ->count();
            $drug->expired_count = $batches->where('expiry_date', '<', now())
                ->where('quantity_on_hand', '>', 0)
                ->count();
            $drug->avg_unit_cost = $batches->where('quantity_on_hand', '>', 0)->avg('unit_cost') ?? 0;
            $drug->stock_value = $batches->sum(fn($b) => $b->quantity_on_hand * $b->unit_cost);
            $drug->needs_reorder = $drug->total_stock <= $drug->reorder_level;

            return $drug;
        });

        $totals = [
            'total_drugs' => Drug::where('tenant_id', $tid)->count(),
            'total_batches' => DrugBatch::where('tenant_id', $tid)->count(),
            'total_stock_value' => DrugBatch::where('tenant_id', $tid)->where('quantity_on_hand', '>', 0)
                ->get()->sum(fn($b) => $b->quantity_on_hand * $b->unit_cost),
            'near_expiry' => DrugBatch::where('tenant_id', $tid)
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now())
                ->where('quantity_on_hand', '>', 0)
                ->count(),
            'expired' => DrugBatch::where('tenant_id', $tid)
                ->where('expiry_date', '<', now())
                ->where('quantity_on_hand', '>', 0)
                ->count(),
            'needs_reorder' => Drug::where('tenant_id', $tid)->where('is_active', true)->get()
                ->filter(fn($d) => DrugBatch::where('drug_id', $d->id)->sum('quantity_on_hand') <= $d->reorder_level)->count(),
        ];

        return view('livewire.pharmacy.drug-inventory-crud', compact('drugs', 'totals'));
    }
}
