<?php

namespace App\Livewire\Philhealth;

use App\Models\ER\ErAdmission;
use App\Models\Admission;
use App\Models\Opd\OpdConsultation;
use App\Models\Billing\BillingInvoice;
use App\Models\Billing\BillingInvoiceItem;
use Livewire\Component;

class ClinicalDataPullUp extends Component
{
    public ?int $patientId = null;
    public bool $showModal = true;
    public $erAdmissions;
    public $admissions;
    public $opdConsultations;

    public string $patientSearch = '';
    public $searchedPatients = [];

    public function mount(?int $patientId = null): void
    {
        if ($patientId) {
            $this->patientId = $patientId;
            $this->loadEncounters();
        }
    }

    #[\Livewire\Attributes\On('open-pull-up')]
    public function openPullUp(?int $patientId = null): void
    {
        if ($patientId) {
            $this->patientId = $patientId;
            $this->loadEncounters();
        }
        $this->showModal = true;
    }

    #[\Livewire\Attributes\On('patient-selected')]
    public function onPatientSelected(int $patientId): void
    {
        $this->patientId = $patientId;
        $this->loadEncounters();
        $this->showModal = true;
    }

    public function updatedPatientSearch(): void
    {
        if (strlen(trim($this->patientSearch)) >= 2) {
            $this->searchedPatients = \App\Models\Patient::where('history', 1)
                ->where(function ($q) {
                    $q->where('last_name', 'like', '%' . $this->patientSearch . '%')
                      ->orWhere('first_name', 'like', '%' . $this->patientSearch . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->patientSearch . '%');
                })
                ->limit(8)
                ->get();
        } else {
            $this->searchedPatients = [];
        }
    }

    public function selectPatient(int $id): void
    {
        $this->patientId = $id;
        $this->patientSearch = '';
        $this->searchedPatients = [];
        $this->loadEncounters();
        $this->showModal = true;
        $this->dispatch('patient-selected', patientId: $id);
    }

    public function loadEncounters(): void
    {
        if (!$this->patientId) return;

        $this->erAdmissions = ErAdmission::where('patient_id', $this->patientId)
            ->with('oecbEntries')
            ->orderBy('admission_datetime', 'desc')
            ->get();

        $this->admissions = Admission::where('patient_id', $this->patientId)
            ->orderBy('admission_date', 'desc')
            ->get();

        $this->opdConsultations = OpdConsultation::where('patient_id', $this->patientId)
            ->orderBy('consultation_datetime', 'desc')
            ->get();
    }

    public function pullFromEr(int $erAdmissionId): void
    {
        $er = ErAdmission::with(['oecbEntries', 'triageRecord', 'attendingPhysician'])->find($erAdmissionId);
        if (!$er) return;

        $triage = $er->triageRecord;
        $bp = '';
        if ($triage?->vital_bp_systolic && $triage?->vital_bp_diastolic) {
            $bp = "{$triage->vital_bp_systolic}/{$triage->vital_bp_diastolic}";
        } elseif ($triage?->vital_bp_systolic) {
            $bp = (string) $triage->vital_bp_systolic;
        }

        $outcome = match ($er->disposition) {
            'Discharged Home' => 'Recovered',
            'Admitted to Ward', 'Admitted to ICU' => 'Improved',
            'Expired' => 'Died',
            'AMA' => 'HAMA',
            'Transferred' => 'Transferred',
            default => 'Recovered',
        };

        $data = [
            'date_admitted' => $er->admission_datetime?->format('Y-m-d'),
            'time_admitted' => $er->admission_datetime?->format('H:i'),
            'date_discharged' => $er->disposition_datetime?->format('Y-m-d'),
            'time_discharged' => $er->disposition_datetime?->format('H:i'),
            'admitting_diagnosis' => $er->initial_diagnosis ?? '',
            'er_admission_id' => $er->id,
            'admission_id' => null,
            'attending_physician_id' => $er->attending_physician_id,
            'encounter_type' => 'emergency',
            'claim_type' => $er->is_oecb ? 'OECB' : 'ACR',
            'blood_pressure' => $bp,
            'heart_rate' => $triage?->vital_heart_rate ? (string) $triage->vital_heart_rate : '',
            'respiratory_rate' => $triage?->vital_respiratory_rate ? (string) $triage->vital_respiratory_rate : '',
            'temperature' => $triage?->vital_temperature ? (string) $triage->vital_temperature : '',
            'chief_complaint' => $triage?->chief_complaint ?? '',
            'course_in_ward' => $er->treatment_notes ?? '',
            'outcome' => $outcome,
        ];

        if ($er->is_oecb && $er->oecbEntries->isNotEmpty()) {
            $data['chief_complaint'] = $er->oecbEntries->pluck('core_presentation')->implode('; ');
        }

        $this->dispatch('data-pulled', data: $data);
        $this->dispatch('toast', type: 'success', message: 'ER encounter data pulled into Claim Form.');
        $this->dispatch('switch-claim-tab', tab: 'form');
    }

    public function pullFromWard(int $admissionId): void
    {
        $adm = Admission::with(['attendingPhysician', 'nursingVitals' => fn($q) => $q->orderBy('recorded_at', 'desc')->take(1), 'erAdmission.triageRecord'])->find($admissionId);
        if (!$adm) return;

        $latestVital = $adm->nursingVitals->first();
        $erTriage = $adm->erAdmission?->triageRecord;

        $bp = '';
        if ($latestVital?->vital_bp_systolic && $latestVital?->vital_bp_diastolic) {
            $bp = "{$latestVital->vital_bp_systolic}/{$latestVital->vital_bp_diastolic}";
        } elseif ($erTriage?->vital_bp_systolic && $erTriage?->vital_bp_diastolic) {
            $bp = "{$erTriage->vital_bp_systolic}/{$erTriage->vital_bp_diastolic}";
        }

        $hr = $latestVital?->vital_heart_rate ?? $erTriage?->vital_heart_rate;
        $rr = $latestVital?->vital_respiratory_rate ?? $erTriage?->vital_respiratory_rate;
        $temp = $latestVital?->vital_temperature ?? $erTriage?->vital_temperature;

        $outcome = match ($adm->disposition) {
            'Discharged', 'Discharged Home' => 'Recovered',
            'Improved' => 'Improved',
            'Expired', 'Died' => 'Died',
            'AMA', 'HAMA' => 'HAMA',
            'Transferred' => 'Transferred',
            default => 'Recovered',
        };

        $data = [
            'date_admitted' => $adm->admission_date?->format('Y-m-d'),
            'time_admitted' => $adm->admission_date?->format('H:i'),
            'date_discharged' => $adm->discharge_date?->format('Y-m-d'),
            'time_discharged' => $adm->discharge_date?->format('H:i'),
            'admitting_diagnosis' => $adm->admitting_diagnosis ?? $adm->assessment ?? '',
            'er_admission_id' => $adm->er_admission_id,
            'admission_id' => $adm->id,
            'attending_physician_id' => $adm->attending_physician_id,
            'encounter_type' => 'admission',
            'claim_type' => 'ACR',
            'blood_pressure' => $bp,
            'heart_rate' => $hr ? (string) $hr : '',
            'respiratory_rate' => $rr ? (string) $rr : '',
            'temperature' => $temp ? (string) $temp : '',
            'history_of_present_illness' => $adm->history_of_present_illness ?? '',
            'physical_exam_findings' => $adm->physical_exam_findings ?? '',
            'course_in_ward' => $adm->treatment_notes ?? $adm->notes ?? '',
            'outcome' => $outcome,
        ];

        $this->dispatch('data-pulled', data: $data);
        $this->dispatch('toast', type: 'success', message: 'Ward admission data pulled into Claim Form.');
        $this->dispatch('switch-claim-tab', tab: 'form');
    }

    public function pullFromOpd(int $opdId): void
    {
        $opd = OpdConsultation::with('attendingPhysician')->find($opdId);
        if (!$opd) return;

        $bp = '';
        if ($opd->vital_bp_systolic && $opd->vital_bp_diastolic) {
            $bp = "{$opd->vital_bp_systolic}/{$opd->vital_bp_diastolic}";
        } elseif ($opd->vital_bp_systolic) {
            $bp = (string) $opd->vital_bp_systolic;
        }

        $data = [
            'date_admitted' => $opd->consultation_datetime?->format('Y-m-d'),
            'time_admitted' => $opd->consultation_datetime?->format('H:i'),
            'date_discharged' => null,
            'time_discharged' => null,
            'admitting_diagnosis' => $opd->assessment ?? $opd->chief_complaint ?? '',
            'er_admission_id' => null,
            'admission_id' => null,
            'attending_physician_id' => $opd->attending_physician_id,
            'encounter_type' => 'outpatient',
            'claim_type' => 'ACR',
            'blood_pressure' => $bp,
            'heart_rate' => $opd->vital_heart_rate ? (string) $opd->vital_heart_rate : '',
            'respiratory_rate' => $opd->vital_respiratory_rate ? (string) $opd->vital_respiratory_rate : '',
            'temperature' => $opd->vital_temperature ? (string) $opd->vital_temperature : '',
            'chief_complaint' => $opd->chief_complaint ?? '',
            'history_of_present_illness' => $opd->history_of_present_illness ?? '',
            'physical_exam_findings' => $opd->physical_exam_findings ?? '',
            'course_in_ward' => $opd->treatment_plan ?? '',
            'outcome' => 'Recovered',
        ];

        $this->dispatch('data-pulled', data: $data);
        $this->dispatch('toast', type: 'success', message: 'OPD consultation data pulled into Claim Form.');
        $this->dispatch('switch-claim-tab', tab: 'form');
    }

    public function importFromBilling(int $erAdmissionId = null, int $admissionId = null, int $opdConsultationId = null): void
    {
        $invoiceQuery = BillingInvoice::where('patient_id', $this->patientId)->with('items');

        if ($erAdmissionId) {
            $invoiceQuery->where('er_admission_id', $erAdmissionId);
        } elseif ($admissionId) {
            $invoiceQuery->where('admission_id', $admissionId);
        } elseif ($opdConsultationId) {
            $invoiceQuery->where('opd_consultation_id', $opdConsultationId);
        }

        $invoice = $invoiceQuery->first();

        if (!$invoice) {
            $this->dispatch('toast', type: 'error', message: 'No billing invoice found for this encounter.');
            return;
        }

        $items = [];
        foreach ($invoice->items as $bi) {
            $type = match ($bi->source_type ?? '') {
                'Admission', 'OECB' => 'RoomBoard',
                'Pharmacy' => 'Drug/Medicine',
                'Lab' => 'Laboratory',
                'Radiology' => 'Radiology',
                'Procedure' => 'Procedure',
                'CSR' => 'Supply',
                'DoctorFee', 'PF' => 'ProfessionalFee',
                default => 'Others',
            };

            $items[] = [
                'item_type' => $type,
                'item_code' => $bi->source_request_code ?? '',
                'item_name' => $bi->description ?? $bi->source_type ?? 'Item',
                'quantity' => (float) $bi->quantity,
                'unit_price' => (float) $bi->unit_price,
                'actual_amount' => (float) $bi->total_price,
                'claimed_amount' => (float) $bi->total_price,
            ];
        }

        if (empty($items)) {
            $this->dispatch('toast', type: 'info', message: 'No items found in billing invoice.');
            return;
        }

        $this->dispatch('items-imported', items: $items);
        $this->dispatch('toast', type: 'success', message: count($items) . ' items imported from billing.');
        $this->dispatch('switch-claim-tab', tab: 'items');
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.philhealth.clinical-data-pull-up');
    }
}
