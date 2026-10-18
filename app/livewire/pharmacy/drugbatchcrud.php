<?php

namespace App\Livewire\Pharmacy;

use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\DrugBatch;
use Livewire\Component;
use Livewire\WithPagination;

class DrugBatchCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'expiry_date', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    public ?string $drug_id = null;
    public string $batch_no = '', $expiry_date = '', $location = '';
    public string $quantity_on_hand = '0', $unit_cost = '0', $selling_price = '0';

    public string $drugSearch = '';
    public $drugSearchResults = [];
    public string $selectedDrugName = '';
    public bool $showDrugDropdown = false;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $drugId = $this->drug_id ?? 0;
        $u = "unique:drug_batches,batch_no,NULL,id,drug_id,{$drugId},tenant_id,{$tid}";
        if ($this->isEditing && $this->editingId)
            $u = "unique:drug_batches,batch_no,{$this->editingId},id,drug_id,{$drugId},tenant_id,{$tid}";
        return [
            'drug_id'         => ['required', 'integer', 'exists:drugs,id'],
            'batch_no'        => ['required', 'string', 'max:100', $u],
            'expiry_date'     => ['required', 'date'],
            'quantity_on_hand' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'unit_cost'       => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'selling_price'   => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'location'        => ['nullable', 'string', 'max:100'],
        ];
    }

    protected array $validationAttributes = [
        'drug_id' => 'Drug',
        'batch_no' => 'Batch No.',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function updatedDrugSearch(): void
    {
        $this->showDrugDropdown = strlen($this->drugSearch) > 0;
        if ($this->showDrugDropdown) {
            $this->drugSearchResults = Drug::query()
                ->where('is_active', 1)
                ->where(fn ($q) =>
                    $q->where('drug_code', 'like', '%' . $this->drugSearch . '%')
                      ->orWhere('generic_name', 'like', '%' . $this->drugSearch . '%')
                      ->orWhere('brand_name', 'like', '%' . $this->drugSearch . '%')
                )
                ->orderBy('generic_name')
                ->get(['id', 'drug_code', 'generic_name', 'brand_name'])
                ->toArray();
        } else {
            $this->drugSearchResults = [];
        }
    }

    public function selectDrug(int $id): void
    {
        $drug = Drug::find($id, ['id', 'drug_code', 'generic_name', 'brand_name']);
        if ($drug) {
            $this->drug_id = (string) $drug->id;
            $this->selectedDrugName = $drug->drug_name;
            $this->drugSearch = $drug->drug_code . ' — ' . $drug->drug_name;
            $this->showDrugDropdown = false;
            $this->drugSearchResults = [];
        }
    }

    public function openCreate(): void { $this->resetForm(); $this->isEditing = false; $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $r = DrugBatch::with('drug')->findOrFail($id);
        $this->editingId = $r->id;
        $this->drug_id = (string) $r->drug_id;
        $this->selectedDrugName = $r->drug?->drug_name ?? '';
        $this->drugSearch = ($r->drug?->drug_code ?? '') . ' — ' . $this->selectedDrugName;
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
            DrugBatch::findOrFail($this->editingId)->update([
                'drug_id' => $data['drug_id'],
                'batch_no' => $data['batch_no'],
                'expiry_date' => $data['expiry_date'],
                'quantity_on_hand' => $data['quantity_on_hand'],
                'unit_cost' => $data['unit_cost'] ?? 0,
                'selling_price' => $data['selling_price'] ?? 0,
                'location' => $data['location'] ?? '',
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $this->dispatch('toast', type: 'success', message: 'Drug batch updated successfully.');
        } else {
            DrugBatch::create([
                'tenant_id' => $tid,
                'drug_id' => $data['drug_id'],
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
            $this->dispatch('toast', type: 'success', message: 'Drug batch created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        DrugBatch::findOrFail($this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Drug batch archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'drug_id', 'batch_no', 'expiry_date', 'location', 'drugSearch', 'selectedDrugName']);
        $this->quantity_on_hand = '0';
        $this->unit_cost = '0';
        $this->selling_price = '0';
        $this->drugSearchResults = [];
        $this->showDrugDropdown = false;
        $this->resetValidation();
    }

    /**
     * FEFO: Get available batches for a drug ordered by expiry_date ASC.
     * Used by the dispensing component for batch selection.
     */
    public static function getAvailableBatches(int $drugId): array
    {
        return DrugBatch::withoutGlobalScopes()
            ->where('drug_id', $drugId)
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
        $batches = DrugBatch::query()
            ->with('drug')
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('batch_no', 'like', '%' . $this->search . '%')
                          ->orWhere('location', 'like', '%' . $this->search . '%')
                          ->orWhereHas('drug', fn ($d) =>
                              $d->where('drug_code', 'like', '%' . $this->search . '%')
                                ->orWhere('generic_name', 'like', '%' . $this->search . '%')
                                ->orWhere('brand_name', 'like', '%' . $this->search . '%')
                          )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.pharmacy.drug-batch-crud', compact('batches'));
    }
}
