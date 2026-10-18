<?php

namespace App\Livewire\Pharmacy;

use App\Models\Pharmacy\Drug;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class DrugCrud extends Component
{
    use WithPagination;

    // ── Datagrid / Search ─────────────────────────────────────────────────────
    public string $search        = '';
    public string $sortField     = 'drug_code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    // ── Modal state ───────────────────────────────────────────────────────────
    public bool $showModal       = false;
    public bool $showDetailModal = false;
    public bool $showDeleteModal = false;
    public bool $isEditing       = false;

    // ── Form fields ───────────────────────────────────────────────────────────
    public ?int   $editingId          = null;
    public string $drug_code          = '';
    public string $generic_name       = '';
    public string $brand_name         = '';
    public string $drug_strength      = '';
    public string $dosage_form        = '';
    public string $therapeutic_class  = '';
    public string $drug_schedule      = '';
    public array  $route              = [];
    public string $active_ingredient  = '';
    public string $manufacturer       = '';
    public string $side_effects       = '';
    public string $contraindications  = '';
    public ?int   $reorder_level      = null;
    public ?int   $reorder_qty        = null;
    public bool   $is_active          = true;

    // ── Detail view ───────────────────────────────────────────────────────────
    public ?array $detailDrug = null;

    public array $dosageForms = [
        'Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Ointment',
        'Solution', 'Suspension', 'Drops', 'Inhaler', 'Patch', 'Suppository',
    ];

    public array $drugSchedules = [
        'OTC', 'Prescription', 'Controlled', 'Narcotic',
    ];

    public array $routeOptions = [
        'PO' => 'Oral',
        'IV' => 'Intravenous',
        'IM' => 'Intramuscular',
        'SC' => 'Subcutaneous',
        'SL' => 'Sublingual',
        'TOP' => 'Topical',
        'PR' => 'Rectal',
        'INH' => 'Inhalation',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function currentTenantId(): ?int
    {
        return request()->attributes->get('tenant_id');
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    private function isSuperAdmin(): bool
    {
        return (bool) request()->attributes->get('is_superadmin', false);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $uniqueRule = 'unique:drugs,drug_code,NULL,id,tenant_id,' . ($this->currentTenantId() ?? 'NULL') . ',history,1';
        if ($this->isEditing && $this->editingId) {
            $uniqueRule = 'unique:drugs,drug_code,' . $this->editingId . ',id,tenant_id,' . ($this->currentTenantId() ?? 'NULL') . ',history,1';
        }
        return [
            'drug_code'         => ['required', 'string', 'max:20', $uniqueRule],
            'generic_name'      => ['required', 'string', 'max:255'],
            'brand_name'        => ['nullable', 'string', 'max:255'],
            'drug_strength'     => ['nullable', 'string', 'max:100'],
            'dosage_form'       => ['nullable', 'string', 'max:50'],
            'therapeutic_class' => ['nullable', 'string', 'max:100'],
            'drug_schedule'     => ['nullable', 'string', 'max:20'],
            'route'             => ['nullable', 'array'],
            'active_ingredient' => ['nullable', 'string', 'max:255'],
            'manufacturer'      => ['nullable', 'string', 'max:255'],
            'side_effects'      => ['nullable', 'string'],
            'contraindications' => ['nullable', 'string'],
            'reorder_level'     => ['nullable', 'integer', 'min:0'],
            'reorder_qty'       => ['nullable', 'integer', 'min:0'],
            'is_active'         => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'drug_code'    => 'Drug Code',
        'generic_name' => 'Generic Name',
    ];

    // ── Datagrid helpers ──────────────────────────────────────────────────────

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // ── Drug code auto-generation ─────────────────────────────────────────────

    private function generateDrugCode(): string
    {
        $last = Drug::query()
            ->where('drug_code', 'like', 'DRG-%')
            ->orderByRaw("CAST(SUBSTRING(drug_code, 5, LEN(drug_code) - 4) AS INT) DESC")
            ->value('drug_code');

        $nextNumber = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'DRG-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    // ── CRUD actions ──────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetForm();
        $this->drug_code = $this->generateDrugCode();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $drug                   = Drug::findOrFail($id);
        $this->editingId        = $drug->id;
        $this->drug_code        = $drug->drug_code;
        $this->generic_name     = $drug->generic_name;
        $this->brand_name       = $drug->brand_name ?? '';
        $this->drug_strength    = $drug->drug_strength ?? '';
        $this->dosage_form      = $drug->dosage_form ?? '';
        $this->therapeutic_class = $drug->therapeutic_class ?? '';
        $this->drug_schedule    = $drug->drug_schedule ?? '';
        $this->route            = $drug->route ? explode(',', $drug->route) : [];
        $this->active_ingredient = $drug->active_ingredient ?? '';
        $this->manufacturer     = $drug->manufacturer ?? '';
        $this->side_effects     = $drug->side_effects ?? '';
        $this->contraindications = $drug->contraindications ?? '';
        $this->reorder_level    = $drug->reorder_level;
        $this->reorder_qty      = $drug->reorder_qty;
        $this->is_active        = $drug->is_active;
        $this->isEditing        = true;
        $this->showModal        = true;
    }

    public function openDetail(int $id): void
    {
        $drug = Drug::with(['batches' => fn ($q) => $q->selectRaw('drug_id, SUM(quantity_on_hand) as total_qty, AVG(unit_cost) as avg_cost')
            ->groupBy('drug_id')])
            ->findOrFail($id);

        $this->detailDrug = [
            'id'                => $drug->id,
            'drug_code'         => $drug->drug_code,
            'generic_name'      => $drug->generic_name,
            'brand_name'        => $drug->brand_name,
            'drug_strength'     => $drug->drug_strength,
            'dosage_form'       => $drug->dosage_form,
            'therapeutic_class' => $drug->therapeutic_class,
            'drug_schedule'     => $drug->drug_schedule,
            'route'             => $drug->route,
            'active_ingredient' => $drug->active_ingredient,
            'manufacturer'      => $drug->manufacturer,
            'side_effects'      => $drug->side_effects,
            'contraindications' => $drug->contraindications,
            'reorder_level'     => $drug->reorder_level,
            'reorder_qty'       => $drug->reorder_qty,
            'is_active'         => $drug->is_active,
            'stock_on_hand'     => $drug->batches->sum('total_qty'),
            'unit_cost'         => round($drug->batches->avg('avg_cost') ?? 0, 2),
        ];
        $this->showDetailModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['route'] = ! empty($this->route) ? implode(',', $this->route) : null;

        if ($this->isEditing) {
            Drug::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by'   => $this->currentUserId(),
                'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Drug updated successfully.');
        } else {
            Drug::create(array_merge($data, [
                'tenant_id'    => $this->currentTenantId(),
                'history'      => 1,
                'created_by'   => $this->currentUserId(),
                'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Drug created successfully.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId       = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Drug::findOrFail($this->editingId)->update([
            'history'      => 0,
            'updated_by'   => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Drug archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showDetailModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'drug_code', 'generic_name', 'brand_name', 'drug_strength',
            'dosage_form', 'therapeutic_class', 'drug_schedule', 'route',
            'active_ingredient', 'manufacturer', 'side_effects', 'contraindications',
            'reorder_level', 'reorder_qty',
        ]);
        $this->is_active = true;
        $this->resetValidation();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $drugs = Drug::query()
            ->select('drugs.*')
            ->selectSub(
                DB::table('drug_batches')
                    ->whereColumn('drug_id', 'drugs.id')
                    ->where('history', 1)
                    ->selectRaw('COALESCE(SUM(quantity_on_hand), 0)'),
                'stock_on_hand'
            )
            ->selectSub(
                DB::table('drug_batches')
                    ->whereColumn('drug_id', 'drugs.id')
                    ->where('history', 1)
                    ->selectRaw('COALESCE(AVG(unit_cost), 0)'),
                'unit_cost_avg'
            )
            ->when(! $this->isSuperAdmin(), fn ($q) =>
                $q->where('tenant_id', $this->currentTenantId())
            )
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('drug_code', 'like', '%' . $this->search . '%')
                          ->orWhere('generic_name', 'like', '%' . $this->search . '%')
                          ->orWhere('brand_name', 'like', '%' . $this->search . '%')
                          ->orWhere('therapeutic_class', 'like', '%' . $this->search . '%')
                          ->orWhere('drug_strength', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.pharmacy.drug-crud', compact('drugs'));
    }
}
