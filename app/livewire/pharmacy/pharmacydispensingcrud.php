<?php

namespace App\Livewire\Pharmacy;

use App\Models\Department;
use App\Models\HealthCareProfessional;
use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\DrugBatch;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Pharmacy\PharmacyRequestItem;
use App\Models\Pharmacy\StockAdjustment;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Er\ErAdmissionCrud;

class PharmacyDispensingCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $showDetailModal = false, $showDispenseModal = false, $isEditing = false;
    public ?int $editingId = null, $viewingId = null, $dispensingId = null;

    public string $request_code = '', $priority = 'Routine', $request_notes = '';
    public ?string $requesting_department_id = null, $requesting_person = null, $requesting_doctor_id = null;

    public array $lineItems = [];
    public string $total_amount = '0';
    public array $dispensingQuantities = [];
    public array $dispensingBatchSelections = [];

    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        return [
            'requesting_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'requesting_person'        => ['nullable', 'string', 'max:150'],
            'requesting_doctor_id'     => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'priority'                => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'request_notes'           => ['nullable', 'string'],
            'lineItems'               => ['required', 'array', 'min:1'],
            'lineItems.*.drug_id'     => ['required', 'integer', 'exists:drugs,id'],
            'lineItems.*.item_name'   => ['required', 'string', 'max:200'],
            'lineItems.*.dosage'      => ['nullable', 'string', 'max:100'],
            'lineItems.*.frequency'   => ['nullable', 'string', 'max:100'],
            'lineItems.*.route'       => ['nullable', 'string', 'max:100'],
            'lineItems.*.duration'    => ['nullable', 'string', 'max:100'],
            'lineItems.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'lineItems.*.unit_cost'   => ['required', 'numeric', 'min:0'],
        ];
    }

    protected array $validationAttributes = [
        'lineItems' => 'Medication Items',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function generateRequestCode(): string
    {
        $tid = $this->currentTenantId();
        $last = PharmacyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'PR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->request_code = $this->generateRequestCode();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $r = PharmacyRequest::with('items')->findOrFail($id);
        $this->editingId = $r->id;
        $this->request_code = $r->request_code;
        $this->requesting_department_id = $r->requesting_department_id ? (string) $r->requesting_department_id : null;
        $this->requesting_person = $r->requesting_person;
        $this->requesting_doctor_id = $r->requesting_doctor_id ? (string) $r->requesting_doctor_id : null;
        $this->priority = $r->priority;
        $this->request_notes = $r->notes ?? '';
        $this->lineItems = $r->items->map(fn ($i) => [
            'drug_id' => $i->drug_id ?? $i->item_id,
            'item_name' => $i->item_name,
            'item_code' => $i->item_code,
            'supply_unit_name' => $i->supply_unit_name,
            'dosage' => $i->dosage,
            'frequency' => $i->frequency,
            'route' => $i->route,
            'duration' => $i->duration,
            'quantity' => (string) $i->quantity,
            'unit_cost' => (string) $i->unit_cost,
            'drug_batch_id' => $i->drug_batch_id,
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

    public function addItem(int $drugId): void
    {
        $drug = Drug::find($drugId);
        if (!$drug) return;
        $unitCost = (float) $drug->batches()->where('quantity_on_hand', '>', 0)->avg('selling_price') ?: 0;
        $qty = 1;
        $this->lineItems[] = [
            'drug_id' => $drug->id,
            'item_name' => $drug->generic_name . ' ' . $drug->drug_strength,
            'item_code' => $drug->drug_code,
            'supply_unit_name' => $drug->dosage_form ?? '',
            'dosage' => $drug->drug_strength ?? '',
            'frequency' => '',
            'route' => $drug->route ?? '',
            'duration' => '',
            'quantity' => (string) $qty,
            'unit_cost' => (string) $unitCost,
            'drug_batch_id' => null,
        ];
        $this->recalculateTotal();
    }

    public function removeLineItem(int $index): void
    {
        unset($this->lineItems[$index]);
        $this->lineItems = array_values($this->lineItems);
        $this->recalculateTotal();
    }

    public function recalculateTotal(): void
    {
        $total = 0;
        foreach ($this->lineItems as $item) {
            $total += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_cost'] ?? 0);
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
            $request = PharmacyRequest::findOrFail($this->editingId);
            $request->update([
                'requesting_department_id' => $data['requesting_department_id'],
                'requesting_person' => $data['requesting_person'],
                'requesting_doctor_id' => $data['requesting_doctor_id'],
                'priority' => $data['priority'],
                'notes' => $data['request_notes'],
                'total_amount' => $this->total_amount,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $request->items()->delete();
            foreach ($data['lineItems'] as $li) {
                $request->items()->create([
                    'item_id' => $li['drug_id'],
                    'item_name' => $li['item_name'],
                    'item_code' => $li['item_code'] ?? null,
                    'supply_unit_name' => $li['supply_unit_name'] ?? null,
                    'drug_id' => $li['drug_id'],
                    'drug_batch_id' => $li['drug_batch_id'] ?? null,
                    'dosage' => $li['dosage'] ?? null,
                    'frequency' => $li['frequency'] ?? null,
                    'route' => $li['route'] ?? null,
                    'duration' => $li['duration'] ?? null,
                    'quantity' => $li['quantity'],
                    'unit_cost' => $li['unit_cost'],
                    'total_cost' => (float) $li['quantity'] * (float) $li['unit_cost'],
                    'status' => 'Pending',
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Pharmacy request updated successfully.');
            if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
            if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
            if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
            if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
            if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
            if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        } else {
            $request = PharmacyRequest::create([
                'tenant_id' => $tid,
                'request_code' => $this->request_code,
                'requesting_department_id' => $data['requesting_department_id'],
                'requesting_person' => $data['requesting_person'],
                'requesting_doctor_id' => $data['requesting_doctor_id'],
                'priority' => $data['priority'],
                'status' => 'Pending',
                'billing_status' => 'Pending',
                'total_amount' => $this->total_amount,
                'notes' => $data['request_notes'],
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            foreach ($data['lineItems'] as $li) {
                PharmacyRequestItem::create([
                    'pharmacy_request_id' => $request->id,
                    'item_id' => $li['drug_id'],
                    'item_name' => $li['item_name'],
                    'item_code' => $li['item_code'] ?? null,
                    'supply_unit_name' => $li['supply_unit_name'] ?? null,
                    'drug_id' => $li['drug_id'],
                    'drug_batch_id' => $li['drug_batch_id'] ?? null,
                    'dosage' => $li['dosage'] ?? null,
                    'frequency' => $li['frequency'] ?? null,
                    'route' => $li['route'] ?? null,
                    'duration' => $li['duration'] ?? null,
                    'quantity' => $li['quantity'],
                    'unit_cost' => $li['unit_cost'],
                    'total_cost' => (float) $li['quantity'] * (float) $li['unit_cost'],
                    'status' => 'Pending',
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Pharmacy request created successfully.');
            if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
            if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
            if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
            if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
            if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
            if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        }
        $this->closeModal();
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $item = PharmacyRequestItem::findOrFail($itemId);
        $item->update([
            'status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);

        $allStatuses = $item->pharmacyRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $item->pharmacyRequest->update(['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()]);

        if ($status === 'Complete' && $item->total_cost > 0) {
            PharmacyRequest::postChargeToLedger($item->pharmacyRequest, (float) $item->total_cost, $uid);
        }

        if ($parentStatus === 'Complete' && $item->pharmacyRequest->billing_status === 'Pending') {
            $item->pharmacyRequest->update(['billing_status' => 'Billed', 'updated_by' => $uid, 'updated_date' => now()]);
        }

        $this->dispatch('toast', type: 'success', message: "Pharmacy item status updated to {$status}.");
        $this->dispatch('$refresh');
    }

    public function updateBillingStatus(int $id, string $status): void
    {
        $valid = ['Billed', 'Exempt', 'Credited', 'Pending'];
        if (!in_array($status, $valid)) return;

        $uid = $this->currentUserId();
        $request = PharmacyRequest::findOrFail($id);
        $request->update([
            'billing_status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: "Pharmacy billing status updated to {$status}.");
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
    }

    public function openDispense(int $id): void
    {
        $this->dispensingId = $id;
        $request = PharmacyRequest::with('items')->findOrFail($id);
        $this->dispensingQuantities = [];
        $this->dispensingBatchSelections = [];
        foreach ($request->items as $item) {
            if ($item->status === 'Complete' || $item->status === 'Cancelled') continue;
            $this->dispensingQuantities[$item->id] = 0;
            $this->dispensingBatchSelections[$item->id] = [];
        }
        $this->showDispenseModal = true;
    }

    public function processDispense(): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $request = PharmacyRequest::with('items')->findOrFail($this->dispensingId);

        $anyGiven = false;
        foreach ($this->dispensingQuantities as $itemId => $qtyToGive) {
            $qtyToGive = max(0, (float) $qtyToGive);
            if ($qtyToGive <= 0) continue;

            $anyGiven = true;
            $item = $request->items->firstWhere('id', $itemId);
            if (!$item) continue;

            $selectedBatchId = $this->dispensingBatchSelections[$itemId] ?? null;
            if (!$selectedBatchId) {
                $this->dispatch('toast', type: 'error', message: "Select a batch for {$item->item_name}.");
                return;
            }

            $batch = DrugBatch::where('id', $selectedBatchId)
                ->where('quantity_on_hand', '>', 0)
                ->where('expiry_date', '>=', $now->toDateString())
                ->first();

            if (!$batch || $batch->quantity_on_hand < $qtyToGive) {
                $this->dispatch('toast', type: 'error', message: "Insufficient stock in batch for {$item->item_name}.");
                return;
            }

            $batch->decrement('quantity_on_hand', $qtyToGive);
            $batch->updated_by = $uid;
            $batch->updated_date = $now;
            $batch->save();

            StockAdjustment::create([
                'tenant_id' => $request->tenant_id,
                'adjustment_code' => 'DISP-' . str_pad(StockAdjustment::withoutGlobalScopes()->where('tenant_id', $request->tenant_id)->count() + 1, 5, '0', STR_PAD_LEFT),
                'drug_id' => $batch->drug_id,
                'drug_batch_id' => $batch->id,
                'adjustment_type' => 'Dispensed',
                'quantity' => -$qtyToGive,
                'unit_cost' => $batch->unit_cost,
                'reason' => 'Dispensed via ' . ($request->request_code ?? ''),
                'reference_type' => 'Dispensing',
                'reference_id' => $request->id,
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);

            $item->drug_batch_id = $batch->id;
            $newGiven = $item->given_quantity + $qtyToGive;
            if ($newGiven > $item->quantity) $newGiven = $item->quantity;

            $itemStatus = $newGiven >= $item->quantity ? 'Complete' : 'Partial';
            $item->update([
                'given_quantity' => $newGiven,
                'status'         => $itemStatus,
                'total_cost'     => $newGiven * $item->unit_cost,
                'drug_batch_id'  => $batch->id,
                'dispensed_date' => $now,   // CF4 eSOA service_date — stamped at actual dispense time
                'updated_by'     => $uid,
                'updated_date'   => $now,
            ]);
        }

        if (!$anyGiven) {
            $this->dispatch('toast', type: 'warning', message: 'No quantities entered. Enter at least one item quantity to dispense.');
            return;
        }

        $allStatuses = $request->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Partial') || $allStatuses->contains('Approved') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $totalAmount = $request->items->sum(fn ($i) => $i->given_quantity * $i->unit_cost);
        $request->update([
            'status' => $parentStatus,
            'total_amount' => $totalAmount,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->showDispenseModal = false;
        $this->dispatch('toast', type: 'success', message: 'Medications dispensed successfully.');
        PharmacyRequest::postChargeToLedger($request, $totalAmount, $uid);
        if ($request->billing_status === 'Pending') {
            $request->update(['billing_status' => 'Billed', 'updated_by' => $uid, 'updated_date' => $now]);
        }

        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        if ($request->opd_consultation_id) $this->dispatch('refreshOpdOrders')->to(\App\Livewire\Opd\OpdConsultationCrud::class);
    }

    public function completeAllItems(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $request = PharmacyRequest::with('items')->findOrFail($requestId);
        $request->items()->update(['status' => 'Complete', 'updated_by' => $uid, 'updated_date' => $now]);
        $totalAmount = $request->items->sum(fn ($i) => $i->given_quantity * $i->unit_cost);
        $request->update(['status' => 'Complete', 'total_amount' => $totalAmount, 'updated_by' => $uid, 'updated_date' => $now]);
        $this->dispatch('toast', type: 'success', message: 'All pharmacy items marked as Complete.');
        PharmacyRequest::postChargeToLedger($request, $totalAmount, $uid);
        if ($request->billing_status === 'Pending') {
            $request->update(['billing_status' => 'Billed', 'updated_by' => $uid, 'updated_date' => $now]);
        }
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        if ($request->opd_consultation_id) $this->dispatch('refreshOpdOrders')->to(\App\Livewire\Opd\OpdConsultationCrud::class);
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        $uid = $this->currentUserId();
        $request = PharmacyRequest::findOrFail($this->editingId);
        $erId = $request->er_admission_id;
        $dsId = $request->dialysis_session_id;
        $orId = $request->or_schedule_id;
        $admissionId = $request->admission_id;
        $obId = $request->ob_consultation_id;
        $drId = $request->dr_schedule_id;
        $request->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        PharmacyRequestItem::where('pharmacy_request_id', $this->editingId)->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Pharmacy request cancelled successfully.');
        if ($erId) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($dsId) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($orId) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($admissionId) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($obId) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($drId) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showDetailModal = false;
        $this->showDispenseModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'viewingId', 'dispensingId', 'request_code', 'requesting_department_id',
            'requesting_person', 'requesting_doctor_id', 'priority', 'request_notes', 'lineItems',
        ]);
        $this->total_amount = '0';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $items = PharmacyRequest::query()
            ->with(['requestingDepartment', 'items', 'patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('request_code', 'like', '%' . $this->search . '%')
                          ->orWhere('requesting_person', 'like', '%' . $this->search . '%')
                          ->orWhere('notes', 'like', '%' . $this->search . '%')
                          ->orWhereHas('requestingDepartment', fn ($d) =>
                              $d->where('dept_name', 'like', '%' . $this->search . '%')
                                ->orWhere('dept_code', 'like', '%' . $this->search . '%')
                          )
                          ->orWhereHas('erAdmission.patient', fn($p) => 
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          )
                          ->orWhereHas('opdConsultation.patient', fn($p) => 
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          )
                           ->orWhereHas('admission.patient', fn($p) => 
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('patient_code', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('dialysisSession.patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('orSchedule.patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('drSchedule.patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('obConsultation.patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $departments = Department::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('history', 1)
            ->orderBy('dept_name')
            ->get(['id', 'dept_code', 'dept_name']);

        $doctors = HealthCareProfessional::query()
            ->where('tenant_id', $this->currentTenantId())
            ->orderBy('full_name')
            ->get(['id', 'professional_code', 'full_name']);

        $drugs = Drug::query()
            ->orderBy('generic_name')
            ->get(['id', 'drug_code', 'generic_name', 'brand_name', 'drug_strength', 'dosage_form', 'route']);

        $detailRequest = null;
        if ($this->showDetailModal && $this->viewingId) {
            $detailRequest = PharmacyRequest::with(['requestingDepartment', 'requestingDoctor', 'items.drug', 'items.drugBatch', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
                ->find($this->viewingId);
        }

        $dispensingRequest = null;
        $dispensingBatches = [];
        if ($this->showDispenseModal && $this->dispensingId) {
            $dispensingRequest = PharmacyRequest::with(['requestingDepartment', 'items.drug', 'items.drugBatch', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
                ->find($this->dispensingId);
            foreach ($dispensingRequest->items as $di) {
                if ($di->drug_id) {
                    $dispensingBatches[$di->id] = DrugBatch::where('drug_id', $di->drug_id)
                        ->available()
                        ->orderBy('expiry_date')
                        ->orderBy('id')
                        ->get(['id', 'batch_no', 'expiry_date', 'quantity_on_hand', 'selling_price']);
                }
            }
        }

        return view('livewire.pharmacy.pharmacy-dispensing-crud', compact(
            'items', 'departments', 'doctors', 'drugs', 'detailRequest', 'dispensingRequest', 'dispensingBatches'
        ));
    }
}
