<?php

namespace App\Livewire\Or;

use App\Models\HealthCareProfessional;
use App\Models\Or\OrRoom;
use App\Models\Or\OrSchedule;
use App\Models\LabTest;
use App\Models\Supply\Item;
use App\Models\Radiology\RadiologyExamType;
use App\Models\Pharmacy\Drug;
use App\Models\Procedures\Procedure;
use App\Models\Department;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\CsrRequest;
use App\Models\CsrRequestItem;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Radiology\RadiologyRequestItem;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Pharmacy\PharmacyRequestItem;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Procedures\ProcedureRequestItem;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;

class OrScheduleCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'scheduled_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    public string $schedule_code = '', $scheduled_date = '', $procedure_type = '', $procedure_description = '';
    public ?string $or_room_id = null, $patient_id = null, $patient_search = '', $surgeon_id = null, $anesthesiologist_id = null;
    public ?string $er_admission_id = null;
    public ?string $start_time = null, $end_time = null;
    public bool $skipPatientSearchUpdate = false;
    public string $status = 'Scheduled';
    public ?string $pre_op_notes = null, $intra_op_notes = null, $post_op_notes = null, $cancellation_reason = null;

    // Treatment form flags
    public bool $showTreatmentForm = false, $showDispositionModal = false;
    public ?int $treatingId = null;

    // Clinical notes
    public ?string $treatment_history_of_present_illness = null;
    public ?string $treatment_physical_exam_findings = null;
    public ?string $treatment_assessment = null;
    public ?string $treatment_orders_summary = null;
    public ?string $treatment_treatment_notes = null;
    public ?string $disposition = null;
    public ?string $discharge_instructions = null;

    // Order modal flags
    public bool $showOrderLabModal = false, $showOrderCsrModal = false, $showOrderRadiologyModal = false, $showOrderPharmacyModal = false, $showOrderProcedureModal = false;

    // Lab order form
    public array $orderLabSelection = [];
    public string $orderLabPriority = 'Routine';
    public ?string $orderLabNotes = null;

    // CSR order form
    public array $orderCsrSelection = [];
    public array $orderCsrQuantities = [];
    public ?string $orderCsrNotes = null;

    // Radiology order form
    public array $orderRadiologySelection = [];
    public string $orderRadiologyPriority = 'Routine';
    public ?string $orderRadiologyNotes = null;

    // Pharmacy order form
    public array $orderPharmacySelection = [];
    public array $orderPharmacyQuantities = [];
    public array $orderPharmacyFrequencies = [];
    public array $orderPharmacyRoutes = [];
    public string $orderPharmacyPriority = 'Routine';
    public ?string $orderPharmacyNotes = null;

    // Procedure order form
    public array $orderProcedureSelection = [];
    public array $orderProcedureQuantities = [];
    public ?string $orderProcedureNotes = null;

    protected $listeners = ['refreshOrOrders' => 'refreshOrders'];

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:or_schedules,schedule_code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:or_schedules,schedule_code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'schedule_code'        => ['required', 'string', 'max:30', $u],
            'or_room_id'           => ['required', 'integer', 'exists:or_rooms,id'],
            'scheduled_date'       => ['required', 'date'],
            'procedure_type'       => ['nullable', 'string', 'max:100'],
            'procedure_description' => ['nullable', 'string'],
            'patient_id'           => ['required', 'integer', 'exists:patients,id'],
            'er_admission_id'      => ['nullable', 'integer', 'exists:er_admissions,id'],
            'surgeon_id'           => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'anesthesiologist_id'  => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'start_time'           => ['nullable', 'string', 'max:8'],
            'end_time'             => ['nullable', 'string', 'max:8'],
            'status'               => ['required', 'string', 'max:30'],
            'pre_op_notes'         => ['nullable', 'string'],
            'intra_op_notes'       => ['nullable', 'string'],
            'post_op_notes'        => ['nullable', 'string'],
            'cancellation_reason'  => ['nullable', 'string'],
        ];
    }

    protected array $validationAttributes = [
        'schedule_code' => 'Schedule Code',
        'or_room_id' => 'OR Room',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

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
        $this->schedule_code = $this->generateScheduleCode();
        $this->scheduled_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $r = OrSchedule::with(['orRoom', 'patient', 'surgeon', 'anesthesiologist'])->findOrFail($id);
        $this->editingId = $r->id;
        $this->patient_search = '';
        $this->schedule_code = $r->schedule_code;
        $this->or_room_id = (string) $r->or_room_id;
        $this->patient_id = (string) $r->patient_id;
        $this->scheduled_date = $r->scheduled_date?->format('Y-m-d') ?? '';
        $this->start_time = $r->start_time;
        $this->end_time = $r->end_time;
        $this->procedure_type = $r->procedure_type ?? '';
        $this->procedure_description = $r->procedure_description ?? '';
        $this->surgeon_id = $r->surgeon_id ? (string) $r->surgeon_id : null;
        $this->anesthesiologist_id = $r->anesthesiologist_id ? (string) $r->anesthesiologist_id : null;
        $this->er_admission_id = $r->er_admission_id ? (string) $r->er_admission_id : null;
        $this->status = $r->status;
        $this->pre_op_notes = $r->pre_op_notes;
        $this->intra_op_notes = $r->intra_op_notes;
        $this->post_op_notes = $r->post_op_notes;
        $this->cancellation_reason = $r->cancellation_reason;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            OrSchedule::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'OR schedule updated successfully.');
        } else {
            OrSchedule::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
                'updated_by' => $this->currentUserId(), 'updated_date' => null,
            ]));
            $this->dispatch('toast', type: 'success', message: 'OR schedule created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        OrSchedule::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'OR schedule archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'schedule_code', 'or_room_id', 'patient_id', 'patient_search',
            'scheduled_date', 'start_time', 'end_time', 'procedure_type', 'procedure_description',
            'surgeon_id', 'anesthesiologist_id', 'er_admission_id',
            'pre_op_notes', 'intra_op_notes', 'post_op_notes', 'cancellation_reason']);
        $this->status = 'Scheduled'; $this->resetValidation();
    }

    public function updatedPatientSearch(): void
    {
        if ($this->skipPatientSearchUpdate) {
            $this->skipPatientSearchUpdate = false;
            return;
        }
        $this->patient_id = null;
    }

    public function selectPatient(int $id): void
    {
        $this->skipPatientSearchUpdate = true;
        $this->patient_id = (string) $id;
        $this->patient_search = '';
    }

    private function generateScheduleCode(): string
    {
        $last = OrSchedule::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->orderBy('id', 'desc')
            ->first();

        $num = $last ? ((int) substr($last->schedule_code, 4)) + 1 : 1;
        return 'ORS-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ——— Treatment Form ———

    public function openTreatment(int $id): void
    {
        $r = OrSchedule::with(['orRoom', 'patient', 'surgeon', 'anesthesiologist'])->findOrFail($id);
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
            'treatment_physical_exam_findings' => ['nullable', 'string', 'max:10000'],
            'treatment_assessment' => ['nullable', 'string', 'max:10000'],
            'treatment_orders_summary' => ['nullable', 'string', 'max:10000'],
            'treatment_treatment_notes' => ['nullable', 'string', 'max:10000'],
            'disposition' => ['nullable', 'string', 'max:100'],
            'discharge_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        OrSchedule::findOrFail($this->treatingId)->update([
            'history_of_present_illness' => $validated['treatment_history_of_present_illness'],
            'physical_exam_findings' => $validated['treatment_physical_exam_findings'],
            'assessment' => $validated['treatment_assessment'],
            'orders_summary' => $validated['treatment_orders_summary'],
            'treatment_notes' => $validated['treatment_treatment_notes'],
            'disposition' => $validated['disposition'],
            'discharge_instructions' => $validated['discharge_instructions'],
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
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
        $this->reset(['treatingId', 'orderLabPriority', 'orderLabNotes', 'orderCsrNotes', 'orderRadiologyPriority', 'orderRadiologyNotes', 'orderCsrQuantities', 'orderPharmacyPriority', 'orderPharmacyNotes', 'orderPharmacyQuantities', 'orderProcedureNotes', 'orderProcedureQuantities', 'treatment_history_of_present_illness', 'treatment_physical_exam_findings', 'treatment_assessment', 'treatment_orders_summary', 'treatment_treatment_notes', 'disposition', 'discharge_instructions']);
    }

    // ——— Disposition ———

    public function openDisposition(int $id): void
    {
        $r = OrSchedule::findOrFail($id);
        $this->editingId = $r->id;
        $this->disposition = $r->disposition ?? 'Pending';
        $this->discharge_instructions = $r->discharge_instructions ?? '';
        $this->showDispositionModal = true;
    }

    public function saveDisposition(): void
    {
        $validated = $this->validate([
            'disposition' => ['required', 'string', 'in:Pending,Completed,Transferred,Discharged,Cancelled'],
            'discharge_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $id = $this->editingId ?? $this->treatingId;
        $r = OrSchedule::findOrFail($id);

        $status = 'Scheduled';
        $dispositionDate = null;
        if ($validated['disposition'] !== 'Pending') {
            $status = 'Completed';
            $dispositionDate = now();
        }

        $r->update([
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
        $this->showDispositionModal = false;
        $this->reset(['editingId']);
    }

    // ——— Order Refresh ———

    public function refreshOrders(): void
    {
        if ($this->treatingId === null) return;
        $this->dispatch('$refresh');
    }

    // ——— Department Resolver ———

    private function orDepartmentId(): ?int
    {
        static $id = null;
        if ($id === null) {
            $dept = Department::where('dept_code', 'OR')->where('is_active', true)->first();
            $id = $dept?->id;
        }
        return $id;
    }

    // ——— Computed Property ———

    public function getActiveScheduleProperty()
    {
        if ($this->showTreatmentForm && $this->treatingId !== null) {
            return OrSchedule::with([
                'orRoom', 'patient', 'surgeon', 'anesthesiologist',
                'labRequests.items.labTest',
                'csrRequests.items.item',
                'radiologyRequests.items.examType',
                'pharmacyRequests.items.drug',
                'procedureRequests.items.procedure',
            ])->find($this->treatingId);
        }
        return null;
    }

    // ——— Lab Orders ———

    public function openOrderLab(): void
    {
        $this->orderLabSelection = [];
        $this->orderLabPriority = 'Routine';
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

        $schedule = OrSchedule::findOrFail($this->treatingId);
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
            'tenant_id' => $tid, 'or_schedule_id' => $schedule->id,
            'request_code' => $code, 'patient_id' => $schedule->patient_id,
            'requesting_department_id' => $this->orDepartmentId(),
            'requesting_doctor_id' => $schedule->surgeon_id,
            'priority' => $this->orderLabPriority,
            'request_notes' => 'Ordered from OR Schedule' . ($this->orderLabNotes ? "\n\n" . $this->orderLabNotes : ''),
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
        return 'LR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
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

        $schedule = OrSchedule::findOrFail($this->treatingId);
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
            'tenant_id' => $tid, 'or_schedule_id' => $schedule->id,
            'request_code' => $code, 'requesting_department_id' => $this->orDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'), 'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from OR Schedule' . ($this->orderCsrNotes ? "\n\n" . $this->orderCsrNotes : ''),
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

        if ($parentStatus === 'Complete') {
            \App\Models\CsrRequest::postChargeToLedger($item->csrRequest, (float) $item->csrRequest->total_amount, $uid);
        }

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
        return 'CR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ——— Radiology Orders ———

    public function openOrderRadiology(): void
    {
        $this->orderRadiologySelection = [];
        $this->orderRadiologyPriority = 'Routine';
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

        $schedule = OrSchedule::findOrFail($this->treatingId);
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
            'tenant_id' => $tid, 'or_schedule_id' => $schedule->id,
            'request_code' => $code, 'requesting_department_id' => $this->orDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'), 'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from OR Schedule' . ($this->orderRadiologyNotes ? "\n\n" . $this->orderRadiologyNotes : ''),
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
        return 'RR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ——— Pharmacy Orders ———

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

    public function requestPharmacyOrder(): void
    {
        $this->validate([
            'orderPharmacySelection' => ['required', 'array', 'min:1'],
            'orderPharmacySelection.*' => ['required', 'integer'],
            'orderPharmacyPriority' => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'orderPharmacyNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderPharmacySelection.required' => 'Select at least one drug.']);

        $schedule = OrSchedule::findOrFail($this->treatingId);
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
            'tenant_id' => $tid, 'or_schedule_id' => $schedule->id,
            'request_code' => $code, 'requesting_department_id' => $this->orDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'), 'priority' => $this->orderPharmacyPriority,
            'status' => 'Pending', 'billing_status' => 'Pending', 'total_amount' => $total,
            'notes' => 'Ordered from OR Schedule' . ($this->orderPharmacyNotes ? "\n\n" . $this->orderPharmacyNotes : ''),
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
        return 'PR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
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

        $schedule = OrSchedule::findOrFail($this->treatingId);
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
            'tenant_id' => $tid, 'or_schedule_id' => $schedule->id,
            'request_code' => $code, 'requesting_department_id' => $this->orDepartmentId(),
            'requesting_doctor_id' => $schedule->surgeon_id, 'status' => 'Pending', 'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from OR Schedule' . ($this->orderProcedureNotes ? "\n\n" . $this->orderProcedureNotes : ''),
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
        return 'PRCD-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $tid = $this->currentTenantId();

        $items = OrSchedule::with(['orRoom', 'patient', 'surgeon', 'anesthesiologist'])
            ->where('tenant_id', $tid)
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('schedule_code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('patient', fn ($pq) =>
                            $pq->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                        )
                        ->orWhere('procedure_type', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $orRooms = OrRoom::query()
            ->where('tenant_id', $tid)
            ->where('status', 'Available')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $doctors = HealthCareProfessional::query()
            ->where('tenant_id', $tid)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $patients = $this->patient_search
            ? Patient::where('tenant_id', $tid)
                ->where(function ($q) {
                    $q->where('last_name', 'like', '%' . $this->patient_search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->patient_search . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->patient_search . '%');
                })
                ->orderBy('last_name')
                ->take(10)
                ->get(['id', 'last_name', 'first_name', 'patient_code'])
            : collect();

        $activeSchedule = null;
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

        if ($this->showTreatmentForm && $this->treatingId !== null) {
            $activeSchedule = OrSchedule::with([
                'orRoom', 'patient', 'surgeon', 'anesthesiologist',
                'labRequests.items.labTest',
                'csrRequests.items.item',
                'radiologyRequests.items.examType',
                'pharmacyRequests.items.drug',
                'procedureRequests.items.procedure',
            ])->find($this->treatingId);

            $labOrders = $activeSchedule->labRequests()->with('items.labTest')->orderBy('created_date', 'desc')->get();
            $csrOrders = $activeSchedule->csrRequests()->with('items.item')->orderBy('created_date', 'desc')->get();
            $radiologyOrders = $activeSchedule->radiologyRequests()->with('items.examType')->orderBy('created_date', 'desc')->get();
            $pharmacyOrders = $activeSchedule->pharmacyRequests()->with('items.drug')->orderBy('created_date', 'desc')->get();
            $procedureOrders = $activeSchedule->procedureRequests()->with('items.procedure')->orderBy('created_date', 'desc')->get();

            $labTests = LabTest::where('tenant_id', $tid)->where('is_active', 1)->orderBy('name')->get(['id', 'code', 'name', 'standard_charge']);
            $supplyItems = Item::with('unit')->where('tenant_id', $tid)->where('is_active', 1)
                ->whereHas('batches', function ($q) {
                    $q->where('quantity_on_hand', '>', 0)
                      ->where('expiry_date', '>=', now()->toDateString())
                      ->where('history', 1)
                      ->where('is_active', 1);
                })
                ->orderBy('name')->get(['id', 'code', 'name', 'supply_unit_id', 'unit_cost']);
            $examTypes = RadiologyExamType::where('tenant_id', $tid)->where('is_active', 1)->orderBy('name')->get(['id', 'code', 'name', 'standard_charge', 'modality_id']);
            $drugs = Drug::where('tenant_id', $tid)->where('is_active', 1)->whereHas('batches', function ($q) { $q->where('quantity_on_hand', '>', 0)->where('expiry_date', '>=', now()->toDateString())->where('history', 1); })->orderBy('generic_name')->get(['id', 'generic_name', 'brand_name', 'drug_strength', 'dosage_form']);
            $procedures = Procedure::where('tenant_id', $tid)->where('is_active', 1)->orderBy('name')->get(['id', 'code', 'name', 'standard_charge', 'category_id']);
        }

        return view('livewire.or.or-schedule-crud', compact(
            'items', 'orRooms', 'doctors', 'patients',
            'activeSchedule', 'labOrders', 'csrOrders', 'radiologyOrders', 'pharmacyOrders', 'procedureOrders',
            'labTests', 'supplyItems', 'examTypes', 'drugs', 'procedures'
        ));
    }
}
