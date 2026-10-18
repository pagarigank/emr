<?php

namespace App\Livewire\Supply;

use App\Models\CsrRequest;
use App\Models\CsrRequestItem;
use App\Models\Department;
use App\Models\Supply\Item;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Er\ErAdmissionCrud;

class CsrDispensingCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $showDetailModal = false, $showDispenseModal = false, $isEditing = false;
    public ?int $editingId = null, $viewingId = null, $dispensingId = null;

    public string $request_code = '', $requesting_person = '', $notes = '';
    public ?string $requesting_department_id = null;
    public array $dispensingQuantities = [];

    public array $lineItems = [];
    public string $total_amount = '0';

    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        return [
            'requesting_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'requesting_person'        => ['nullable', 'string', 'max:150'],
            'notes'                    => ['nullable', 'string'],
            'lineItems'                => ['required', 'array', 'min:1'],
            'lineItems.*.item_id'      => ['required', 'integer', 'exists:items,id'],
            'lineItems.*.item_name'    => ['required', 'string', 'max:200'],
            'lineItems.*.quantity'     => ['required', 'numeric', 'min:0.01'],
            'lineItems.*.unit_cost'    => ['required', 'numeric', 'min:0'],
            'lineItems.*.total_cost'   => ['required', 'numeric', 'min:0'],
        ];
    }

    protected array $validationAttributes = [
        'lineItems' => 'Request Items',
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
        $last = CsrRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'CR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
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
        $r = CsrRequest::with('items')->findOrFail($id);
        $this->editingId = $r->id;
        $this->request_code = $r->request_code;
        $this->requesting_department_id = $r->requesting_department_id ? (string) $r->requesting_department_id : null;
        $this->requesting_person = $r->requesting_person ?? '';
        $this->notes = $r->notes ?? '';
        $this->lineItems = $r->items->map(fn ($i) => [
            'item_id' => $i->item_id,
            'item_name' => $i->item_name,
            'item_code' => $i->item_code,
            'supply_unit_name' => $i->supply_unit_name,
            'quantity' => (string) $i->quantity,
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

    public function addItem(int $itemId): void
    {
        $item = Item::find($itemId);
        if (!$item) return;
        $unitCost = (float) $item->unit_cost;
        $qty = 1;
        $this->lineItems[] = [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_code' => $item->code,
            'supply_unit_name' => $item->unit?->name ?? '',
            'quantity' => (string) $qty,
            'unit_cost' => (string) $unitCost,
            'total_cost' => (string) ($unitCost * $qty),
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
        foreach ($this->lineItems as $i => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $cost = (float) ($item['unit_cost'] ?? 0);
            $lineTotal = $qty * $cost;
            $this->lineItems[$i]['total_cost'] = number_format($lineTotal, 2, '.', '');
            $total += $lineTotal;
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
            $request = CsrRequest::findOrFail($this->editingId);
            $request->update([
                'requesting_department_id' => $data['requesting_department_id'],
                'requesting_person' => $data['requesting_person'],
                'notes' => $data['notes'],
                'total_amount' => $this->total_amount,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $request->items()->delete();
            foreach ($data['lineItems'] as $li) {
                $request->items()->create([
                    'item_id' => $li['item_id'],
                    'item_name' => $li['item_name'],
                    'item_code' => $li['item_code'] ?? null,
                    'supply_unit_name' => $li['supply_unit_name'] ?? null,
                    'quantity' => $li['quantity'],
                    'unit_cost' => $li['unit_cost'],
                    'total_cost' => $li['total_cost'],
                    'status' => 'Pending',
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'CSR request updated successfully.');
            if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
            if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
            if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
            if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
            if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
            if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        } else {
            $request = CsrRequest::create([
                'tenant_id' => $tid,
                'request_code' => $this->request_code,
                'requesting_department_id' => $data['requesting_department_id'],
                'requesting_person' => $data['requesting_person'],
                'status' => 'Pending',
                'billing_status' => 'Pending',
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
                CsrRequestItem::create([
                    'csr_request_id' => $request->id,
                    'item_id' => $li['item_id'],
                    'item_name' => $li['item_name'],
                    'item_code' => $li['item_code'] ?? null,
                    'supply_unit_name' => $li['supply_unit_name'] ?? null,
                    'quantity' => $li['quantity'],
                    'unit_cost' => $li['unit_cost'],
                    'total_cost' => $li['total_cost'],
                    'status' => 'Pending',
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'CSR request created successfully.');
            if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
            if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
            if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
            if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
            if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
            if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        }
        $this->closeModal();
    }

    public function updateBillingStatus(int $id, string $status): void
    {
        $valid = ['Billed', 'Exempt', 'Credited', 'Pending'];
        if (!in_array($status, $valid)) return;

        $uid = $this->currentUserId();
        $request = CsrRequest::findOrFail($id);
        $request->update([
            'billing_status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: "CSR billing status updated to {$status}.");
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $item = CsrRequestItem::findOrFail($itemId);
        $item->update([
            'status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);

        $allStatuses = $item->csrRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $updateData = ['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()];
        if ($parentStatus === 'Complete' && $item->csrRequest->billing_status === 'Pending') {
            $updateData['billing_status'] = 'Billed';
        }
        $item->csrRequest->update($updateData);

        if ($parentStatus === 'Complete') {
            CsrRequest::postChargeToLedger($item->csrRequest, (float) $item->csrRequest->total_amount, $uid);
        }

        $this->dispatch('toast', type: 'success', message: "CSR item status updated to {$status}.");
        $this->dispatch('$refresh');
    }

    public function completeAllItems(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $request = CsrRequest::findOrFail($requestId);
        $request->items()->update(['status' => 'Complete', 'updated_by' => $uid, 'updated_date' => $now]);

        $updateData = ['status' => 'Complete', 'updated_by' => $uid, 'updated_date' => $now];
        if ($request->billing_status === 'Pending') {
            $updateData['billing_status'] = 'Billed';
        }
        $request->update($updateData);

        CsrRequest::postChargeToLedger($request, (float) $request->total_amount, $uid);

        $this->dispatch('toast', type: 'success', message: 'All CSR items marked as Complete.');
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        if ($request->opd_consultation_id) $this->dispatch('refreshOpdOrders')->to(\App\Livewire\Opd\OpdConsultationCrud::class);
    }

    public function openDispense(int $id): void
    {
        $this->dispensingId = $id;
        $request = CsrRequest::with('items')->findOrFail($id);
        $this->dispensingQuantities = [];
        foreach ($request->items as $item) {
            if ($item->status === 'Complete' || $item->status === 'Cancelled') continue;
            $this->dispensingQuantities[$item->id] = 0;
        }
        $this->showDispenseModal = true;
    }

    public function processDispense(): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $request = CsrRequest::with('items')->findOrFail($this->dispensingId);

        $anyGiven = false;
        foreach ($this->dispensingQuantities as $itemId => $qtyToGive) {
            $qtyToGive = max(0, (float) $qtyToGive);
            if ($qtyToGive <= 0) continue;

            $anyGiven = true;
            $item = $request->items->firstWhere('id', $itemId);
            if (!$item) continue;

            $newGiven = $item->given_quantity + $qtyToGive;
            if ($newGiven > $item->quantity) $newGiven = $item->quantity;

            $itemStatus = $newGiven >= $item->quantity ? 'Complete' : 'Partial';
            $item->update([
                'given_quantity' => $newGiven,
                'status' => $itemStatus,
                'total_cost' => $newGiven * $item->unit_cost,
                'updated_by' => $uid,
                'updated_date' => $now,
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

        if ($parentStatus === 'Complete' && $request->billing_status === 'Pending') {
            $request->update(['billing_status' => 'Billed', 'updated_by' => $uid, 'updated_date' => $now]);
        }

        $this->showDispenseModal = false;
        CsrRequest::postChargeToLedger($request, $totalAmount, $uid);
        $this->dispatch('toast', type: 'success', message: 'Items dispensed successfully.');
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
        $request = CsrRequest::findOrFail($this->editingId);
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
        CsrRequestItem::where('csr_request_id', $this->editingId)->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'CSR request cancelled successfully.');
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
            'editingId', 'viewingId', 'request_code', 'requesting_department_id',
            'requesting_person', 'notes', 'lineItems',
        ]);
        $this->total_amount = '0';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $items = CsrRequest::query()
            ->with(['requestingDepartment', 'items', 'patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('request_code', 'like', '%' . $this->search . '%')
                          ->orWhere('requesting_person', 'like', '%' . $this->search . '%')
                          ->orWhere('notes', 'like', '%' . $this->search . '%')
                          ->orWhereHas('patient', fn ($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('patient_code', 'like', '%' . $this->search . '%')
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

        $supplyItems = Item::query()
            ->with('unit')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'unit_cost', 'supply_unit_id']);

        $detailRequest = null;
        if ($this->showDetailModal && $this->viewingId) {
            $detailRequest = CsrRequest::with(['requestingDepartment', 'items.item', 'patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
                ->find($this->viewingId);
        }

        $dispensingRequest = null;
        if ($this->showDispenseModal && $this->dispensingId) {
            $dispensingRequest = CsrRequest::with(['requestingDepartment', 'items.item', 'patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
                ->find($this->dispensingId);
        }

        return view('livewire.supply.csr-dispensing-crud', compact(
            'items', 'departments', 'supplyItems', 'detailRequest', 'dispensingRequest'
        ));
    }
}
