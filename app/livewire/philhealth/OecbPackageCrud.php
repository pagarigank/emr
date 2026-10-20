<?php

namespace App\Livewire\Philhealth;

use App\Models\Oecb\EeclItem;
use App\Models\Oecb\OecbCorePresentation;
use App\Models\Oecb\OecbFeeSchedule;
use App\Models\LabTest;
use App\Models\Pharmacy\Drug;
use App\Models\Radiology\RadiologyExamType;
use App\Models\Procedures\Procedure;
use App\Models\Supply\Item as SupplyItem;
use Livewire\Component;
use Livewire\WithPagination;

class OecbPackageCrud extends Component
{
    use WithPagination;

    public string $activeTab = 'packages'; // 'packages' or 'mapping'
    public string $search = '';
    public int $perPage = 15;

    // Package Editing
    public bool $showPackageModal = false;
    public ?int $editingFeeScheduleId = null;
    public string $package_code = '';
    public string $presentation_name = '';
    public string $description = '';
    public string $fixed_fee = '0.00';
    public bool $is_active = true;

    // Mapping Tool State
    public string $mappingCatalog = 'drugs'; // 'drugs', 'lab_tests', 'radiology', 'supplies', 'procedures'
    public string $mappingSearch = '';
    public string $mappingFilter = 'all'; // 'all', 'mapped', 'unmapped'

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMappingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMappingCatalog(): void
    {
        $this->resetPage();
    }

    public function updatingMappingFilter(): void
    {
        $this->resetPage();
    }

    // ——— Package Management ———
    public function openEditPackage(int $feeScheduleId): void
    {
        $fee = OecbFeeSchedule::with('corePresentation')->findOrFail($feeScheduleId);
        $this->editingFeeScheduleId = $fee->id;
        $this->package_code = $fee->package_code;
        $this->presentation_name = $fee->corePresentation?->name ?? 'Core Presentation';
        $this->description = $fee->description ?? '';
        $this->fixed_fee = (string) $fee->fixed_fee;
        $this->is_active = (bool) $fee->is_active;

        $this->showPackageModal = true;
    }

    public function savePackage(): void
    {
        $this->validate([
            'package_code' => ['required', 'string', 'max:30'],
            'fixed_fee' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingFeeScheduleId) {
            $fee = OecbFeeSchedule::findOrFail($this->editingFeeScheduleId);
            $fee->update([
                'package_code' => trim($this->package_code),
                'fixed_fee' => (float) $this->fixed_fee,
                'description' => trim($this->description),
                'is_active' => $this->is_active,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);

            $this->dispatch('toast', type: 'success', message: 'OECB fee schedule updated successfully.');
        }

        $this->closePackageModal();
    }

    public function closePackageModal(): void
    {
        $this->showPackageModal = false;
        $this->reset(['editingFeeScheduleId', 'package_code', 'presentation_name', 'description', 'fixed_fee', 'is_active']);
    }

    // ——— 1-Click Interactive Mapping Actions ———
    public function updateItemMapping(string $catalog, int $itemId, ?string $eeclItemId): void
    {
        $eeclId = !empty($eeclItemId) ? (int) $eeclItemId : null;
        $uid = $this->currentUserId();
        $now = now();

        switch ($catalog) {
            case 'drugs':
                Drug::where('id', $itemId)->update(['eecl_item_id' => $eeclId, 'updated_by' => $uid, 'updated_date' => $now]);
                break;
            case 'lab_tests':
                LabTest::where('id', $itemId)->update(['eecl_item_id' => $eeclId, 'updated_by' => $uid, 'updated_date' => $now]);
                break;
            case 'radiology':
                RadiologyExamType::where('id', $itemId)->update(['eecl_item_id' => $eeclId, 'updated_by' => $uid, 'updated_date' => $now]);
                break;
            case 'supplies':
                SupplyItem::where('id', $itemId)->update(['eecl_item_id' => $eeclId, 'updated_by' => $uid, 'updated_date' => $now]);
                break;
            case 'procedures':
                Procedure::where('id', $itemId)->update(['eecl_item_id' => $eeclId, 'updated_by' => $uid, 'updated_date' => $now]);
                break;
        }

        $this->dispatch('toast', type: 'success', message: 'Mapping updated successfully.');
    }

    public function autoMatchCatalog(): void
    {
        $matchedCount = 0;
        $uid = $this->currentUserId();
        $now = now();

        $eeclItems = EeclItem::where('is_active', true)->get();

        switch ($this->mappingCatalog) {
            case 'drugs':
                $drugs = Drug::whereNull('eecl_item_id')->get();
                $medsEecl = $eeclItems->where('category', 'Medicines');
                foreach ($drugs as $drug) {
                    $generic = strtolower($drug->generic_name ?? '');
                    foreach ($medsEecl as $eecl) {
                        $eeclName = strtolower($eecl->item_name);
                        // Substring or common drug root match
                        $root = explode(' ', $generic)[0] ?? '';
                        if (strlen($root) >= 4 && str_contains($eeclName, $root)) {
                            $drug->update(['eecl_item_id' => $eecl->id, 'updated_by' => $uid, 'updated_date' => $now]);
                            $matchedCount++;
                            break;
                        }
                    }
                }
                break;

            case 'lab_tests':
                $labs = LabTest::whereNull('eecl_item_id')->get();
                $labEecl = $eeclItems->where('category', 'Diagnostic Services');
                foreach ($labs as $lab) {
                    $labName = strtolower($lab->name ?? '');
                    foreach ($labEecl as $eecl) {
                        $eeclName = strtolower($eecl->item_name);
                        if (str_contains($labName, 'cbc') && str_contains($eeclName, 'complete blood count')) {
                            $lab->update(['eecl_item_id' => $eecl->id, 'updated_by' => $uid, 'updated_date' => $now]);
                            $matchedCount++;
                            break;
                        } elseif (str_contains($labName, 'urinalysis') && str_contains($eeclName, 'urinalysis')) {
                            $lab->update(['eecl_item_id' => $eecl->id, 'updated_by' => $uid, 'updated_date' => $now]);
                            $matchedCount++;
                            break;
                        } elseif (str_contains($labName, 'fecalysis') && str_contains($eeclName, 'fecalysis')) {
                            $lab->update(['eecl_item_id' => $eecl->id, 'updated_by' => $uid, 'updated_date' => $now]);
                            $matchedCount++;
                            break;
                        } else {
                            $words = array_filter(explode(' ', $labName), fn ($w) => strlen($w) > 3);
                            foreach ($words as $w) {
                                if (str_contains($eeclName, $w)) {
                                    $lab->update(['eecl_item_id' => $eecl->id, 'updated_by' => $uid, 'updated_date' => $now]);
                                    $matchedCount++;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                break;

            case 'radiology':
                $exams = RadiologyExamType::whereNull('eecl_item_id')->get();
                $radEecl = $eeclItems->where('category', 'Imaging Services');
                foreach ($exams as $exam) {
                    $examName = strtolower($exam->name ?? '');
                    foreach ($radEecl as $eecl) {
                        $eeclName = strtolower($eecl->item_name);
                        if ((str_contains($examName, 'chest') && str_contains($eeclName, 'chest')) ||
                            (str_contains($examName, 'ecg') && str_contains($eeclName, 'ecg')) ||
                            (str_contains($examName, 'ultrasound') && str_contains($eeclName, 'ultrasound'))) {
                            $exam->update(['eecl_item_id' => $eecl->id, 'updated_by' => $uid, 'updated_date' => $now]);
                            $matchedCount++;
                            break;
                        }
                    }
                }
                break;

            case 'supplies':
                $supplies = SupplyItem::whereNull('eecl_item_id')->get();
                $supEecl = $eeclItems->where('category', 'Supplies');
                foreach ($supplies as $item) {
                    $itemName = strtolower($item->name ?? '');
                    foreach ($supEecl as $eecl) {
                        $eeclName = strtolower($eecl->item_name);
                        $words = array_filter(explode(' ', $itemName), fn ($w) => strlen($w) > 4);
                        foreach ($words as $w) {
                            if (str_contains($eeclName, $w)) {
                                $item->update(['eecl_item_id' => $eecl->id, 'updated_by' => $uid, 'updated_date' => $now]);
                                $matchedCount++;
                                break 2;
                            }
                        }
                    }
                }
                break;

            case 'procedures':
                $procs = Procedure::whereNull('eecl_item_id')->get();
                $procEecl = $eeclItems->where('category', 'ER Services');
                foreach ($procs as $p) {
                    $procName = strtolower($p->name ?? '');
                    foreach ($procEecl as $eecl) {
                        $eeclName = strtolower($eecl->item_name);
                        $words = array_filter(explode(' ', $procName), fn ($w) => strlen($w) > 4);
                        foreach ($words as $w) {
                            if (str_contains($eeclName, $w)) {
                                $p->update(['eecl_item_id' => $eecl->id, 'updated_by' => $uid, 'updated_date' => $now]);
                                $matchedCount++;
                                break 2;
                            }
                        }
                    }
                }
                break;
        }

        if ($matchedCount > 0) {
            $this->dispatch('toast', type: 'success', message: "Smart Auto-Match linked {$matchedCount} item(s) to PhilHealth EECL codes!");
        } else {
            $this->dispatch('toast', type: 'info', message: 'No new automatic matches found. You can map items manually below.');
        }
    }

    public function render()
    {
        // 1. Fee Schedules / Core Presentations
        $feeSchedules = OecbFeeSchedule::with('corePresentation')
            ->where('history', 1)
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('package_code', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('corePresentation', fn ($cp) => $cp->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->get();

        // 2. Mapping Items Query
        $catalogItems = collect();
        $targetCategory = match($this->mappingCatalog) {
            'drugs' => 'Medicines',
            'lab_tests' => 'Diagnostic Services',
            'radiology' => 'Imaging Services',
            'supplies' => 'Supplies',
            'procedures' => 'ER Services',
            default => 'Medicines'
        };

        $availableEeclOptions = EeclItem::where('is_active', true)
            ->where('category', $targetCategory)
            ->orderBy('item_name')
            ->get(['id', 'item_code', 'item_name', 'fixed_fee']);

        if ($this->activeTab === 'mapping') {
            switch ($this->mappingCatalog) {
                case 'drugs':
                    $q = Drug::query()->with('eeclItem')
                        ->when($this->mappingSearch, fn($query) => $query->where('generic_name', 'like', '%' . $this->mappingSearch . '%')->orWhere('brand_name', 'like', '%' . $this->mappingSearch . '%'))
                        ->when($this->mappingFilter === 'mapped', fn($query) => $query->whereNotNull('eecl_item_id'))
                        ->when($this->mappingFilter === 'unmapped', fn($query) => $query->whereNull('eecl_item_id'))
                        ->orderBy('generic_name');
                    $catalogItems = $q->paginate($this->perPage);
                    break;

                case 'lab_tests':
                    $q = LabTest::query()->with('eeclItem')
                        ->when($this->mappingSearch, fn($query) => $query->where('name', 'like', '%' . $this->mappingSearch . '%')->orWhere('code', 'like', '%' . $this->mappingSearch . '%'))
                        ->when($this->mappingFilter === 'mapped', fn($query) => $query->whereNotNull('eecl_item_id'))
                        ->when($this->mappingFilter === 'unmapped', fn($query) => $query->whereNull('eecl_item_id'))
                        ->orderBy('name');
                    $catalogItems = $q->paginate($this->perPage);
                    break;

                case 'radiology':
                    $q = RadiologyExamType::query()->with(['eeclItem', 'modality'])
                        ->when($this->mappingSearch, fn($query) => $query->where('name', 'like', '%' . $this->mappingSearch . '%')->orWhere('code', 'like', '%' . $this->mappingSearch . '%'))
                        ->when($this->mappingFilter === 'mapped', fn($query) => $query->whereNotNull('eecl_item_id'))
                        ->when($this->mappingFilter === 'unmapped', fn($query) => $query->whereNull('eecl_item_id'))
                        ->orderBy('name');
                    $catalogItems = $q->paginate($this->perPage);
                    break;

                case 'supplies':
                    $q = SupplyItem::query()->with('eeclItem')
                        ->when($this->mappingSearch, fn($query) => $query->where('name', 'like', '%' . $this->mappingSearch . '%')->orWhere('code', 'like', '%' . $this->mappingSearch . '%'))
                        ->when($this->mappingFilter === 'mapped', fn($query) => $query->whereNotNull('eecl_item_id'))
                        ->when($this->mappingFilter === 'unmapped', fn($query) => $query->whereNull('eecl_item_id'))
                        ->orderBy('name');
                    $catalogItems = $q->paginate($this->perPage);
                    break;

                case 'procedures':
                    $q = Procedure::query()->with('eeclItem')
                        ->when($this->mappingSearch, fn($query) => $query->where('name', 'like', '%' . $this->mappingSearch . '%'))
                        ->when($this->mappingFilter === 'mapped', fn($query) => $query->whereNotNull('eecl_item_id'))
                        ->when($this->mappingFilter === 'unmapped', fn($query) => $query->whereNull('eecl_item_id'))
                        ->orderBy('name');
                    $catalogItems = $q->paginate($this->perPage);
                    break;
            }
        }

        // Mapping stats
        $mappingStats = [
            'drugs_mapped' => Drug::whereNotNull('eecl_item_id')->count(),
            'drugs_total' => Drug::count(),
            'labs_mapped' => LabTest::whereNotNull('eecl_item_id')->count(),
            'labs_total' => LabTest::count(),
            'radiology_mapped' => RadiologyExamType::whereNotNull('eecl_item_id')->count(),
            'radiology_total' => RadiologyExamType::count(),
            'supplies_mapped' => SupplyItem::whereNotNull('eecl_item_id')->count(),
            'supplies_total' => SupplyItem::count(),
            'procedures_mapped' => Procedure::whereNotNull('eecl_item_id')->count(),
            'procedures_total' => Procedure::count(),
        ];

        return view('livewire.philhealth.oecb-package-crud', [
            'feeSchedules' => $feeSchedules,
            'catalogItems' => $catalogItems,
            'availableEeclOptions' => $availableEeclOptions,
            'mappingStats' => $mappingStats,
        ])->layout('components.layouts.app', ['title' => 'OECB Packages & EECL Mapping — PhilHealth']);
    }
}
