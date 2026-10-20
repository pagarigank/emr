<?php

namespace App\Livewire\Er;

use App\Models\Er\ErAdmission;
use App\Models\Er\ErBed;
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
use App\Models\Oecb\OecbCorePresentation;
use App\Models\Oecb\OecbFeeSchedule;
use App\Models\Or\OrSchedule;
use App\Models\Or\OrRoom;
use Livewire\Component;
use Livewire\WithPagination;

class ErAdmissionCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'admission_datetime';
    public string $sortDirection = 'desc';
    public int    $perPage       = 10;

    public bool $showEditModal = false, $showDispositionModal = false, $showTreatmentForm = false;
    public ?int $editingId = null, $treatingId = null;

    // Edit Fields
    public ?int $er_bed_id = null;
    public ?int $attending_physician_id = null;
    public string $initial_diagnosis = '';

    // Treatment Form Fields
    public string $treatment_history_of_present_illness = '';
    public string $treatment_physical_exam_findings = '';
    public string $treatment_assessment = '';
    public string $treatment_orders_summary = '';
    public string $treatment_treatment_notes = '';

    // OECB Fields
    public bool $is_oecb = false;
    public array $oecbEntries = [];

    public bool $showCompleted = false;

    // Order Modals
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

    // Pharmacy Order Modal
    public bool $showOrderPharmacyModal = false;
    public array $orderPharmacySelection = [];
    public array $orderPharmacyQuantities = [];
    public array $orderPharmacyFrequencies = []; // CF4: per-drug frequency (BID, TID, etc.)
    public array $orderPharmacyRoutes = [];      // CF4: per-drug route (PO, IV, IM, SC, etc.)
    public string $orderPharmacyPriority = 'Urgent';
    public string $orderPharmacyNotes = '';

    // Procedure Order Modal
    public bool $showOrderProcedureModal = false;
    public array $orderProcedureSelection = [];
    public array $orderProcedureQuantities = [];
    public string $orderProcedureNotes = '';

    // OR Schedule form
    public bool $showOrScheduleModal = false;
    public ?string $or_room_id = null, $or_surgeon_id = null, $or_anesthesiologist_id = null;
    public string $or_procedure_type = '', $or_procedure_description = '';
    public string $or_scheduled_date = '', $or_start_time = '', $or_end_time = '';
    public string $or_pre_op_notes = '', $or_intra_op_notes = '', $or_post_op_notes = '';

    // Disposition Fields
    public string $disposition = 'Pending';
    public string $discharge_instructions = '';

    protected $listeners = ['refreshErOrders' => 'refreshOrders'];

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

    private function erDepartmentId(): ?int
    {
        static $id = null;
        if ($id === null) {
            $dept = Department::where('dept_code', 'ER')->where('is_active', true)->first();
            $id = $dept?->id;
        }
        return $id;
    }

    public function addOecbEntry(): void
    {
        $this->oecbEntries[] = [
            'id' => null,
            'eecl_item_id' => null,
            'core_presentation' => '',
            'oecb_package_code' => '',
            'oecb_fixed_fee' => null,
            'quantity' => 1,
            'total_amount' => 0,
        ];
    }

    public function removeOecbEntry(int $index): void
    {
        unset($this->oecbEntries[$index]);
        $this->oecbEntries = array_values($this->oecbEntries);
    }

    public function updatedOecbEntries($value, $key): void
    {
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $field = $parts[1] ?? null;

        if ($field === 'core_presentation' && $value) {
            $fee = OecbFeeSchedule::whereHas('corePresentation', fn ($q) => $q->where('name', $value))
                ->first();
            if ($fee) {
                $this->oecbEntries[$index]['oecb_package_code'] = $fee->package_code;
                $this->oecbEntries[$index]['oecb_fixed_fee'] = (float) $fee->fixed_fee;
                $this->oecbEntries[$index]['quantity'] = $this->oecbEntries[$index]['quantity'] ?? 1;
                $this->oecbEntries[$index]['total_amount'] = (float) $fee->fixed_fee * ($this->oecbEntries[$index]['quantity'] ?? 1);
            }
        } elseif ($field === 'quantity') {
            $qty = max(1, (float) ($value ?: 1));
            $this->oecbEntries[$index]['quantity'] = $qty;
            $fee = (float) ($this->oecbEntries[$index]['oecb_fixed_fee'] ?? 0);
            $this->oecbEntries[$index]['total_amount'] = $qty * $fee;
        }
    }

    public function autoSyncEeclFromOrders(): void
    {
        if (!$this->treatingId) return;

        $adm = ErAdmission::with([
            'triageRecord.acuityLevel',
            'labRequests.items.labTest.eeclItem',
            'radiologyRequests.items.examType.eeclItem',
            'pharmacyRequests.items.drug.eeclItem',
            'csrRequests.items.item.eeclItem',
            'procedureRequests.items.procedure.eeclItem',
        ])->find($this->treatingId);

        if (!$adm) return;

        $this->is_oecb = true;
        $syncedCount = 0;
        $existingEeclIds = collect($this->oecbEntries)->pluck('eecl_item_id')->filter()->all();

        // 1. Mandatory ED Consultation based on Triage Acuity Level (PhilHealth Circular 2025-0020)
        $acuityName = strtolower($adm->triageRecord?->acuityLevel?->name ?? '');
        $acuityCode = strtolower($adm->triageRecord?->acuityLevel?->code ?? '');
        $isEmergent = str_contains($acuityName, 'resuscitation') || str_contains($acuityName, 'emergent') || str_contains($acuityName, 'level 1') || str_contains($acuityName, 'level 2') || in_array($acuityCode, ['esi-1', 'esi-2', '1', '2']);

        $consultationEecl = null;
        if ($isEmergent) {
            $consultationEecl = \App\Models\Oecb\EeclItem::where('is_active', true)
                ->where(function ($q) {
                    $q->where('item_name', 'like', '%Emergent ER Consultation%')
                      ->orWhere('item_code', 'EECL-ED-001')
                      ->orWhere('item_code', 'EECL-ES-001');
                })->first();
        } else {
            $consultationEecl = \App\Models\Oecb\EeclItem::where('is_active', true)
                ->where(function ($q) {
                    $q->where('item_name', 'like', '%Urgent ER Consultation%')
                      ->orWhere('item_code', 'EECL-ED-002')
                      ->orWhere('item_code', 'EECL-ES-002');
                })->first();
        }

        if ($consultationEecl && !in_array($consultationEecl->id, $existingEeclIds)) {
            $this->oecbEntries[] = [
                'id' => null,
                'eecl_item_id' => $consultationEecl->id,
                'core_presentation' => $consultationEecl->item_name,
                'oecb_package_code' => $consultationEecl->item_code,
                'oecb_fixed_fee' => (float) $consultationEecl->fixed_fee,
                'quantity' => 1,
                'total_amount' => (float) $consultationEecl->fixed_fee,
            ];
            $existingEeclIds[] = $consultationEecl->id;
            $syncedCount++;
        }

        // 2. Sync from Lab Requests
        foreach ($adm->labRequests as $req) {
            foreach ($req->items as $item) {
                $eecl = $item->labTest?->eeclItem;
                if ($eecl && !in_array($eecl->id, $existingEeclIds)) {
                    $this->oecbEntries[] = [
                        'id' => null,
                        'eecl_item_id' => $eecl->id,
                        'core_presentation' => $eecl->item_name,
                        'oecb_package_code' => $eecl->item_code,
                        'oecb_fixed_fee' => (float) $eecl->fixed_fee,
                        'quantity' => 1,
                        'total_amount' => (float) $eecl->fixed_fee,
                    ];
                    $existingEeclIds[] = $eecl->id;
                    $syncedCount++;
                }
            }
        }

        // 3. Sync from Radiology Requests
        foreach ($adm->radiologyRequests as $req) {
            foreach ($req->items as $item) {
                $eecl = $item->examType?->eeclItem;
                if ($eecl && !in_array($eecl->id, $existingEeclIds)) {
                    $this->oecbEntries[] = [
                        'id' => null,
                        'eecl_item_id' => $eecl->id,
                        'core_presentation' => $eecl->item_name,
                        'oecb_package_code' => $eecl->item_code,
                        'oecb_fixed_fee' => (float) $eecl->fixed_fee,
                        'quantity' => 1,
                        'total_amount' => (float) $eecl->fixed_fee,
                    ];
                    $existingEeclIds[] = $eecl->id;
                    $syncedCount++;
                }
            }
        }

        // 4. Sync from Pharmacy Requests
        foreach ($adm->pharmacyRequests as $req) {
            foreach ($req->items as $item) {
                $eecl = $item->drug?->eeclItem;
                if ($eecl && !in_array($eecl->id, $existingEeclIds)) {
                    $qty = (float) ($item->quantity_dispensed ?: ($item->quantity_requested ?: 1));
                    $fee = (float) $eecl->fixed_fee;
                    $this->oecbEntries[] = [
                        'id' => null,
                        'eecl_item_id' => $eecl->id,
                        'core_presentation' => $eecl->item_name,
                        'oecb_package_code' => $eecl->item_code,
                        'oecb_fixed_fee' => $fee,
                        'quantity' => $qty,
                        'total_amount' => $qty * $fee,
                    ];
                    $existingEeclIds[] = $eecl->id;
                    $syncedCount++;
                }
            }
        }

        // 5. Sync from CSR Supply Requests
        foreach ($adm->csrRequests as $req) {
            foreach ($req->items as $item) {
                $eecl = $item->item?->eeclItem;
                if ($eecl && !in_array($eecl->id, $existingEeclIds)) {
                    $qty = (float) ($item->quantity_dispensed ?: ($item->quantity_requested ?: 1));
                    $fee = (float) $eecl->fixed_fee;
                    $this->oecbEntries[] = [
                        'id' => null,
                        'eecl_item_id' => $eecl->id,
                        'core_presentation' => $eecl->item_name,
                        'oecb_package_code' => $eecl->item_code,
                        'oecb_fixed_fee' => $fee,
                        'quantity' => $qty,
                        'total_amount' => $qty * $fee,
                    ];
                    $existingEeclIds[] = $eecl->id;
                    $syncedCount++;
                }
            }
        }

        // 6. Sync from Procedure Requests
        foreach ($adm->procedureRequests as $req) {
            foreach ($req->items as $item) {
                $eecl = $item->procedure?->eeclItem;
                if ($eecl && !in_array($eecl->id, $existingEeclIds)) {
                    $fee = (float) $eecl->fixed_fee;
                    $this->oecbEntries[] = [
                        'id' => null,
                        'eecl_item_id' => $eecl->id,
                        'core_presentation' => $eecl->item_name,
                        'oecb_package_code' => $eecl->item_code,
                        'oecb_fixed_fee' => $fee,
                        'quantity' => 1,
                        'total_amount' => $fee,
                    ];
                    $existingEeclIds[] = $eecl->id;
                    $syncedCount++;
                }
            }
        }

        // 7. Auto-suggest Core Presentation if no presentation exists
        $hasCorePresentation = collect($this->oecbEntries)->contains(fn ($e) => empty($e['eecl_item_id']) && !empty($e['core_presentation']));
        if (!$hasCorePresentation) {
            $complaint = strtolower($adm->chief_complaint ?? $adm->initial_diagnosis ?? '');
            $suggested = null;
            if (str_contains($complaint, 'breath') || str_contains($complaint, 'dyspnea') || str_contains($complaint, 'asthma')) {
                $suggested = 'Difficulty of Breathing';
            } elseif (str_contains($complaint, 'chest') || str_contains($complaint, 'angina')) {
                $suggested = 'Chest Pain';
            } elseif (str_contains($complaint, 'abdom') || str_contains($complaint, 'stomach') || str_contains($complaint, 'epigastric')) {
                $suggested = 'Severe Abdominal Pain';
            } elseif (str_contains($complaint, 'headache') || str_contains($complaint, 'migraine')) {
                $suggested = 'Severe Headache';
            } elseif (str_contains($complaint, 'fever') || str_contains($complaint, 'pyrexia') || str_contains($complaint, 'febrile')) {
                $suggested = 'Fever';
            } elseif (str_contains($complaint, 'trauma') || str_contains($complaint, 'injury') || str_contains($complaint, 'wound') || str_contains($complaint, 'fall')) {
                $suggested = 'Trauma/Injury';
            } elseif (str_contains($complaint, 'seizure') || str_contains($complaint, 'convulsion')) {
                $suggested = 'Active Seizure';
            }

            if ($suggested) {
                $feeSchedule = \App\Models\Oecb\OecbFeeSchedule::whereHas('corePresentation', fn ($q) => $q->where('name', $suggested))->first();
                if ($feeSchedule) {
                    array_unshift($this->oecbEntries, [
                        'id' => null,
                        'eecl_item_id' => null,
                        'core_presentation' => $suggested,
                        'oecb_package_code' => $feeSchedule->package_code,
                        'oecb_fixed_fee' => (float) $feeSchedule->fixed_fee,
                        'quantity' => 1,
                        'total_amount' => (float) $feeSchedule->fixed_fee,
                    ]);
                    $syncedCount++;
                }
            }
        }

        if ($syncedCount > 0) {
            $this->dispatch('toast', type: 'success', message: "Auto-synced {$syncedCount} EECL item(s) & consultation from ER clinical orders!");
        } else {
            $this->dispatch('toast', type: 'info', message: 'No unlinked orders found. Ensure hospital items are mapped in the EECL Mapping Tool.');
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field)
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    // ——— Quick Edit Modal ———

    public function openEdit(int $id): void
    {
        $adm = ErAdmission::findOrFail($id);
        $this->editingId = $adm->id;
        $this->er_bed_id = $adm->er_bed_id;
        $this->attending_physician_id = $adm->attending_physician_id;
        $this->initial_diagnosis = $adm->initial_diagnosis ?? '';
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        $validated = $this->validate([
            'er_bed_id' => ['nullable', 'exists:er_beds,id'],
            'attending_physician_id' => ['nullable', 'exists:health_care_professionals,id'],
            'initial_diagnosis' => ['nullable', 'string', 'max:2000'],
        ]);

        $adm = ErAdmission::findOrFail($this->editingId);

        if ($adm->er_bed_id !== $validated['er_bed_id']) {
            if ($adm->er_bed_id) {
                ErBed::where('id', $adm->er_bed_id)->update(['status' => 'Available']);
            }
            if ($validated['er_bed_id']) {
                ErBed::where('id', $validated['er_bed_id'])->update(['status' => 'Occupied']);
            }
        }

        $adm->update(array_merge($validated, [
            'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]));

        $this->dispatch('toast', type: 'success', message: 'Medical notes updated.');
        $this->showEditModal = false;
        $this->reset(['editingId']);
    }

    // ——— Treatment Form ———

    public function openTreatment(int $id): void
    {
        $adm = ErAdmission::with('triageRecord', 'oecbEntries')->findOrFail($id);
        $this->treatingId = $adm->id;
        $this->treatment_history_of_present_illness = $adm->history_of_present_illness ?? '';
        $this->treatment_physical_exam_findings = $adm->physical_exam_findings ?? '';
        $this->treatment_assessment = $adm->assessment ?? '';
        $this->treatment_orders_summary = $adm->orders_summary ?? '';
        $this->treatment_treatment_notes = $adm->treatment_notes ?? '';
        $this->is_oecb = (bool) ($adm->is_oecb ?? false);
        $this->oecbEntries = $adm->oecbEntries->map(fn ($e) => [
            'id' => $e->id,
            'eecl_item_id' => $e->eecl_item_id,
            'core_presentation' => $e->core_presentation ?? '',
            'oecb_package_code' => $e->oecb_package_code ?? '',
            'oecb_fixed_fee' => $e->oecb_fixed_fee ? (float) $e->oecb_fixed_fee : null,
            'quantity' => $e->quantity ? (float) $e->quantity : 1,
            'total_amount' => $e->total_amount ? (float) $e->total_amount : ($e->oecb_fixed_fee ? (float) $e->oecb_fixed_fee : 0),
        ])->toArray();
        $this->disposition = $adm->disposition ?? 'Pending';
        $this->discharge_instructions = $adm->discharge_instructions ?? '';
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
            'is_oecb' => ['boolean'],
            'oecbEntries' => ['nullable', 'array'],
            'oecbEntries.*.core_presentation' => ['nullable', 'required_with:oecbEntries.*.oecb_package_code', 'string', 'max:200'],
            'oecbEntries.*.oecb_package_code' => ['nullable', 'string', 'max:30'],
            'oecbEntries.*.oecb_fixed_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $adm = ErAdmission::findOrFail($this->treatingId);

        $adm->update([
            'history_of_present_illness' => $validated['treatment_history_of_present_illness'],
            'physical_exam_findings' => $validated['treatment_physical_exam_findings'],
            'assessment' => $validated['treatment_assessment'],
            'orders_summary' => $validated['treatment_orders_summary'],
            'treatment_notes' => $validated['treatment_treatment_notes'],
            'is_oecb' => $validated['is_oecb'] ?? false,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->syncOecbEntries($adm);

        // Also save discharge_instructions if modified from disposition tab
        if ($this->discharge_instructions !== ($adm->discharge_instructions ?? '')) {
            $adm->update(['discharge_instructions' => $this->discharge_instructions]);
        }

        $this->dispatch('toast', type: 'success', message: 'Treatment record updated.');
    }

    private function syncOecbEntries(ErAdmission $adm): void
    {
        if (!$this->is_oecb || empty($this->oecbEntries)) {
            $adm->oecbEntries()->delete();
            return;
        }

        $existingIds = $adm->oecbEntries()->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($this->oecbEntries as $entry) {
            if (empty($entry['core_presentation'])) continue;

            $data = [
                'eecl_item_id' => $entry['eecl_item_id'] ?? null,
                'core_presentation' => $entry['core_presentation'],
                'oecb_package_code' => $entry['oecb_package_code'] ?? null,
                'oecb_fixed_fee' => $entry['oecb_fixed_fee'] ?? null,
                'quantity' => $entry['quantity'] ?? 1,
                'total_amount' => $entry['total_amount'] ?? ($entry['oecb_fixed_fee'] ?? 0),
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ];

            if (!empty($entry['id'])) {
                $oecbEntry = $adm->oecbEntries()->find($entry['id']);
                if ($oecbEntry) {
                    $oecbEntry->update($data);
                    $submittedIds[] = $oecbEntry->id;
                }
            } else {
                $oecbEntry = $adm->oecbEntries()->create(array_merge($data, [
                    'tenant_id' => $adm->tenant_id,
                    'created_by' => $this->currentUserId(),
                    'created_date' => now(),
                ]));
                $submittedIds[] = $oecbEntry->id;
            }
        }

        $idsToDelete = array_diff($existingIds, $submittedIds);
        if (!empty($idsToDelete)) {
            $adm->oecbEntries()->whereIn('id', $idsToDelete)->delete();
        }
    }

    public function closeTreatment(): void
    {
        $this->showTreatmentForm = false;
        $this->showOrderLabModal = false;
        $this->showOrderCsrModal = false;
        $this->showOrderRadiologyModal = false;
        $this->showOrderPharmacyModal = false;
        $this->showOrderProcedureModal = false;
        $this->reset(['treatingId', 'orderLabPriority', 'orderLabNotes', 'orderCsrNotes', 'orderRadiologyPriority', 'orderRadiologyNotes', 'orderCsrQuantities', 'orderPharmacyPriority', 'orderPharmacyNotes', 'orderPharmacyQuantities', 'orderProcedureNotes', 'orderProcedureQuantities', 'is_oecb', 'oecbEntries']);
    }

    // ——— OR Schedule ———
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

        $adm = ErAdmission::findOrFail($this->treatingId);
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        OrSchedule::create([
            'tenant_id' => $tid,
            'schedule_code' => $this->generateOrScheduleCode($tid),
            'or_room_id' => $this->or_room_id,
            'patient_id' => $adm->patient_id,
            'er_admission_id' => $adm->id,
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

    // ——— Order Refresh (called by polling + event listeners) ———
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

    // ——— Order Requests ———

    public function openOrderLab(): void
    {
        $this->orderLabSelection = [];
        $this->orderLabPriority = 'Urgent';
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
        $this->orderRadiologyPriority = 'Urgent';
        $this->orderRadiologyNotes = '';
        $this->showOrderRadiologyModal = true;
    }

    public function requestLabOrder(): void
    {
        $this->validate([
            'orderLabSelection' => ['required', 'array', 'min:1'],
            'orderLabSelection.*' => ['required', 'integer'],
            'orderLabPriority' => ['required', 'string', 'in:Routine,Urgent,STAT'],
            'orderLabNotes' => ['nullable', 'string', 'max:2000'],
        ], ['orderLabSelection.required' => 'Select at least one test or panel.']);

        $adm = ErAdmission::findOrFail($this->treatingId);
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
            'er_admission_id' => $adm->id,
            'request_code' => $code,
            'patient_id' => $adm->patient_id,
            'requesting_department_id' => $this->erDepartmentId(),
            'requesting_doctor_id' => $adm->attending_physician_id,
            'priority' => $this->orderLabPriority,
            'request_notes' => 'Ordered from ER Area' . ($this->orderLabNotes ? "\n\n" . $this->orderLabNotes : ''),
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
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
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

        $adm = ErAdmission::findOrFail($this->treatingId);
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
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'supply_unit_name' => $item->unit?->name ?? '',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'total_cost' => $lineTotal,
                ];
                $total += $lineTotal;
            }
        }

        $request = CsrRequest::create([
            'tenant_id' => $tid,
            'er_admission_id' => $adm->id,
            'request_code' => $code,
            'requesting_department_id' => $this->erDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
            'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
            'status' => 'Pending',
            'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from ER Area' . ($this->orderCsrNotes ? "\n\n" . $this->orderCsrNotes : ''),
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
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

        $adm = ErAdmission::findOrFail($this->treatingId);
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
                    'exam_type_id' => $exam->id,
                    'exam_name' => $exam->name,
                    'modality_name' => $exam->modality?->name ?? '',
                    'charge_amount' => $charge,
                ];
                $total += $charge;
            }
        }

        $request = RadiologyRequest::create([
            'tenant_id' => $tid,
            'er_admission_id' => $adm->id,
            'request_code' => $code,
            'requesting_department_id' => $this->erDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
            'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
            'status' => 'Pending',
            'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from ER Area' . ($this->orderRadiologyNotes ? "\n\n" . $this->orderRadiologyNotes : ''),
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]));
        }

        $this->showOrderRadiologyModal = false;
        $this->orderRadiologySelection = [];
        $this->orderRadiologyNotes = '';
        $this->dispatch('toast', type: 'success', message: "Radiology request {$code} created.");
    }

    private function generateLabRequestCode(int $tid): string
    {
        $last = LabRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'LR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateCsrRequestCode(int $tid): string
    {
        $last = CsrRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'CR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateRadiologyRequestCode(int $tid): string
    {
        $last = RadiologyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'RR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generatePharmacyRequestCode(int $tid): string
    {
        $last = PharmacyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'PR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
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

        $adm = ErAdmission::findOrFail($this->treatingId);
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
                    // CF4 / eClaims denormalized fields
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
            'tenant_id' => $tid,
            'er_admission_id' => $adm->id,
            'request_code' => $code,
            'requesting_department_id' => $this->erDepartmentId(),
            'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
            'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
            'priority' => $this->orderPharmacyPriority,
            'status' => 'Pending',
            'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from ER Area' . ($this->orderPharmacyNotes ? "\n\n" . $this->orderPharmacyNotes : ''),
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
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

        $adm = ErAdmission::findOrFail($this->treatingId);
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
                    'procedure_id' => $procedure->id,
                    'procedure_name' => $procedure->name,
                    'category_name' => $procedure->category?->name ?? '',
                    'quantity' => $qty,
                    'unit_charge' => $charge,
                    'total_charge' => $lineTotal,
                ];
                $total += $lineTotal;
            }
        }

        $request = ProcedureRequest::create([
            'tenant_id' => $tid,
            'er_admission_id' => $adm->id,
            'request_code' => $code,
            'requesting_department_id' => $this->erDepartmentId(),
            'requesting_doctor_id' => $adm->attending_physician_id,
            'status' => 'Pending',
            'billing_status' => 'Pending',
            'total_amount' => $total,
            'notes' => 'Ordered from ER Area' . ($this->orderProcedureNotes ? "\n\n" . $this->orderProcedureNotes : ''),
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        foreach ($items as $li) {
            $request->items()->create(array_merge($li, [
                'status' => 'Pending',
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
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

    // ——— Disposition ———

    public function openDisposition(int $id): void
    {
        $adm = ErAdmission::findOrFail($id);
        $this->editingId = $adm->id;
        $this->disposition = $adm->disposition ?? 'Pending';
        $this->discharge_instructions = $adm->discharge_instructions ?? '';
        $this->showDispositionModal = true;
    }

    public function saveDisposition(): void
    {
        $validated = $this->validate([
            'disposition' => ['required', 'string', 'in:Pending,Admitted to Ward,Admitted to ICU,Discharged Home,Expired,AMA'],
            'discharge_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $id = $this->editingId ?? $this->treatingId;
        $adm = ErAdmission::findOrFail($id);

        $status = 'Active';
        $dispositionDate = null;

        if ($validated['disposition'] !== 'Pending') {
            $status = 'Completed';
            $dispositionDate = now();
            if ($adm->er_bed_id) {
                ErBed::where('id', $adm->er_bed_id)->update(['status' => 'Available']);
            }
        }

        $isOecb = (bool) ($adm->is_oecb ?? false);
        if (in_array($validated['disposition'], ['Admitted to Ward', 'Admitted to ICU'])) {
            $isOecb = false;
        }

        $adm->update([
            'disposition' => $validated['disposition'],
            'status' => $status,
            'is_oecb' => $isOecb,
            'disposition_datetime' => $dispositionDate,
            'discharge_instructions' => $validated['discharge_instructions'],
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        if ($status === 'Completed' && $isOecb) {
            // Auto-sync EECL if entries are empty
            if ($adm->oecbEntries()->count() === 0) {
                $this->treatingId = $adm->id;
                $this->oecbEntries = [];
                $this->autoSyncEeclFromOrders();
                $this->syncOecbEntries($adm);
            }

            $uid = $this->currentUserId();
            $oecbEntries = $adm->oecbEntries()->where('billing_status', 'Pending')->get();
            foreach ($oecbEntries as $oecbEntry) {
                $chargeAmount = (float) ($oecbEntry->total_amount ?? $oecbEntry->oecb_fixed_fee ?? 0);
                if ($chargeAmount > 0) {
                    $oecbEntry->update(['billing_status' => 'Billed', 'updated_by' => $uid, 'updated_date' => now()]);
                    \App\Models\Oecb\OecbEntry::postChargeToLedger($oecbEntry, $chargeAmount, $uid);
                }
            }
        }

        $this->dispatch('toast', type: 'success', message: 'Disposition updated.');
        if ($this->showTreatmentForm) {
            $this->showTreatmentForm = false;
            $this->reset(['treatingId']);
        }
        $this->showDispositionModal = false;
        $this->reset(['editingId']);
    }

    public function closeModal(): void
    {
        $this->showEditModal = false;
        $this->showDispositionModal = false;
        $this->reset(['editingId']);
    }

    public function render()
    {
        $statusFilter = $this->showCompleted ? 'Completed' : 'Active';

        $admissions = ErAdmission::with(['patient', 'bed.zone', 'attendingPhysician', 'triageRecord.acuityLevel'])
            ->where('status', $statusFilter)
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->when($this->search, fn ($q) =>
                $q->whereHas('patient', fn($p) =>
                    $p->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                )->orWhere('admission_code', 'like', '%' . $this->search . '%')
            )
            ->when($this->showCompleted, fn ($q) => $q->orderBy('disposition_datetime', 'desc'), fn ($q) => $q->orderBy($this->sortField, $this->sortDirection))
            ->paginate($this->perPage);

        $activeAdmission = null;
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
        $erOrSchedules = collect();
        $orRooms = collect();
        $orDoctors = collect();

        if ($this->showTreatmentForm && $this->treatingId !== null) {
            $activeAdmission = ErAdmission::with([
                'patient.gender', 'patient.nationality',
                'bed.zone', 'attendingPhysician',
                'triageRecord.acuityLevel', 'triageRecord.mobilityStatus',
                'labRequests.items', 'csrRequests.items', 'radiologyRequests.items', 'pharmacyRequests.items', 'procedureRequests.items',
            ])->find($this->treatingId);

            $labOrders = $activeAdmission->labRequests()->with('items.labTest', 'items.labPanel')
                ->orderBy('created_date', 'desc')->get();
            $csrOrders = $activeAdmission->csrRequests()->with('items.item')
                ->orderBy('created_date', 'desc')->get();
            $radiologyOrders = $activeAdmission->radiologyRequests()->with('items.examType')
                ->orderBy('created_date', 'desc')->get();
            $pharmacyOrders = $activeAdmission->pharmacyRequests()->with('items.drug')
                ->orderBy('created_date', 'desc')->get();
            $procedureOrders = $activeAdmission->procedureRequests()->with('items.procedure')
                ->orderBy('created_date', 'desc')->get();

            $erOrSchedules = OrSchedule::with(['orRoom', 'surgeon', 'anesthesiologist'])
                ->where('er_admission_id', $this->treatingId)
                ->orderBy('scheduled_date', 'desc')
                ->get();
        }

        $orRooms = OrRoom::query()
            ->where('tenant_id', $this->currentTenantId())
            ->where('status', 'Available')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $orDoctors = HealthCareProfessional::query()
            ->where('tenant_id', $this->currentTenantId())
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $labTests = LabTest::where('is_active', true)
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->orderBy('name')->get(['id', 'code', 'name', 'standard_charge']);

        $supplyItems = Item::where('is_active', true)
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->whereHas('batches', function ($query) {
                $query->where('quantity_on_hand', '>', 0)
                      ->where('expiry_date', '>=', now()->toDateString())
                      ->where('history', 1)
                      ->where('is_active', 1);
            })
            ->with('unit')
            ->orderBy('name')->get(['id', 'code', 'name', 'unit_cost']);

        $examTypes = RadiologyExamType::where('is_active', true)
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->with('modality')
            ->orderBy('name')->get(['id', 'code', 'name', 'standard_charge']);

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
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->with('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'standard_charge']);

        $beds = ErBed::where('status', 'Available')
            ->when($this->editingId, function ($q) {
                $currentBedId = ErAdmission::find($this->editingId)?->er_bed_id;
                if ($currentBedId) {
                    $q->orWhere('id', $currentBedId);
                }
            })
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->orderBy('name')->get();

        $physicians = HealthCareProfessional::when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->orderBy('full_name')->get();

        $corePresentations = OecbCorePresentation::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name')
            ->toArray();

        return view('livewire.er.er-admission-crud', compact('admissions', 'activeAdmission', 'labOrders', 'csrOrders', 'radiologyOrders', 'pharmacyOrders', 'procedureOrders', 'labTests', 'supplyItems', 'examTypes', 'drugs', 'procedures', 'beds', 'physicians', 'statusFilter', 'corePresentations', 'erOrSchedules', 'orRooms', 'orDoctors'));
    }
}
