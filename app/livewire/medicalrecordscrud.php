<?php

namespace App\Livewire;

use App\Models\Admission;
use App\Models\Billing\BillingInvoice;
use App\Models\Er\ErAdmission;
use App\Models\Er\ErTriageRecord;
use App\Models\LabRequest;
use App\Models\Ob\ObConsultation;
use App\Models\Opd\OpdConsultation;
use App\Models\Patient;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Radiology\RadiologyRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class MedicalRecordsCrud extends Component
{
    public string $patient_search = '';
    public ?int $selectedPatientId = null;
    public ?Patient $selectedPatient = null;
    public ?array $encounters = null;
    public ?string $selectedEncounterType = null;
    public ?int $selectedEncounterId = null;
    public ?array $detailData = null;
    public bool $showDetailModal = false;
    public string $activeTimelineFilter = 'all';

    public function render()
    {
        $patients = [];
        if (strlen($this->patient_search) >= 2) {
            $term = $this->patient_search;
            $patients = Patient::withoutGlobalScopes()
                ->where('history', 1)
                ->where(function ($q) use ($term) {
                    $q->where('last_name', 'like', "%{$term}%")
                      ->orWhere('first_name', 'like', "%{$term}%")
                      ->orWhere('patient_code', 'like', "%{$term}%");
                })
                ->orderBy('last_name')
                ->limit(10)
                ->get();
        }

        return view('livewire.medical-records-crud', compact('patients'));
    }

    public function selectPatient(int $id): void
    {
        $this->selectedPatientId = $id;
        $this->patient_search = '';
        $this->selectedPatient = Patient::withoutGlobalScopes()
            ->with(['gender', 'philhealthMember'])
            ->find($id);
        $this->loadEncounters();
    }

    public function clearPatient(): void
    {
        $this->selectedPatientId = null;
        $this->selectedPatient = null;
        $this->encounters = null;
        $this->detailData = null;
        $this->selectedEncounterType = null;
        $this->selectedEncounterId = null;
        $this->showDetailModal = false;
    }

    public function loadEncounters(): void
    {
        if (!$this->selectedPatientId) return;

        $pid = $this->selectedPatientId;
        $encounters = [];

        foreach (OpdConsultation::withoutGlobalScopes()
            ->where('patient_id', $pid)->where('history', 1)
            ->with(['opdClinic', 'attendingPhysician'])
            ->get() as $e) {
            $encounters[] = $this->formatEncounter('OPD', $e);
        }

        foreach (ErTriageRecord::withoutGlobalScopes()
            ->where('patient_id', $pid)->where('history', 1)
            ->with(['acuityLevel'])
            ->get() as $e) {
            $encounters[] = $this->formatEncounter('ER Triage', $e);
        }

        foreach (ErAdmission::withoutGlobalScopes()
            ->where('patient_id', $pid)->where('history', 1)
            ->with(['attendingPhysician', 'erBed', 'admission'])
            ->get() as $e) {
            $encounters[] = $this->formatEncounter('ER Treatment', $e);
        }

        foreach (Admission::withoutGlobalScopes()
            ->where('patient_id', $pid)->where('history', 1)
            ->with(['attendingPhysician', 'wardRoom', 'icuBed'])
            ->get() as $e) {
            $encounters[] = $this->formatEncounter('Admission', $e);
        }

        foreach (ObConsultation::withoutGlobalScopes()
            ->where('patient_id', $pid)->where('history', 1)
            ->with(['attendingPhysician', 'visitType'])
            ->get() as $e) {
            $encounters[] = $this->formatEncounter('OB', $e);
        }

        $sorted = collect($encounters)->sortByDesc('date')->values()->toArray();
        $this->encounters = $sorted;
    }

    private function formatEncounter(string $type, $e): array
    {
        $date = match (true) {
            $e instanceof OpdConsultation => $e->consultation_date ?? $e->created_date,
            $e instanceof ErTriageRecord => $e->triage_date ?? $e->created_date,
            $e instanceof ErAdmission => $e->admission_datetime ?? $e->created_date,
            $e instanceof Admission => $e->admission_date ?? $e->created_date,
            $e instanceof ObConsultation => $e->consultation_date ?? $e->created_date,
            default => $e->created_date,
        };

        $diagnosis = match (true) {
            $e instanceof OpdConsultation => $e->assessment ?? $e->chief_complaint ?? '',
            $e instanceof ErAdmission => $e->diagnosis ?? $e->chief_complaint ?? '',
            $e instanceof Admission => $e->admitting_diagnosis ?? '',
            $e instanceof ObConsultation => $e->assessment ?? $e->chief_complaint ?? '',
            default => '',
        };

        $doctorName = $e->attendingPhysician?->full_name ?? ($e->attendingPhysician?->full_name ?? '—');
        $code = $e->admission_code ?? $e->consultation_code ?? $e->er_triage_code ?? $e->admission_code ?? ('#' . $e->id);

        return [
            'type' => $type,
            'id' => $e->id,
            'code' => $code,
            'date' => $date ? Carbon::parse($date)->format('Y-m-d H:i') : '—',
            'raw_date' => $date ? Carbon::parse($date) : null,
            'diagnosis' => $diagnosis,
            'doctor' => $doctorName,
            'status' => $e->status ?? 'Completed',
            'color' => match ($type) {
                'OPD' => 'emerald',
                'ER Triage' => 'red',
                'ER Treatment' => 'rose',
                'Admission' => 'indigo',
                'OB' => 'purple',
                default => 'gray',
            },
        ];
    }

    public function viewEncounterDetail(string $type, int $id): void
    {
        $this->selectedEncounterType = $type;
        $this->selectedEncounterId = $id;

        $data = match ($type) {
            'OPD' => $this->loadOpdDetail($id),
            'ER Triage' => $this->loadErTriageDetail($id),
            'ER Treatment' => $this->loadErTreatmentDetail($id),
            'Admission' => $this->loadAdmissionDetail($id),
            'OB' => $this->loadObDetail($id),
            default => null,
        };

        $this->detailData = $data;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailData = null;
        $this->selectedEncounterType = null;
        $this->selectedEncounterId = null;
    }

    public function filterTimeline(string $filter): void
    {
        $this->activeTimelineFilter = $filter;
    }

    public function printCertificate(string $type): void
    {
        if (!$this->selectedPatientId || !$this->selectedEncounterId || !$this->detailData) {
            return;
        }
        $encounterType = $this->detailData['print_type'] ?? 'opd';
        $url = "/medical-records/certificate/{$type}/{$encounterType}/{$this->selectedEncounterId}/{$this->selectedPatientId}";
        $this->dispatch('open-url', url: $url);
    }

    // ── Detail Loaders ─────────────────────────────────────────────────────────

    private function loadOpdDetail(int $id): array
    {
        $e = OpdConsultation::withoutGlobalScopes()
            ->with(['patient', 'opdClinic', 'attendingPhysician'])
            ->find($id);
        if (!$e) return [];

        $labs = LabRequest::withoutGlobalScopes()
            ->where('opd_consultation_id', $id)->where('history', 1)
            ->with(['items.labTest'])->get();

        $pharmacy = PharmacyRequest::withoutGlobalScopes()
            ->where('opd_consultation_id', $id)->where('history', 1)
            ->with(['items.drug'])->get();

        $invoices = BillingInvoice::withoutGlobalScopes()
            ->where('opd_consultation_id', $id)->where('history', 1)->get();

        return [
            'encounter' => $e,
            'title' => 'OPD Consultation',
            'code' => $e->consultation_code ?? '#' . $e->id,
            'date' => $e->consultation_date?->format('Y-m-d H:i') ?? '—',
            'fields' => [
                'Clinic' => $e->opdClinic?->name ?? '—',
                'Attending Physician' => $e->attendingPhysician?->full_name ?? '—',
                'Chief Complaint' => $e->chief_complaint ?? '—',
                'HPI' => $e->history_of_present_illness ?? '—',
                'Physical Exam' => $e->physical_exam ?? '—',
                'Assessment' => $e->assessment ?? '—',
                'Treatment Plan' => $e->treatment_plan ?? '—',
                'Disposition' => $e->disposition ?? '—',
                'ICD-10 Code' => $e->icd_10_code ?? '—',
            ],
            'labs' => $labs,
            'pharmacy' => $pharmacy,
            'invoices' => $invoices,
            'print_type' => 'opd',
        ];
    }

    private function loadErTriageDetail(int $id): array
    {
        $e = ErTriageRecord::withoutGlobalScopes()
            ->with(['patient', 'acuityLevel', 'mobilityStatus'])
            ->find($id);
        if (!$e) return [];

        return [
            'encounter' => $e,
            'title' => 'ER Triage',
            'code' => $e->er_triage_code ?? '#' . $e->id,
            'date' => $e->triage_date?->format('Y-m-d H:i') ?? '—',
            'fields' => [
                'Chief Complaint' => $e->chief_complaint ?? '—',
                'ESI Level' => $e->acuityLevel?->name ?? ($e->esi_level ?? '—'),
                'Mobility Status' => $e->mobilityStatus?->name ?? '—',
                'BP' => $e->blood_pressure ?? '—',
                'HR' => $e->heart_rate ?? '—',
                'Temp' => $e->temperature ? $e->temperature . '°C' : '—',
                'RR' => $e->respiratory_rate ?? '—',
                'O2 Sat' => $e->oxygen_saturation ? $e->oxygen_saturation . '%' : '—',
                'Pain Scale' => $e->pain_scale ?? '—',
                'Triage Notes' => $e->triage_notes ?? '—',
            ],
            'print_type' => 'er_triage',
        ];
    }

    private function loadErTreatmentDetail(int $id): array
    {
        $e = ErAdmission::withoutGlobalScopes()
            ->with(['patient', 'attendingPhysician', 'erBed', 'admission', 'triageRecord', 'oecbEntries'])
            ->find($id);
        if (!$e) return [];

        $labs = LabRequest::withoutGlobalScopes()
            ->where('er_admission_id', $id)->where('history', 1)
            ->with(['items.labTest'])->get();

        $pharmacy = PharmacyRequest::withoutGlobalScopes()
            ->where('er_admission_id', $id)->where('history', 1)
            ->with(['items.drug'])->get();

        $invoices = BillingInvoice::withoutGlobalScopes()
            ->where('er_admission_id', $id)->where('history', 1)->get();

        return [
            'encounter' => $e,
            'title' => 'ER Treatment',
            'code' => $e->admission_code ?? '#' . $e->id,
            'date' => $e->admission_datetime?->format('Y-m-d H:i') ?? '—',
            'fields' => [
                'Chief Complaint' => $e->chief_complaint ?? '—',
                'Diagnosis' => $e->diagnosis ?? '—',
                'Attending Physician' => $e->attendingPhysician?->full_name ?? '—',
                'ER Bed' => $e->erBed?->name ?? '—',
                'Mode of Arrival' => $e->mode_of_arrival ?? '—',
                'Disposition' => $e->disposition ?? '—',
                'Disposition Date' => $e->disposition_datetime?->format('Y-m-d H:i') ?? '—',
                'Is OECB' => $e->is_oecb ? 'Yes' : 'No',
                'OECB Entries' => $e->is_oecb && $e->oecbEntries->isNotEmpty()
                    ? $e->oecbEntries->map(fn ($oe) => $oe->core_presentation . ' [' . $oe->oecb_package_code . '] — ₱' . number_format($oe->oecb_fixed_fee ?? 0, 2))->implode("\n")
                    : ($e->is_oecb ? '—' : 'N/A'),
                'Status' => $e->status ?? '—',
            ],
            'labs' => $labs,
            'pharmacy' => $pharmacy,
            'clinical_notes' => $e->clinical_notes ?? null,
            'invoices' => $invoices,
            'print_type' => 'er',
        ];
    }

    private function loadAdmissionDetail(int $id): array
    {
        $e = Admission::withoutGlobalScopes()
            ->with(['patient', 'attendingPhysician', 'admittingPhysician', 'wardRoom.ward', 'icuBed.ward', 'erAdmission'])
            ->find($id);
        if (!$e) return [];

        $labs = LabRequest::withoutGlobalScopes()
            ->where('admission_id', $id)->where('history', 1)
            ->with(['items.labTest'])->get();

        $pharmacy = PharmacyRequest::withoutGlobalScopes()
            ->where('admission_id', $id)->where('history', 1)
            ->with(['items.drug'])->get();

        $radiology = RadiologyRequest::withoutGlobalScopes()
            ->where('admission_id', $id)->where('history', 1)
            ->with(['items.examType'])->get();

        $invoices = BillingInvoice::withoutGlobalScopes()
            ->where('admission_id', $id)->where('history', 1)->get();

        $days = $e->admission_date && $e->discharge_date
            ? max(1, (int) ceil($e->admission_date->diffInDays($e->discharge_date)))
            : ($e->admission_date ? max(1, (int) ceil($e->admission_date->diffInDays(now()))) : '—');

        return [
            'encounter' => $e,
            'title' => 'Admission / Ward Confinement',
            'code' => $e->admission_code ?? '#' . $e->id,
            'date' => $e->admission_date?->format('Y-m-d H:i') ?? '—',
            'fields' => [
                'Admission Type' => $e->admission_type ?? '—',
                'Admission Source' => $e->admission_source ?? '—',
                'Attending Physician' => $e->attendingPhysician?->full_name ?? '—',
                'Admitting Physician' => $e->admittingPhysician?->full_name ?? '—',
                'Room' => $e->wardRoom?->name ?? $e->icuBed?->name ?? '—',
                'Ward / ICU' => $e->wardRoom?->ward?->name ?? $e->icuBed?->ward?->name ?? '—',
                'Admitting Diagnosis' => $e->admitting_diagnosis ?? '—',
                'Admission Date' => $e->admission_date?->format('Y-m-d H:i') ?? '—',
                'Discharge Date' => $e->discharge_date?->format('Y-m-d H:i') ?? '—',
                'Length of Stay' => $days . ' day(s)',
                'Status' => $e->status ?? '—',
                'Discharge Notes' => $e->discharge_notes ?? '—',
            ],
            'labs' => $labs,
            'pharmacy' => $pharmacy,
            'radiology' => $radiology,
            'invoices' => $invoices,
            'print_type' => 'admission',
        ];
    }

    private function loadObDetail(int $id): array
    {
        $e = ObConsultation::withoutGlobalScopes()
            ->with(['patient', 'attendingPhysician', 'visitType'])
            ->find($id);
        if (!$e) return [];

        return [
            'encounter' => $e,
            'title' => 'OB Consultation',
            'code' => $e->consultation_code ?? '#' . $e->id,
            'date' => $e->consultation_date?->format('Y-m-d H:i') ?? '—',
            'fields' => [
                'Visit Type' => $e->visitType?->name ?? '—',
                'Attending Physician' => $e->attendingPhysician?->full_name ?? '—',
                'Chief Complaint' => $e->chief_complaint ?? '—',
                'AOG' => $e->aog ?? '—',
                'LMP' => $e->lmp ? Carbon::parse($e->lmp)->format('Y-m-d') : '—',
                'EDC' => $e->edc ? Carbon::parse($e->edc)->format('Y-m-d') : '—',
                'Gravida' => $e->gravida ?? '—',
                'Para' => $e->para ?? '—',
                'Assessment' => $e->assessment ?? '—',
                'Treatment Plan' => $e->treatment_plan ?? '—',
                'Disposition' => $e->disposition ?? '—',
                'Status' => $e->status ?? '—',
            ],
            'print_type' => 'ob',
        ];
    }


    public function getFilteredEncountersProperty(): array
    {
        if (!$this->encounters) return [];
        if ($this->activeTimelineFilter === 'all') return $this->encounters;
        return collect($this->encounters)
            ->where('type', $this->activeTimelineFilter)
            ->values()
            ->toArray();
    }
}
