<?php

namespace App\Livewire\Er;

use App\Models\ER\ErTriageRecord;
use App\Models\ER\ErAcuityLevel;
use App\Models\ER\ErMobilityStatus;
use App\Models\Patient;
use App\Models\PhilhealthClaim;
use App\Livewire\Concerns\ChecksActiveEncounters;
use Livewire\Component;
use Livewire\WithPagination;

class ErTriageRecordCrud extends Component
{
    use WithPagination, ChecksActiveEncounters;

    public string $search        = '';
    public string $sortField     = 'triage_datetime';
    public string $sortDirection = 'desc';
    public int    $perPage       = 10;
    public bool $showModal = false, $showDeleteModal = false, $showTransferModal = false, $isEditing = false;
    public ?int $editingId = null;

    // Transfer fields
    public ?int $transfer_er_bed_id = null;
    public ?int $transfer_attending_physician_id = null;
    public string $transfer_arrival_mode = 'Walk-in';
    public string $transfer_initial_diagnosis = '';

    // Form fields
    public bool $is_new_patient = false;
    public string $patient_last_name = '';
    public string $patient_first_name = '';
    public string $patient_middle_name = '';
    public string $patient_dob = '';
    public ?int $patient_gender_id = null;
    public string $patient_philhealth_pin = ''; // kept for DB compatibility — set by boot hook if member linked
    public bool $is_philhealth_member = false;

    public ?int $patient_id = null;
    public ?int $er_acuity_level_id = null;
    public ?int $er_mobility_status_id = null;
    public string $triage_datetime = '';
    public string $chief_complaint = '';
    public ?float $vital_temperature = null;
    public ?int $vital_heart_rate = null;
    public ?int $vital_respiratory_rate = null;
    public ?int $vital_bp_systolic = null;
    public ?int $vital_bp_diastolic = null;
    public ?float $vital_o2_saturation = null;
    public ?int $vital_pain_score = null;
    public ?string $level_of_consciousness = null;
    public string $allergies = '';
    public string $notes = '';
    public bool $is_active = true;

    // Search for Patient dropdown
    public string $patientSearch = '';

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function isSuperAdmin(): bool
    {
        return (bool) request()->attributes->get('is_superadmin', false);
    }

    private function generatePatientCode(): string
    {
        $tid = $this->currentTenantId();
        $year = now()->format('Y');
        $last = Patient::withoutGlobalScope('current')
            ->where('tenant_id', $tid)
            ->where('patient_code', 'like', $year . '%')
            ->orderBy('id', 'desc')
            ->first();
        $next = $last ? ((int) substr($last->patient_code, 4)) + 1 : 1;
        return $year . str_pad($next, 10, '0', STR_PAD_LEFT);
    }

    protected function rules(): array
    {
        $rules = [
            'er_acuity_level_id' => ['required', 'exists:er_acuity_levels,id'],
            'er_mobility_status_id' => ['nullable', 'exists:er_mobility_statuses,id'],
            'triage_datetime' => ['required', 'date'],
            'chief_complaint' => ['required', 'string', 'max:1000'],
            'vital_temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'vital_heart_rate' => ['nullable', 'integer', 'min:0', 'max:300'],
            'vital_respiratory_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'vital_bp_systolic' => ['nullable', 'integer', 'min:0', 'max:300'],
            'vital_bp_diastolic' => ['nullable', 'integer', 'min:0', 'max:200'],
            'vital_o2_saturation' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vital_pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'level_of_consciousness' => ['nullable', 'string', 'max:50'],
            'allergies' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];

        if ($this->is_new_patient) {
            $rules['patient_last_name'] = ['required', 'string', 'max:100'];
            $rules['patient_first_name'] = ['required', 'string', 'max:100'];
            $rules['patient_middle_name'] = ['nullable', 'string', 'max:100'];
            $rules['patient_dob'] = ['required', 'date'];
            $rules['patient_gender_id'] = ['required', 'exists:genders,id'];
            // no PIN rule needed — membership is a toggle only
        } else {
            $rules['patient_id'] = ['required', 'exists:patients,id'];
        }

        return $rules;
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field)
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openCreate(): void 
    { 
        $this->resetForm(); 
        $this->triage_datetime = now()->format('Y-m-d\TH:i');
        $this->isEditing = false; 
        $this->is_philhealth_member = false;
        $this->showModal = true; 
    }

    public function nextStep(): void
    {
        if ($this->is_new_patient) {
            $this->validate([
                'patient_last_name' => ['required', 'string', 'max:100'],
                'patient_first_name' => ['required', 'string', 'max:100'],
                'patient_middle_name' => ['nullable', 'string', 'max:100'],
                'patient_dob' => ['required', 'date'],
                'patient_gender_id' => ['required', 'exists:genders,id'],
                // no PIN validation needed
            ]);
        } else {
            $this->validate([
                'patient_id' => ['required', 'exists:patients,id'],
            ], [
                'patient_id.required' => 'Please select a patient or toggle "New Patient".',
            ]);
        }

        $this->dispatch('go-to-step-2');
    }

    public function openEdit(int $id): void
    {
        $record = ErTriageRecord::findOrFail($id);
        $this->editingId = $record->id; 
        $this->patient_id = $record->patient_id;
        $this->er_acuity_level_id = $record->er_acuity_level_id;
        $this->er_mobility_status_id = $record->er_mobility_status_id;
        $this->triage_datetime = $record->triage_datetime->format('Y-m-d\TH:i');
        $this->chief_complaint = $record->chief_complaint;
        $this->vital_temperature = $record->vital_temperature;
        $this->vital_heart_rate = $record->vital_heart_rate;
        $this->vital_respiratory_rate = $record->vital_respiratory_rate;
        $this->vital_bp_systolic = $record->vital_bp_systolic;
        $this->vital_bp_diastolic = $record->vital_bp_diastolic;
        $this->vital_o2_saturation = $record->vital_o2_saturation;
        $this->vital_pain_score = $record->vital_pain_score;
        $this->level_of_consciousness = $record->level_of_consciousness;
        $this->allergies = $record->allergies ?? '';
        $this->notes = $record->notes ?? '';
        $this->is_active = $record->is_active; 
        $this->isEditing = true; 
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $patientId = $this->patient_id;

        if ($this->is_new_patient && !$this->isEditing) {
            // Register New Patient
            $code = $this->generatePatientCode();
            
            $newPatient = Patient::create([
                'tenant_id' => $this->currentTenantId(),
                'patient_code' => $code,
                'last_name' => $this->patient_last_name,
                'first_name' => $this->patient_first_name,
                'middle_name' => $this->patient_middle_name,
                'date_of_birth' => $this->patient_dob,
                'gender_id' => $this->patient_gender_id,
                'is_philhealth_member' => $this->is_philhealth_member,
                'philhealth_pin' => null, // PIN filled later from eClaims module
                'history' => 1,
                'created_by' => $this->currentUserId(),
                'created_date' => now(),
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);
            $patientId = $newPatient->id;

            if ($this->is_philhealth_member) {
                PhilhealthClaim::ensureDraftForPatient($newPatient, $this->currentTenantId(), $this->currentUserId());
            }
        } elseif (!$this->is_new_patient && $this->is_philhealth_member && $patientId) {
            // Existing patient — update PhilHealth flag and ensure draft claim
            Patient::where('id', $patientId)->update(['is_philhealth_member' => true]);
            $existingPatient = Patient::find($patientId);
            if ($existingPatient) {
                PhilhealthClaim::ensureDraftForPatient($existingPatient, $this->currentTenantId(), $this->currentUserId());
            }
        }

        if (!$this->isEditing) {
            $this->ensureNoActiveEncounter($patientId);
        }

        $triageData = [
            'er_acuity_level_id' => $this->er_acuity_level_id,
            'er_mobility_status_id' => $this->er_mobility_status_id,
            'triage_datetime' => $this->triage_datetime,
            'chief_complaint' => $this->chief_complaint,
            'vital_temperature' => $this->vital_temperature,
            'vital_heart_rate' => $this->vital_heart_rate,
            'vital_respiratory_rate' => $this->vital_respiratory_rate,
            'vital_bp_systolic' => $this->vital_bp_systolic,
            'vital_bp_diastolic' => $this->vital_bp_diastolic,
            'vital_o2_saturation' => $this->vital_o2_saturation,
            'vital_pain_score' => $this->vital_pain_score,
            'level_of_consciousness' => $this->level_of_consciousness,
            'allergies' => $this->allergies,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
        ];
        
        if ($this->isEditing) {
            $triageData['updated_by'] = $this->currentUserId();
            $triageData['updated_date'] = now();
            // Typically patient_id doesn't change on edit, but we update it just in case
            if (!$this->is_new_patient) {
                 $triageData['patient_id'] = $patientId;
            }

            ErTriageRecord::findOrFail($this->editingId)->update($triageData);
            $this->dispatch('toast', type: 'success', message: 'Triage record updated successfully.');
        } else {
            $triageData['tenant_id'] = $this->currentTenantId();
            $triageData['patient_id'] = $patientId;
            $triageData['history'] = 1;
            $triageData['created_by'] = $this->currentUserId();
            $triageData['created_date'] = now();

            ErTriageRecord::create($triageData);
            $this->dispatch('toast', type: 'success', message: 'Triage record created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        ErTriageRecord::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Triage record archived successfully.');
    }

    public function openTransfer(int $id): void
    {
        $this->editingId = $id;
        $this->transfer_er_bed_id = null;
        $this->transfer_attending_physician_id = null;
        $this->transfer_arrival_mode = 'Walk-in';
        $this->transfer_initial_diagnosis = '';
        $this->showTransferModal = true;
    }

    public function saveTransfer(): void
    {
        $validated = $this->validate([
            'transfer_er_bed_id' => ['nullable', 'exists:er_beds,id'],
            'transfer_attending_physician_id' => ['nullable', 'exists:health_care_professionals,id'],
            'transfer_arrival_mode' => ['nullable', 'string', 'max:50'],
            'transfer_initial_diagnosis' => ['nullable', 'string', 'max:1000'],
        ]);

        $triage = ErTriageRecord::findOrFail($this->editingId);

        // Create ER Admission
        $admCode = 'ERA-' . now()->format('Ymd') . '-' . random_int(1000, 9999);
        $erAdmission = \App\Models\Er\ErAdmission::create([
            'tenant_id' => $triage->tenant_id,
            'admission_code' => $admCode,
            'er_triage_record_id' => $triage->id,
            'patient_id' => $triage->patient_id,
            'er_bed_id' => $validated['transfer_er_bed_id'] ?: null,
            'attending_physician_id' => $validated['transfer_attending_physician_id'] ?: null,
            'arrival_mode' => $validated['transfer_arrival_mode'],
            'admission_datetime' => now(),
            'initial_diagnosis' => $validated['transfer_initial_diagnosis'],
            'status' => 'Active',
            'disposition' => 'Pending',
            'history' => 1,
            'created_by' => $this->currentUserId(),
            'created_date' => now(),
        ]);

        // Auto-link ER admission to patient's Draft PhilHealth claim
        $draftClaim = PhilhealthClaim::where('patient_id', $triage->patient_id)
            ->where('status', 'Draft')
            ->whereNull('er_admission_id')
            ->where('history', 1)
            ->first();
        if ($draftClaim) {
            $draftClaim->update([
                'er_admission_id' => $erAdmission->id,
                'encounter_type' => 'emergency',
                'date_admitted' => $erAdmission->admission_datetime,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);
        }

        // Occupy bed if selected
        if (!empty($validated['transfer_er_bed_id'])) {
            \App\Models\Er\ErBed::where('id', $validated['transfer_er_bed_id'])->update(['status' => 'Occupied']);
        }

        // Update triage status
        $triage->update([
            'status' => 'Transferred',
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Patient successfully transferred to ER Area.');
        $this->closeModal();
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->showTransferModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'patient_id', 'er_acuity_level_id', 'er_mobility_status_id',
            'triage_datetime', 'chief_complaint', 'vital_temperature', 'vital_heart_rate',
            'vital_respiratory_rate', 'vital_bp_systolic', 'vital_bp_diastolic',
            'vital_o2_saturation', 'vital_pain_score', 'level_of_consciousness',
            'allergies', 'notes', 'patientSearch',
            'is_new_patient', 'patient_last_name', 'patient_first_name', 'patient_middle_name',
            'patient_dob', 'patient_gender_id', 'is_philhealth_member',
            'transfer_er_bed_id', 'transfer_attending_physician_id', 'transfer_arrival_mode', 'transfer_initial_diagnosis'
        ]); 
        $this->is_active = true; 
        $this->resetValidation();
    }

    public function render()
    {
        $records = ErTriageRecord::with(['patient', 'acuityLevel'])
            ->where('status', 'Pending')
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->when($this->search, function ($q) {
                $q->whereHas('patient', function ($p) {
                    $p->where('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->search . '%');
                })->orWhere('chief_complaint', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $patients = [];
        if (strlen($this->patientSearch) >= 2) {
            $patients = Patient::when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
                ->where(function($q) {
                    $q->where('last_name', 'like', '%' . $this->patientSearch . '%')
                      ->orWhere('first_name', 'like', '%' . $this->patientSearch . '%')
                      ->orWhere('middle_name', 'like', '%' . $this->patientSearch . '%')
                      ->orWhere('maiden_name', 'like', '%' . $this->patientSearch . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->patientSearch . '%');
                })
                ->limit(20)
                ->get();
        } elseif ($this->patient_id) {
            $patients = Patient::where('id', $this->patient_id)->get();
        }

        $acuityLevels = ErAcuityLevel::orderBy('code')->get();
        $mobilityStatuses = ErMobilityStatus::orderBy('code')->get();
        $locOptions = ['Alert', 'Voice (Responsive to)', 'Pain (Responsive to)', 'Unresponsive'];
        $genders = \App\Models\Gender::orderBy('name')->get();

        $beds = \App\Models\Er\ErBed::where('status', 'Available')
            ->when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->orderBy('name')->get();

        $physicians = \App\Models\HealthCareProfessional::when(! $this->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $this->currentTenantId()))
            ->orderBy('full_name')->get();

        return view('livewire.er.er-triage-record-crud', compact('records', 'patients', 'acuityLevels', 'mobilityStatuses', 'beds', 'physicians', 'locOptions', 'genders'));
    }
}
