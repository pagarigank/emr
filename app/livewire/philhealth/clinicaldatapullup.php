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
    public bool $showModal = false;
    public $erAdmissions;
    public $admissions;
    public $opdConsultations;

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
        if (!$patientId) return;
        $this->patientId = $patientId;
        $this->loadEncounters();
        $this->showModal = true;
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
        $er = ErAdmission::with('oecbEntries')->find($erAdmissionId);
        if (!$er) return;

        $data = [
            'date_admitted' => $er->admission_datetime?->format('Y-m-d'),
            'time_admitted' => $er->admission_datetime?->format('H:i'),
            'date_discharged' => $er->disposition_datetime?->format('Y-m-d'),
            'time_discharged' => $er->disposition_datetime?->format('H:i'),
            'admitting_diagnosis' => $er->initial_diagnosis ?? '',
            'er_admission_id' => $er->id,
            'admission_id' => null,
            'encounter_type' => 'emergency',
            'claim_type' => $er->is_oecb ? 'OECB' : 'ACR',
        ];

        if ($er->is_oecb && $er->oecbEntries->isNotEmpty()) {
            $data['chief_complaint'] = $er->oecbEntries->pluck('core_presentation')->implode('; ');
        }

        $this->dispatch('data-pulled', data: $data);
        $this->dispatch('toast', type: 'success', message: 'ER encounter data pulled up.');
        $this->showModal = false;
    }

    public function pullFromWard(int $admissionId): void
    {
        $adm = Admission::find($admissionId);
        if (!$adm) return;

        $data = [
            'date_admitted' => $adm->admission_date?->format('Y-m-d'),
            'time_admitted' => $adm->admission_date?->format('H:i'),
            'date_discharged' => $adm->discharge_date?->format('Y-m-d'),
            'time_discharged' => $adm->discharge_date?->format('H:i'),
            'admitting_diagnosis' => $adm->admitting_diagnosis ?? $adm->assessment ?? '',
            'er_admission_id' => $adm->er_admission_id,
            'admission_id' => $adm->id,
            'encounter_type' => 'admission',
            'claim_type' => 'ACR',
        ];

        $this->dispatch('data-pulled', data: $data);
        $this->dispatch('toast', type: 'success', message: 'Ward admission data pulled up.');
        $this->showModal = false;
    }

    public function pullFromOpd(int $opdId): void
    {
        $opd = OpdConsultation::find($opdId);
        if (!$opd) return;

        $data = [
            'date_admitted' => $opd->consultation_datetime?->format('Y-m-d'),
            'time_admitted' => $opd->consultation_datetime?->format('H:i'),
            'date_discharged' => null,
            'time_discharged' => null,
            'admitting_diagnosis' => $opd->assessment ?? $opd->chief_complaint ?? '',
            'er_admission_id' => null,
            'admission_id' => null,
            'encounter_type' => 'outpatient',
            'claim_type' => 'ACR',
        ];

        $this->dispatch('data-pulled', data: $data);
        $this->dispatch('toast', type: 'success', message: 'OPD consultation data pulled up.');
        $this->showModal = false;
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
            $type = 'Others';
            if (in_array($bi->source_type ?? '', ['Admission', 'OECB'])) $type = 'RoomBoard';
            elseif (($bi->source_type ?? '') === 'Pharmacy') $type = 'Drug/Medicine';
            elseif (in_array($bi->source_type ?? '', ['Lab', 'Radiology'])) $type = 'Others';
            elseif (($bi->source_type ?? '') === 'Procedure') $type = 'Procedure';
            elseif (($bi->source_type ?? '') === 'CSR') $type = 'Supply';

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
