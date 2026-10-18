<?php

namespace App\Livewire\MICU;

use App\Models\Admission;
use App\Models\HealthCareProfessional;
use App\Models\MICU\MicuAssessment;
use App\Models\MICU\MicuAssessmentParameter;
use App\Models\Supply\Item as SupplyItem;
use Livewire\Component;
use Livewire\WithPagination;

class MicuPatientCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'admission_code', $sortDirection = 'asc';
    public int $perPage = 10;

    public bool $showTreatmentForm = false;
    public ?int $activeAdmissionForTreatmentId = null;

    // Treatment fields (mirror admissions table columns)
    public ?string $treatment_history_of_present_illness = null;
    public ?string $treatment_physical_exam_findings = null;
    public ?string $treatment_assessment = null;
    public ?string $treatment_orders_summary = null;
    public ?string $treatment_treatment_notes = null;
    public ?string $treatment_disposition = null;
    public ?string $treatment_discharge_instructions = null;

    // Order modals
    public bool $showOrderLabModal = false, $showOrderCsrModal = false, $showOrderRadiologyModal = false, $showOrderPharmacyModal = false, $showOrderProcedureModal = false;
    public array $selectedLabItems = [], $selectedCsrItems = [], $selectedRadiologyItems = [], $selectedPharmacyItems = [], $selectedProcedureItems = [];
    public array $labItemQuantities = [], $csrItemQuantities = [], $pharmacyItemQuantities = [];
    public ?string $orderNotes = null, $orderPriority = 'Routine';
    public bool $showDispoModal = false;

    // Assessment
    public bool $showAssessmentModal = false;
    public array $assessmentValues = [];
    public ?string $assessmentNotes = null;

    protected $listeners = ['refreshAdmissionOrders' => 'refreshOrders'];

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    // ── Treatment Workflow ──

    public function openTreatment(int $admissionId): void
    {
        $adm = Admission::findOrFail($admissionId);
        $this->activeAdmissionForTreatmentId = $adm->id;
        $this->treatment_history_of_present_illness = $adm->history_of_present_illness;
        $this->treatment_physical_exam_findings = $adm->physical_exam_findings;
        $this->treatment_assessment = $adm->assessment;
        $this->treatment_orders_summary = $adm->orders_summary;
        $this->treatment_treatment_notes = $adm->treatment_notes;
        $this->treatment_disposition = $adm->disposition;
        $this->treatment_discharge_instructions = $adm->discharge_instructions;
        $this->showTreatmentForm = true;
    }

    public function saveTreatment(): void
    {
        $this->validate([
            'treatment_history_of_present_illness' => ['nullable', 'string'],
            'treatment_physical_exam_findings' => ['nullable', 'string'],
            'treatment_assessment' => ['nullable', 'string'],
            'treatment_orders_summary' => ['nullable', 'string'],
            'treatment_treatment_notes' => ['nullable', 'string'],
        ]);

        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);
        $adm->update([
            'history_of_present_illness' => $this->treatment_history_of_present_illness,
            'physical_exam_findings' => $this->treatment_physical_exam_findings,
            'assessment' => $this->treatment_assessment,
            'orders_summary' => $this->treatment_orders_summary,
            'treatment_notes' => $this->treatment_treatment_notes,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Clinical notes saved.');
    }

    public function closeTreatment(): void
    {
        $this->showTreatmentForm = false;
        $this->activeAdmissionForTreatmentId = null;
        $this->resetTreatmentForm();
    }

    private function resetTreatmentForm(): void
    {
        $this->reset([
            'treatment_history_of_present_illness', 'treatment_physical_exam_findings',
            'treatment_assessment', 'treatment_orders_summary', 'treatment_treatment_notes',
            'treatment_disposition', 'treatment_discharge_instructions',
        ]);
    }

    // ── Disposition ──

    public function openDisposition(): void
    {
        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);
        $this->treatment_disposition = $adm->disposition;
        $this->treatment_discharge_instructions = $adm->discharge_instructions;
        $this->showDispoModal = true;
    }

    public function saveDisposition(): void
    {
        $this->validate([
            'treatment_disposition' => ['required', 'string', 'max:30'],
            'treatment_discharge_instructions' => ['nullable', 'string'],
        ]);

        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);
        $adm->update([
            'disposition' => $this->treatment_disposition,
            'discharge_instructions' => $this->treatment_discharge_instructions,
            'disposition_datetime' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->showDispoModal = false;
        $this->dispatch('toast', type: 'success', message: 'Disposition saved.');
    }

    // ── Lab Orders ──

    public function openOrderLab(): void
    {
        $this->resetOrderModals();
        $this->selectedLabItems = [];
        $this->labItemQuantities = [];
        $this->orderNotes = null;
        $this->orderPriority = 'Routine';
        $this->showOrderLabModal = true;
    }

    public function requestLabOrder(): void
    {
        $this->validate(['selectedLabItems' => ['required', 'array', 'min:1']]);
        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);
        $code = $this->generateOrderCode('LR', \App\Models\LabRequest::class);

        $total = 0;
        $items = \App\Models\LabTest::whereIn('id', $this->selectedLabItems)->get();
        foreach ($items as $item) { $total += ($item->standard_charge ?? 0); }

        $req = \App\Models\LabRequest::create([
            'tenant_id' => $this->currentTenantId(),
            'admission_id' => $adm->id,
            'request_code' => $code,
            'patient_id' => $adm->patient_id,
            'requesting_department_id' => null,
            'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
            'priority' => $this->orderPriority,
            'request_notes' => $this->orderNotes,
            'status' => 'Pending', 'billing_status' => 'Pending',
            'total_charge' => $total,
            'is_active' => 1, 'history' => 1,
            'created_by' => $this->currentUserId(), 'created_date' => now(),
        ]);

        foreach ($items as $item) {
            \App\Models\LabRequestItem::create([
                'lab_request_id' => $req->id, 'lab_test_id' => $item->id,
                'test_name' => $item->name, 'lab_section_id' => $item->lab_section_id,
                'standard_charge' => $item->standard_charge ?? 0, 'status' => 'Pending',
                'is_active' => 1, 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]);
        }

        $this->showOrderLabModal = false;
        $this->dispatch('toast', type: 'success', message: "Lab request {$code} created.");
    }

    public function updateLabItemStatus(int $itemId, string $status): void
    {
        $item = \App\Models\LabRequestItem::findOrFail($itemId);
        $req = \App\Models\LabRequest::findOrFail($item->lab_request_id);
        $item->update(['status' => $status, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $allDone = \App\Models\LabRequestItem::where('lab_request_id', $req->id)->whereNotIn('status', ['Completed', 'Cancelled'])->count() === 0;
        if ($allDone) $req->update(['status' => 'Completed', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
    }

    public function cancelLabRequest(int $requestId): void
    {
        $req = \App\Models\LabRequest::findOrFail($requestId);
        $req->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        \App\Models\LabRequestItem::where('lab_request_id', $req->id)->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->dispatch('toast', type: 'success', message: 'Lab request cancelled.');
    }

    // ── CSR Orders ──

    public function openOrderCsr(): void
    {
        $this->resetOrderModals();
        $this->selectedCsrItems = [];
        $this->csrItemQuantities = [];
        $this->orderNotes = null;
        $this->showOrderCsrModal = true;
    }

    public function requestCsrOrder(): void
    {
        $this->validate(['selectedCsrItems' => ['required', 'array', 'min:1']]);
        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);
        $code = $this->generateOrderCode('CR', \App\Models\CsrRequest::class);
        $total = 0;
        $items = \App\Models\Item::whereIn('id', $this->selectedCsrItems)->get();
        foreach ($items as $item) {
            $qty = max((int)($this->csrItemQuantities[$item->id] ?? 1), 1);
            $total += ($item->unit_cost ?? 0) * $qty;
        }

        $req = \App\Models\CsrRequest::create([
            'tenant_id' => $this->currentTenantId(), 'admission_id' => $adm->id,
            'request_code' => $code, 'patient_id' => $adm->patient_id,
            'requesting_department_id' => null, 'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
            'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total, 'notes' => $this->orderNotes,
            'is_active' => 1, 'history' => 1,
            'created_by' => $this->currentUserId(), 'created_date' => now(),
        ]);

        foreach ($items as $item) {
            $qty = max((int)($this->csrItemQuantities[$item->id] ?? 1), 1);
            \App\Models\CsrRequestItem::create([
                'csr_request_id' => $req->id, 'item_id' => $item->id,
                'item_name' => $item->item_name, 'item_category_id' => $item->item_category_id,
                'quantity' => $qty, 'given_quantity' => 0,
                'unit_cost' => $item->unit_cost ?? 0, 'total_cost' => ($item->unit_cost ?? 0) * $qty,
                'status' => 'Pending', 'is_active' => 1, 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]);
        }

        $this->showOrderCsrModal = false;
        $this->dispatch('toast', type: 'success', message: "Supply request {$code} created.");
    }

    public function updateCsrItemStatus(int $itemId, string $status): void
    {
        $item = \App\Models\CsrRequestItem::findOrFail($itemId);
        $req = \App\Models\CsrRequest::findOrFail($item->csr_request_id);
        $item->update(['status' => $status, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $allDone = \App\Models\CsrRequestItem::where('csr_request_id', $req->id)->whereNotIn('status', ['Approved', 'Complete', 'Cancelled'])->count() === 0;
        if ($allDone) $req->update(['status' => 'Complete', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
    }

    public function cancelCsrRequest(int $requestId): void
    {
        $req = \App\Models\CsrRequest::findOrFail($requestId);
        $req->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        \App\Models\CsrRequestItem::where('csr_request_id', $req->id)->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->dispatch('toast', type: 'success', message: 'Supply request cancelled.');
    }

    // ── Radiology Orders ──

    public function openOrderRadiology(): void
    {
        $this->resetOrderModals();
        $this->selectedRadiologyItems = [];
        $this->orderNotes = null;
        $this->showOrderRadiologyModal = true;
    }

    public function requestRadiologyOrder(): void
    {
        $this->validate(['selectedRadiologyItems' => ['required', 'array', 'min:1']]);
        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);
        $code = $this->generateOrderCode('RR', \App\Models\Radiology\RadiologyRequest::class);
        $total = 0;
        $items = \App\Models\Radiology\RadiologyExamType::whereIn('id', $this->selectedRadiologyItems)->get();
        foreach ($items as $item) { $total += ($item->standard_charge ?? 0); }

        $req = \App\Models\Radiology\RadiologyRequest::create([
            'tenant_id' => $this->currentTenantId(), 'admission_id' => $adm->id,
            'request_code' => $code, 'patient_id' => $adm->patient_id,
            'requesting_department_id' => null, 'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
            'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total, 'notes' => $this->orderNotes,
            'is_active' => 1, 'history' => 1,
            'created_by' => $this->currentUserId(), 'created_date' => now(),
        ]);

        foreach ($items as $item) {
            \App\Models\Radiology\RadiologyRequestItem::create([
                'radiology_request_id' => $req->id, 'radiology_exam_type_id' => $item->id,
                'exam_name' => $item->exam_name, 'standard_charge' => $item->standard_charge ?? 0,
                'status' => 'Pending', 'is_active' => 1, 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]);
        }

        $this->showOrderRadiologyModal = false;
        $this->dispatch('toast', type: 'success', message: "Radiology request {$code} created.");
    }

    public function updateRadiologyItemStatus(int $itemId, string $status): void
    {
        $item = \App\Models\Radiology\RadiologyRequestItem::findOrFail($itemId);
        $req = \App\Models\Radiology\RadiologyRequest::findOrFail($item->radiology_request_id);
        $item->update(['status' => $status, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $allDone = \App\Models\Radiology\RadiologyRequestItem::where('radiology_request_id', $req->id)->whereNotIn('status', ['Approved', 'Complete', 'Cancelled'])->count() === 0;
        if ($allDone) $req->update(['status' => 'Complete', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
    }

    public function cancelRadiologyRequest(int $requestId): void
    {
        $req = \App\Models\Radiology\RadiologyRequest::findOrFail($requestId);
        $req->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        \App\Models\Radiology\RadiologyRequestItem::where('radiology_request_id', $req->id)->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->dispatch('toast', type: 'success', message: 'Radiology request cancelled.');
    }

    // ── Pharmacy Orders ──

    public function openOrderPharmacy(): void
    {
        $this->resetOrderModals();
        $this->selectedPharmacyItems = [];
        $this->pharmacyItemQuantities = [];
        $this->orderNotes = null;
        $this->showOrderPharmacyModal = true;
    }

    public function requestPharmacyOrder(): void
    {
        $this->validate(['selectedPharmacyItems' => ['required', 'array', 'min:1']]);
        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);
        $code = $this->generateOrderCode('PR', \App\Models\Pharmacy\PharmacyRequest::class);
        $total = 0;
        $items = \App\Models\Pharmacy\Drug::whereIn('id', $this->selectedPharmacyItems)->get();
        foreach ($items as $item) {
            $qty = max((int)($this->pharmacyItemQuantities[$item->id] ?? 1), 1);
            $total += ($item->unit_price ?? 0) * $qty;
        }

        $req = \App\Models\Pharmacy\PharmacyRequest::create([
            'tenant_id' => $this->currentTenantId(), 'admission_id' => $adm->id,
            'request_code' => $code, 'requesting_department_id' => null,
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'), 'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
            'priority' => $this->orderPriority, 'status' => 'Pending',
            'billing_status' => 'Pending', 'total_amount' => $total,
            'notes' => $this->orderNotes, 'is_active' => 1, 'history' => 1,
            'created_by' => $this->currentUserId(), 'created_date' => now(),
        ]);

        foreach ($items as $item) {
            $qty = max((int)($this->pharmacyItemQuantities[$item->id] ?? 1), 1);
            \App\Models\Pharmacy\PharmacyRequestItem::create([
                'pharmacy_request_id' => $req->id, 'drug_id' => $item->id,
                'drug_name' => $item->drug_name, 'quantity' => $qty,
                'unit_price' => $item->unit_price ?? 0,
                'total_price' => ($item->unit_price ?? 0) * $qty,
                'status' => 'Pending', 'is_active' => 1, 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]);
        }

        $this->showOrderPharmacyModal = false;
        $this->dispatch('toast', type: 'success', message: "Pharmacy request {$code} created.");
    }

    public function updatePharmacyItemStatus(int $itemId, string $status): void
    {
        $item = \App\Models\Pharmacy\PharmacyRequestItem::findOrFail($itemId);
        $req = \App\Models\Pharmacy\PharmacyRequest::findOrFail($item->pharmacy_request_id);
        $item->update(['status' => $status, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $allDone = \App\Models\Pharmacy\PharmacyRequestItem::where('pharmacy_request_id', $req->id)->whereNotIn('status', ['Approved', 'Complete', 'Cancelled'])->count() === 0;
        if ($allDone) $req->update(['status' => 'Complete', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
    }

    public function cancelPharmacyRequest(int $requestId): void
    {
        $req = \App\Models\Pharmacy\PharmacyRequest::findOrFail($requestId);
        $req->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        \App\Models\Pharmacy\PharmacyRequestItem::where('pharmacy_request_id', $req->id)->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->dispatch('toast', type: 'success', message: 'Pharmacy request cancelled.');
    }

    // ── Procedure Orders ──

    public function openOrderProcedure(): void
    {
        $this->resetOrderModals();
        $this->selectedProcedureItems = [];
        $this->orderNotes = null;
        $this->showOrderProcedureModal = true;
    }

    public function requestProcedureOrder(): void
    {
        $this->validate(['selectedProcedureItems' => ['required', 'array', 'min:1']]);
        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);
        $code = $this->generateOrderCode('PRCD', \App\Models\Procedures\ProcedureRequest::class);
        $total = 0;
        $items = \App\Models\Procedures\Procedure::whereIn('id', $this->selectedProcedureItems)->get();
        foreach ($items as $item) { $total += ($item->standard_charge ?? 0); }

        $req = \App\Models\Procedures\ProcedureRequest::create([
            'tenant_id' => $this->currentTenantId(), 'admission_id' => $adm->id,
            'request_code' => $code, 'requesting_department_id' => null,
            'requesting_doctor_id' => auth()->user()?->health_care_professional_id, 'status' => 'Pending',
            'billing_status' => 'Pending', 'total_amount' => $total,
            'notes' => $this->orderNotes, 'is_active' => 1, 'history' => 1,
            'created_by' => $this->currentUserId(), 'created_date' => now(),
        ]);

        foreach ($items as $item) {
            \App\Models\Procedures\ProcedureRequestItem::create([
                'procedure_request_id' => $req->id, 'procedure_id' => $item->id,
                'procedure_name' => $item->procedure_name,
                'procedure_category_id' => $item->procedure_category_id,
                'category_name' => $item->category?->name ?? '',
                'standard_charge' => $item->standard_charge ?? 0,
                'total_charge' => $item->standard_charge ?? 0,
                'status' => 'Pending', 'is_active' => 1, 'history' => 1,
                'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]);
        }

        $this->showOrderProcedureModal = false;
        $this->dispatch('toast', type: 'success', message: "Procedure request {$code} created.");
    }

    public function updateProcedureItemStatus(int $itemId, string $status): void
    {
        $item = \App\Models\Procedures\ProcedureRequestItem::findOrFail($itemId);
        $req = \App\Models\Procedures\ProcedureRequest::findOrFail($item->procedure_request_id);
        $item->update(['status' => $status, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $allDone = \App\Models\Procedures\ProcedureRequestItem::where('procedure_request_id', $req->id)->whereNotIn('status', ['Approved', 'Complete', 'Cancelled'])->count() === 0;
        if ($allDone) $req->update(['status' => 'Complete', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
    }

    public function cancelProcedureRequest(int $requestId): void
    {
        $req = \App\Models\Procedures\ProcedureRequest::findOrFail($requestId);
        $req->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        \App\Models\Procedures\ProcedureRequestItem::where('procedure_request_id', $req->id)->update(['status' => 'Cancelled', 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->dispatch('toast', type: 'success', message: 'Procedure request cancelled.');
    }

    public function refreshOrders(): void { }

    // ── MICU Assessments ──

    public function openNewAssessment(): void
    {
        $this->assessmentValues = [];
        $this->assessmentNotes = null;
        $this->showAssessmentModal = true;
    }

    public function saveAssessment(): void
    {
        $adm = Admission::findOrFail($this->activeAdmissionForTreatmentId);

        $assessment = MicuAssessment::create([
            'tenant_id' => $this->currentTenantId(),
            'admission_id' => $adm->id,
            'assessed_by' => $this->currentUserId(),
            'assessed_at' => now(),
            'notes' => $this->assessmentNotes,
            'is_active' => 1,
            'history' => 1,
            'created_by' => $this->currentUserId(),
            'created_date' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        foreach ($this->assessmentValues as $paramId => $value) {
            if ($value === null || $value === '') continue;
            $param = MicuAssessmentParameter::find($paramId);
            \App\Models\MICU\MicuAssessmentValue::create([
                'micu_assessment_id' => $assessment->id,
                'micu_assessment_parameter_id' => $paramId,
                'value' => $value,
                'unit' => $param->default_unit ?? null,
            ]);
        }

        $this->showAssessmentModal = false;
        $this->dispatch('toast', type: 'success', message: 'Assessment recorded.');
    }

    // ── Helpers ──

    private function generateOrderCode(string $prefix, string $modelClass): string
    {
        $last = $modelClass::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('request_code', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->request_code, strlen($prefix) + 1)) + 1 : 1;
        return $prefix . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function resetOrderModals(): void
    {
        $this->reset([
            'showOrderLabModal', 'showOrderCsrModal', 'showOrderRadiologyModal',
            'showOrderPharmacyModal', 'showOrderProcedureModal',
            'selectedLabItems', 'selectedCsrItems', 'selectedRadiologyItems',
            'selectedPharmacyItems', 'selectedProcedureItems',
            'labItemQuantities', 'csrItemQuantities', 'pharmacyItemQuantities',
            'orderNotes', 'orderPriority',
        ]);
    }

    public function render()
    {
        $tid = $this->currentTenantId();

        $currentAdmission = $this->activeAdmissionForTreatmentId
            ? Admission::with(['patient', 'attendingPhysician', 'admittingPhysician', 'icuBed.ward',
                'labRequests.requestItems', 'csrRequests.requestItems', 'radiologyRequests.requestItems',
                'pharmacyRequests.requestItems', 'procedureRequests.requestItems'])
                ->find($this->activeAdmissionForTreatmentId)
            : null;

        $micuPatients = Admission::with(['patient', 'attendingPhysician', 'admittingPhysician', 'icuBed.ward'])
            ->where('tenant_id', $tid)
            ->where('status', 'Admitted')
            ->whereHas('icuBed.ward', fn ($q) => $q->where('code', 'MICU'))
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('admission_code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('patient', fn ($pq) =>
                            $pq->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                        )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $labTests = \App\Models\LabTest::with('labSection')->where('tenant_id', $tid)->orderBy('name')->get();
        $csrItems = \App\Models\Supply\Item::with('category')->where('tenant_id', $tid)
            ->whereHas('batches', function ($q) {
                $q->where('quantity_on_hand', '>', 0)
                  ->where('expiry_date', '>=', now()->toDateString())
                  ->where('history', 1)
                  ->where('is_active', 1);
            })
            ->orderBy('name')->get();
        $radiologyExams = \App\Models\Radiology\RadiologyExamType::where('tenant_id', $tid)->orderBy('name')->get();
        $pharmacyDrugs = \App\Models\Pharmacy\Drug::where('tenant_id', $tid)->whereHas('batches', function ($q) { $q->where('quantity_on_hand', '>', 0)->where('expiry_date', '>=', now()->toDateString())->where('history', 1); })->orderBy('generic_name')->get();
        $procedures = \App\Models\Procedures\Procedure::with('category')->where('tenant_id', $tid)->orderBy('name')->get();

        $assessmentParameters = MicuAssessmentParameter::query()
            ->where('is_active', true)
            ->orderBy('category')->orderBy('name')
            ->get();

        $assessments = $currentAdmission
            ? MicuAssessment::with(['values.parameter', 'assessedBy'])
                ->where('admission_id', $currentAdmission->id)
                ->orderBy('assessed_at', 'desc')
                ->get()
            : collect();

        $doctors = HealthCareProfessional::query()
            ->where('tenant_id', $tid)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return view('livewire.micu.micu-patient-crud', compact(
            'micuPatients', 'currentAdmission', 'labTests', 'csrItems',
            'radiologyExams', 'pharmacyDrugs', 'procedures',
            'assessmentParameters', 'assessments', 'doctors'
        ));
    }
}
