<?php

namespace App\Livewire\Procedures;

use App\Models\Department;
use App\Models\HealthCareProfessional;
use App\Models\Procedures\Procedure;
use App\Models\Procedures\ProcedureCategory;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Procedures\ProcedureRequestItem;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Er\ErAdmissionCrud;

class ProcedureDispensingCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $showDetailModal = false, $isEditing = false;
    public ?int $editingId = null, $viewingId = null;

    // Form fields
    public string $request_code = '', $request_notes = '';
    public ?string $requesting_department_id = null, $requesting_doctor_id = null;

    // Line items
    public array $lineItems = [];
    public string $total_amount = '0';

    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        return [
            'requesting_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'requesting_doctor_id'    => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'request_notes'           => ['nullable', 'string'],
            'lineItems'               => ['required', 'array', 'min:1'],
            'lineItems.*.procedure_id'=> ['nullable', 'integer', 'exists:procedures,id'],
            'lineItems.*.procedure_name'=> ['required', 'string', 'max:200'],
            'lineItems.*.quantity'    => ['required', 'integer', 'min:1'],
            'lineItems.*.unit_charge' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected array $validationAttributes = [
        'lineItems' => 'Procedure Items',
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
        $last = ProcedureRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 5)) + 1 : 1;
        return 'PRCD-' . str_pad($num, 5, '0', STR_PAD_LEFT);
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
        $r = ProcedureRequest::with('items')->findOrFail($id);
        $this->editingId = $r->id;
        $this->request_code = $r->request_code;
        $this->requesting_department_id = $r->requesting_department_id ? (string) $r->requesting_department_id : null;
        $this->requesting_doctor_id = $r->requesting_doctor_id ? (string) $r->requesting_doctor_id : null;
        $this->request_notes = $r->notes ?? '';
        $this->lineItems = $r->items->map(fn ($i) => [
            'procedure_id' => $i->procedure_id,
            'procedure_name' => $i->procedure_name,
            'category_name' => $i->category_name,
            'quantity' => (string) $i->quantity,
            'unit_charge' => (string) $i->unit_charge,
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

    public function addProcedure(int $procedureId): void
    {
        $procedure = Procedure::with('category')->find($procedureId);
        if (!$procedure) return;
        $this->lineItems[] = [
            'procedure_id' => $procedure->id,
            'procedure_name' => $procedure->name,
            'category_name' => $procedure->category?->name ?? '',
            'quantity' => '1',
            'unit_charge' => (string) $procedure->standard_charge,
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
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $total += (float) ($item['unit_charge'] ?? 0) * $qty;
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
            $request = ProcedureRequest::findOrFail($this->editingId);
            $request->update([
                'requesting_department_id' => $data['requesting_department_id'],
                'requesting_doctor_id'    => $data['requesting_doctor_id'],
                'notes'                   => $data['request_notes'],
                'total_amount'            => $this->total_amount,
                'updated_by'              => $uid,
                'updated_date'            => $now,
            ]);
            $request->items()->delete();
            foreach ($data['lineItems'] as $li) {
                $qty = max(1, (int) ($li['quantity'] ?? 1));
                $charge = (float) ($li['unit_charge'] ?? 0);
                $request->items()->create([
                    'procedure_id'   => $li['procedure_id'],
                    'procedure_name' => $li['procedure_name'],
                    'category_name'  => $li['category_name'] ?? '',
                    'quantity'       => $qty,
                    'qty_given'      => 0,
                    'unit_charge'    => $charge,
                    'total_charge'   => $qty * $charge,
                    'status'         => 'Pending',
                    'is_active'      => true,
                    'history'        => 1,
                    'created_by'     => $uid,
                    'created_date'   => $now,
                    'updated_by'     => $uid,
                    'updated_date'   => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Procedure request updated successfully.');
            if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
            if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
            if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
            if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
            if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
            if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        } else {
            $request = ProcedureRequest::create([
                'tenant_id'                => $tid,
                'request_code'             => $this->request_code,
                'requesting_department_id'  => $data['requesting_department_id'],
                'requesting_doctor_id'     => $data['requesting_doctor_id'],
                'status'                   => 'Pending',
                'billing_status'           => 'Pending',
                'total_amount'             => $this->total_amount,
                'notes'                    => $data['request_notes'],
                'is_active'                => true,
                'history'                  => 1,
                'created_by'               => $uid,
                'created_date'             => $now,
                'updated_by'               => $uid,
                'updated_date'             => $now,
            ]);
            foreach ($data['lineItems'] as $li) {
                $qty = max(1, (int) ($li['quantity'] ?? 1));
                $charge = (float) ($li['unit_charge'] ?? 0);
                ProcedureRequestItem::create([
                    'procedure_request_id' => $request->id,
                    'procedure_id'         => $li['procedure_id'],
                    'procedure_name'       => $li['procedure_name'],
                    'category_name'        => $li['category_name'] ?? '',
                    'quantity'             => $qty,
                    'qty_given'            => 0,
                    'unit_charge'          => $charge,
                    'total_charge'         => $qty * $charge,
                    'status'               => 'Pending',
                    'is_active'            => true,
                    'history'              => 1,
                    'created_by'           => $uid,
                    'created_date'         => $now,
                    'updated_by'           => $uid,
                    'updated_date'         => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Procedure request created successfully.');
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
        $request = ProcedureRequest::findOrFail($id);
        $request->update([
            'billing_status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: "Procedure billing status updated to {$status}.");
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
        $item = ProcedureRequestItem::findOrFail($itemId);
        $item->update([
            'status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);

        $allStatuses = $item->procedureRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $item->procedureRequest->update(['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()]);

        if ($parentStatus === 'Complete' && $item->procedureRequest->billing_status === 'Pending') {
            $item->procedureRequest->update(['billing_status' => 'Billed', 'updated_by' => $uid, 'updated_date' => now()]);
        }

        if ($parentStatus === 'Complete') {
            \App\Models\Procedures\ProcedureRequest::postChargeToLedger($item->procedureRequest, (float) $item->procedureRequest->total_amount, $uid);
        }

        $this->dispatch('toast', type: 'success', message: "Procedure item status updated to {$status}.");
        $this->dispatch('$refresh');
    }

    public function completeAllItems(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $request = ProcedureRequest::findOrFail($requestId);
        $request->items()->update(['status' => 'Complete', 'updated_by' => $uid, 'updated_date' => $now]);

        $updateData = ['status' => 'Complete', 'updated_by' => $uid, 'updated_date' => $now];
        if ($request->billing_status === 'Pending') {
            $updateData['billing_status'] = 'Billed';
        }
        $request->update($updateData);

        \App\Models\Procedures\ProcedureRequest::postChargeToLedger($request, (float) $request->total_amount, $uid);

        $this->dispatch('toast', type: 'success', message: 'All procedure items marked as Complete.');
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
        $request = ProcedureRequest::findOrFail($this->editingId);
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
        ProcedureRequestItem::where('procedure_request_id', $this->editingId)->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Procedure request cancelled successfully.');
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
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'viewingId', 'request_code', 'requesting_department_id',
            'requesting_doctor_id', 'request_notes', 'lineItems',
        ]);
        $this->total_amount = '0';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $items = ProcedureRequest::query()
            ->with(['requestingDepartment', 'items', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('request_code', 'like', '%' . $this->search . '%')
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

        $doctors = HealthCareProfessional::orderBy('full_name')
            ->get(['id', 'full_name']);

        $procedures = Procedure::query()
            ->with('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'standard_charge']);

        $categories = ProcedureCategory::orderBy('name')
            ->get(['id', 'name']);

        $detailRequest = null;
        if ($this->showDetailModal && $this->viewingId) {
            $detailRequest = ProcedureRequest::with(['requestingDepartment', 'requestingDoctor', 'items.procedure.category', 'patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
                ->find($this->viewingId);
        }

        return view('livewire.procedures.procedure-dispensing-crud', compact(
            'items', 'departments', 'doctors', 'procedures', 'categories', 'detailRequest'
        ));
    }
}
