<?php

namespace App\Livewire\Philhealth;

use App\Models\ClaimItem;
use App\Models\ClaimProcedure;
use App\Models\PhilhealthClaim;
use Livewire\Component;

class ClaimItems extends Component
{
    public ?int $claimId = null;
    public array $items = [];
    public float $totalActual = 0;
    public float $totalClaimed = 0;
    public bool $showModal = false;
    public function mount(?int $claimId = null): void
    {
        if ($claimId) {
            $this->claimId = $claimId;
            $this->loadItems();
        }
    }

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    #[\Livewire\Attributes\On('open-items')]
    public function openItems(int $claimId): void
    {
        $this->claimId = $claimId;
        $this->loadItems();
        $this->showModal = true;
    }

    #[\Livewire\Attributes\On('items-imported')]
    public function receiveImportedItems(array $importedItems): void
    {
        foreach ($importedItems as $item) {
            $this->items[] = [
                'item_type' => $item['item_type'] ?? 'Others',
                'item_code' => $item['item_code'] ?? '',
                'item_name' => $item['item_name'] ?? '',
                'description' => $item['description'] ?? '',
                'quantity' => (float) ($item['quantity'] ?? 1),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'actual_amount' => (float) ($item['actual_amount'] ?? 0),
                'claimed_amount' => (float) ($item['claimed_amount'] ?? 0),
            ];
        }
        $this->recalculateTotals();
        $this->dispatch('toast', type: 'success', message: count($importedItems) . ' items imported.');
    }

    public function loadItems(): void
    {
        if (!$this->claimId) return;

        $dbItems = ClaimItem::where('philhealth_claim_id', $this->claimId)
            ->where('history', 1)
            ->orderBy('id')
            ->get();

        $this->items = [];
        foreach ($dbItems as $item) {
            $this->items[] = [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'item_code' => $item->item_code ?? '',
                'item_name' => $item->item_name,
                'description' => $item->description ?? '',
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'actual_amount' => (float) $item->actual_amount,
                'claimed_amount' => (float) $item->claimed_amount,
            ];
        }
        $this->recalculateTotals();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'item_type' => 'Others',
            'item_code' => '',
            'item_name' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'actual_amount' => 0,
            'claimed_amount' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalculateTotals();
    }

    public function updatedItems(): void
    {
        $this->recalculateTotals();
    }

    private function recalculateTotals(): void
    {
        $actual = 0;
        $claimed = 0;
        foreach ($this->items as $item) {
            $actual += (float) ($item['actual_amount'] ?? 0);
            $claimed += (float) ($item['claimed_amount'] ?? 0);
        }
        $this->totalActual = $actual;
        $this->totalClaimed = $claimed;
    }

    public function save(): void
    {
        if (!$this->claimId) {
            $this->dispatch('toast', type: 'error', message: 'No claim selected.');
            return;
        }

        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) {
            $this->dispatch('toast', type: 'error', message: 'Claim not found.');
            return;
        }

        $existingIds = ClaimItem::where('philhealth_claim_id', $this->claimId)->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($this->items as $item) {
            $itemData = [
                'tenant_id' => $claim->tenant_id,
                'philhealth_claim_id' => $this->claimId,
                'item_type' => $item['item_type'],
                'item_code' => $item['item_code'] ?? null,
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'actual_amount' => $item['actual_amount'],
                'claimed_amount' => $item['claimed_amount'],
                'history' => 1,
            ];

            if (isset($item['id']) && in_array($item['id'], $existingIds)) {
                ClaimItem::findOrFail($item['id'])->update(array_merge($itemData, [
                    'updated_by' => $this->currentUserId(),
                    'updated_date' => now(),
                ]));
                $submittedIds[] = $item['id'];
            } else {
                $new = ClaimItem::create(array_merge($itemData, [
                    'created_by' => $this->currentUserId(),
                    'created_date' => now(),
                ]));
                $submittedIds[] = $new->id;
            }
        }

        $toArchive = array_diff($existingIds, $submittedIds);
        if (!empty($toArchive)) {
            ClaimItem::whereIn('id', $toArchive)->update([
                'history' => 0,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);
        }

        $claim->update([
            'total_actual_amount' => $this->totalActual,
            'total_claimed_amount' => $this->totalClaimed,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->syncProcedures($claim);

        $this->dispatch('toast', type: 'success', message: 'Claim items saved.');
        $this->dispatch('items-saved', totals: ['actual' => $this->totalActual, 'claimed' => $this->totalClaimed]);
        $this->showModal = false;
    }

    private function syncProcedures(PhilhealthClaim $claim): void
    {
        $existingProcIds = ClaimProcedure::where('philhealth_claim_id', $claim->id)->pluck('id')->toArray();
        $keptIds = [];
        $seq = 1;

        foreach ($this->items as $item) {
            if (($item['item_type'] ?? '') !== 'Procedure') continue;
            if (empty($item['item_code'])) continue;

            $procData = [
                'tenant_id' => $claim->tenant_id,
                'philhealth_claim_id' => $claim->id,
                'sequence' => $seq++,
                'procedure_description' => $item['item_name'] ?? '',
                'rvs_code' => $item['item_code'],
                'procedure_date' => $claim->date_admitted,
                'is_active' => 1,
                'history' => 1,
            ];

            if (isset($item['procedure_id']) && in_array($item['procedure_id'], $existingProcIds)) {
                ClaimProcedure::findOrFail($item['procedure_id'])->update(array_merge($procData, [
                    'updated_by' => $this->currentUserId(),
                    'updated_date' => now(),
                ]));
                $keptIds[] = $item['procedure_id'];
            } else {
                $new = ClaimProcedure::create(array_merge($procData, [
                    'created_by' => $this->currentUserId(),
                    'created_date' => now(),
                ]));
                $keptIds[] = $new->id;
            }
        }

        $toArchive = array_diff($existingProcIds, $keptIds);
        if (!empty($toArchive)) {
            ClaimProcedure::whereIn('id', $toArchive)->update([
                'history' => 0,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);
        }
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.philhealth.claim-items');
    }
}
