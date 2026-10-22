<?php

namespace App\Livewire\RenalCare;

use App\Models\Admission;
use App\Models\CsrRequest;
use App\Models\CsrRequestItem;
use App\Models\Department;
use App\Models\Er\ErAdmission;
use App\Models\HealthCareProfessional;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Pharmacy\PharmacyRequestItem;
use App\Models\Procedures\Procedure;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Procedures\ProcedureRequestItem;
use App\Models\Radiology\RadiologyExamType;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Radiology\RadiologyRequestItem;
use App\Models\RenalCare\DialysisMachine;
use App\Models\RenalCare\DialysisSession;
use App\Models\RenalCare\DialysisType;
use App\Models\Supply\Item;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class DialysisSessionCrud extends Component
{
    use WithPagination;

    public string $search = '', $patient_search = '', $sortField = 'code', $sortDirection = 'desc';
    public string $stationFilter = '', $statusFilter = '', $shiftFilter = '';
    public int $perPage = 10;

    public bool $showModal = false, $showDeleteModal = false, $showTreatmentForm = false, $showDispositionModal = false, $isEditing = false;
    public ?int $editingId = null, $treatingId = null;

    // Session fields
    public string $code = '', $session_date = '', $access_type = 'AV Fistula', $access_site = '';
    public string $shift = 'Shift 1 (AM)';
    public ?int $session_number = null;
    public string $pre_weight = '', $post_weight = '', $dry_weight = '';
    public string $uf_goal_liters = '', $uf_actual_liters = '';
    public ?int $blood_flow_rate_qb = 300, $dialysate_flow_rate_qd = 500;
    public string $dialysate_bath = 'Bicarbonate / K+ 2.0 / Ca++ 1.25';
    public string $anticoagulation_type = 'Heparin';
    public ?int $heparin_dose_units = 5000;
    public string $dialyzer_model = 'Fresenius FX80';
    public int $dialyzer_reuse_number = 0;
    public string $pre_bp = '', $post_bp = '', $heart_rate = '';
    public string $pre_temp = '36.5', $post_temp = '36.6';
    public ?int $pre_rr = 18, $post_rr = 18;
    public string $complications = 'None';
    public string $medications = '', $notes = '', $status = 'Scheduled';

    public ?string $patient_id = null;
    public ?string $dialysis_type_id = null;
    public ?string $dialysis_machine_id = null;
    public ?string $attending_physician_id = null;
    public ?string $dialysis_nurse_id = null;
    public ?string $admission_id = null;
    public ?string $er_admission_id = null;
    public bool $is_active = true;
    public bool $auto_post_billing = true;

    // Treatment form fields
    public string $treatment_history_of_present_illness = '';
    public string $treatment_physical_exam_findings = '';
    public string $treatment_assessment = '';
    public string $treatment_orders_summary = '';
    public string $treatment_treatment_notes = '';

    // Disposition fields
    public string $disposition = 'Pending';
    public string $discharge_instructions = '';

    // Order modals
    public bool $showOrderLabModal = false, $showOrderCsrModal = false, $showOrderRadiologyModal = false;
    public array $orderLabSelection = [];
    public string $orderLabPriority = 'Urgent';
    public string $orderLabNotes = '';
    public array $orderCsrSelection = [];
    public array $orderCsrQuantities = [];
    public string $orderCsrNotes = '';
    public array $orderRadiologySelection = [];
    public string $orderRadiologyPriority = 'Urgent';
    public string $orderRadiologyNotes = '';

    public bool $showOrderPharmacyModal = false;
    public array $orderPharmacySelection = [];
    public array $orderPharmacyQuantities = [];
    public array $orderPharmacyFrequencies = [];
    public array $orderPharmacyRoutes = [];
    public string $orderPharmacyPriority = 'Urgent';
    public string $orderPharmacyNotes = '';

    public bool $showOrderProcedureModal = false;
    public array $orderProcedureSelection = [];
    public array $orderProcedureQuantities = [];
    public string $orderProcedureNotes = '';

    protected $listeners = ['refreshDsOrders' => 'refreshOrders'];

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    private function renalDepartmentId(): ?int
    {
        static $id = null;
        if ($id === null) {
            $dept = Department::where('dept_code', 'NEPHRO')->where('is_active', true)->first();
            $id = $dept?->id;
        }
        return $id;
    }

    protected function rules(): array
    {
        return [
            'code'                   => ['required', 'string', 'max:30'],
            'patient_id'             => ['required', 'integer', 'exists:patients,id'],
            'dialysis_type_id'       => ['nullable', 'integer', 'exists:dialysis_types,id'],
            'dialysis_machine_id'    => ['nullable', 'integer', 'exists:dialysis_machines,id'],
            'shift'                  => ['nullable', 'string', 'max:30'],
            'session_number'         => ['nullable', 'integer', 'min:1', 'max:156'],
            'attending_physician_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'dialysis_nurse_id'      => ['nullable', 'integer'],
            'admission_id'           => ['nullable', 'integer'],
            'er_admission_id'        => ['nullable', 'integer'],
            'access_type'            => ['nullable', 'string', 'max:50'],
            'access_site'            => ['nullable', 'string', 'max:100'],
            'session_date'           => ['required', 'date'],
            'pre_weight'             => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'post_weight'            => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'dry_weight'             => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'uf_goal_liters'         => ['nullable', 'numeric', 'min:0', 'max:20'],
            'uf_actual_liters'       => ['nullable', 'numeric', 'min:0', 'max:20'],
            'blood_flow_rate_qb'     => ['nullable', 'integer', 'min:0', 'max:1000'],
            'dialysate_flow_rate_qd' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'dialysate_bath'         => ['nullable', 'string', 'max:100'],
            'anticoagulation_type'   => ['nullable', 'string', 'max:50'],
            'heparin_dose_units'     => ['nullable', 'integer', 'min:0', 'max:50000'],
            'dialyzer_model'         => ['nullable', 'string', 'max:100'],
            'dialyzer_reuse_number'  => ['nullable', 'integer', 'min:0', 'max:30'],
            'pre_bp'                 => ['nullable', 'string', 'max:20'],
            'post_bp'                => ['nullable', 'string', 'max:20'],
            'pre_temp'               => ['nullable', 'numeric', 'min:30', 'max:45'],
            'post_temp'              => ['nullable', 'numeric', 'min:30', 'max:45'],
            'pre_rr'                 => ['nullable', 'integer', 'min:0', 'max:100'],
            'post_rr'                => ['nullable', 'integer', 'min:0', 'max:100'],
            'heart_rate'             => ['nullable', 'integer', 'min:0', 'max:300'],
            'complications'          => ['nullable', 'string', 'max:255'],
            'medications'            => ['nullable', 'string'],
            'notes'                  => ['nullable', 'string'],
            'status'                 => ['required', 'string', 'max:30'],
            'is_active'              => ['boolean'],
        ];
    }

    protected array $validationAttributes = ['code' => 'Session Code'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStationFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingShiftFilter(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->session_date = now()->format('Y-m-d');
        $this->code = $this->generateCode();
        $this->auto_post_billing = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $r = DialysisSession::findOrFail($id);
        $this->editingId = $r->id;
        $this->code = $r->code;
        $this->patient_id = (string) $r->patient_id;
        $this->patient_search = $r->patient ? "{$r->patient->last_name}, {$r->patient->first_name} ({$r->patient->patient_code})" : '';
        $this->dialysis_type_id = $r->dialysis_type_id ? (string) $r->dialysis_type_id : null;
        $this->dialysis_machine_id = $r->dialysis_machine_id ? (string) $r->dialysis_machine_id : null;
        $this->shift = $r->shift ?? 'Shift 1 (AM)';
        $this->session_number = $r->session_number;
        $this->attending_physician_id = $r->attending_physician_id ? (string) $r->attending_physician_id : null;
        $this->dialysis_nurse_id = $r->dialysis_nurse_id ? (string) $r->dialysis_nurse_id : null;
        $this->admission_id = $r->admission_id ? (string) $r->admission_id : null;
        $this->er_admission_id = $r->er_admission_id ? (string) $r->er_admission_id : null;

        $this->access_type = $r->access_type ?? 'AV Fistula';
        $this->access_site = $r->access_site ?? '';
        $this->session_date = $r->session_date ? $r->session_date->format('Y-m-d') : '';
        $this->pre_weight = (string) ($r->pre_weight ?? '');
        $this->post_weight = (string) ($r->post_weight ?? '');
        $this->dry_weight = (string) ($r->dry_weight ?? '');
        $this->uf_goal_liters = (string) ($r->uf_goal_liters ?? '');
        $this->uf_actual_liters = (string) ($r->uf_actual_liters ?? '');
        $this->blood_flow_rate_qb = $r->blood_flow_rate_qb ?? 300;
        $this->dialysate_flow_rate_qd = $r->dialysate_flow_rate_qd ?? 500;
        $this->dialysate_bath = $r->dialysate_bath ?? 'Bicarbonate / K+ 2.0 / Ca++ 1.25';
        $this->anticoagulation_type = $r->anticoagulation_type ?? 'Heparin';
        $this->heparin_dose_units = $r->heparin_dose_units ?? 5000;
        $this->dialyzer_model = $r->dialyzer_model ?? 'Fresenius FX80';
        $this->dialyzer_reuse_number = $r->dialyzer_reuse_number ?? 0;

        $this->pre_bp = $r->pre_bp ?? '';
        $this->post_bp = $r->post_bp ?? '';
        $this->pre_temp = (string) ($r->pre_temp ?? '36.5');
        $this->post_temp = (string) ($r->post_temp ?? '36.6');
        $this->pre_rr = $r->pre_rr ?? 18;
        $this->post_rr = $r->post_rr ?? 18;
        $this->heart_rate = (string) ($r->heart_rate ?? '');
        $this->complications = $r->complications ?? 'None';

        $this->medications = $r->medications ?? '';
        $this->notes = $r->notes ?? '';
        $this->status = $r->status;
        $this->is_active = (bool) $r->is_active;
        $this->auto_post_billing = (bool) ($r->procedure_request_id !== null);

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        if (!$this->isEditing) {
            $this->code = $this->generateCode();
        }

        $data = $this->validate();
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();

        if ($this->isEditing) {
            $session = DialysisSession::findOrFail($this->editingId);
            $session->update(array_merge($data, [
                'updated_by' => $uid,
                'updated_date' => now(),
            ]));
            $this->syncMachineStatus($session);
            if ($this->auto_post_billing && !$session->procedure_request_id) {
                $this->createProcedureBilling($session);
            }
            $this->dispatch('toast', type: 'success', message: 'Dialysis session updated successfully.');
        } else {
            $session = DialysisSession::create(array_merge($data, [
                'tenant_id' => $tid,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => now(),
                'updated_by' => $uid,
                'updated_date' => now(),
            ]));
            $this->syncMachineStatus($session);
            if ($this->auto_post_billing) {
                $this->createProcedureBilling($session);
            }
            $this->dispatch('toast', type: 'success', message: 'Dialysis session created and queued.');
        }

        $this->closeModal();
    }

    /**
     * Automatic Billing Hook: Auto-create Hemodialysis procedure charge
     */
    private function createProcedureBilling(DialysisSession $session): void
    {
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        // Find standard Hemodialysis procedure from catalog
        $procedure = Procedure::where('tenant_id', $tid)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', 'HD-SESS')
                  ->orWhere('name', 'like', '%Hemodialysis%');
            })
            ->first();

        $charge = $procedure ? (float) ($procedure->standard_charge ?? 4500.00) : 4500.00;
        $procName = $procedure ? $procedure->name : 'Hemodialysis Session (4 hrs)';
        $procCode = $this->generateProcedureRequestCode($tid);

        $procStatus = ($session->status === 'Completed') ? 'Complete' : 'Pending';

        $procRequest = ProcedureRequest::create([
            'tenant_id' => $tid,
            'dialysis_session_id' => $session->id,
            'request_code' => $procCode,
            'requesting_department_id' => $this->renalDepartmentId(),
            'requesting_doctor_id' => $session->attending_physician_id,
            'status' => $procStatus,
            'billing_status' => 'Pending',
            'total_amount' => $charge,
            'notes' => "Auto-posted for Hemodialysis Session {$session->code} ({$session->shift})",
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        ProcedureRequestItem::create([
            'procedure_request_id' => $procRequest->id,
            'procedure_id' => $procedure?->id,
            'procedure_name' => $procName,
            'category_name' => 'Renal / Hemodialysis',
            'quantity' => 1,
            'unit_charge' => $charge,
            'total_charge' => $charge,
            'status' => $procStatus,
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $session->update([
            'procedure_request_id' => $procRequest->id,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);
    }

    private function syncMachineStatus(DialysisSession $session): void
    {
        if (!$session->dialysis_machine_id) return;

        $machine = DialysisMachine::find($session->dialysis_machine_id);
        if (!$machine) return;

        $newStatus = match ($session->status) {
            'In Progress' => 'Occupied',
            'Completed'   => 'Disinfecting',
            'Cancelled'   => 'Available',
            default       => ($machine->status === 'Occupied' ? 'Available' : $machine->status),
        };

        if ($machine->status !== $newStatus) {
            $machine->update([
                'status' => $newStatus,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);
        }
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        $s = DialysisSession::findOrFail($this->editingId);
        $s->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        if ($s->dialysis_machine_id) {
            DialysisMachine::where('id', $s->dialysis_machine_id)->update(['status' => 'Available']);
        }
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Dialysis session archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'code', 'patient_id', 'patient_search', 'dialysis_type_id', 'dialysis_machine_id',
            'shift', 'session_number', 'attending_physician_id', 'dialysis_nurse_id', 'admission_id',
            'er_admission_id', 'access_site', 'session_date', 'pre_weight', 'post_weight', 'dry_weight',
            'uf_goal_liters', 'uf_actual_liters', 'pre_bp', 'post_bp', 'pre_temp', 'post_temp',
            'pre_rr', 'post_rr', 'heart_rate', 'medications', 'notes'
        ]);
        $this->access_type = 'AV Fistula';
        $this->shift = 'Shift 1 (AM)';
        $this->blood_flow_rate_qb = 300;
        $this->dialysate_flow_rate_qd = 500;
        $this->dialysate_bath = 'Bicarbonate / K+ 2.0 / Ca++ 1.25';
        $this->anticoagulation_type = 'Heparin';
        $this->heparin_dose_units = 5000;
        $this->dialyzer_model = 'Fresenius FX80';
        $this->dialyzer_reuse_number = 0;
        $this->complications = 'None';
        $this->status = 'Scheduled';
        $this->is_active = true;
        $this->auto_post_billing = true;
        $this->resetValidation();
    }

    private function generateCode(): string
    {
        $last = DialysisSession::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->code, 3)) + 1 : 1;
        return 'DS-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    // ——— Treatment Form ———

    public function openTreatment(int $id): void
    {
        $r = DialysisSession::findOrFail($id);
        $this->treatingId = $r->id;
        $this->treatment_history_of_present_illness = $r->history_of_present_illness ?? '';
        $this->treatment_physical_exam_findings = $r->physical_exam_findings ?? '';
        $this->treatment_assessment = $r->assessment ?? '';
        $this->treatment_orders_summary = $r->orders_summary ?? '';
        $this->treatment_treatment_notes = $r->treatment_notes ?? '';
        $this->disposition = $r->disposition ?? 'Pending';
        $this->discharge_instructions = $r->discharge_instructions ?? '';
        $this->showTreatmentForm = true;
    }

    public function saveTreatment(): void
    {
        $validated = $this->validate([
            'treatment_history_of_present_illness' => ['nullable', 'string', 'max:10000'],
            'treatment_physical_exam_findings'    => ['nullable', 'string', 'max:10000'],
            'treatment_assessment'                 => ['nullable', 'string', 'max:10000'],
            'treatment_orders_summary'             => ['nullable', 'string', 'max:10000'],
            'treatment_treatment_notes'            => ['nullable', 'string', 'max:10000'],
        ]);

        DialysisSession::findOrFail($this->treatingId)->update([
            'history_of_present_illness' => $validated['treatment_history_of_present_illness'],
            'physical_exam_findings'     => $validated['treatment_physical_exam_findings'],
            'assessment'                 => $validated['treatment_assessment'],
            'orders_summary'             => $validated['treatment_orders_summary'],
            'treatment_notes'            => $validated['treatment_treatment_notes'],
            'updated_by'                 => $this->currentUserId(),
            'updated_date'               => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Treatment record updated.');
    }

    public function closeTreatment(): void
    {
        $this->showTreatmentForm = false;
        $this->showOrderLabModal = false;
        $this->showOrderCsrModal = false;
        $this->showOrderRadiologyModal = false;
        $this->showOrderPharmacyModal = false;
        $this->showOrderProcedureModal = false;
        $this->reset([
            'treatingId', 'orderLabPriority', 'orderLabNotes', 'orderCsrNotes',
            'orderRadiologyPriority', 'orderRadiologyNotes', 'orderCsrQuantities',
            'orderPharmacyPriority', 'orderPharmacyNotes', 'orderPharmacyQuantities',
            'orderProcedureNotes', 'orderProcedureQuantities'
        ]);
    }

    public function closeOrderLab(): void { $this->showOrderLabModal = false; $this->reset(['orderLabPriority', 'orderLabNotes']); }
    public function closeOrderCsr(): void { $this->showOrderCsrModal = false; $this->reset(['orderCsrNotes', 'orderCsrQuantities']); }
    public function closeOrderRadiology(): void { $this->showOrderRadiologyModal = false; $this->reset(['orderRadiologyPriority', 'orderRadiologyNotes']); }
    public function closeOrderPharmacy(): void { $this->showOrderPharmacyModal = false; $this->reset(['orderPharmacyPriority', 'orderPharmacyNotes', 'orderPharmacyQuantities', 'orderPharmacyFrequencies', 'orderPharmacyRoutes']); }
    public function closeOrderProcedure(): void { $this->showOrderProcedureModal = false; $this->reset(['orderProcedureNotes', 'orderProcedureQuantities']); }

    // ——— Disposition ———

    public function openDisposition(int $id): void
    {
        $r = DialysisSession::findOrFail($id);
        $this->editingId = $r->id;
        $this->disposition = $r->disposition ?? 'Pending';
        $this->discharge_instructions = $r->discharge_instructions ?? '';
        $this->showDispositionModal = true;
    }

    public function saveDisposition(): void
    {
        $validated = $this->validate([
            'disposition'            => ['required', 'string', 'in:Pending,Completed,Transferred,Discharged,Cancelled'],
            'discharge_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $id = $this->editingId ?? $this->treatingId;
        $r = DialysisSession::findOrFail($id);

        $status = 'Scheduled';
        $dispositionDate = null;
        if ($validated['disposition'] !== 'Pending') {
            $status = ($validated['disposition'] === 'Cancelled') ? 'Cancelled' : 'Completed';
            $dispositionDate = now();
        }

        $r->update([
            'disposition'          => $validated['disposition'],
            'status'               => $status,
            'disposition_datetime' => $dispositionDate,
            'discharge_instructions' => $validated['discharge_instructions'],
            'updated_by'           => $this->currentUserId(),
            'updated_date'         => now(),
        ]);

        $this->syncMachineStatus($r);

        // Complete procedure request if completed
        if ($status === 'Completed' && $r->procedure_request_id) {
            ProcedureRequest::where('id', $r->procedure_request_id)->update(['status' => 'Complete']);
            ProcedureRequestItem::where('procedure_request_id', $r->procedure_request_id)->update(['status' => 'Complete']);
        }

        $this->dispatch('toast', type: 'success', message: 'Disposition and status updated.');
        if ($this->showTreatmentForm) {
            $this->showTreatmentForm = false;
            $this->reset(['treatingId']);
        }
        $this->showDispositionModal = false;
        $this->reset(['editingId']);
    }

    public function refreshOrders(): void
    {
        if ($this->treatingId === null) return;
        $this->dispatch('$refresh');
    }

    // ——— Order Placement Methods ———

    public function openOrderLab(): void
    {
        $this->orderLabSelection = [];
        $this->orderLabPriority = 'Urgent';
        $this->orderLabNotes = '';
        $this->showOrderLabModal = true;
    }

    public function requestLabOrder(): void
    {
        $this->validate([
            'orderLabSelection' => ['required', 'array', 'min:1'],
            'orderLabSelection.*' => ['required', 'integer'],
            'orderLabPriority' => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'orderLabNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderLabSelection.required' => 'Select at least one test or panel.']);

        $session = DialysisSession::findOrFail($this->treatingId);
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
                $items[] = ['item_type' => 'Test', 'lab_test_id' => $test->id, 'test_name' => $test->name, 'charge_amount' => $charge];
                $total += $charge;
            }
        }

        $request = LabRequest::create([
            'tenant_id' => $tid, 'dialysis_session_id' => $session->id,
            'request_code' => $code, 'patient_id' => $session->patient_id,
            'requesting_department_id' => $this->renalDepartmentId(),
            'priority' => $this->orderLabPriority,
            'request_notes' => 'Ordered from Dialysis Session' . ($this->orderLabNotes ? "\n\n" . $this->orderLabNotes : ''),
            'status' => 'Pending', 'billing_status' => 'Pending', 'total_charge' => $total,
            'is_active' => true, 'history' => 1,
            'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderLabModal = false;
        $this->orderLabSelection = [];
        $this->orderLabNotes = '';
        $this->dispatch('toast', type: 'success', message: "Lab request {$code} created.");
    }

    public function updateLabItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $item = LabRequestItem::findOrFail($itemId);
        $item->update(['status' => $status, 'updated_by' => $uid, 'updated_date' => now()]);
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

    private function generateLabRequestCode(int $tid): string
    {
        $last = LabRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'LR-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    // ——— CSR Orders ———

    public function openOrderCsr(): void
    {
        $this->orderCsrSelection = [];
        $this->orderCsrQuantities = [];
        $this->orderCsrNotes = '';
        $this->showOrderCsrModal = true;
    }

    public function requestCsrOrder(): void
    {
        $this->validate([
            'orderCsrSelection' => ['required', 'array', 'min:1'],
            'orderCsrSelection.*' => ['required', 'integer'],
            'orderCsrQuantities' => ['nullable', 'array'],
            'orderCsrNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderCsrSelection.required' => 'Select at least one supply item.']);

        $session = DialysisSession::findOrFail($this->treatingId);
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
                $items[] = ['item_id' => $item->id, 'item_name' => $item->name, 'item_code' => $item->code, 'supply_unit_name' => $item->unit?->name ?? '', 'quantity' => $qty, 'unit_cost' => $cost, 'total_cost' => $lineTotal];
                $total += $lineTotal;
            }
        }

        $request = CsrRequest::create([
            'tenant_id' => $tid, 'dialysis_session_id' => $session->id,
            'request_code' => $code, 'requesting_department_id' => $this->renalDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'), 'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from Dialysis Session' . ($this->orderCsrNotes ? "\n\n" . $this->orderCsrNotes : ''),
            'is_active' => true, 'history' => 1,
            'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderCsrModal = false;
        $this->orderCsrSelection = [];
        $this->orderCsrQuantities = [];
        $this->orderCsrNotes = '';
        $this->dispatch('toast', type: 'success', message: "Supply request {$code} created.");
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

    private function generateCsrRequestCode(int $tid): string
    {
        $last = CsrRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'CR-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    // ——— Radiology Orders ———

    public function openOrderRadiology(): void
    {
        $this->orderRadiologySelection = [];
        $this->orderRadiologyPriority = 'Urgent';
        $this->orderRadiologyNotes = '';
        $this->showOrderRadiologyModal = true;
    }

    public function requestRadiologyOrder(): void
    {
        $this->validate([
            'orderRadiologySelection' => ['required', 'array', 'min:1'],
            'orderRadiologySelection.*' => ['required', 'integer'],
            'orderRadiologyPriority' => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'orderRadiologyNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderRadiologySelection.required' => 'Select at least one exam type.']);

        $session = DialysisSession::findOrFail($this->treatingId);
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
                $items[] = ['exam_type_id' => $exam->id, 'exam_name' => $exam->name, 'modality_name' => $exam->modality?->name ?? '', 'charge_amount' => $charge];
                $total += $charge;
            }
        }

        $request = RadiologyRequest::create([
            'tenant_id' => $tid, 'dialysis_session_id' => $session->id,
            'request_code' => $code, 'requesting_department_id' => $this->renalDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'), 'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from Dialysis Session' . ($this->orderRadiologyNotes ? "\n\n" . $this->orderRadiologyNotes : ''),
            'is_active' => true, 'history' => 1,
            'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderRadiologyModal = false;
        $this->orderRadiologySelection = [];
        $this->orderRadiologyNotes = '';
        $this->dispatch('toast', type: 'success', message: "Radiology request {$code} created.");
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

    private function generateRadiologyRequestCode(int $tid): string
    {
        $last = RadiologyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'RR-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    // ——— Pharmacy Orders ———

    public function openOrderPharmacy(): void
    {
        $this->orderPharmacySelection = [];
        $this->orderPharmacyQuantities = [];
        $this->orderPharmacyFrequencies = [];
        $this->orderPharmacyRoutes = [];
        $this->orderPharmacyPriority = 'Urgent';
        $this->orderPharmacyNotes = '';
        $this->showOrderPharmacyModal = true;
    }

    public function requestPharmacyOrder(): void
    {
        $this->validate([
            'orderPharmacySelection' => ['required', 'array', 'min:1'],
            'orderPharmacySelection.*' => ['required', 'integer'],
            'orderPharmacyPriority' => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'orderPharmacyNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderPharmacySelection.required' => 'Select at least one drug.']);

        $session = DialysisSession::findOrFail($this->treatingId);
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
                    'drug_id' => $drug->id, 'item_id' => $drug->id,
                    'item_name' => $drug->generic_name . ' ' . $drug->drug_strength,
                    'item_code' => $drug->drug_code, 'supply_unit_name' => $drug->dosage_form ?? '',
                    'generic_name' => $drug->generic_name, 'brand_name' => $drug->brand_name ?? null,
                    'dosage_form' => $drug->dosage_form ?? null, 'philhealth_item_code' => $drug->philhealth_item_code ?? null,
                    'dosage' => $drug->drug_strength ?? '', 'frequency' => $this->orderPharmacyFrequencies[$id] ?? null,
                    'route' => $this->orderPharmacyRoutes[$id] ?? $drug->route ?? null,
                    'quantity' => $qty, 'unit_cost' => $cost, 'total_cost' => $lineTotal,
                ];
                $total += $lineTotal;
            }
        }

        $request = PharmacyRequest::create([
            'tenant_id' => $tid, 'dialysis_session_id' => $session->id,
            'request_code' => $code, 'requesting_department_id' => $this->renalDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'), 'priority' => $this->orderPharmacyPriority,
            'status' => 'Pending', 'billing_status' => 'Pending', 'total_amount' => $total,
            'notes' => 'Ordered from Dialysis Session' . ($this->orderPharmacyNotes ? "\n\n" . $this->orderPharmacyNotes : ''),
            'is_active' => true, 'history' => 1,
            'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
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

    private function generatePharmacyRequestCode(int $tid): string
    {
        $last = PharmacyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'PR-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    // ——— Procedure Orders ———

    public function openOrderProcedure(): void
    {
        $this->orderProcedureSelection = [];
        $this->orderProcedureQuantities = [];
        $this->orderProcedureNotes = '';
        $this->showOrderProcedureModal = true;
    }

    public function requestProcedureOrder(): void
    {
        $this->validate([
            'orderProcedureSelection' => ['required', 'array', 'min:1'],
            'orderProcedureSelection.*' => ['required', 'integer'],
            'orderProcedureNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderProcedureSelection.required' => 'Select at least one procedure.']);

        $session = DialysisSession::findOrFail($this->treatingId);
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
                $items[] = ['procedure_id' => $procedure->id, 'procedure_name' => $procedure->name, 'category_name' => $procedure->category?->name ?? '', 'quantity' => $qty, 'unit_charge' => $charge, 'total_charge' => $lineTotal];
                $total += $lineTotal;
            }
        }

        $request = ProcedureRequest::create([
            'tenant_id' => $tid, 'dialysis_session_id' => $session->id,
            'request_code' => $code, 'requesting_department_id' => $this->renalDepartmentId(),
            'requesting_doctor_id' => $session->attending_physician_id ?? auth()->user()?->health_care_professional_id,
            'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from Dialysis Session' . ($this->orderProcedureNotes ? "\n\n" . $this->orderProcedureNotes : ''),
            'is_active' => true, 'history' => 1,
            'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending', 'is_active' => true, 'history' => 1,
                'created_by' => $uid, 'created_date' => $now, 'updated_by' => $uid, 'updated_date' => $now,
            ]));
        }

        $this->showOrderProcedureModal = false;
        $this->orderProcedureSelection = [];
        $this->orderProcedureQuantities = [];
        $this->orderProcedureNotes = '';
        $this->dispatch('toast', type: 'success', message: "Procedure request {$code} created.");
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

    private function generateProcedureRequestCode(int $tid): string
    {
        $last = ProcedureRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 5)) + 1 : 1;
        return 'PRCD-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $tid = $this->currentTenantId();

        $items = DialysisSession::query()
            ->where('tenant_id', $tid)
            ->with(['patient', 'dialysisType', 'dialysisMachine', 'attendingPhysician'])
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($inner) use ($s) {
                    $inner->where('code', 'like', $s)
                          ->orWhere('access_type', 'like', $s)
                          ->orWhere('access_site', 'like', $s)
                          ->orWhereHas('patient', fn ($pq) => $pq->where('last_name', 'like', $s)
                              ->orWhere('first_name', 'like', $s)
                              ->orWhere('patient_code', 'like', $s));
                });
            })
            ->when($this->stationFilter, fn ($q) => $q->where('dialysis_machine_id', $this->stationFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->shiftFilter, fn ($q) => $q->where('shift', $this->shiftFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $activeSession = null;
        $labOrders = collect();
        $csrOrders = collect();
        $radiologyOrders = collect();
        $pharmacyOrders = collect();
        $procedureOrders = collect();

        if ($this->showTreatmentForm && $this->treatingId !== null) {
            $activeSession = DialysisSession::with([
                'patient', 'dialysisType', 'dialysisMachine', 'attendingPhysician', 'dialysisNurse',
                'labRequests.items.labTest',
                'csrRequests.items.item',
                'radiologyRequests.items.examType',
                'pharmacyRequests.items.drug',
                'procedureRequests.items.procedure',
            ])->find($this->treatingId);

            $labOrders = $activeSession?->labRequests()->with('items.labTest')->orderBy('created_date', 'desc')->get() ?? collect();
            $csrOrders = $activeSession?->csrRequests()->with('items.item')->orderBy('created_date', 'desc')->get() ?? collect();
            $radiologyOrders = $activeSession?->radiologyRequests()->with('items.examType')->orderBy('created_date', 'desc')->get() ?? collect();
            $pharmacyOrders = $activeSession?->pharmacyRequests()->with('items.drug')->orderBy('created_date', 'desc')->get() ?? collect();
            $procedureOrders = $activeSession?->procedureRequests()->with('items.procedure')->orderBy('created_date', 'desc')->get() ?? collect();
        }

        $patients = $this->patient_search
            ? Patient::where('tenant_id', $tid)
                ->where(function ($q) {
                    $q->where('last_name', 'like', '%' . $this->patient_search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->patient_search . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->patient_search . '%');
                })
                ->orderBy('last_name')
                ->get(['id', 'patient_code', 'first_name', 'last_name'])
            : collect();

        $dialysisTypes = DialysisType::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);
        $dialysisMachines = DialysisMachine::query()->where('tenant_id', $tid)->where('is_active', true)->orderBy('station_code')->get(['id', 'station_code', 'station_name', 'station_category', 'status']);
        $doctors = HealthCareProfessional::query()->where('tenant_id', $tid)->where('is_active', true)->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'specialization', 'philhealth_accreditation_number']);
        $nurses = User::query()->where('tenant_id', $tid)->where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        // Inpatient admissions if patient selected
        $admissions = $this->patient_id
            ? Admission::where('tenant_id', $tid)->where('patient_id', $this->patient_id)->where('status', 'Admitted')->orderBy('admission_date', 'desc')->get(['id', 'admission_code', 'admission_date'])
            : collect();

        $erAdmissions = $this->patient_id
            ? ErAdmission::where('tenant_id', $tid)->where('patient_id', $this->patient_id)->where('status', 'Active')->orderBy('admission_datetime', 'desc')->get(['id', 'er_case_no', 'admission_datetime'])
            : collect();

        $labTests = LabTest::where('is_active', true)->where('tenant_id', $tid)->orderBy('name')->get(['id', 'code', 'name', 'standard_charge']);
        $supplyItems = Item::where('is_active', true)->where('tenant_id', $tid)->with('unit')->orderBy('name')->get(['id', 'code', 'name', 'unit_cost']);
        $examTypes = RadiologyExamType::where('is_active', true)->where('tenant_id', $tid)->with('modality')->orderBy('name')->get(['id', 'code', 'name', 'standard_charge']);
        $drugs = Drug::where('is_active', true)->where('tenant_id', $tid)->orderBy('generic_name')->get(['id', 'drug_code', 'generic_name', 'brand_name', 'drug_strength', 'dosage_form', 'route']);
        $procedures = Procedure::where('is_active', true)->where('tenant_id', $tid)->with('category')->orderBy('name')->get(['id', 'name', 'category_id', 'standard_charge']);

        return view('livewire.renal-care.dialysis-session-crud', compact(
            'items', 'activeSession', 'labOrders', 'csrOrders', 'radiologyOrders', 'pharmacyOrders', 'procedureOrders',
            'patients', 'dialysisTypes', 'dialysisMachines', 'doctors', 'nurses', 'admissions', 'erAdmissions',
            'labTests', 'supplyItems', 'examTypes', 'drugs', 'procedures'
        ));
    }
}
