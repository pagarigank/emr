<?php

namespace App\Livewire\Nursing;

use App\Models\Admission;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\CsrRequest;
use App\Models\CsrRequestItem;
use App\Models\Nursing\NursingVital;
use App\Models\Nursing\NursingCareNote;
use App\Models\Nursing\NursingMedicationAdministration;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Pharmacy\PharmacyRequestItem;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Radiology\RadiologyRequestItem;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Procedures\ProcedureRequestItem;
use App\Models\Or\OrSchedule;
use App\Models\Or\OrRoom;
use App\Models\HealthCareProfessional;
use App\Models\Ward\WardRoom;
use App\Models\Icu\IcuBed;
use App\Models\AdmissionRoomHistory;
use Livewire\Component;
use Livewire\WithPagination;

class NursingCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'admission_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showCompleted = false;

    // Detail / Treatment view
    public bool $showDetailView = false;
    public ?int $activeAdmissionId = null;
    public ?Admission $activeAdmission = null;
    public int $detailTab = 1;

    // Vitals form
    public bool $showVitalModal = false;
    public ?int $editingVitalId = null;
    public string $vital_temperature = '', $vital_heart_rate = '', $vital_respiratory_rate = '';
    public string $vital_bp_systolic = '', $vital_bp_diastolic = '', $vital_o2_saturation = '';
    public string $vital_pain_score = '', $level_of_consciousness = '', $blood_sugar = '', $vital_notes = '';

    // Care Note form
    public bool $showCareNoteModal = false;
    public ?int $editingCareNoteId = null;
    public string $care_date = '', $shift = 'AM';
    public string $subjective = '', $objective = '', $assessment = '', $plan = '', $care_notes = '';

    // Discharge
    public bool $showDischargeModal = false;
    public string $discharge_date = '', $discharge_notes = '';

    // Orders — batch queue system
    public bool $showOrderModal = false;
    public string $orderType = 'Lab', $orderNotes = '', $orderPriority = 'Routine';
    public array $orderSelection = [];
    public array $orderQuantities = [];
    public array $orderFrequencies = [];
    public array $orderRoutes = [];
    public array $batchOrders = [];
    public ?string $previousOrderType = null;

    // OR Schedule form
    public bool $showOrScheduleModal = false;
    public ?string $or_room_id = null, $or_surgeon_id = null, $or_anesthesiologist_id = null;
    public string $or_procedure_type = '', $or_procedure_description = '';
    public string $or_scheduled_date = '', $or_start_time = '', $or_end_time = '';
    public string $or_pre_op_notes = '', $or_intra_op_notes = '', $or_post_op_notes = '';

    // MAR (Medication Administration Record)
    public bool $showMarModal = false;
    public ?int $editingMarId = null;
    public string $mar_drug_name = '', $mar_dosage = '', $mar_dose_given = '', $mar_route = 'PO';
    public string $mar_administered_at = '', $mar_notes = '';

    // Transfer Room
    public bool $showTransferModal = false;
    public string $transferTargetType = 'ward_room';
    public ?string $transferTargetId = null;
    public string $transferReason = '';

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    public function mount(): void
    {
        $this->care_date = now()->format('Y-m-d');
        $this->discharge_date = now()->format('Y-m-d');
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function setDetailTab(int $tab): void { $this->detailTab = $tab; }

    public function viewAdmission(int $id): void
    {
        $this->activeAdmissionId = $id;
        $this->activeAdmission = Admission::with([
            'patient', 'wardRoom.ward', 'wardRoom.roomType', 'icuBed.ward',
            'attendingPhysician', 'admittingPhysician', 'erAdmission',
            'erAdmission.labRequests.items', 'erAdmission.pharmacyRequests.items',
            'erAdmission.csrRequests.items', 'erAdmission.radiologyRequests.items',
            'erAdmission.procedureRequests.items',
            'labRequests.items', 'pharmacyRequests.items', 'csrRequests.items',
            'radiologyRequests.items', 'procedureRequests.items',
        ])->findOrFail($id);
        $this->showDetailView = true;
        $this->detailTab = 1;
    }

    public function backToList(): void
    {
        $this->showDetailView = false;
        $this->activeAdmissionId = null;
        $this->activeAdmission = null;
    }

    // ── Order Code Generators ─────────────────────────────────────────────────

    private function generateLabRequestCode(int $tid): string
    {
        $last = LabRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'LR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generatePharmacyRequestCode(int $tid): string
    {
        $last = PharmacyRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'PR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateCsrRequestCode(int $tid): string
    {
        $last = CsrRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'CR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateRadiologyRequestCode(int $tid): string
    {
        $last = RadiologyRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'RR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateProcedureRequestCode(int $tid): string
    {
        $last = ProcedureRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->request_code, 5)) + 1 : 1;
        return 'PRCD-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ── Vitals ────────────────────────────────────────────────────────────────

    public function openAddVital(): void
    {
        $this->resetVitalForm();
        $this->showVitalModal = true;
    }

    public function editVital(int $id): void
    {
        $v = NursingVital::findOrFail($id);
        $this->editingVitalId = $v->id;
        $this->vital_temperature = (string) $v->vital_temperature;
        $this->vital_heart_rate = (string) $v->vital_heart_rate;
        $this->vital_respiratory_rate = (string) $v->vital_respiratory_rate;
        $this->vital_bp_systolic = (string) $v->vital_bp_systolic;
        $this->vital_bp_diastolic = (string) $v->vital_bp_diastolic;
        $this->vital_o2_saturation = (string) $v->vital_o2_saturation;
        $this->vital_pain_score = (string) $v->vital_pain_score;
        $this->level_of_consciousness = (string) $v->level_of_consciousness;
        $this->blood_sugar = (string) $v->blood_sugar;
        $this->vital_notes = (string) $v->notes;
        $this->showVitalModal = true;
    }

    public function saveVital(): void
    {
        $this->validate([
            'vital_temperature' => ['nullable', 'numeric', 'min:32', 'max:43'],
            'vital_heart_rate' => ['nullable', 'integer', 'min:20', 'max:300'],
            'vital_respiratory_rate' => ['nullable', 'integer', 'min:4', 'max:80'],
            'vital_bp_systolic' => ['nullable', 'integer', 'min:50', 'max:300'],
            'vital_bp_diastolic' => ['nullable', 'integer', 'min:20', 'max:200'],
            'vital_o2_saturation' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vital_pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'blood_sugar' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ]);

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();
        $now = now();

        $data = [
            'admission_id' => $this->activeAdmissionId,
            'recorded_by' => $uid,
            'recorded_at' => $now,
            'vital_temperature' => $this->vital_temperature !== '' ? (float) $this->vital_temperature : null,
            'vital_heart_rate' => $this->vital_heart_rate !== '' ? (int) $this->vital_heart_rate : null,
            'vital_respiratory_rate' => $this->vital_respiratory_rate !== '' ? (int) $this->vital_respiratory_rate : null,
            'vital_bp_systolic' => $this->vital_bp_systolic !== '' ? (int) $this->vital_bp_systolic : null,
            'vital_bp_diastolic' => $this->vital_bp_diastolic !== '' ? (int) $this->vital_bp_diastolic : null,
            'vital_o2_saturation' => $this->vital_o2_saturation !== '' ? (float) $this->vital_o2_saturation : null,
            'vital_pain_score' => $this->vital_pain_score !== '' ? (int) $this->vital_pain_score : null,
            'level_of_consciousness' => $this->level_of_consciousness ?: null,
            'blood_sugar' => $this->blood_sugar !== '' ? (float) $this->blood_sugar : null,
            'notes' => $this->vital_notes ?: null,
        ];

        if ($this->editingVitalId) {
            NursingVital::findOrFail($this->editingVitalId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Vital signs updated.');
        } else {
            $data['tenant_id'] = $tid;
            $data['history'] = 1;
            $data['created_by'] = $uid;
            $data['created_date'] = $now;
            NursingVital::create($data);
            $this->dispatch('toast', type: 'success', message: 'Vital signs recorded.');
        }

        $this->closeVitalModal();
    }

    public function deleteVital(int $id): void
    {
        NursingVital::findOrFail($id)->update(['history' => 0]);
        $this->dispatch('toast', type: 'success', message: 'Vital record removed.');
    }

    private function resetVitalForm(): void
    {
        $this->reset([
            'editingVitalId', 'vital_temperature', 'vital_heart_rate', 'vital_respiratory_rate',
            'vital_bp_systolic', 'vital_bp_diastolic', 'vital_o2_saturation',
            'vital_pain_score', 'level_of_consciousness', 'blood_sugar', 'vital_notes',
        ]);
    }

    public function closeVitalModal(): void
    {
        $this->showVitalModal = false;
        $this->resetVitalForm();
    }

    // ── Care Notes ────────────────────────────────────────────────────────────

    public function openAddCareNote(): void
    {
        $this->resetCareNoteForm();
        $this->care_date = now()->format('Y-m-d');
        $this->showCareNoteModal = true;
    }

    public function editCareNote(int $id): void
    {
        $n = NursingCareNote::findOrFail($id);
        $this->editingCareNoteId = $n->id;
        $this->care_date = $n->care_date->format('Y-m-d');
        $this->shift = $n->shift;
        $this->subjective = $n->subjective ?? '';
        $this->objective = $n->objective ?? '';
        $this->assessment = $n->assessment ?? '';
        $this->plan = $n->plan ?? '';
        $this->care_notes = $n->notes ?? '';
        $this->showCareNoteModal = true;
    }

    public function saveCareNote(): void
    {
        $this->validate([
            'care_date' => ['required', 'date'],
            'shift' => ['required', 'in:AM,PM,Night'],
        ]);

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();
        $now = now();

        $data = [
            'admission_id' => $this->activeAdmissionId,
            'nurse_id' => $uid,
            'care_date' => $this->care_date,
            'shift' => $this->shift,
            'subjective' => $this->subjective ?: null,
            'objective' => $this->objective ?: null,
            'assessment' => $this->assessment ?: null,
            'plan' => $this->plan ?: null,
            'notes' => $this->care_notes ?: null,
        ];

        if ($this->editingCareNoteId) {
            $data['updated_by'] = $uid;
            $data['updated_date'] = $now;
            NursingCareNote::findOrFail($this->editingCareNoteId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Care note updated.');
        } else {
            $data['tenant_id'] = $tid;
            $data['history'] = 1;
            $data['created_by'] = $uid;
            $data['created_date'] = $now;
            $data['updated_by'] = $uid;
            $data['updated_date'] = $now;
            NursingCareNote::create($data);
            $this->dispatch('toast', type: 'success', message: 'Care note recorded.');
        }

        $this->closeCareNoteModal();
    }

    public function deleteCareNote(int $id): void
    {
        NursingCareNote::findOrFail($id)->update(['history' => 0]);
        $this->dispatch('toast', type: 'success', message: 'Care note removed.');
    }

    private function resetCareNoteForm(): void
    {
        $this->reset([
            'editingCareNoteId', 'subjective', 'objective',
            'assessment', 'plan', 'care_notes',
        ]);
        $this->shift = 'AM';
    }

    public function closeCareNoteModal(): void
    {
        $this->showCareNoteModal = false;
        $this->resetCareNoteForm();
    }

    // ── Discharge ─────────────────────────────────────────────────────────────

    public function openDischarge(): void
    {
        $this->discharge_date = now()->format('Y-m-d');
        $this->discharge_notes = '';
        $this->showDischargeModal = true;
    }

    public function confirmDischarge(): void
    {
        $this->validate([
            'discharge_date' => ['required', 'date'],
        ]);

        $uid = $this->currentUserId();
        $now = now();

        $admission = Admission::findOrFail($this->activeAdmissionId);
        $admission->update([
            'status' => 'Discharged',
            'discharge_date' => $this->discharge_date,
            'notes' => $this->discharge_notes ?: $admission->notes,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        // Free up ward room
        if ($admission->ward_room_id) {
            WardRoom::where('id', $admission->ward_room_id)->update(['status' => 'Available']);
        }
        if ($admission->icu_bed_id) {
            IcuBed::where('id', $admission->icu_bed_id)->update(['status' => 'Available']);
        }

        AdmissionRoomHistory::closeActive($admission->id, $now, $uid);

        $this->closeDischargeModal();
        $this->backToList();
        $this->dispatch('toast', type: 'success', message: 'Patient discharged successfully.');
    }

    public function closeDischargeModal(): void
    {
        $this->showDischargeModal = false;
        $this->discharge_date = '';
        $this->discharge_notes = '';
    }

    // ── Orders ────────────────────────────────────────────────────────────────

    public function openOrderModal(string $type): void
    {
        $this->orderType = $type;
        $this->previousOrderType = $type;
        $this->orderSelection = [];
        $this->orderQuantities = [];
        $this->orderFrequencies = [];
        $this->orderRoutes = [];
        $this->orderNotes = '';
        $this->orderPriority = 'Routine';
        $this->batchOrders = [];
        $this->showOrderModal = true;
    }

    public function updatedOrderType(string $value): void
    {
        // Auto-save: Save current tab's selections to batch before switching
        if ($this->previousOrderType && !empty($this->orderSelection)) {
            $found = false;
            foreach ($this->batchOrders as $idx => &$batch) {
                if ($batch['type'] === $this->previousOrderType) {
                    $batch['selection'] = $this->orderSelection;
                    $batch['quantities'] = $this->orderQuantities;
                    $batch['frequencies'] = $this->orderFrequencies;
                    $batch['routes'] = $this->orderRoutes;
                    $batch['priority'] = $this->orderPriority;
                    $batch['notes'] = $this->orderNotes;
                    $batch['item_count'] = count($this->orderSelection);
                    $found = true;
                    break;
                }
            }
            unset($batch);
            if (!$found) {
                $this->batchOrders[] = [
                    'type' => $this->previousOrderType,
                    'selection' => $this->orderSelection,
                    'quantities' => $this->orderQuantities,
                    'frequencies' => $this->orderFrequencies,
                    'routes' => $this->orderRoutes,
                    'priority' => $this->orderPriority,
                    'notes' => $this->orderNotes,
                    'item_count' => count($this->orderSelection),
                ];
            }
        }

        // Restore: Load new tab's selections from batch if exists
        $this->orderSelection = [];
        $this->orderQuantities = [];
        $this->orderFrequencies = [];
        $this->orderRoutes = [];
        $this->orderNotes = '';
        $this->orderPriority = 'Routine';

        foreach ($this->batchOrders as $batch) {
            if ($batch['type'] === $value) {
                $this->orderSelection = $batch['selection'];
                $this->orderQuantities = $batch['quantities'];
                $this->orderFrequencies = $batch['frequencies'];
                $this->orderRoutes = $batch['routes'];
                $this->orderPriority = $batch['priority'];
                $this->orderNotes = $batch['notes'];
                break;
            }
        }

        $this->previousOrderType = $value;
    }

    public function queueOrder(): void
    {
        $this->validate([
            'orderType' => ['required', 'in:Lab,Pharmacy,CSR,Radiology,Procedure'],
            'orderSelection' => ['required', 'array', 'min:1'],
        ], [
            'orderSelection.required' => 'Please select at least one item to queue.',
            'orderSelection.min' => 'Please select at least one item to queue.',
        ]);

        if ($this->orderType === 'Pharmacy') {
            foreach ($this->orderSelection as $id) {
                if (empty($this->orderFrequencies[$id] ?? '')) {
                    $this->addError('orderFrequencies.' . $id, 'Frequency is required for pharmacy items.');
                    return;
                }
                if (empty($this->orderRoutes[$id] ?? '')) {
                    $this->addError('orderRoutes.' . $id, 'Route is required for pharmacy items.');
                    return;
                }
            }
        }

        $newBatch = [
            'type' => $this->orderType,
            'selection' => $this->orderSelection,
            'quantities' => $this->orderQuantities,
            'frequencies' => $this->orderFrequencies,
            'routes' => $this->orderRoutes,
            'priority' => $this->orderPriority,
            'notes' => $this->orderNotes,
            'item_count' => count($this->orderSelection),
        ];

        $found = false;
        foreach ($this->batchOrders as $idx => &$batch) {
            if ($batch['type'] === $this->orderType) {
                $batch = $newBatch;
                $found = true;
                break;
            }
        }
        unset($batch);
        if (!$found) {
            $this->batchOrders[] = $newBatch;
        }

        $typeName = $this->orderType;
        $this->orderSelection = [];
        $this->orderQuantities = [];
        $this->orderFrequencies = [];
        $this->orderRoutes = [];
        $this->orderNotes = '';
        $this->orderPriority = 'Routine';
        $this->dispatch('toast', type: 'success', message: ucfirst($typeName) . ' order queued (' . $newBatch['item_count'] . ' items).');
    }

    public function removeBatchOrder(int $index): void
    {
        if (isset($this->batchOrders[$index])) {
            unset($this->batchOrders[$index]);
            $this->batchOrders = array_values($this->batchOrders);
        }
    }

    public function clearBatch(): void
    {
        $this->batchOrders = [];
        $this->orderSelection = [];
        $this->orderQuantities = [];
        $this->orderFrequencies = [];
        $this->orderRoutes = [];
        $this->orderNotes = '';
        $this->orderPriority = 'Routine';
    }

    public function placeOrder(): void
    {
        if (empty($this->batchOrders)) {
            $this->dispatch('toast', type: 'error', message: 'No orders queued. Add items to the batch first.');
            return;
        }

        $adm = $this->activeAdmission;
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        $count = 0;
        foreach ($this->batchOrders as $batch) {
            $data = [
                'tenant_id' => $tid,
                'admission_id' => $adm->id,
                'request_code' => '',
                'requesting_department_id' => null,
                'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
                'request_notes' => 'Ordered from Nursing Station' . ($batch['notes'] ? "\n\n" . $batch['notes'] : ''),
                'notes' => 'Ordered from Nursing Station' . ($batch['notes'] ? "\n\n" . $batch['notes'] : ''),
                'status' => 'Pending',
                'billing_status' => 'Pending',
                'total_amount' => 0,
                'total_charge' => 0,
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ];

            match ($batch['type']) {
                'Lab' => $this->placeLabOrder($adm, $data, $batch),
                'Pharmacy' => $this->placePharmacyOrder($adm, $data, $batch),
                'CSR' => $this->placeCsrOrder($adm, $data, $batch),
                'Radiology' => $this->placeRadiologyOrder($adm, $data, $batch),
                'Procedure' => $this->placeProcedureOrder($adm, $data, $batch),
            };
            $count++;
        }

        $this->showOrderModal = false;
        $this->batchOrders = [];
        $this->resetOrderForm();
        $this->dispatch('toast', type: 'success', message: $count . ' order(s) placed successfully.');
    }

    private function placeLabOrder(Admission $adm, array $data, array $batch): void
    {
        $code = $this->generateLabRequestCode($data['tenant_id']);
        $data['request_code'] = $code;
        $data['patient_id'] = $adm->patient_id;
        $data['priority'] = $batch['priority'];
        unset($data['notes'], $data['total_amount'], $data['requesting_person']);

        $total = 0;
        $items = [];
        foreach ($batch['selection'] as $id) {
            $test = \App\Models\LabTest::find($id);
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
        $data['total_charge'] = $total;

        $request = LabRequest::create($data);
        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $data['created_by'],
                'created_date' => $data['created_date'],
                'updated_by' => $data['updated_by'],
                'updated_date' => $data['updated_date'],
            ]));
        }
    }

    private function placePharmacyOrder(Admission $adm, array $data, array $batch): void
    {
        $code = $this->generatePharmacyRequestCode($data['tenant_id']);
        $data['request_code'] = $code;
        $data['priority'] = $batch['priority'];
        unset($data['request_notes'], $data['total_charge']);

        $total = 0;
        $items = [];
        foreach ($batch['selection'] as $id) {
            $drug = \App\Models\Pharmacy\Drug::find($id);
            if ($drug) {
                $qty = (int) ($batch['quantities'][$id] ?? 1);
                $avgcost = (float) $drug->batches()->avg('selling_price') ?: 0;
                $cost = $avgcost * $qty;
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
                    'dosage'              => $drug->drug_strength ?? null,
                    'frequency'           => $batch['frequencies'][$id] ?? null,
                    'route'               => $batch['routes'][$id] ?? $drug->route ?? null,
                    'quantity'            => $qty,
                    'unit_cost'           => $avgcost,
                    'total_cost'          => $cost,
                ];
                $total += $cost;
            }
        }
        $data['total_amount'] = $total;

        $request = PharmacyRequest::create($data);
        foreach ($items as $pi) {
            $request->items()->create(array_merge($pi, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $data['created_by'],
                'created_date' => $data['created_date'],
                'updated_by' => $data['updated_by'],
                'updated_date' => $data['updated_date'],
            ]));
        }
    }

    private function placeCsrOrder(Admission $adm, array $data, array $batch): void
    {
        $code = $this->generateCsrRequestCode($data['tenant_id']);
        $data['request_code'] = $code;
        unset($data['patient_id'], $data['request_notes'], $data['total_charge'], $data['priority']);

        $total = 0;
        $items = [];
        foreach ($batch['selection'] as $id) {
            $item = \App\Models\Supply\Item::find($id);
            if ($item) {
                $qty = (int) ($batch['quantities'][$id] ?? 1);
                $cost = (float) ($item->unit_cost ?? 0) * $qty;
                $items[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'supply_unit_name' => $item->unit?->name ?? '',
                    'quantity' => $qty,
                    'unit_cost' => $item->unit_cost ?? 0,
                    'total_cost' => $cost,
                ];
                $total += $cost;
            }
        }
        $data['total_amount'] = $total;

        $request = CsrRequest::create($data);
        foreach ($items as $ci) {
            $request->items()->create(array_merge($ci, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $data['created_by'],
                'created_date' => $data['created_date'],
                'updated_by' => $data['updated_by'],
                'updated_date' => $data['updated_date'],
            ]));
        }
    }

    private function placeRadiologyOrder(Admission $adm, array $data, array $batch): void
    {
        $code = $this->generateRadiologyRequestCode($data['tenant_id']);
        $data['request_code'] = $code;
        unset($data['patient_id'], $data['priority'], $data['request_notes'], $data['total_charge']);

        $total = 0;
        $items = [];
        foreach ($batch['selection'] as $id) {
            $exam = \App\Models\Radiology\RadiologyExamType::find($id);
            if ($exam) {
                $charge = (float) ($exam->standard_charge ?? 0);
                $items[] = [
                    'exam_type_id' => $exam->id,
                    'exam_name' => $exam->name,
                    'modality_name' => $exam->modality?->name ?? '',
                    'charge_amount' => $charge,
                ];
                $total += $charge;
            }
        }
        $data['total_amount'] = $total;

        $request = RadiologyRequest::create($data);
        foreach ($items as $ri) {
            $request->items()->create(array_merge($ri, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $data['created_by'],
                'created_date' => $data['created_date'],
                'updated_by' => $data['updated_by'],
                'updated_date' => $data['updated_date'],
            ]));
        }
    }

    private function placeProcedureOrder(Admission $adm, array $data, array $batch): void
    {
        $code = $this->generateProcedureRequestCode($data['tenant_id']);
        $data['request_code'] = $code;
        unset($data['requesting_person'], $data['request_notes'], $data['total_charge']);

        $total = 0;
        $items = [];
        foreach ($batch['selection'] as $id) {
            $proc = \App\Models\Procedures\Procedure::find($id);
            if ($proc) {
                $qty = (int) ($batch['quantities'][$id] ?? 1);
                $charge = (float) ($proc->standard_charge ?? 0) * $qty;
                $items[] = [
                    'procedure_id' => $proc->id,
                    'procedure_name' => $proc->name,
                    'category_name' => $proc->category?->name ?? '',
                    'quantity' => $qty,
                    'unit_charge' => $proc->standard_charge ?? 0,
                    'total_charge' => $charge,
                ];
                $total += $charge;
            }
        }
        $data['total_amount'] = $total;

        $request = ProcedureRequest::create($data);
        foreach ($items as $pi) {
            $request->items()->create(array_merge($pi, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $data['created_by'],
                'created_date' => $data['created_date'],
                'updated_by' => $data['updated_by'],
                'updated_date' => $data['updated_date'],
            ]));
        }
    }

    private function resetOrderForm(): void
    {
        $this->reset([
            'orderType', 'orderSelection', 'orderQuantities', 'orderFrequencies', 'orderRoutes',
            'orderNotes', 'orderPriority', 'batchOrders',
        ]);
    }

    // ── Inline Order Status Updates ──────────────────────────────────────────

    public function refreshOrders(): void
    {
        if ($this->activeAdmissionId === null) return;
        $this->dispatch('$refresh');
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

    // ── Medication Administration Record (MAR) ───────────────────────────────

    public function openAddMar(): void
    {
        $this->resetMarForm();
        $this->mar_administered_at = now()->format('Y-m-d\TH:i');
        $this->showMarModal = true;
    }

    public function editMar(int $id): void
    {
        $m = NursingMedicationAdministration::findOrFail($id);
        $this->editingMarId = $m->id;
        $this->mar_drug_name = $m->drug_name;
        $this->mar_dosage = $m->dosage ?? '';
        $this->mar_dose_given = $m->dose_given ?? '';
        $this->mar_route = $m->route ?? 'PO';
        $this->mar_administered_at = $m->administered_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->mar_notes = $m->notes ?? '';
        $this->showMarModal = true;
    }

    public function saveMar(): void
    {
        $this->validate([
            'mar_drug_name' => ['required', 'string', 'max:200'],
            'mar_administered_at' => ['required'],
        ]);

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();
        $now = now();

        $data = [
            'admission_id' => $this->activeAdmissionId,
            'drug_name' => $this->mar_drug_name,
            'dosage' => $this->mar_dosage ?: null,
            'dose_given' => $this->mar_dose_given ?: null,
            'route' => $this->mar_route ?: null,
            'administered_at' => $this->mar_administered_at,
            'administered_by' => $uid,
            'notes' => $this->mar_notes ?: null,
        ];

        if ($this->editingMarId) {
            NursingMedicationAdministration::findOrFail($this->editingMarId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'MAR entry updated.');
        } else {
            $data['tenant_id'] = $tid;
            $data['history'] = 1;
            $data['created_by'] = $uid;
            $data['created_date'] = $now;
            NursingMedicationAdministration::create($data);
            $this->dispatch('toast', type: 'success', message: 'Medication administration recorded.');
        }

        $this->closeMarModal();
    }

    public function deleteMar(int $id): void
    {
        NursingMedicationAdministration::findOrFail($id)->update(['history' => 0]);
        $this->dispatch('toast', type: 'success', message: 'MAR entry removed.');
    }

    private function resetMarForm(): void
    {
        $this->reset([
            'editingMarId', 'mar_drug_name', 'mar_dosage', 'mar_dose_given',
            'mar_route', 'mar_administered_at', 'mar_notes',
        ]);
    }

    public function closeMarModal(): void
    {
        $this->showMarModal = false;
        $this->resetMarForm();
    }

    // ── OR Schedule ──────────────────────────────────────────────────────────

    public function openOrScheduleForm(): void
    {
        $this->resetOrScheduleForm();
        $this->or_scheduled_date = now()->format('Y-m-d');
        $this->showOrScheduleModal = true;
    }

    public function saveOrSchedule(): void
    {
        $this->validate([
            'or_room_id' => ['required', 'integer', 'exists:or_rooms,id'],
            'or_surgeon_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'or_anesthesiologist_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'or_scheduled_date' => ['required', 'date'],
            'or_procedure_type' => ['nullable', 'string', 'max:100'],
            'or_start_time' => ['nullable', 'string', 'max:8'],
            'or_end_time' => ['nullable', 'string', 'max:8'],
        ]);

        $adm = $this->activeAdmission;
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        OrSchedule::create([
            'tenant_id' => $tid,
            'schedule_code' => $this->generateOrScheduleCode($tid),
            'or_room_id' => $this->or_room_id,
            'patient_id' => $adm->patient_id,
            'admission_id' => $adm->id,
            'surgeon_id' => $this->or_surgeon_id,
            'anesthesiologist_id' => $this->or_anesthesiologist_id,
            'procedure_type' => $this->or_procedure_type,
            'procedure_description' => $this->or_procedure_description,
            'scheduled_date' => $this->or_scheduled_date,
            'start_time' => $this->or_start_time ?: null,
            'end_time' => $this->or_end_time ?: null,
            'pre_op_notes' => $this->or_pre_op_notes ?: null,
            'intra_op_notes' => $this->or_intra_op_notes ?: null,
            'post_op_notes' => $this->or_post_op_notes ?: null,
            'status' => 'Scheduled',
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->closeOrScheduleModal();
        $this->dispatch('toast', type: 'success', message: 'OR schedule created successfully.');
    }

    private function generateOrScheduleCode(int $tid): string
    {
        $last = OrSchedule::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->schedule_code, 4)) + 1 : 1;
        return 'ORS-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function resetOrScheduleForm(): void
    {
        $this->reset([
            'or_room_id', 'or_surgeon_id', 'or_anesthesiologist_id',
            'or_procedure_type', 'or_procedure_description',
            'or_scheduled_date', 'or_start_time', 'or_end_time',
            'or_pre_op_notes', 'or_intra_op_notes', 'or_post_op_notes',
        ]);
    }

    public function closeOrScheduleModal(): void
    {
        $this->showOrScheduleModal = false;
        $this->resetOrScheduleForm();
    }

    // ── Transfer Room ─────────────────────────────────────────────────────────

    public function openTransferModal(): void
    {
        $this->resetTransferForm();
        $this->transferTargetType = 'ward_room';
        $this->showTransferModal = true;
    }

    public function saveTransfer(): void
    {
        $this->validate([
            'transferTargetType' => ['required', 'in:ward_room,icu_bed'],
            'transferTargetId' => ['required', 'integer'],
            'transferReason' => ['nullable', 'string', 'max:500'],
        ]);

        $adm = Admission::findOrFail($this->activeAdmissionId);
        $uid = $this->currentUserId();
        $now = now();
        $tid = $this->currentTenantId();

        // Close old room history
        AdmissionRoomHistory::closeActive($adm->id, $now, $uid);

        // Free old room/bed
        if ($adm->ward_room_id) {
            WardRoom::where('id', $adm->ward_room_id)->update(['status' => 'Available']);
        }
        if ($adm->icu_bed_id) {
            IcuBed::where('id', $adm->icu_bed_id)->update(['status' => 'Available']);
        }

        // Assign new room/bed
        if ($this->transferTargetType === 'ward_room') {
            WardRoom::where('id', $this->transferTargetId)->update(['status' => 'Occupied']);
            $adm->update([
                'ward_room_id' => $this->transferTargetId,
                'icu_bed_id' => null,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $wr = WardRoom::with('ward')->find($this->transferTargetId);
            AdmissionRoomHistory::recordAssignment(
                $adm->id, 'ward_room', $wr->id, $wr->name, $wr->ward?->name, (float) ($wr->room_rate ?? 0),
                $tid, $uid, $now
            );
        } else {
            IcuBed::where('id', $this->transferTargetId)->update(['status' => 'Occupied']);
            $adm->update([
                'icu_bed_id' => $this->transferTargetId,
                'ward_room_id' => null,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
            $icu = IcuBed::with('ward')->find($this->transferTargetId);
            AdmissionRoomHistory::recordAssignment(
                $adm->id, 'icu_bed', $icu->id, $icu->name, $icu->ward?->name, (float) ($icu->room_rate ?? 0),
                $tid, $uid, $now
            );
        }

        // Refresh admission data
        $this->activeAdmission = Admission::with([
            'patient', 'wardRoom.ward', 'wardRoom.roomType', 'icuBed.ward',
            'attendingPhysician', 'admittingPhysician', 'erAdmission',
        ])->findOrFail($this->activeAdmissionId);

        $this->closeTransferModal();
        $this->dispatch('toast', type: 'success', message: 'Patient transferred successfully.');
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->resetTransferForm();
    }

    private function resetTransferForm(): void
    {
        $this->reset([
            'transferTargetType', 'transferTargetId', 'transferReason',
        ]);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $tid = $this->currentTenantId();

        $admissions = Admission::with([
                'patient', 'wardRoom.ward', 'wardRoom.roomType', 'icuBed.ward',
                'attendingPhysician', 'admittingPhysician',
            ])
            ->where('tenant_id', $tid)
            ->where('status', $this->showCompleted ? 'Discharged' : 'Admitted')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('admission_code', 'like', '%' . $this->search . '%')
                          ->orWhere('admitting_diagnosis', 'like', '%' . $this->search . '%')
                          ->orWhereHas('patient', fn ($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('patient_code', 'like', '%' . $this->search . '%')
                          )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $latestVitals = collect();
        $careNotes = collect();
        $marRecords = collect();
        $admissionLabRequests = collect();
        $admissionPharmacyRequests = collect();
        $admissionCsrRequests = collect();
        $admissionRadiologyRequests = collect();
        $admissionProcedureRequests = collect();
        if ($this->activeAdmissionId !== null) {
            $latestVitals = NursingVital::where('admission_id', $this->activeAdmissionId)
                ->orderBy('recorded_at', 'desc')
                ->limit(20)
                ->get();

            $careNotes = NursingCareNote::where('admission_id', $this->activeAdmissionId)
                ->orderBy('care_date', 'desc')
                ->orderByRaw("CASE shift WHEN 'AM' THEN 0 WHEN 'PM' THEN 1 WHEN 'Night' THEN 2 END")
                ->limit(50)
                ->get();

            $admissionLabRequests = LabRequest::with('items')->where('admission_id', $this->activeAdmissionId)
                ->orderBy('created_date', 'desc')->get();
            $admissionPharmacyRequests = PharmacyRequest::with('items')->where('admission_id', $this->activeAdmissionId)
                ->orderBy('created_date', 'desc')->get();
            $admissionCsrRequests = CsrRequest::with('items')->where('admission_id', $this->activeAdmissionId)
                ->orderBy('created_date', 'desc')->get();
            $admissionRadiologyRequests = RadiologyRequest::with('items')->where('admission_id', $this->activeAdmissionId)
                ->orderBy('created_date', 'desc')->get();
            $admissionProcedureRequests = ProcedureRequest::with('items')->where('admission_id', $this->activeAdmissionId)
                ->orderBy('created_date', 'desc')->get();

            $marRecords = NursingMedicationAdministration::where('admission_id', $this->activeAdmissionId)
                ->orderBy('administered_at', 'desc')
                ->limit(50)
                ->get();
        }

        $admissionOrSchedules = $this->activeAdmissionId !== null
            ? OrSchedule::with(['orRoom', 'surgeon', 'anesthesiologist'])
                ->where('admission_id', $this->activeAdmissionId)
                ->orderBy('scheduled_date', 'desc')
                ->get()
            : collect();

        $orRooms = OrRoom::query()
            ->where('tenant_id', $tid)
            ->where('status', 'Available')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $orDoctors = HealthCareProfessional::query()
            ->where('tenant_id', $tid)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $availableWardRooms = WardRoom::with('ward', 'roomType')
            ->where('tenant_id', $tid)
            ->where('status', 'Available')
            ->orderBy('name')
            ->get();

        $availableIcuBeds = IcuBed::with('ward')
            ->where('tenant_id', $tid)
            ->where('status', 'Available')
            ->orderBy('name')
            ->get();

        return view('livewire.nursing.nursing-crud', compact(
            'admissions', 'latestVitals', 'careNotes', 'marRecords',
            'admissionLabRequests', 'admissionPharmacyRequests',
            'admissionCsrRequests', 'admissionRadiologyRequests',
            'admissionProcedureRequests', 'admissionOrSchedules',
            'orRooms', 'orDoctors', 'availableWardRooms', 'availableIcuBeds',
        ));
    }
}
