<?php

namespace App\Livewire\Pharmacy;

use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\DrugBatch;
use App\Models\Pharmacy\PharmacyRequest;
use Livewire\Component;

class PharmacyReports extends Component
{
    public string $reportType = 'expiry';
    public string $dateFrom = '';
    public string $dateTo = '';
    public ?int $drug_id = null;

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    public function render()
    {
        $tid = $this->currentTenantId();
        $drugs = Drug::where('tenant_id', $tid)->where('is_active', true)->orderBy('generic_name')->get();
        $data = collect();

        switch ($this->reportType) {
            case 'expiry':
                $data = DrugBatch::with('drug')
                    ->where('tenant_id', $tid)
                    ->where('quantity_on_hand', '>', 0)
                    ->orderBy('expiry_date')
                    ->get()
                    ->groupBy(fn($b) =>
                    $b->expiry_date < now() ? 'Expired' :
                        ($b->expiry_date <= now()->addDays(30) ? 'Expiring Soon (≤30 days)' :
                            ($b->expiry_date <= now()->addDays(90) ? 'Expiring (31-90 days)' : 'Good (>90 days)')));
                break;

            case 'stock-status':
                $allDrugs = Drug::with('batches')->where('tenant_id', $tid)->where('is_active', true)->get();
                $data = $allDrugs->map(fn($d) => [
                    'drug' => $d,
                    'total_stock' => $d->batches->sum('quantity_on_hand'),
                    'stock_value' => $d->batches->sum(fn($b) => $b->quantity_on_hand * $b->unit_cost),
                    'status' => $d->batches->sum('quantity_on_hand') <= $d->reorder_level ? 'Low Stock' : (
                        $d->batches->sum('quantity_on_hand') >= $d->reorder_level * 3 ? 'Over Stock' : 'Adequate'
                    ),
                ]);
                break;

            case 'consumption':
                $requests = PharmacyRequest::where('tenant_id', $tid)
                    ->where('status', 'Complete')
                    ->when($this->dateFrom, fn($q) => $q->whereDate('created_date', '>=', $this->dateFrom))
                    ->when($this->dateTo, fn($q) => $q->whereDate('created_date', '<=', $this->dateTo))
                    ->with('items')
                    ->get();

                $data = $requests->flatMap->items
                    ->groupBy('drug_id')
                    ->map(fn($items, $drugId) => [
                        'drug' => Drug::find($drugId),
                        'total_qty' => $items->sum('given_quantity'),
                        'total_cost' => $items->sum(fn($i) => $i->given_quantity * $i->unit_cost),
                        'request_count' => $items->count(),
                    ])
                    ->sortByDesc('total_qty')
                    ->values();
                break;
        }

        return view('livewire.pharmacy.pharmacy-reports', compact('data', 'drugs'));
    }
}
