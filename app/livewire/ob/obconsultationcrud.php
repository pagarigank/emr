<?php

namespace App\Livewire\Ob;

use App\Models\Ob\ObConsultation;
use App\Models\Ob\ObVisitType;
use App\Livewire\Concerns\ChecksActiveEncounters;
use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\LabTest;
use App\Models\LabPanel;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\Supply\Item;
use App\Models\CsrRequest;
use App\Models\CsrRequestItem;
use App\Models\Department;
use App\Models\Radiology\RadiologyExamType;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Radiology\RadiologyRequestItem;
use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Pharmacy\PharmacyRequestItem;
use App\Models\Procedures\Procedure;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Procedures\ProcedureRequestItem;
use App\Models\Delivery\DeliverySchedule;
use App\Models\Delivery\DeliveryRoom;
use App\Models\Delivery\DeliveryType;
use App\Models\Delivery\DeliveryComplication;
use Livewire\Component;
use Livewire\WithPagination;

class ObConsultationCrud extends Component
{
    use WithPagination, ChecksActiveEncounters;

    public string $search        = '';
    public string $sortField     = 'id';
    public string $sortDirection = 'desc';
    public int    $perPage       = 10;

    public bool $showTreatmentForm = false;
    public ?int $treatingId = null;

    // OB-specific fields
    public ?int $attending_physician_id = null;
    public ?int $visit_type_id = null;
    public string $gestational_age = '';
    public string $lmp_date = '';
    public string $edc_date = '';
    public ?int $gravida = null;
    public ?int $para = null;
    public ?int $full_term = null;
    public ?int $preterm = null;
    public ?int $abortion = null;
    public ?int $living_children = null;
    public string $obstetrical_history = '';

    // Treatment Form Fields
    public string $treatment_history_of_present_illness = '';
    public string $treatment_physical_exam_findings = '';
    public string $treatment_assessment = '';
    public string $treatment_orders_summary = '';
    public string $treatment_treatment_notes = '';

    // Disposition Fields
    public string $disposition = 'Pending';
    public string $discharge_instructions = '';

    // Order Modals
    public bool $showOrderLabModal = false, $showOrderCsrModal = false, $showOrderRadiologyModal = false;
    public array $orderLabSelection = [];
    public string $orderLabPriority = 'Routine';
    public string $orderLabNotes = '';
    public array $orderCsrSelection = [];
    public array $orderCsrQuantities = [];
    public string $orderCsrNotes = '';
    public array $orderRadiologySelection = [];
    public string $orderRadiologyPriority = 'Routine';
    public string $orderRadiologyNotes = '';

    // Pharmacy Order Modal
    public bool $showOrderPharmacyModal = false;
    public array $orderPharmacySelection = [];
    public array $orderPharmacyQuantities = [];
    public array $orderPharmacyFrequencies = [];
    public array $orderPharmacyRoutes = [];
    public string $orderPharmacyPriority = 'Routine';
    public string $orderPharmacyNotes = '';

    // Procedure Order Modal
    public bool $showOrderProcedureModal = false;
    public array $orderProcedureSelection = [];
    public array $orderProcedureQuantities = [];
    public string $orderProcedureNotes = '';

    // DR Schedule form
    public bool $showDrScheduleModal = false;
    public ?string $dr_room_id = null;
    public ?int $dr_attending_physician_id = null, $dr_anesthesiologist_id = null;
    public ?int $dr_pediatrician_id = null, $dr_midwife_id = null;
    public string $delivery_type = '', $delivery_description = '';
    public string $dr_scheduled_date = '', $dr_start_time = '', $dr_end_time = '';
    public string $dr_pre_op_notes = '', $dr_intra_op_notes = '', $dr_post_op_notes = '';

    public bool $showCompleted = false;

    protected $listeners = ['refreshObOrders' => 'refreshOrders'];

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

    private function obDepartmentId(): ?int
    {
        static $id = null;
        if ($id === null) {
            $dept = Department::where('dept_code', 'OB')->where('is_active', true)->first();
            $id = $dept?->id;
        }
        return $id;
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field)
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    // Patient search for create modal
    public string $patient_search = '';
    public ?int $patient_id = null;
    public bool $skipPatientSearchUpdate = false;
    public string $selected_patient_name = '';

    public function updatedPatientSearch(): void
    {
        if ($this->skipPatientSearchUpdate) {
            $this->skipPatientSearchUpdate = false;
            return;
        }
        $this->patient_id = null;
        $this->selected_patient_name = '';
    }

    public function selectPatient(int $id): void
    {
        $patient = Patient::find($id);
        if ($patient) {
            $this->skipPatientSearchUpdate = true;
            $this->patient_id = $patient->id;
            $this->selected_patient_name = $patient->last_name . ', ' . $patient->first_name;
            $this->patient_search = '';
        }
    }

    public function clearPatient(): void
    {
        $this->patient_id = null;
        $this->selected_patient_name = '';
    }

    // ——— Create ———
    public bool $showCreateModal = false;

    public function openCreate(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'attending_physician_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'visit_type_id' => ['nullable', 'integer', 'exists:ob_visit_types,id'],
            'gestational_age' => ['nullable', 'string', 'max:20'],
            'lmp_date' => ['nullable', 'date'],
            'edc_date' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'para' => ['nullable', 'integer', 'min:0'],
            'full_term' => ['nullable', 'integer', 'min:0'],
            'preterm' => ['nullable', 'integer', 'min:0'],
            'abortion' => ['nullable', 'integer', 'min:0'],
            'living_children' => ['nullable', 'integer', 'min:0'],
            'obstetrical_history' => ['nullable', 'string', 'max:5000'],
        ], ['patient_id.required' => 'Select a patient.']);

        $this->ensureNoActiveEncounter($this->patient_id);

        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        ObConsultation::create([
            'tenant_id' => $tid,
            'consultation_code' => $this->generateCode($tid),
            'patient_id' => $this->patient_id,
            'attending_physician_id' => $this->attending_physician_id,
            'visit_type_id' => $this->visit_type_id,
            'gestational_age' => $this->gestational_age ?: null,
            'lmp_date' => $this->lmp_date ?: null,
            'edc_date' => $this->edc_date ?: null,
            'gravida' => $this->gravida,
            'para' => $this->para,
            'full_term' => $this->full_term,
            'preterm' => $this->preterm,
            'abortion' => $this->abortion,
            'living_children' => $this->living_children,
            'obstetrical_history' => $this->obstetrical_history ?: null,
            'status' => 'Active',
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->showCreateModal = false;
        $this->resetCreateForm();
        $this->dispatch('toast', type: 'success', message: 'OB consultation created.');
    }

    private function generateCode(int $tid): string
    {
        $last = ObConsultation::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->consultation_code, 4)) + 1 : 1;
        return 'OBC-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function resetCreateForm(): void
    {
        $this->reset([
            'patient_id', 'patient_search', 'selected_patient_name',
            'attending_physician_id', 'visit_type_id',
            'gestational_age', 'lmp_date', 'edc_date',
            'gravida', 'para', 'full_term', 'preterm', 'abortion', 'living_children',
            'obstetrical_history',
        ]);
    }

    // ——— Treatment Form ———
    public function openTreatment(int $id): void
    {
        $adm = ObConsultation::findOrFail($id);
        $this->treatingId = $adm->id;
        $this->attending_physician_id = $adm->attending_physician_id;
        $this->visit_type_id = $adm->visit_type_id;
        $this->gestational_age = $adm->gestational_age ?? '';
        $this->lmp_date = $adm->lmp_date?->format('Y-m-d') ?? '';
        $this->edc_date = $adm->edc_date?->format('Y-m-d') ?? '';
        $this->gravida = $adm->gravida;
        $this->para = $adm->para;
        $this->full_term = $adm->full_term;
        $this->preterm = $adm->preterm;
        $this->abortion = $adm->abortion;
        $this->living_children = $adm->living_children;
        $this->obstetrical_history = $adm->obstetrical_history ?? '';
        $this->treatment_history_of_present_illness = $adm->history_of_present_illness ?? '';
        $this->treatment_physical_exam_findings = $adm->physical_exam_findings ?? '';
        $this->treatment_assessment = $adm->assessment ?? '';
        $this->treatment_orders_summary = $adm->orders_summary ?? '';
        $this->treatment_treatment_notes = $adm->treatment_notes ?? '';
        $this->disposition = $adm->disposition ?? 'Pending';
        $this->discharge_instructions = $adm->discharge_instructions ?? '';
        $this->showTreatmentForm = true;
    }

    public function saveTreatment(): void
    {
        $validated = $this->validate([
            'attending_physician_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'visit_type_id' => ['nullable', 'integer', 'exists:ob_visit_types,id'],
            'gestational_age' => ['nullable', 'string', 'max:20'],
            'lmp_date' => ['nullable', 'date'],
            'edc_date' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'para' => ['nullable', 'integer', 'min:0'],
            'full_term' => ['nullable', 'integer', 'min:0'],
            'preterm' => ['nullable', 'integer', 'min:0'],
            'abortion' => ['nullable', 'integer', 'min:0'],
            'living_children' => ['nullable', 'integer', 'min:0'],
            'obstetrical_history' => ['nullable', 'string', 'max:5000'],
            'treatment_history_of_present_illness' => ['nullable', 'string', 'max:10000'],
            'treatment_physical_exam_findings' => ['nullable', 'string', 'max:10000'],
            'treatment_assessment' => ['nullable', 'string', 'max:10000'],
            'treatment_orders_summary' => ['nullable', 'string', 'max:10000'],
            'treatment_treatment_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $adm = ObConsultation::findOrFail($this->treatingId);
        $adm->update([
            'attending_physician_id' => $validated['attending_physician_id'],
            'visit_type_id' => $validated['visit_type_id'],
            'gestational_age' => $validated['gestational_age'] ?: null,
            'lmp_date' => $validated['lmp_date'] ?: null,
            'edc_date' => $validated['edc_date'] ?: null,
            'gravida' => $validated['gravida'],
            'para' => $validated['para'],
            'full_term' => $validated['full_term'],
            'preterm' => $validated['preterm'],
            'abortion' => $validated['abortion'],
            'living_children' => $validated['living_children'],
            'obstetrical_history' => $validated['obstetrical_history'] ?: null,
            'history_of_present_illness' => $validated['treatment_history_of_present_illness'],
            'physical_exam_findings' => $validated['treatment_physical_exam_findings'],
            'assessment' => $validated['treatment_assessment'],
            'orders_summary' => $validated['treatment_orders_summary'],
            'treatment_notes' => $validated['treatment_treatment_notes'],
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'OB consultation updated.');
    }

    public function closeTreatment(): void
    {
        $this->showTreatmentForm = false;
        $this->showOrderLabModal = false;
        $this->showOrderCsrModal = false;
        $this->showOrderRadiologyModal = false;
        $this->showOrderPharmacyModal = false;
        $this->showOrderProcedureModal = false;
        $this->showDrScheduleModal = false;
        $this->reset(['treatingId', 'orderLabPriority', 'orderLabNotes', 'orderCsrNotes', 'orderRadiologyPriority', 'orderRadiologyNotes', 'orderCsrQuantities', 'orderPharmacyPriority', 'orderPharmacyNotes', 'orderPharmacyQuantities', 'orderProcedureNotes', 'orderProcedureQuantities']);
    }

    // ——— DR Schedule ———
    public function openDrScheduleForm(): void
    {
        $this->resetDrScheduleForm();
        $this->dr_scheduled_date = now()->format('Y-m-d');
        if ($this->treatingId) {
            $adm = ObConsultation::find($this->treatingId);
            if ($adm) {
                $this->dr_attending_physician_id = $adm->attending_physician_id;
            }
        }
        $this->showDrScheduleModal = true;
    }

    public function saveDrSchedule(): void
    {
        $this->validate([
            'dr_room_id' => ['required', 'integer', 'exists:dr_rooms,id'],
            'dr_scheduled_date' => ['required', 'date'],
            'delivery_type' => ['nullable', 'string', 'max:50'],
            'dr_attending_physician_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'dr_anesthesiologist_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'dr_pediatrician_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'dr_midwife_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
        ]);

        $adm = ObConsultation::findOrFail($this->treatingId);
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        DeliverySchedule::create([
            'tenant_id' => $tid,
            'schedule_code' => $this->generateDrScheduleCode($tid),
            'dr_room_id' => $this->dr_room_id,
            'patient_id' => $adm->patient_id,
            'ob_consultation_id' => $adm->id,
            'attending_physician_id' => $this->dr_attending_physician_id,
            'pediatrician_id' => $this->dr_pediatrician_id,
            'anesthesiologist_id' => $this->dr_anesthesiologist_id,
            'midwife_id' => $this->dr_midwife_id,
            'delivery_type' => $this->delivery_type ?: null,
            'delivery_description' => $this->delivery_description ?: null,
            'scheduled_date' => $this->dr_scheduled_date,
            'start_time' => $this->dr_start_time ?: null,
            'end_time' => $this->dr_end_time ?: null,
            'gestational_age' => $adm->gestational_age,
            'lmp_date' => $adm->lmp_date,
            'edc_date' => $adm->edc_date,
            'gravida' => $adm->gravida,
            'para' => $adm->para,
            'full_term' => $adm->full_term,
            'preterm' => $adm->preterm,
            'abortion' => $adm->abortion,
            'living_children' => $adm->living_children,
            'pre_op_notes' => $this->dr_pre_op_notes ?: null,
            'intra_op_notes' => $this->dr_intra_op_notes ?: null,
            'post_op_notes' => $this->dr_post_op_notes ?: null,
            'status' => 'Scheduled',
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->closeDrScheduleModal();
        $this->dispatch('toast', type: 'success', message: 'Delivery schedule created successfully.');
    }

    private function generateDrScheduleCode(int $tid): string
    {
        $num = (DeliverySchedule::withoutGlobalScopes()->where('tenant_id', $tid)->max('id') ?? 0) + 1;
        return 'DRS-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function resetDrScheduleForm(): void
    {
        $this->reset([
            'dr_room_id', 'dr_attending_physician_id', 'dr_anesthesiologist_id',
            'dr_pediatrician_id', 'dr_midwife_id',
            'delivery_type', 'delivery_description',
            'dr_scheduled_date', 'dr_start_time', 'dr_end_time',
            'dr_pre_op_notes', 'dr_intra_op_notes', 'dr_post_op_notes',
        ]);
    }

    public function closeDrScheduleModal(): void
    {
        $this->showDrScheduleModal = false;
        $this->resetDrScheduleForm();
    }

    // ——— Order Refresh ———
    public function refreshOrders(): void
    {
        if ($this->treatingId === null) return;
        $this->dispatch('$refresh');
    }

    // ——— Inline Order Status Updates ———
    public function updateLabItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $item = LabRequestItem::findOrFail($itemId);
        $item->update([
            'status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);

        $allStatuses = $item->labRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Completed') => 'Completed',
            $allStatuses->contains('Processing') || $allStatuses->contains('Collected') => 'Processing',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $item->labRequest->update(['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()]);

        $this->dispatch('toast', type: 'success', message: 'Lab item status updated.');
    }

    public function cancelLabRequest(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        LabRequest::findOrFail($requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        LabRequestItem::where('lab_request_id', $requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        $this->dispatch('toast', type: 'success', message: 'Lab request cancelled.');
    }

    public function updateCsrItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $item = CsrRequestItem::findOrFail($itemId);
        $item->update(['status' => $status, 'updated_by' => $uid, 'updated_date' => now()]);

        $allStatuses = $item->csrRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $item->csrRequest->update(['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()]);

        $this->dispatch('toast', type: 'success', message: 'CSR item status updated.');
    }

    public function cancelCsrRequest(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        CsrRequest::findOrFail($requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        CsrRequestItem::where('csr_request_id', $requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        $this->dispatch('toast', type: 'success', message: 'CSR request cancelled.');
    }

    public function updateRadiologyItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $item = RadiologyRequestItem::findOrFail($itemId);
        $item->update(['status' => $status, 'updated_by' => $uid, 'updated_date' => now()]);

        $allStatuses = $item->radiologyRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $item->radiologyRequest->update(['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()]);

        $this->dispatch('toast', type: 'success', message: 'Radiology item status updated.');
    }

    public function cancelRadiologyRequest(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        RadiologyRequest::findOrFail($requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        RadiologyRequestItem::where('radiology_request_id', $requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        $this->dispatch('toast', type: 'success', message: 'Radiology request cancelled.');
    }

    public function updatePharmacyItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $item = PharmacyRequestItem::findOrFail($itemId);
        $item->update(['status' => $status, 'updated_by' => $uid, 'updated_date' => now()]);

        $allStatuses = $item->pharmacyRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $item->pharmacyRequest->update(['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()]);

        $this->dispatch('toast', type: 'success', message: 'Pharmacy item status updated.');
    }

    public function cancelPharmacyRequest(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        PharmacyRequest::findOrFail($requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        PharmacyRequestItem::where('pharmacy_request_id', $requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        $this->dispatch('toast', type: 'success', message: 'Pharmacy request cancelled.');
    }

    public function updateProcedureItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $item = ProcedureRequestItem::findOrFail($itemId);
        $item->update(['status' => $status, 'updated_by' => $uid, 'updated_date' => now()]);

        $allStatuses = $item->procedureRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $item->procedureRequest->update(['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()]);

        $this->dispatch('toast', type: 'success', message: 'Procedure item status updated.');
    }

    public function cancelProcedureRequest(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        ProcedureRequest::findOrFail($requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        ProcedureRequestItem::where('procedure_request_id', $requestId)->update(['status' => 'Cancelled', 'updated_by' => $uid, 'updated_date' => $now]);
        $this->dispatch('toast', type: 'success', message: 'Procedure request cancelled.');
    }

    // ——— Order Requests ———
    public function openOrderLab(): void
    {
        $this->orderLabSelection = [];
        $this->orderLabPriority = 'Routine';
        $this->orderLabNotes = '';
        $this->showOrderLabModal = true;
    }

    public function openOrderCsr(): void
    {
        $this->orderCsrSelection = [];
        $this->orderCsrQuantities = [];
        $this->orderCsrNotes = '';
        $this->showOrderCsrModal = true;
    }

    public function openOrderRadiology(): void
    {
        $this->orderRadiologySelection = [];
        $this->orderRadiologyPriority = 'Routine';
        $this->orderRadiologyNotes = '';
        $this->showOrderRadiologyModal = true;
    }

    public function openOrderPharmacy(): void
    {
        $this->orderPharmacySelection = [];
        $this->orderPharmacyQuantities = [];
        $this->orderPharmacyFrequencies = [];
        $this->orderPharmacyRoutes = [];
        $this->orderPharmacyPriority = 'Routine';
        $this->orderPharmacyNotes = '';
        $this->showOrderPharmacyModal = true;
    }

    public function openOrderProcedure(): void
    {
        $this->orderProcedureSelection = [];
        $this->orderProcedureQuantities = [];
        $this->orderProcedureNotes = '';
        $this->showOrderProcedureModal = true;
    }

    public function requestLabOrder(): void
    {
        $this->validate([
            'orderLabSelection' => ['required', 'array', 'min:1'],
            'orderLabSelection.*' => ['required', 'integer'],
            'orderLabPriority' => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'orderLabNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderLabSelection.required' => 'Select at least one test or panel.']);

        $adm = ObConsultation::findOrFail($this->treatingId);
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        $code = $this->generateLabRequestCode($tid);
        $total = 0;
        $items = [];

        foreach ($this->orderLabSelection as $id) {
            $test = LabTest::find($id);
            if ($test) {
                $charge = (float) ($test->standard_charge ?? 0);
                $items[] = [
                    'item_type' => 'Test',
                    'lab_test_id' => $test->id,
                    'test_name' => $test->name,
                    'charge_amount' => $charge,
                ];
                $total += $charge;
            }
        }

        $request = LabRequest::create([
            'tenant_id' => $tid,
            'ob_consultation_id' => $adm->id,
            'request_code' => $code,
            'patient_id' => $adm->patient_id,
            'requesting_department_id' => $this->obDepartmentId(),
            'requesting_doctor_id' => $adm->attending_physician_id,
            'priority' => $this->orderLabPriority,
            'request_notes' => 'Ordered from OB Consultation' . ($this->orderLabNotes ? "\n\n" . $this->orderLabNotes : ''),
            'status' => 'Pending',
            'billing_status' => 'Pending',
            'total_charge' => $total,
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now,
                'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderLabModal = false;
        $this->orderLabSelection = [];
        $this->orderLabNotes = '';
        $this->dispatch('toast', type: 'success', message: "Lab request {$code} created.");
    }

    public function requestCsrOrder(): void
    {
        $this->validate([
            'orderCsrSelection' => ['required', 'array', 'min:1'],
            'orderCsrSelection.*' => ['required', 'integer'],
            'orderCsrQuantities' => ['nullable', 'array'],
            'orderCsrNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderCsrSelection.required' => 'Select at least one supply item.']);

        $adm = ObConsultation::findOrFail($this->treatingId);
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        $code = $this->generateCsrRequestCode($tid);
        $total = 0;
        $items = [];

        foreach ($this->orderCsrSelection as $id) {
            $item = Item::find($id);
            if ($item) {
                $qty = max(1, (int) ($this->orderCsrQuantities[$id] ?? 1));
                $cost = (float) ($item->unit_cost ?? 0);
                $lineTotal = $qty * $cost;
                $items[] = [
                    'item_id' => $item->id, 'item_name' => $item->name, 'item_code' => $item->code,
                    'supply_unit_name' => $item->unit?->name ?? '', 'quantity' => $qty,
                    'unit_cost' => $cost, 'total_cost' => $lineTotal,
                ];
                $total += $lineTotal;
            }
        }

        $request = CsrRequest::create([
            'tenant_id' => $tid, 'ob_consultation_id' => $adm->id, 'request_code' => $code,
            'requesting_department_id' => $this->obDepartmentId(), 'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
            'status' => 'Pending', 'billing_status' => 'Pending', 'total_amount' => $total,
            'notes' => 'Ordered from OB Consultation' . ($this->orderCsrNotes ? "\n\n" . $this->orderCsrNotes : ''),
            'is_active' => true, 'history' => 1, 'created_by' => $uid, 'created_date' => $now,
            'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now,
                'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderCsrModal = false;
        $this->orderCsrSelection = [];
        $this->orderCsrQuantities = [];
        $this->orderCsrNotes = '';
        $this->dispatch('toast', type: 'success', message: "Supply request {$code} created.");
    }

    public function requestRadiologyOrder(): void
    {
        $this->validate([
            'orderRadiologySelection' => ['required', 'array', 'min:1'],
            'orderRadiologySelection.*' => ['required', 'integer'],
            'orderRadiologyPriority' => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'orderRadiologyNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderRadiologySelection.required' => 'Select at least one exam type.']);

        $adm = ObConsultation::findOrFail($this->treatingId);
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        $code = $this->generateRadiologyRequestCode($tid);
        $total = 0;
        $items = [];

        foreach ($this->orderRadiologySelection as $id) {
            $exam = RadiologyExamType::with('modality')->find($id);
            if ($exam) {
                $charge = (float) ($exam->standard_charge ?? 0);
                $items[] = [
                    'exam_type_id' => $exam->id, 'exam_name' => $exam->name,
                    'modality_name' => $exam->modality?->name ?? '', 'charge_amount' => $charge,
                ];
                $total += $charge;
            }
        }

        $request = RadiologyRequest::create([
            'tenant_id' => $tid, 'ob_consultation_id' => $adm->id, 'request_code' => $code,
            'requesting_department_id' => $this->obDepartmentId(), 'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
            'status' => 'Pending', 'billing_status' => 'Pending', 'total_amount' => $total,
            'notes' => 'Ordered from OB Consultation' . ($this->orderRadiologyNotes ? "\n\n" . $this->orderRadiologyNotes : ''),
            'is_active' => true, 'history' => 1, 'created_by' => $uid, 'created_date' => $now,
            'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now,
                'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderRadiologyModal = false;
        $this->orderRadiologySelection = [];
        $this->orderRadiologyNotes = '';
        $this->dispatch('toast', type: 'success', message: "Radiology request {$code} created.");
    }

    public function requestPharmacyOrder(): void
    {
        $this->validate([
            'orderPharmacySelection' => ['required', 'array', 'min:1'],
            'orderPharmacySelection.*' => ['required', 'integer'],
            'orderPharmacyPriority' => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'orderPharmacyNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderPharmacySelection.required' => 'Select at least one drug.']);

        $adm = ObConsultation::findOrFail($this->treatingId);
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        $code = $this->generatePharmacyRequestCode($tid);
        $total = 0;
        $items = [];

        foreach ($this->orderPharmacySelection as $id) {
            $drug = Drug::find($id);
            if ($drug) {
                $qty = max(1, (int) ($this->orderPharmacyQuantities[$id] ?? 1));
                $cost = (float) ($drug->batches()->where('quantity_on_hand', '>', 0)->avg('selling_price') ?: 0);
                $lineTotal = $qty * $cost;
                $items[] = [
                    'drug_id'             => $drug->id,
                    'item_id'             => $drug->id,
                    'item_name'           => $drug->generic_name . ' ' . $drug->drug_strength,
                    'item_code'           => $drug->drug_code,
                    'supply_unit_name'    => $drug->dosage_form ?? '',
                    'generic_name'        => $drug->generic_name,
                    'brand_name'          => $drug->brand_name ?? null,
                    'dosage_form'         => $drug->dosage_form ?? null,
                    'philhealth_item_code'=> $drug->philhealth_item_code ?? null,
                    'dosage'              => $drug->drug_strength ?? '',
                    'frequency'           => $this->orderPharmacyFrequencies[$id] ?? null,
                    'route'               => $this->orderPharmacyRoutes[$id] ?? $drug->route ?? null,
                    'quantity'            => $qty,
                    'unit_cost'           => $cost,
                    'total_cost'          => $lineTotal,
                ];
                $total += $lineTotal;
            }
        }

        $request = PharmacyRequest::create([
            'tenant_id' => $tid, 'ob_consultation_id' => $adm->id, 'request_code' => $code,
            'requesting_department_id' => $this->obDepartmentId(), 'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
            'priority' => $this->orderPharmacyPriority, 'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from OB Consultation' . ($this->orderPharmacyNotes ? "\n\n" . $this->orderPharmacyNotes : ''),
            'is_active' => true, 'history' => 1, 'created_by' => $uid, 'created_date' => $now,
            'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now,
                'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderPharmacyModal = false;
        $this->orderPharmacySelection = [];
        $this->orderPharmacyQuantities = [];
        $this->orderPharmacyFrequencies = [];
        $this->orderPharmacyRoutes = [];
        $this->orderPharmacyNotes = '';
        $this->dispatch('toast', type: 'success', message: "Pharmacy request {$code} created.");
    }

    public function requestProcedureOrder(): void
    {
        $this->validate([
            'orderProcedureSelection' => ['required', 'array', 'min:1'],
            'orderProcedureSelection.*' => ['required', 'integer'],
            'orderProcedureNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderProcedureSelection.required' => 'Select at least one procedure.']);

        $adm = ObConsultation::findOrFail($this->treatingId);
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        $code = $this->generateProcedureRequestCode($tid);
        $total = 0;
        $items = [];

        foreach ($this->orderProcedureSelection as $id) {
            $procedure = Procedure::with('category')->find($id);
            if ($procedure) {
                $qty = max(1, (int) ($this->orderProcedureQuantities[$id] ?? 1));
                $charge = (float) ($procedure->standard_charge ?? 0);
                $lineTotal = $qty * $charge;
                $items[] = [
                    'procedure_id' => $procedure->id, 'procedure_name' => $procedure->name,
                    'category_name' => $procedure->category?->name ?? '', 'quantity' => $qty,
                    'unit_charge' => $charge, 'total_charge' => $lineTotal,
                ];
                $total += $lineTotal;
            }
        }

        $request = ProcedureRequest::create([
            'tenant_id' => $tid, 'ob_consultation_id' => $adm->id, 'request_code' => $code,
            'requesting_department_id' => $this->obDepartmentId(), 'requesting_doctor_id' => $adm->attending_physician_id,
            'status' => 'Pending', 'billing_status' => 'Pending', 'total_amount' => $total,
            'notes' => 'Ordered from OB Consultation' . ($this->orderProcedureNotes ? "\n\n" . $this->orderProcedureNotes : ''),
            'is_active' => true, 'history' => 1, 'created_by' => $uid, 'created_date' => $now,
            'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now,
                'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderProcedureModal = false;
        $this->orderProcedureSelection = [];
        $this->orderProcedureQuantities = [];
        $this->orderProcedureNotes = '';
        $this->dispatch('toast', type: 'success', message: "Procedure request {$code} created.");
    }

    private function generateLabRequestCode(int $tid): string
    {
        $num = (LabRequest::withoutGlobalScopes()->where('tenant_id', $tid)->max('id') ?? 0) + 1;
        return 'LR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateCsrRequestCode(int $tid): string
    {
        $num = (CsrRequest::withoutGlobalScopes()->where('tenant_id', $tid)->max('id') ?? 0) + 1;
        return 'CR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateRadiologyRequestCode(int $tid): string
    {
        $num = (RadiologyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->max('id') ?? 0) + 1;
        return 'RR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generatePharmacyRequestCode(int $tid): string
    {
        $num = (PharmacyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->max('id') ?? 0) + 1;
        return 'PR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateProcedureRequestCode(int $tid): string
    {
        $num = (ProcedureRequest::withoutGlobalScopes()->where('tenant_id', $tid)->max('id') ?? 0) + 1;
        return 'PRCD-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ——— Disposition ———
    public function saveDisposition(): void
    {
        $validated = $this->validate([
            'disposition' => ['required', 'string', 'in:Pending,Delivered,Admitted to Ward,Admitted to ICU,Discharged Home,AMA,Expired'],
            'discharge_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $adm = ObConsultation::findOrFail($this->treatingId);

        $status = 'Active';
        $dispositionDate = null;

        if ($validated['disposition'] !== 'Pending') {
            $status = 'Completed';
            $dispositionDate = now();
        }

        $adm->update([
            'disposition' => $validated['disposition'],
            'status' => $status,
            'disposition_datetime' => $dispositionDate,
            'discharge_instructions' => $validated['discharge_instructions'],
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Disposition updated.');
        if ($this->showTreatmentForm) {
            $this->showTreatmentForm = false;
            $this->reset(['treatingId']);
        }
    }

    public function render()
    {
        $statusFilter = $this->showCompleted ? 'Completed' : 'Active';

        $consultations = ObConsultation::with(['patient', 'attendingPhysician', 'visitType'])
            ->where('status', $statusFilter)
            ->when(!$this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('consultation_code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('patient', fn ($p) =>
                            $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                        )
                )
            )
            ->when($this->showCompleted, fn ($q) => $q->orderBy('disposition_datetime', 'desc'), fn ($q) => $q->orderBy($this->sortField, $this->sortDirection))
            ->paginate($this->perPage);

        $activeConsultation = null;
        $labOrders = collect();
        $csrOrders = collect();
        $radiologyOrders = collect();
        $pharmacyOrders = collect();
        $procedureOrders = collect();
        $labTests = collect();
        $supplyItems = collect();
        $examTypes = collect();
        $drugs = collect();
        $procedures = collect();
        $drSchedules = collect();
        $drRooms = collect();
        $doctors = collect();
        $deliveryTypes = collect();
        $deliveryComplications = collect();

        if ($this->showTreatmentForm && $this->treatingId !== null) {
            $activeConsultation = ObConsultation::with([
                'patient', 'attendingPhysician', 'visitType',
                'labRequests.items', 'csrRequests.items', 'radiologyRequests.items',
                'pharmacyRequests.items', 'procedureRequests.items',
            ])->find($this->treatingId);

            $labOrders = $activeConsultation->labRequests()->with('items.labTest', 'items.labPanel')
                ->orderBy('created_date', 'desc')->get();
            $csrOrders = $activeConsultation->csrRequests()->with('items.item')
                ->orderBy('created_date', 'desc')->get();
            $radiologyOrders = $activeConsultation->radiologyRequests()->with('items.examType')
                ->orderBy('created_date', 'desc')->get();
            $pharmacyOrders = $activeConsultation->pharmacyRequests()->with('items.drug')
                ->orderBy('created_date', 'desc')->get();
            $procedureOrders = $activeConsultation->procedureRequests()->with('items.procedure')
                ->orderBy('created_date', 'desc')->get();

            $drSchedules = DeliverySchedule::with(['drRoom', 'attendingPhysician', 'anesthesiologist', 'pediatrician', 'midwife'])
                ->where('ob_consultation_id', $this->treatingId)
                ->orderBy('scheduled_date', 'desc')
                ->get();
        }

        $patients = collect();
        if ($this->patient_search && !$this->patient_id) {
            $s = '%' . $this->patient_search . '%';
            $patients = Patient::where(function ($q) use ($s) {
                $q->where('last_name', 'like', $s)
                    ->orWhere('first_name', 'like', $s)
                    ->orWhere('patient_code', 'like', $s);
            })->when(!$this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
                ->orderBy('last_name')->take(10)->get();
        }
        $labTests = LabTest::where('is_active', true)
            ->when(!$this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->orderBy('name')->get(['id', 'code', 'name', 'standard_charge']);

        $supplyItems = Item::where('is_active', true)
            ->when(!$this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->whereHas('batches', function ($query) {
                $query->where('quantity_on_hand', '>', 0)
                      ->where('expiry_date', '>=', now()->toDateString())
                      ->where('history', 1)
                      ->where('is_active', 1);
            })
            ->with('unit')->orderBy('name')->get(['id', 'code', 'name', 'unit_cost']);

        $examTypes = RadiologyExamType::where('is_active', true)
            ->when(!$this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->with('modality')->orderBy('name')->get(['id', 'code', 'name', 'standard_charge']);

        $drugs = Drug::where('is_active', true)
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->whereHas('batches', function ($query) {
                $query->where('quantity_on_hand', '>', 0)
                      ->where('expiry_date', '>=', now()->toDateString())
                      ->where('history', 1)
                      ->where('is_active', 1);
            })
            ->orderBy('generic_name')
            ->get(['id', 'drug_code', 'generic_name', 'brand_name', 'drug_strength', 'dosage_form', 'route']);

        $procedures = Procedure::where('is_active', true)
            ->when(!$this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->with('category')->orderBy('name')
            ->get(['id', 'name', 'category_id', 'standard_charge']);

        $drRooms = DeliveryRoom::query()
            ->where('tenant_id', $this->currentTenantId())
            ->where('status', 'Available')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $doctors = HealthCareProfessional::query()
            ->where('tenant_id', $this->currentTenantId())
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $deliveryTypes = DeliveryType::where('is_active', true)
            ->orderBy('name')->get(['id', 'name']);

        $deliveryComplications = DeliveryComplication::where('is_active', true)
            ->orderBy('name')->get(['id', 'name']);

        $physicians = HealthCareProfessional::when(!$this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->orderBy('full_name')->get();

        $visitTypes = ObVisitType::where('is_active', true)
            ->orderBy('name')->get(['id', 'name']);

        return view('livewire.ob.ob-consultation-crud', compact(
            'consultations', 'activeConsultation', 'labOrders', 'csrOrders',
            'radiologyOrders', 'pharmacyOrders', 'procedureOrders',
            'labTests', 'supplyItems', 'examTypes', 'drugs', 'procedures',
            'drSchedules', 'drRooms', 'doctors', 'deliveryTypes', 'deliveryComplications',
            'patients', 'physicians', 'visitTypes', 'statusFilter',
        ));
    }
}
