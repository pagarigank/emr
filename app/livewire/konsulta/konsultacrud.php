<?php

namespace App\Livewire\Konsulta;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\PhilhealthMember;
use App\Models\Icd10Code;
use App\Models\Konsulta\KonsultaEnrollment;
use App\Models\Konsulta\KonsultaConsultation;
use App\Models\Konsulta\KonsultaPrescription;
use App\Models\Konsulta\KonsultaLabReferral;
use Livewire\Component;
use Livewire\WithPagination;

class KonsultaCrud extends Component
{
    use WithPagination;

    public int $activeTab = 1;
    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;

    // Enrollment form
    public bool $showEnrollmentModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $patient_search = '';
    public ?int $patient_id = null, $philhealth_member_id = null, $primary_care_provider_id = null;
    public string $enrollment_date = '', $expiry_date = '', $status = 'Active', $risk_classification = '';

    // Consultation form
    public bool $showConsultationModal = false;
    public ?int $consultEnrollmentId = null, $consultPatientId = null;
    public string $consultPatientSearch = '';
    public ?int $consultProviderId = null, $consultIcd10Id = null;
    public string $consultDate = '', $chiefComplaint = '', $diagnosis = '', $assessment = '', $managementPlan = '', $consultNotes = '';
    public string $bp = '', $temperature = '', $heartRate = '', $respiratoryRate = '', $weight = '', $height = '';
    public bool $isFpe = false;
    public ?int $viewingConsultationId = null;
    public bool $showConsultationView = false;

    // Prescription form
    public bool $showRxModal = false;
    public ?int $rxConsultationId = null, $rxEnrollmentId = null, $rxPatientId = null;
    public string $medicineName = '', $dosage = '', $frequency = '', $duration = '', $rxStatus = 'Prescribed';
    public int $rxQuantity = 0;
    public bool $isGamotCovered = false;

    // Lab Referral form
    public bool $showLabModal = false;
    public ?int $labConsultationId = null, $labEnrollmentId = null, $labPatientId = null;
    public string $testType = '', $labReferralDate = '', $labStatus = 'Pending', $labNotes = '';

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function switchTab(int $tab): void
    {
        $this->activeTab = $tab;
        $this->search = '';
        $this->resetPage();
    }

    // ── Code Generators ────────────────────────────────────────────────────────

    private function generateEnrollmentCode(int $tid): string
    {
        $last = KonsultaEnrollment::where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->enrollment_code, 4)) + 1 : 1;
        return 'KON-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function generateConsultationCode(int $tid): string
    {
        $last = KonsultaConsultation::where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->consultation_code, 4)) + 1 : 1;
        return 'KNC-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ── Patient Search ─────────────────────────────────────────────────────────

    public function updatedPatientSearch(): void
    {
        $this->patient_id = null;
        $this->philhealth_member_id = null;
    }

    public function selectPatient(int $id): void
    {
        $patient = Patient::find($id);
        $this->patient_id = $id;
        $this->patient_search = $patient?->full_name ?? '';
        $this->philhealth_member_id = $patient?->philhealth_member_id;
    }

    public function selectConsultPatient(int $id): void
    {
        $patient = Patient::find($id);
        $this->consultPatientId = $id;
        $this->consultPatientSearch = $patient?->full_name ?? '';
    }

    // ── Enrollment CRUD ────────────────────────────────────────────────────────

    public function openCreateEnrollment(): void
    {
        $this->reset(['editingId', 'patient_id', 'patient_search', 'philhealth_member_id', 'primary_care_provider_id', 'enrollment_date', 'expiry_date', 'status', 'risk_classification']);
        $this->status = 'Active';
        $this->enrollment_date = now()->format('Y-m-d');
        $this->isEditing = false;
        $this->showEnrollmentModal = true;
    }

    public function editEnrollment(int $id): void
    {
        $e = KonsultaEnrollment::findOrFail($id);
        $this->editingId = $e->id;
        $this->patient_id = $e->patient_id;
        $this->patient_search = $e->patient?->full_name ?? '';
        $this->philhealth_member_id = $e->philhealth_member_id;
        $this->primary_care_provider_id = $e->primary_care_provider_id;
        $this->enrollment_date = $e->enrollment_date?->format('Y-m-d') ?? '';
        $this->expiry_date = $e->expiry_date?->format('Y-m-d') ?? '';
        $this->status = $e->status;
        $this->risk_classification = $e->risk_classification ?? '';
        $this->isEditing = true;
        $this->showEnrollmentModal = true;
    }

    public function saveEnrollment(): void
    {
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();

        $this->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'enrollment_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'primary_care_provider_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
        ]);

        $data = [
            'tenant_id' => $tid,
            'patient_id' => $this->patient_id,
            'philhealth_member_id' => $this->philhealth_member_id,
            'primary_care_provider_id' => $this->primary_care_provider_id,
            'enrollment_date' => $this->enrollment_date,
            'expiry_date' => $this->expiry_date ?: null,
            'status' => $this->status,
            'risk_classification' => $this->risk_classification ?: null,
        ];

        if ($this->isEditing && $this->editingId) {
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            KonsultaEnrollment::where('id', $this->editingId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Enrollment updated.');
        } else {
            $data['enrollment_code'] = $this->generateEnrollmentCode($tid);
            $data['created_by'] = $uid;
            $data['created_date'] = now();
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            KonsultaEnrollment::create($data);
            $this->dispatch('toast', type: 'success', message: 'Patient enrolled in Konsulta successfully.');
        }

        $this->showEnrollmentModal = false;
    }

    // ── Consultation CRUD ──────────────────────────────────────────────────────

    public function openCreateConsultation(?int $enrollmentId = null): void
    {
        $this->reset(['consultEnrollmentId', 'consultPatientId', 'consultPatientSearch', 'consultProviderId', 'consultIcd10Id', 'consultDate', 'chiefComplaint', 'diagnosis', 'assessment', 'managementPlan', 'consultNotes', 'bp', 'temperature', 'heartRate', 'respiratoryRate', 'weight', 'height']);
        $this->isFpe = false;
        $this->consultEnrollmentId = $enrollmentId;
        if ($enrollmentId) {
            $enr = KonsultaEnrollment::find($enrollmentId);
            $this->consultPatientId = $enr?->patient_id;
        }
        $this->consultDate = now()->format('Y-m-d H:i');
        $this->showConsultationModal = true;
    }

    public function saveConsultation(): void
    {
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();

        $this->validate([
            'consultPatientId' => ['required', 'integer', 'exists:patients,id'],
            'consultDate' => ['required'],
        ]);

        $data = [
            'tenant_id' => $tid,
            'enrollment_id' => $this->consultEnrollmentId,
            'patient_id' => $this->consultPatientId,
            'provider_id' => $this->consultProviderId,
            'consultation_date' => $this->consultDate,
            'chief_complaint' => $this->chiefComplaint ?: null,
            'diagnosis' => $this->diagnosis ?: null,
            'icd10_code_id' => $this->consultIcd10Id,
            'assessment' => $this->assessment ?: null,
            'management_plan' => $this->managementPlan ?: null,
            'notes' => $this->consultNotes ?: null,
            'bp' => $this->bp ?: null,
            'temperature' => $this->temperature !== '' ? (float) $this->temperature : null,
            'heart_rate' => $this->heartRate !== '' ? (int) $this->heartRate : null,
            'respiratory_rate' => $this->respiratoryRate !== '' ? (int) $this->respiratoryRate : null,
            'weight' => $this->weight !== '' ? (float) $this->weight : null,
            'height' => $this->height !== '' ? (float) $this->height : null,
            'is_fpe' => $this->isFpe,
            'consultation_code' => $this->generateConsultationCode($tid),
            'created_by' => $uid,
            'created_date' => now(),
            'updated_by' => $uid,
            'updated_date' => now(),
        ];

        KonsultaConsultation::create($data);

        if ($this->isFpe && $this->consultEnrollmentId) {
            KonsultaEnrollment::where('id', $this->consultEnrollmentId)->update([
                'fpe_completed' => true,
                'fpe_completed_at' => now(),
                'updated_by' => $uid,
                'updated_date' => now(),
            ]);
        }

        $this->showConsultationModal = false;
        $this->dispatch('toast', type: 'success', message: 'Consultation recorded.');
    }

    public function viewConsultation(int $id): void
    {
        $this->viewingConsultationId = $id;
        $this->showConsultationView = true;
    }

    public function closeViewConsultation(): void
    {
        $this->viewingConsultationId = null;
        $this->showConsultationView = false;
    }

    // ── Prescription CRUD ──────────────────────────────────────────────────────

    public function openCreateRx(int $consultationId, ?int $enrollmentId, int $patientId): void
    {
        $this->reset(['medicineName', 'dosage', 'frequency', 'duration', 'rxQuantity', 'rxStatus']);
        $this->rxConsultationId = $consultationId;
        $this->rxEnrollmentId = $enrollmentId;
        $this->rxPatientId = $patientId;
        $this->isGamotCovered = false;
        $this->rxStatus = 'Prescribed';
        $this->showRxModal = true;
    }

    public function saveRx(): void
    {
        $this->validate([
            'medicineName' => ['required', 'string', 'max:200'],
            'rxQuantity' => ['required', 'integer', 'min:1'],
        ]);

        KonsultaPrescription::create([
            'tenant_id' => $this->currentTenantId(),
            'consultation_id' => $this->rxConsultationId,
            'enrollment_id' => $this->rxEnrollmentId,
            'patient_id' => $this->rxPatientId,
            'medicine_name' => $this->medicineName,
            'dosage' => $this->dosage ?: null,
            'frequency' => $this->frequency ?: null,
            'duration' => $this->duration ?: null,
            'quantity' => $this->rxQuantity,
            'is_gamot_covered' => $this->isGamotCovered,
            'status' => $this->rxStatus,
            'created_by' => $this->currentUserId(),
            'created_date' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->showRxModal = false;
        $this->dispatch('toast', type: 'success', message: 'Prescription added.');
    }

    // ── Lab Referral CRUD ──────────────────────────────────────────────────────

    public function openCreateLab(int $consultationId, ?int $enrollmentId, int $patientId): void
    {
        $this->reset(['testType', 'labNotes']);
        $this->labConsultationId = $consultationId;
        $this->labEnrollmentId = $enrollmentId;
        $this->labPatientId = $patientId;
        $this->labStatus = 'Pending';
        $this->labReferralDate = now()->format('Y-m-d');
        $this->showLabModal = true;
    }

    public function saveLab(): void
    {
        $this->validate([
            'testType' => ['required', 'string', 'max:200'],
        ]);

        KonsultaLabReferral::create([
            'tenant_id' => $this->currentTenantId(),
            'consultation_id' => $this->labConsultationId,
            'enrollment_id' => $this->labEnrollmentId,
            'patient_id' => $this->labPatientId,
            'test_type' => $this->testType,
            'referral_date' => $this->labReferralDate,
            'status' => $this->labStatus,
            'notes' => $this->labNotes ?: null,
            'created_by' => $this->currentUserId(),
            'created_date' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->showLabModal = false;
        $this->dispatch('toast', type: 'success', message: 'Lab referral added.');
    }

    // ── Delete (soft archive) ──────────────────────────────────────────────────

    public function archive(int $id, string $type): void
    {
        $uid = $this->currentUserId();
        $model = match ($type) {
            'enrollment' => KonsultaEnrollment::class,
            'consultation' => KonsultaConsultation::class,
            'prescription' => KonsultaPrescription::class,
            'lab' => KonsultaLabReferral::class,
            default => null,
        };
        if (!$model) return;

        $record = $model::findOrFail($id);
        $record->update([
            'history' => 0,
            'is_active' => 0,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: 'Record archived.');
    }

    // ── Summary Stats ──────────────────────────────────────────────────────────

    public function getStats(): array
    {
        $tid = $this->currentTenantId();
        return [
            'total_enrolled' => KonsultaEnrollment::where('tenant_id', $tid)->count(),
            'fpe_completed' => KonsultaEnrollment::where('tenant_id', $tid)->where('fpe_completed', true)->count(),
            'total_consults' => KonsultaConsultation::where('tenant_id', $tid)->count(),
            'total_rx' => KonsultaPrescription::where('tenant_id', $tid)->count(),
            'gamot_rx' => KonsultaPrescription::where('tenant_id', $tid)->where('is_gamot_covered', true)->count(),
            'pending_labs' => KonsultaLabReferral::where('tenant_id', $tid)->where('status', 'Pending')->count(),
        ];
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    public function render()
    {
        $tid = $this->currentTenantId();
        $search = $this->search;
        $stats = $this->getStats();

        $enrollments = collect();
        $consultations = collect();
        $prescriptions = collect();
        $labReferrals = collect();

        if ($this->activeTab === 1) {
            $enrollments = KonsultaEnrollment::with(['patient', 'primaryCareProvider'])
                ->where('tenant_id', $tid)
                ->when($search, fn ($q) => $q->whereHas('patient', fn ($q) => $q->where('last_name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('hospital_no', 'like', "%{$search}%"))
                    ->orWhere('enrollment_code', 'like', "%{$search}%"))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage, pageName: 'enrPage');
        } elseif ($this->activeTab === 2) {
            $consultations = KonsultaConsultation::with(['patient', 'provider', 'enrollment'])
                ->where('tenant_id', $tid)
                ->when($search, fn ($q) => $q->whereHas('patient', fn ($q) => $q->where('last_name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%"))
                    ->orWhere('consultation_code', 'like', "%{$search}%"))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage, pageName: 'consPage');
        } elseif ($this->activeTab === 3) {
            $prescriptions = KonsultaPrescription::with(['patient', 'consultation'])
                ->where('tenant_id', $tid)
                ->when($search, fn ($q) => $q->where('medicine_name', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn ($q) => $q->where('last_name', 'like', "%{$search}%")))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage, pageName: 'rxPage');
        } elseif ($this->activeTab === 4) {
            $labReferrals = KonsultaLabReferral::with(['patient', 'consultation'])
                ->where('tenant_id', $tid)
                ->when($search, fn ($q) => $q->where('test_type', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn ($q) => $q->where('last_name', 'like', "%{$search}%")))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage, pageName: 'labPage');
        }

        $providers = HealthCareProfessional::where('tenant_id', $tid)->where('history', 1)->orderBy('full_name')->get(['id', 'full_name']);
        $icd10Codes = Icd10Code::where('history', 1)->orderBy('code')->get(['id', 'code', 'description']);

        $viewingConsultation = null;
        if ($this->showConsultationView && $this->viewingConsultationId) {
            $viewingConsultation = KonsultaConsultation::with(['patient', 'provider', 'icd10Code', 'enrollment', 'prescriptions', 'labReferrals'])->find($this->viewingConsultationId);
        }

        return view('livewire.konsulta.konsulta-crud', compact(
            'stats', 'enrollments', 'consultations', 'prescriptions', 'labReferrals',
            'providers', 'icd10Codes', 'viewingConsultation'
        ));
    }
}
