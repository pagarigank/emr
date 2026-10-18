<?php

namespace App\Livewire\Lab;

use App\Models\LabRequestItem;
use App\Models\LabTest;
use App\Models\ReferenceRange;
use Livewire\Component;
use Livewire\WithPagination;

class LabResultsCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'result_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public string $filterStatus = 'all';

    public bool $showDetailModal = false, $showResultModal = false, $showValidateModal = false;
    public ?int $viewingId = null, $resultItemId = null, $validateItemId = null;

    public ?string $result_value = null;
    public ?string $result_text = null;
    public ?string $result_categorical = null;
    public ?string $result_notes = null;

    public ?string $previewFlag = null;
    public ?string $previewRange = null;
    public ?string $previewUnit = null;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->viewingId = $id;
        $this->showDetailModal = true;
    }

    public function openResultEntry(int $itemId): void
    {
        $item = LabRequestItem::with(['labTest.referenceRanges', 'labTest.labUnit', 'labRequest.patient.gender'])->findOrFail($itemId);
        $this->resultItemId = $item->id;
        $this->result_value = $item->result_value;
        $this->result_text = $item->result_text;
        $this->result_categorical = $item->result_categorical;
        $this->result_notes = $item->result_notes;

        $this->computePreview($item);
        $this->showResultModal = true;
    }

    public function updatedResultValue(): void
    {
        if (!$this->resultItemId) return;
        $item = LabRequestItem::with(['labTest.referenceRanges', 'labTest.labUnit', 'labRequest.patient.gender'])->find($this->resultItemId);
        if ($item) $this->computePreview($item);
    }

    public function updatedResultCategorical(): void
    {
        $this->updatedResultValue();
    }

    private function computePreview($item): void
    {
        $this->previewFlag = null;
        $this->previewRange = null;
        $this->previewUnit = null;

        if (!$item->labTest) return;

        $this->previewUnit = $item->labTest->labUnit?->name;

        $range = $this->findMatchingRange($item);
        if ($range) {
            if ($range->min_value !== null && $range->max_value !== null) {
                $this->previewRange = $range->min_value . ' - ' . $range->max_value;
            } elseif ($range->normal_values_text) {
                $this->previewRange = $range->normal_values_text;
            }
        }

        $this->previewFlag = $this->computeFlag($item, $range);
    }

    private function findMatchingRange($item): ?ReferenceRange
    {
        $ranges = $item->labTest->referenceRanges;
        if ($ranges->isEmpty()) return null;

        $patient = $item->labRequest?->patient;
        $age = $patient?->date_of_birth?->age;
        $genderName = $patient?->gender?->name;
        $gender = match ($genderName) {
            'Male' => 'Male',
            'Female' => 'Female',
            default => null,
        };

        $matched = null;
        foreach ($ranges as $r) {
            $genderMatch = !$r->gender || $r->gender === 'Any' || $r->gender === $gender;
            $ageMatch = true;
            if ($r->age_min_years !== null && $age !== null) $ageMatch = $age >= $r->age_min_years;
            if ($r->age_max_years !== null && $age !== null) $ageMatch = $ageMatch && $age <= $r->age_max_years;
            if ($genderMatch && $ageMatch) { $matched = $r; break; }
        }

        return $matched ?? $ranges->first();
    }

    private function computeFlag($item, ?ReferenceRange $range): ?string
    {
        $value = $this->result_value ?? $item->result_value;
        if ($value === null || $value === '' || !$range) return null;

        if ($range->min_value !== null && $range->max_value !== null) {
            $num = (float) $value;
            if ($num < $range->min_value) return 'L';
            if ($num > $range->max_value) return 'H';
            return 'N';
        }

        if ($range->normal_values_text) {
            $cat = strtolower($this->result_categorical ?? $item->result_categorical ?? '');
            if (in_array($cat, ['positive', 'reactive', 'abnormal', 'high'])) return 'Abnormal';
            if (in_array($cat, ['negative', 'normal', 'non-reactive', 'low'])) return 'Normal';
        }

        return null;
    }

    public function saveResult(): void
    {
        $this->validate([
            'result_value' => 'nullable|string',
            'result_text' => 'nullable|string',
            'result_categorical' => 'nullable|string|max:100',
            'result_notes' => 'nullable|string',
        ]);

        $item = LabRequestItem::with(['labTest.referenceRanges', 'labTest.labUnit', 'labRequest.patient.gender'])->findOrFail($this->resultItemId);
        $uid = $this->currentUserId();

        $range = $this->findMatchingRange($item);
        $flag = $this->computeFlag($item, $range);
        $rangeText = null;
        if ($range) {
            if ($range->min_value !== null && $range->max_value !== null) {
                $rangeText = $range->min_value . ' - ' . $range->max_value;
            } elseif ($range->normal_values_text) {
                $rangeText = $range->normal_values_text;
            }
            if ($item->labTest->labUnit) {
                $rangeText .= ' ' . $item->labTest->labUnit->name;
            }
        }

        $item->update([
            'result_value' => $this->result_value,
            'result_text' => $this->result_text,
            'result_categorical' => $this->result_categorical,
            'result_notes' => $this->result_notes,
            'flag' => $flag,
            'reference_range' => $rangeText,
            'status' => 'Completed',
            'result_date' => now(),
            'performed_by' => $uid,
            'verified_by' => $uid,
            'verified_date' => now(),
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);

        $this->updateParentRequestStatus($item);

        if ($item->labRequest && $item->labRequest->status === 'Completed') {
            \App\Models\LabRequest::postChargeToLedger($item->labRequest, (float) $item->labRequest->total_charge, $uid);
        }

        $this->showResultModal = false;
        $this->dispatch('toast', type: 'success', message: 'Lab result saved successfully.');
    }

    public function openValidate(int $itemId): void
    {
        $this->validateItemId = $itemId;
        $this->showValidateModal = true;
    }

    public function validateResult(): void
    {
        $item = LabRequestItem::findOrFail($this->validateItemId);
        $uid = $this->currentUserId();

        $item->update([
            'validated_by' => $uid,
            'validated_at' => now(),
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);

        $this->showValidateModal = false;
        $this->dispatch('toast', type: 'success', message: 'Result validated successfully.');
    }

    public function printResult(int $itemId): void
    {
        $this->dispatch('printResult', itemId: $itemId);
    }

    private function updateParentRequestStatus($item): void
    {
        $uid = $this->currentUserId();
        $allStatuses = $item->labRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Completed') => 'Completed',
            $allStatuses->contains('Processing') || $allStatuses->contains('Collected') => 'Processing',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $updateData = ['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()];
        $item->labRequest->update($updateData);
    }

    public function render()
    {
        $items = LabRequestItem::query()
            ->with([
                'labRequest.patient',
                'labTest.labSection',
                'labTest.labUnit',
                'performedBy',
                'verifiedBy',
                'validatedBy',
            ])
            ->whereHas('labRequest', fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->when($this->filterStatus !== 'all', fn ($q) =>
                $q->where('status', match ($this->filterStatus) {
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'validated' => 'Completed',
                    default => 'Pending',
                })
            )
            ->when($this->filterStatus === 'validated', fn ($q) => $q->whereNotNull('validated_by'))
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('test_name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('labRequest', fn ($r) =>
                            $r->where('request_code', 'like', '%' . $this->search . '%')
                                ->orWhereHas('patient', fn ($p) =>
                                    $p->where('last_name', 'like', '%' . $this->search . '%')
                                        ->orWhere('first_name', 'like', '%' . $this->search . '%')
                                        ->orWhere('patient_code', 'like', '%' . $this->search . '%')
                                )
                        )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $detailItem = null;
        if ($this->showDetailModal && $this->viewingId) {
            $detailItem = LabRequestItem::with([
                'labRequest.patient.gender',
                'labRequest.requestingDoctor',
                'labTest.labSection',
                'labTest.labUnit',
                'labTest.referenceRanges',
                'performedBy',
                'verifiedBy',
                'validatedBy',
            ])->find($this->viewingId);
        }

        return view('livewire.lab.lab-results-crud', compact('items', 'detailItem'));
    }
}
