<?php

namespace App\Livewire\Lab;

use App\Models\Department;
use App\Models\HealthCareProfessional;
use App\Models\LabPanel;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabTest;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Er\ErAdmissionCrud;

class LabDispensingCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $showDetailModal = false, $isEditing = false, $showResultModal = false;
    public ?int $editingId = null, $viewingId = null, $resultItemId = null;

    // Form fields
    public string $request_code = '', $priority = 'Routine', $request_notes = '';
    public ?string $patient_id = null, $requesting_department_id = null, $requesting_doctor_id = null;
    public string $patient_search = '';
    public bool $skipPatientSearchUpdate = false;
    public ?string $selected_patient_name = null;

    // Line items
    public array $selectedTests = [];
    public array $selectedPanels = [];
    public array $lineItems = [];
    public string $total_charge = '0';

    public bool $is_active = true;

    // Result Entry fields
    public ?string $result_value = null;
    public ?string $result_text = null;
    public ?string $result_categorical = null;
    public ?string $result_notes = null;
    public ?string $previewFlag = null;
    public ?string $previewRange = null;
    public ?string $previewUnit = null;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:lab_requests,request_code,NULL,id,tenant_id,{$tid},history,1";
        return [
            'patient_id'              => ['required', 'integer', 'exists:patients,id'],
            'requesting_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'requesting_doctor_id'     => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'priority'                => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'request_notes'           => ['nullable', 'string'],
            'lineItems'               => ['required', 'array', 'min:1'],
            'lineItems.*.item_type'   => ['required', 'string', 'in:Test,Panel'],
            'lineItems.*.lab_test_id' => ['required_if:lineItems.*.item_type,Test', 'nullable', 'integer', 'exists:lab_tests,id'],
            'lineItems.*.lab_panel_id'=> ['required_if:lineItems.*.item_type,Panel', 'nullable', 'integer', 'exists:lab_panels,id'],
            'lineItems.*.test_name'   => ['required', 'string', 'max:200'],
            'lineItems.*.charge_amount'=> ['required', 'numeric', 'min:0'],
        ];
    }

    protected array $validationAttributes = [
        'patient_id' => 'Patient',
        'lineItems' => 'Test Items',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function updatedPatientSearch()
    {
        if (!$this->skipPatientSearchUpdate) {
            $this->patient_id = null;
            $this->selected_patient_name = null;
        }
        $this->skipPatientSearchUpdate = false;
    }

    public function selectPatient($id, $name)
    {
        $this->patient_id = (string) $id;
        $this->selected_patient_name = $name;
        $this->skipPatientSearchUpdate = true;
        $this->patient_search = '';
    }

    public function generateRequestCode(): string
    {
        $tid = $this->currentTenantId();
        $last = LabRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'LR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
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
        $r = LabRequest::with('items')->findOrFail($id);
        $this->editingId = $r->id;
        $this->request_code = $r->request_code;
        $this->patient_id = (string) $r->patient_id;
        $this->selected_patient_name = $r->patient ? $r->patient->last_name . ', ' . $r->patient->first_name : null;
        $this->requesting_department_id = $r->requesting_department_id ? (string) $r->requesting_department_id : null;
        $this->requesting_doctor_id = $r->requesting_doctor_id ? (string) $r->requesting_doctor_id : null;
        $this->priority = $r->priority;
        $this->request_notes = $r->request_notes ?? '';
        $this->lineItems = $r->items->map(fn ($i) => [
            'item_type' => $i->item_type,
            'lab_test_id' => $i->lab_test_id,
            'lab_panel_id' => $i->lab_panel_id,
            'test_name' => $i->test_name,
            'charge_amount' => (string) $i->charge_amount,
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

    public function addTest(int $testId): void
    {
        $test = LabTest::find($testId);
        if (!$test) return;
        $this->lineItems[] = [
            'item_type' => 'Test',
            'lab_test_id' => $test->id,
            'lab_panel_id' => null,
            'test_name' => $test->name,
            'charge_amount' => (string) ($test->standard_charge ?? 0),
        ];
        $this->recalculateTotal();
    }

    public function addPanel(int $panelId): void
    {
        $panel = LabPanel::with('tests')->find($panelId);
        if (!$panel) return;
        $this->lineItems[] = [
            'item_type' => 'Panel',
            'lab_test_id' => null,
            'lab_panel_id' => $panel->id,
            'test_name' => $panel->name . ' (Panel)',
            'charge_amount' => (string) ($panel->standard_charge ?? 0),
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
            $total += (float) ($item['charge_amount'] ?? 0);
        }
        $this->total_charge = number_format($total, 2, '.', '');
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
            $request = LabRequest::findOrFail($this->editingId);
            $request->update([
                'patient_id' => $data['patient_id'],
                'requesting_department_id' => $data['requesting_department_id'],
                'requesting_doctor_id' => $data['requesting_doctor_id'],
                'priority' => $data['priority'],
                'request_notes' => $data['request_notes'],
                'total_charge' => $this->total_charge,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $request->items()->delete();
            foreach ($data['lineItems'] as $li) {
                $request->items()->create([
                    'item_type' => $li['item_type'],
                    'lab_test_id' => $li['lab_test_id'],
                    'lab_panel_id' => $li['lab_panel_id'],
                    'test_name' => $li['test_name'],
                    'charge_amount' => $li['charge_amount'],
                    'status' => 'Pending',
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Lab request updated successfully.');
            if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
            if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
            if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
            if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
            if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
            if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        } else {
            $request = LabRequest::create([
                'tenant_id' => $tid,
                'request_code' => $this->request_code,
                'patient_id' => $data['patient_id'],
                'requesting_department_id' => $data['requesting_department_id'],
                'requesting_doctor_id' => $data['requesting_doctor_id'],
                'priority' => $data['priority'],
                'request_notes' => $data['request_notes'],
                'status' => 'Pending',
                'billing_status' => 'Pending',
                'total_charge' => $this->total_charge,
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            foreach ($data['lineItems'] as $li) {
                LabRequestItem::create([
                    'lab_request_id' => $request->id,
                    'item_type' => $li['item_type'],
                    'lab_test_id' => $li['lab_test_id'],
                    'lab_panel_id' => $li['lab_panel_id'],
                    'test_name' => $li['test_name'],
                    'charge_amount' => $li['charge_amount'],
                    'status' => 'Pending',
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Lab request created successfully.');
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
        $updateData = ['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()];
        if ($parentStatus === 'Completed' && $item->labRequest->billing_status === 'Pending') {
            $updateData['billing_status'] = 'Billed';
        }
        $item->labRequest->update($updateData);

        if ($parentStatus === 'Completed') {
            \App\Models\LabRequest::postChargeToLedger($item->labRequest, (float) $item->labRequest->total_charge, $uid);
        }

        $this->dispatch('toast', type: 'success', message: "Lab item status updated to {$status}.");
        $this->dispatch('$refresh');
    }

    public function completeAllItems(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $request = LabRequest::findOrFail($requestId);
        $request->items()->update(['status' => 'Completed', 'updated_by' => $uid, 'updated_date' => $now]);

        $updateData = ['status' => 'Completed', 'updated_by' => $uid, 'updated_date' => $now];
        if ($request->billing_status === 'Pending') {
            $updateData['billing_status'] = 'Billed';
        }
        $request->update($updateData);

        \App\Models\LabRequest::postChargeToLedger($request, (float) $request->total_charge, $uid);

        $this->dispatch('toast', type: 'success', message: 'All lab items marked as Completed.');
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
        $request = LabRequest::findOrFail($this->editingId);
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
        LabRequestItem::where('lab_request_id', $this->editingId)->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Lab request cancelled successfully.');
        if ($erId) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($dsId) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($orId) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($admissionId) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($obId) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($drId) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
    }

    public function updateBillingStatus(int $id, string $status): void
    {
        $valid = ['Billed', 'Exempt', 'Credited', 'Pending'];
        if (!in_array($status, $valid)) return;

        $uid = $this->currentUserId();
        $request = LabRequest::findOrFail($id);
        $request->update([
            'billing_status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: "Lab billing status updated to {$status}.");
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showDetailModal = false;
        $this->showResultModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'viewingId', 'resultItemId', 'request_code', 'patient_id', 'requesting_department_id',
            'requesting_doctor_id', 'priority', 'request_notes', 'lineItems',
            'result_value', 'result_text', 'result_categorical', 'result_notes',
            'previewFlag', 'previewRange', 'previewUnit', 'selected_patient_name'
        ]);
        $this->total_charge = '0';
        $this->patient_search = '';
        $this->skipPatientSearchUpdate = false;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function openResultModal(int $itemId): void
    {
        $item = LabRequestItem::with(['labTest.referenceRanges', 'labTest.labUnit', 'labRequest.patient.gender'])->findOrFail($itemId);
        $this->resultItemId = $item->id;
        $this->result_value = $item->result_value;
        $this->result_text = $item->result_text;
        $this->result_categorical = $item->result_categorical;
        $this->result_notes = $item->result_notes;
        $this->computeResultPreview($item);
        $this->showResultModal = true;
    }

    public function updatedResultValue(): void
    {
        if (!$this->resultItemId) return;
        $item = LabRequestItem::with(['labTest.referenceRanges', 'labTest.labUnit', 'labRequest.patient.gender'])->find($this->resultItemId);
        if ($item) $this->computeResultPreview($item);
    }

    public function updatedResultCategorical(): void
    {
        $this->updatedResultValue();
    }

    private function computeResultPreview($item): void
    {
        $this->previewFlag = null;
        $this->previewRange = null;
        $this->previewUnit = null;

        if (!$item->labTest) return;

        $this->previewUnit = $item->labTest->labUnit?->name;

        $ranges = $item->labTest->referenceRanges;
        if ($ranges->isEmpty()) return;

        $patient = $item->labRequest?->patient;
        $age = $patient?->date_of_birth?->age;
        $genderName = $patient?->gender?->name;
        $gender = match ($genderName) {
            'Male' => 'Male',
            'Female' => 'Female',
            default => null,
        };

        $range = null;
        foreach ($ranges as $r) {
            $genderMatch = !$r->gender || $r->gender === 'Any' || $r->gender === $gender;
            $ageMatch = true;
            if ($r->age_min_years !== null && $age !== null) $ageMatch = $age >= $r->age_min_years;
            if ($r->age_max_years !== null && $age !== null) $ageMatch = $ageMatch && $age <= $r->age_max_years;
            if ($genderMatch && $ageMatch) { $range = $r; break; }
        }
        if (!$range) $range = $ranges->first();
        if (!$range) return;

        if ($range->min_value !== null && $range->max_value !== null) {
            $this->previewRange = $range->min_value . ' - ' . $range->max_value;
        } elseif ($range->normal_values_text) {
            $this->previewRange = $range->normal_values_text;
        }

        $value = $this->result_value ?? $item->result_value;
        if ($value !== null && $value !== '') {
            if ($range->min_value !== null && $range->max_value !== null) {
                $num = (float) $value;
                if ($num < $range->min_value) $this->previewFlag = 'L';
                elseif ($num > $range->max_value) $this->previewFlag = 'H';
                else $this->previewFlag = 'N';
            } elseif ($range->normal_values_text) {
                $cat = strtolower($this->result_categorical ?? $item->result_categorical ?? '');
                if (in_array($cat, ['positive', 'reactive', 'abnormal', 'high'])) $this->previewFlag = 'Abnormal';
                elseif (in_array($cat, ['negative', 'normal', 'non-reactive', 'low'])) $this->previewFlag = 'Normal';
            }
        }
    }

    public function saveResult(): void
    {
        $this->validate([
            'result_value' => 'nullable|string',
            'result_text' => 'nullable|string',
            'result_categorical' => 'nullable|string|max:100',
            'result_notes' => 'nullable|string',
        ]);

        $item = LabRequestItem::findOrFail($this->resultItemId);
        $uid = $this->currentUserId();

        $flag = $this->previewFlag;
        $rangeText = $this->previewRange;
        if ($rangeText && $this->previewUnit) {
            $rangeText .= ' ' . $this->previewUnit;
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

        $allStatuses = $item->labRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Completed') => 'Completed',
            $allStatuses->contains('Processing') || $allStatuses->contains('Collected') => 'Processing',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };

        $updateData = ['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()];
        if ($parentStatus === 'Completed' && $item->labRequest->billing_status === 'Pending') {
            $updateData['billing_status'] = 'Billed';
        }
        $item->labRequest->update($updateData);

        if ($parentStatus === 'Completed') {
            \App\Models\LabRequest::postChargeToLedger($item->labRequest, (float) $item->labRequest->total_charge, $uid);
        }

        $this->showResultModal = false;
        $this->dispatch('toast', type: 'success', message: 'Lab result saved successfully.');

        $request = $item->labRequest;
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
    }

    public function render()
    {
        $tenantId = $this->currentTenantId();

        $items = LabRequest::query()
            ->with(['patient', 'requestingDepartment', 'requestingDoctor', 'items.labTest', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
            ->where('tenant_id', $tenantId)
            ->where('history', 1)
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('request_code', 'like', '%' . $this->search . '%')
                          ->orWhere('requesting_person', 'like', '%' . $this->search . '%')
                          ->orWhereHas('requestingDepartment', fn ($d) =>
                              $d->where('dept_name', 'like', '%' . $this->search . '%')
                                ->orWhere('dept_code', 'like', '%' . $this->search . '%')
                          )
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
            ->where('tenant_id', $tenantId)
            ->where('history', 1)
            ->orderBy('dept_name')
            ->get(['id', 'dept_code', 'dept_name']);

        $doctors = HealthCareProfessional::query()
            ->where('tenant_id', $tenantId)
            ->where('history', 1)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $patients = Patient::query()
            ->where('tenant_id', $tenantId)
            ->where('history', 1)
            ->when($this->patient_search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('last_name', 'like', '%' . $this->patient_search . '%')
                          ->orWhere('first_name', 'like', '%' . $this->patient_search . '%')
                          ->orWhere('patient_code', 'like', '%' . $this->patient_search . '%')
                )
            )
            ->orderBy('last_name')
            ->limit(10)
            ->get(['id', 'patient_code', 'first_name', 'last_name', 'middle_name']);

        $labTests = LabTest::query()
            ->with('labSection')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'lab_section_id']);

        $labPanels = LabPanel::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $detailRequest = null;
        if ($this->showDetailModal && $this->viewingId) {
            $detailRequest = LabRequest::with(['patient', 'requestingDepartment', 'requestingDoctor', 'items.performedBy', 'items.verifiedBy', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
                ->find($this->viewingId);
        }

        return view('livewire.lab.lab-dispensing-crud', compact(
            'items', 'patients', 'departments', 'doctors', 'labTests', 'labPanels', 'detailRequest'
        ));
    }
}
